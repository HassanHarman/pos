<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$pid = isset($_GET['product_id']) ? $_GET['product_id'] : '';
	if ($id === '' || $pid === '') {
		header('location: products.php');
		exit();
	}

	$q = $db->prepare("SELECT is_active FROM product_variants WHERE variant_id = :id");
	$q->execute(array(':id' => $id));
	$row = $q->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		header('location: variants.php?product_id=' . $pid);
		exit();
	}

	$new = ((int)$row['is_active'] === 1) ? 0 : 1;
	$u = $db->prepare("UPDATE product_variants SET is_active = :a WHERE variant_id = :id");
	$u->execute(array(':a' => $new, ':id' => $id));

	header('location: variants.php?product_id=' . $pid);
	exit();
?>
