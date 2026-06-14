<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$invoice = isset($_GET['invoice']) ? (string)$_GET['invoice'] : '';
	if ($id === '' || $invoice === '') {
		header('location: pos.php?invoice=' . urlencode($invoice));
		exit();
	}

	$line = $db->prepare("SELECT product_id, variant_id, qty FROM sales_order WHERE transaction_id = :id AND invoice = :inv LIMIT 1");
	$line->execute(array(':id' => $id, ':inv' => $invoice));
	$row = $line->fetch(PDO::FETCH_ASSOC);
	if ($row) {
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

	$del = $db->prepare("DELETE FROM sales_order WHERE transaction_id = :id AND invoice = :inv");
	$del->execute(array(':id' => $id, ':inv' => $invoice));

	header('location: pos.php?invoice=' . urlencode($invoice));
	exit();
?>
