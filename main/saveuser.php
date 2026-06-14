<?php
	session_start();
	require_once('auth.php');
	require_role(array('owner'));
	include('../connect.php');

	$a = $_POST['username'];
	$b = $_POST['password'];
	$c = $_POST['name'];
	$d = $_POST['position'];

	$hash = password_hash($b, PASSWORD_BCRYPT);

	$sql = "INSERT INTO user (username,password,password_hash,name,position,is_active) VALUES (:a,:b,:h,:c,:d,1)";
	$q = $db->prepare($sql);
	$q->execute(array(':a'=>$a, ':b'=>$b, ':h'=>$hash, ':c'=>$c, ':d'=>$d));

	header("location: users.php");
	exit();
?>
