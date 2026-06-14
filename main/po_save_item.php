<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_POST['po_id']) ? $_POST['po_id'] : '';
	$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$variant_id = isset($_POST['variant_id']) ? $_POST['variant_id'] : '';
	$qty = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
	$unit = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;

	if ($po_id === '' || $product_id === '' || $qty <= 0 || $unit < 0) {
		header('location: po_view.php?po_id=' . $po_id);
		exit();
	}

	$total = $qty * $unit;
	$q = $db->prepare("INSERT INTO purchase_order_items (po_id, product_id, variant_id, quantity, unit_price, total_price) VALUES (:po, :pid, :vid, :q, :u, :t)");
	$q->execute(array(':po' => $po_id, ':pid' => $product_id, ':vid' => ($variant_id !== '' ? $variant_id : null), ':q' => $qty, ':u' => $unit, ':t' => $total));

	header('location: po_view.php?po_id=' . $po_id);
	exit();
?>
