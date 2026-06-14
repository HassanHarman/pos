-- Zakat module schema updates
-- Run this once to add Zakat-related tables/columns.

ALTER TABLE products
    ADD COLUMN is_dead_stock TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

ALTER TABLE product_variants
    ADD COLUMN is_dead_stock TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

ALTER TABLE sales
    ADD COLUMN debt_status ENUM('good','doubtful','bad') NOT NULL DEFAULT 'good' AFTER type;

CREATE TABLE IF NOT EXISTS financial_accounts (
    account_id INT(11) NOT NULL AUTO_INCREMENT,
    account_name VARCHAR(100) NOT NULL,
    account_type ENUM('cash','bank','wallet') NOT NULL DEFAULT 'bank',
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    funds_owner ENUM('business','customer') NOT NULL DEFAULT 'business',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    notes TEXT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (account_id),
    KEY idx_financial_accounts_type (account_type),
    KEY idx_financial_accounts_owner (funds_owner)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS liabilities (
    liability_id INT(11) NOT NULL AUTO_INCREMENT,
    liability_name VARCHAR(150) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    due_date DATE DEFAULT NULL,
    liability_type ENUM('payable','expense','loan','other') NOT NULL DEFAULT 'payable',
    is_long_term TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('open','settled') NOT NULL DEFAULT 'open',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (liability_id),
    KEY idx_liabilities_due (due_date),
    KEY idx_liabilities_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS zakat_runs (
    run_id INT(11) NOT NULL AUTO_INCREMENT,
    run_date DATE NOT NULL,
    gold_price_per_gram DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    nisab_threshold DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_assets DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_liabilities DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    net_zakat_pool DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    zakat_due DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    calculation_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (run_id),
    KEY idx_zakat_runs_date (run_date)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO settings (setting_key, setting_value) VALUES
    ('gold_price_per_gram_24k', '0'),
    ('zakat_anniversary_date', ''),
    ('agency_customer_funds_excluded', '0')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
