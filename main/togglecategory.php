<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	if ($id === '') {
		header('location: categories.php');
		exit();
	}

	$q = $db->prepare("SELECT is_active FROM categories WHERE category_id = :id");
	$q->execute(array(':id' => $id));
	$row = $q->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		header('location: categories.php');
		exit();
	}

	$new = ((int)$row['is_active'] === 1) ? 0 : 1;
	$u = $db->prepare("UPDATE categories SET is_active = :a WHERE category_id = :id");
	$u->execute(array(':a' => $new, ':id' => $id));

	header('location: categories.php');
	exit();
?>
