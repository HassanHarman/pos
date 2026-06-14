<?php
	require_once('auth.php');
	require_role(array('owner'));
	include('../connect.php');

	function upsert_setting($db, $key, $value) {
		$q = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
		$q->execute(array(':k' => $key, ':v' => $value));
	}

	$store_name = isset($_POST['store_name']) ? trim($_POST['store_name']) : '';
	$receipt_line1 = isset($_POST['receipt_line1']) ? trim($_POST['receipt_line1']) : '';
	$receipt_line2 = isset($_POST['receipt_line2']) ? trim($_POST['receipt_line2']) : '';
	$vat_rate = isset($_POST['vat_rate']) ? trim($_POST['vat_rate']) : '0.18';

	upsert_setting($db, 'store_name', $store_name);
	upsert_setting($db, 'receipt_line1', $receipt_line1);
	upsert_setting($db, 'receipt_line2', $receipt_line2);
	upsert_setting($db, 'vat_rate', $vat_rate);

	header('location: settings.php?msg=' . urlencode('Settings saved'));
	exit();
?>
