<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_POST['id']) ? $_POST['id'] : '';
	$status = isset($_POST['delivery_status']) ? $_POST['delivery_status'] : '';

	$allowed = array('pending','dispatched','delivered','cancelled');
	if ($id === '' || !in_array($status, $allowed, true)) {
		header('location: deliveries.php');
		exit();
	}

	$q = $db->prepare("UPDATE sales SET delivery_status = :s WHERE transaction_id = :id AND sale_type = 'delivery'");
	$q->execute(array(':s' => $status, ':id' => $id));

	header('location: deliveries.php');
	exit();
?>
