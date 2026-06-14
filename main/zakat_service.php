<?php
function zakat_get_setting(PDO $db, $key, $default = '') {
    $q = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k");
    $q->execute(array(':k' => $key));
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['setting_value'])) {
        return $row['setting_value'];
    }
    return $default;
}

function zakat_upsert_setting(PDO $db, $key, $value) {
    $q = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $q->execute(array(':k' => $key, ':v' => $value));
}

function zakat_get_cash_float_total(PDO $db) {
    $sql = "SELECT COALESCE(SUM(COALESCE(cf.closing_balance, cf.actual_cash, cf.expected_cash, cf.opening_balance)), 0) AS total
        FROM cash_float cf
        INNER JOIN (
            SELECT user_id, MAX(date) AS max_date
            FROM cash_float
            GROUP BY user_id
        ) latest ON latest.user_id = cf.user_id AND latest.max_date = cf.date";
    $stmt = $db->query($sql);
    return (float)$stmt->fetchColumn();
}

function zakat_get_financial_accounts_total(PDO $db, $type = null, $fundsOwner = 'business') {
    $sql = "SELECT COALESCE(SUM(balance), 0) FROM financial_accounts WHERE is_active = 1";
    $params = array();
    if ($type !== null && $type !== '') {
        $sql .= " AND account_type = :type";
        $params[':type'] = $type;
    }
    if ($fundsOwner !== null && $fundsOwner !== '') {
        $sql .= " AND funds_owner = :owner";
        $params[':owner'] = $fundsOwner;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (float)$stmt->fetchColumn();
}

function zakat_get_inventory_total(PDO $db) {
    $variantSql = "SELECT COALESCE(SUM(v.cost * v.current_stock), 0)
        FROM product_variants v
        INNER JOIN products p ON p.product_id = v.product_id
        WHERE v.is_active = 1
            AND (v.is_dead_stock = 0 OR v.is_dead_stock IS NULL)
            AND p.is_active = 1
            AND (p.is_dead_stock = 0 OR p.is_dead_stock IS NULL)";
    $variantTotal = (float)$db->query($variantSql)->fetchColumn();

    $productSql = "SELECT COALESCE(SUM(p.cost * p.qty), 0)
        FROM products p
        WHERE p.is_active = 1
            AND (p.is_dead_stock = 0 OR p.is_dead_stock IS NULL)
            AND NOT EXISTS (
                SELECT 1 FROM product_variants v
                WHERE v.product_id = p.product_id AND v.is_active = 1
            )";
    $productTotal = (float)$db->query($productSql)->fetchColumn();

    return $variantTotal + $productTotal;
}

function zakat_get_receivables_total(PDO $db) {
    $sql = "SELECT COALESCE(SUM(
            GREATEST(
                COALESCE(total_amount, CAST(amount AS DECIMAL(10,2))) - COALESCE(amount_paid, 0),
                0
            )
        ), 0)
        FROM sales
        WHERE type = 'credit'
            AND (debt_status = 'good' OR debt_status IS NULL)
            AND (due_date IS NULL OR LOWER(due_date) <> 'paid')";
    $stmt = $db->query($sql);
    return (float)$stmt->fetchColumn();
}

function zakat_get_liabilities_total(PDO $db, $asOfDate = null) {
    $asOf = $asOfDate ? $asOfDate : date('Y-m-d');
    $monthEnd = date('Y-m-t', strtotime($asOf));

    $liabilityStmt = $db->prepare("SELECT COALESCE(SUM(amount), 0)
        FROM liabilities
        WHERE status = 'open'
            AND is_long_term = 0
            AND (due_date IS NULL OR due_date <= :month_end)");
    $liabilityStmt->execute(array(':month_end' => $monthEnd));
    $liabilityTotal = (float)$liabilityStmt->fetchColumn();

    $poStmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0)
        FROM purchase_orders
        WHERE status = 'pending'
            AND (expected_delivery IS NULL OR expected_delivery <= :month_end)");
    $poStmt->execute(array(':month_end' => $monthEnd));
    $purchaseOrderTotal = (float)$poStmt->fetchColumn();

    return $liabilityTotal + $purchaseOrderTotal;
}

