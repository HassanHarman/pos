<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
	if ($name === '') {
		header('location: categories.php');
		exit();
	}

	$q = $db->prepare("INSERT INTO categories (category_name, is_active) VALUES (:n, 1)");
	$q->execute(array(':n' => $name));

	header('location: categories.php');
	exit();
?>
