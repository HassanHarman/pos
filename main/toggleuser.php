<?php
	require_once('auth.php');
	require_role(array('owner'));
	include('../connect.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	if ($id === '') {
		header("location: users.php");
		exit();
	}

	$q = $db->prepare("SELECT is_active FROM user WHERE id = :id");
	$q->execute(array(':id' => $id));
	$row = $q->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		header("location: users.php");
		exit();
	}

	$new = ((int)$row['is_active'] === 1) ? 0 : 1;
	$u = $db->prepare("UPDATE user SET is_active = :active WHERE id = :id");
	$u->execute(array(':active' => $new, ':id' => $id));

	header("location: users.php");
	exit();
?>
