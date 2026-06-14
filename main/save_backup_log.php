<?php
	require_once('auth.php');
	require_role(array('owner'));
	include('../connect.php');

	$file = isset($_POST['backup_file']) ? trim($_POST['backup_file']) : null;
	$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

	try {
		$q = $db->prepare("INSERT INTO backup_logs (backup_type, backup_file, status, notes) VALUES ('manual', :f, 'success', :n)");
		$q->execute(array(':f' => $file, ':n' => $notes));
		header('location: backup_export.php?msg=' . urlencode('Backup log added'));
		exit();
	} catch (Exception $e) {
		header('location: backup_export.php?err=' . urlencode('Failed to add backup log'));
		exit();
	}
?>
