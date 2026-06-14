<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_GET['po_id']) ? $_GET['po_id'] : '';
	if ($po_id === '') {
		header('location: purchase_orders.php');
		exit();
	}

	$q = $db->prepare("UPDATE purchase_orders SET status='cancelled' WHERE po_id = :id AND status='pending'");
	$q->execute(array(':id' => $po_id));

	header('location: purchase_orders.php');
	exit();
?>
