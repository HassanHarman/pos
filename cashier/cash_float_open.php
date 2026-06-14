<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;
	$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
	$opening = isset($_POST['opening_balance']) ? (float)$_POST['opening_balance'] : 0;
	$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

	if ($userId === null || $opening < 0) {
		header('location: cash_float.php?err=' . urlencode('Invalid data'));
		exit();
	}

	try {
		$q = $db->prepare("INSERT INTO cash_float (user_id, date, opening_balance, notes) VALUES (:uid, :d, :o, :n)");
		$q->execute(array(':uid' => $userId, ':d' => $date, ':o' => $opening, ':n' => $notes));
		header('location: cash_float.php?msg=' . urlencode('Day started'));
		exit();
	} catch (Exception $e) {
		header('location: cash_float.php?err=' . urlencode('Could not start day (maybe already started)'));
		exit();
	}
?>
