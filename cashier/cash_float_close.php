<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

	$float_id = isset($_POST['float_id']) ? $_POST['float_id'] : '';
	$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
	$total_sales = isset($_POST['total_sales']) ? (float)$_POST['total_sales'] : 0;
	$expected_cash = isset($_POST['expected_cash']) ? (float)$_POST['expected_cash'] : 0;
	$actual_cash = isset($_POST['actual_cash']) ? (float)$_POST['actual_cash'] : 0;
	$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

	if ($float_id === '' || $userId === null) {
		header('location: cash_float.php?err=' . urlencode('Invalid data'));
		exit();
	}

	$difference = $actual_cash - $expected_cash;

	try {
		$q = $db->prepare("UPDATE cash_float SET closing_balance = :c, total_sales = :ts, expected_cash = :ec, actual_cash = :ac, difference = :df, notes = :n WHERE float_id = :id AND user_id = :uid AND date = :d");
		$q->execute(array(
			':c' => $actual_cash,
			':ts' => $total_sales,
			':ec' => $expected_cash,
			':ac' => $actual_cash,
			':df' => $difference,
			':n' => $notes,
			':id' => $float_id,
			':uid' => $userId,
			':d' => $date
		));
		header('location: cash_float.php?msg=' . urlencode('Day closed'));
		exit();
	} catch (Exception $e) {
		header('location: cash_float.php?err=' . urlencode('Could not close day'));
		exit();
	}
?>