function calculateBusinessZakat(PDO $db, array $options = array()) {
    $asOfDate = isset($options['as_of_date']) ? $options['as_of_date'] : date('Y-m-d');

    $goldPrice = (float)zakat_get_setting($db, 'gold_price_per_gram_24k', '0');
    $agencyExcluded = (float)zakat_get_setting($db, 'agency_customer_funds_excluded', '0');

    $cashFloatTotal = zakat_get_cash_float_total($db);
    $cashAccountsTotal = zakat_get_financial_accounts_total($db, 'cash', 'business');
    $bankTotal = zakat_get_financial_accounts_total($db, 'bank', 'business');
    $walletTotal = zakat_get_financial_accounts_total($db, 'wallet', 'business');

    $cashTotal = $cashFloatTotal + $cashAccountsTotal - $agencyExcluded;
    if ($cashTotal < 0) {
        $cashTotal = 0;
    }

    $inventoryTotal = zakat_get_inventory_total($db);
    $receivablesTotal = zakat_get_receivables_total($db);

    $totalAssets = $cashTotal + $bankTotal + $walletTotal + $inventoryTotal + $receivablesTotal;
    if ($totalAssets < 0) {
        $totalAssets = 0;
    }

    $liabilitiesTotal = zakat_get_liabilities_total($db, $asOfDate);

    $netZakatPool = $totalAssets - $liabilitiesTotal;
    if ($netZakatPool < 0) {
        $netZakatPool = 0;
    }

    $nisabThreshold = 85 * $goldPrice;
    if ($nisabThreshold < 0) {
        $nisabThreshold = 0;
    }

    $zakatDue = ($netZakatPool >= $nisabThreshold) ? ($netZakatPool * 0.025) : 0;
    if ($zakatDue < 0) {
        $zakatDue = 0;
    }

    return array(
        'as_of_date' => $asOfDate,
        'gold_price_per_gram' => $goldPrice,
        'nisab_threshold' => $nisabThreshold,
        'cash_float_total' => $cashFloatTotal,
        'cash_accounts_total' => $cashAccountsTotal,
        'bank_total' => $bankTotal,
        'wallet_total' => $walletTotal,
        'agency_excluded' => $agencyExcluded,
        'cash_total' => $cashTotal,
        'inventory_total' => $inventoryTotal,
        'receivables_total' => $receivablesTotal,
        'total_assets' => $totalAssets,
        'liabilities_total' => $liabilitiesTotal,
        'net_zakat_pool' => $netZakatPool,
        'zakat_due' => $zakatDue
    );
}

function zakat_should_run_today(PDO $db, $today = null) {
    $todayDate = $today ? $today : date('Y-m-d');
    $anniversary = zakat_get_setting($db, 'zakat_anniversary_date', '');
    if ($anniversary === '' || $anniversary !== $todayDate) {
        return false;
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM zakat_runs WHERE run_date = :run_date");
    $stmt->execute(array(':run_date' => $todayDate));
    return ((int)$stmt->fetchColumn() === 0);
}

function zakat_record_run(PDO $db, array $result, $notes = null) {
    $stmt = $db->prepare("INSERT INTO zakat_runs
        (run_date, gold_price_per_gram, nisab_threshold, total_assets, total_liabilities, net_zakat_pool, zakat_due, calculation_notes)
        VALUES (:run_date, :gold_price, :nisab, :assets, :liabilities, :net_pool, :zakat_due, :notes)");
    $stmt->execute(array(
        ':run_date' => $result['as_of_date'],
        ':gold_price' => $result['gold_price_per_gram'],
        ':nisab' => $result['nisab_threshold'],
        ':assets' => $result['total_assets'],
        ':liabilities' => $result['liabilities_total'],
        ':net_pool' => $result['net_zakat_pool'],
        ':zakat_due' => $result['zakat_due'],
        ':notes' => $notes
    ));
}
?>
