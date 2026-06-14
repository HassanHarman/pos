<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$pid = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$name = isset($_POST['variant_name']) ? trim($_POST['variant_name']) : '';
	$cost = isset($_POST['cost']) ? $_POST['cost'] : 0;
	$price = isset($_POST['price']) ? $_POST['price'] : 0;
	$stock = isset($_POST['current_stock']) ? $_POST['current_stock'] : 0;
	$min = isset($_POST['min_stock_level']) ? $_POST['min_stock_level'] : 2;

	if ($pid === '' || $name === '') {
		header('location: products.php');
		exit();
	}

	$q = $db->prepare("INSERT INTO product_variants (product_id, variant_name, price, cost, current_stock, min_stock_level, is_active) VALUES (:pid, :n, :p, :c, :s, :m, 1)");
	$q->execute(array(':pid' => $pid, ':n' => $name, ':p' => $price, ':c' => $cost, ':s' => $stock, ':m' => $min));

	header('location: variants.php?product_id=' . $pid);
	exit();
?>
