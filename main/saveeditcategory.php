<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_POST['id']) ? $_POST['id'] : '';
	$name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
	if ($id === '' || $name === '') {
		header('location: categories.php');
		exit();
	}

	$q = $db->prepare("UPDATE categories SET category_name = :n WHERE category_id = :id");
	$q->execute(array(':n' => $name, ':id' => $id));

	header('location: categories.php');
	exit();
?>
