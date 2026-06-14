<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$invoice = isset($_GET['invoice']) ? (string)$_GET['invoice'] : '';
	if ($invoice === '') {
		header('location: pos.php');
		exit();
	}

	$lines = $db->prepare("SELECT transaction_id, product_id, variant_id, qty FROM sales_order WHERE invoice = :inv");
	$lines->execute(array(':inv' => $invoice));
	while ($row = $lines->fetch(PDO::FETCH_ASSOC)) {
		$productId = isset($row['product_id']) ? (int)$row['product_id'] : 0;
		$variantId = isset($row['variant_id']) ? (int)$row['variant_id'] : 0;
		$qty = isset($row['qty']) ? (float)$row['qty'] : 0;
		if ($qty > 0 && $productId > 0) {
			if ($variantId > 0) {
				$u = $db->prepare("UPDATE product_variants SET current_stock=current_stock+? WHERE variant_id=?");
				$u->execute(array($qty, $variantId));
			} else {
				$u = $db->prepare("UPDATE products SET qty=qty+? WHERE product_id=?");
				$u->execute(array($qty, $productId));
			}
		}
	}

	$del = $db->prepare("DELETE FROM sales_order WHERE invoice = :inv");
	$del->execute(array(':inv' => $invoice));

	header('location: pos.php?invoice=' . urlencode($invoice) . '&msg=' . urlencode('Cart cleared'));
	exit();
?>
