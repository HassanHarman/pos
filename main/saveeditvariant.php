<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_POST['id']) ? $_POST['id'] : '';
	$pid = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$name = isset($_POST['variant_name']) ? trim($_POST['variant_name']) : '';
	$cost = isset($_POST['cost']) ? $_POST['cost'] : 0;
	$price = isset($_POST['price']) ? $_POST['price'] : 0;
	$stock = isset($_POST['current_stock']) ? $_POST['current_stock'] : 0;
	$min = isset($_POST['min_stock_level']) ? $_POST['min_stock_level'] : 2;

	if ($id === '' || $pid === '' || $name === '') {
		header('location: products.php');
		exit();
	}

	$q = $db->prepare("UPDATE product_variants SET variant_name = :n, cost = :c, price = :p, current_stock = :s, min_stock_level = :m WHERE variant_id = :id");
	$q->execute(array(':n' => $name, ':c' => $cost, ':p' => $price, ':s' => $stock, ':m' => $min, ':id' => $id));

	header('location: variants.php?product_id=' . $pid);
	exit();
?>
