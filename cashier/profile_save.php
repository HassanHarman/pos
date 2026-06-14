<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;
	$current = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
	$new = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
	$confirm = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';

	if ($new === '' || $current === '') {
		header('location: profile.php?err=' . urlencode('Missing password'));
		exit();
	}
	if (strlen($new) < 6) {
		header('location: profile.php?err=' . urlencode('Password must be at least 6 characters'));
		exit();
	}
	if ($new !== $confirm) {
		header('location: profile.php?err=' . urlencode('Passwords do not match'));
		exit();
	}

	$q = $db->prepare("SELECT id, password, password_hash FROM user WHERE id = :id LIMIT 1");
	$q->execute(array(':id' => $userId));
	$u = $q->fetch(PDO::FETCH_ASSOC);
	if (!$u) {
		header('location: profile.php?err=' . urlencode('User not found'));
		exit();
	}

	$ok = false;
	if (!empty($u['password_hash'])) {
		$ok = password_verify($current, $u['password_hash']);
	} else {
		$ok = hash_equals((string)$u['password'], $current);
	}
	if (!$ok) {
		header('location: profile.php?err=' . urlencode('Current password is incorrect'));
		exit();
	}

	$hash = password_hash($new, PASSWORD_BCRYPT);
	$up = $db->prepare("UPDATE user SET password = :p, password_hash = :h WHERE id = :id");
	$up->execute(array(':p' => $new, ':h' => $hash, ':id' => $userId));

	header('location: profile.php?msg=' . urlencode('Password updated'));
	exit();
?>
