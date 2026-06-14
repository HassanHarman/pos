<?php
	require_once('auth.php');
	require_role(array('manager','owner'));
	include('../connect.php');
	$id = isset($_GET['id']) ? $_GET['id'] : '';
	if ($id === '') {
		header("location: sales_inventory.php");
		exit();
	}

	$line = $db->prepare("SELECT product_id, variant_id, qty FROM sales_order WHERE transaction_id = :id LIMIT 1");
	$line->execute(array(':id' => $id));
	$row = $line->fetch(PDO::FETCH_ASSOC);
	if ($row) {
		$productId = isset($row['product_id']) ? (int)$row['product_id'] : 0;
		$variantId = isset($row['variant_id']) ? (int)$row['variant_id'] : 0;
		$qty = isset($row['qty']) ? (float)$row['qty'] : 0;
		if ($qty > 0 && $productId > 0) {
			if ($variantId > 0) {
				$u = $db->prepare("UPDATE product_variants SET current_stock = current_stock + :q WHERE variant_id = :vid AND product_id = :pid");
				$u->execute(array(':q' => $qty, ':vid' => $variantId, ':pid' => $productId));
			} else {
				$u = $db->prepare("UPDATE products SET qty = qty + :q WHERE product_id = :pid");
				$u->execute(array(':q' => $qty, ':pid' => $productId));
			}
		}
	}

	$del = $db->prepare("DELETE FROM sales_order WHERE transaction_id = :id");
	$del->execute(array(':id' => $id));

	header("location: sales_inventory.php");
	exit();
?>