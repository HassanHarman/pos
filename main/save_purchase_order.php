<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po = isset($_POST['po_number']) ? trim($_POST['po_number']) : '';
	$supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';
	$expected = isset($_POST['expected_delivery']) && $_POST['expected_delivery'] !== '' ? $_POST['expected_delivery'] : null;
	$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
	$created_by = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

	if ($po === '' || $supplier_id === '') {
		header('location: purchase_orders.php');
		exit();
	}

	$q = $db->prepare("INSERT INTO purchase_orders (po_number, supplier_id, created_by, expected_delivery, status, notes) VALUES (:po, :sid, :by, :exp, 'pending', :n)");
	$q->execute(array(':po' => $po, ':sid' => $supplier_id, ':by' => $created_by, ':exp' => $expected, ':n' => $notes));

	$po_id = $db->lastInsertId();
	header('location: po_view.php?po_id=' . $po_id);
	exit();
?>
