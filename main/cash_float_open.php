<?php
	require_once('auth.php');
	require_role(array('owner'));
	include('../connect.php');

	$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
	$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
	$opening = isset($_POST['opening_balance']) ? (float)$_POST['opening_balance'] : 0;

	if ($user_id === '' || $opening < 0) {
		header('location: cash_float.php?err=' . urlencode('Invalid data'));
		exit();
	}

	try {
		$q = $db->prepare("INSERT INTO cash_float (user_id, date, opening_balance) VALUES (:uid, :d, :o)");
		$q->execute(array(':uid' => $user_id, ':d' => $date, ':o' => $opening));
		header('location: cash_float.php?user_id=' . urlencode($user_id) . '&msg=' . urlencode('Day opened'));
		exit();
	} catch (Exception $e) {
		header('location: cash_float.php?user_id=' . urlencode($user_id) . '&err=' . urlencode('Could not open day (maybe already opened)'));
		exit();
	}
?>
