<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$poi_id = isset($_GET['poi_id']) ? $_GET['poi_id'] : '';
	$po_id = isset($_GET['po_id']) ? $_GET['po_id'] : '';
	if ($poi_id === '' || $po_id === '') {
		header('location: purchase_orders.php');
		exit();
	}

	$q = $db->prepare("DELETE FROM purchase_order_items WHERE poi_id = :id");
	$q->execute(array(':id' => $poi_id));

	header('location: po_view.php?po_id=' . $po_id);
	exit();
?>
