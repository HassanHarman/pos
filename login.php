<?php
	//Start session
	session_start();
	
	//Array to store validation errors
	$errmsg_arr = array();
	
	//Validation error flag
	$errflag = false;

	include('connect.php');
	
	//Sanitize the POST values
	$login = ($_POST['username']);
	$password = ($_POST['password']);
	
	//Input Validations
	if($login == '') {
		$errmsg_arr[] = 'Username missing';
		$errflag = true;
	}
	if($password == '') {
		$errmsg_arr[] = 'Password missing';
		$errflag = true;
	}
	
	//If there are input validations, redirect back to the login form
	if($errflag) {
		$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
		session_write_close();
		header("location: index.php");
		exit();
	}
	
	try {
		$q = $db->prepare("SELECT * FROM user WHERE username = :username LIMIT 1");
		$q->execute(array(':username' => $login));
		$member = $q->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		die("Query failed");
	}

	if (!$member) {
		header("location: index.php");
		exit();
	}

	if (isset($member['is_active']) && (int)$member['is_active'] === 0) {
		$errmsg_arr[] = 'Account is inactive';
		$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
		session_write_close();
		header("location: index.php");
		exit();
	}

	$authenticated = false;
	if (!empty($member['password_hash'])) {
		$authenticated = password_verify($password, $member['password_hash']);
	} else {
		$authenticated = hash_equals((string)$member['password'], (string)$password);
		if ($authenticated) {
			$hash = password_hash($password, PASSWORD_BCRYPT);
			try {
				$u = $db->prepare("UPDATE user SET password_hash = :hash WHERE id = :id");
				$u->execute(array(':hash' => $hash, ':id' => $member['id']));
			} catch (Exception $e) {
			}
		}
	}

	if (!$authenticated) {
		header("location: index.php");
		exit();
	}

	session_regenerate_id();
	$_SESSION['SESS_MEMBER_ID'] = $member['id'];
	$_SESSION['SESS_FIRST_NAME'] = $member['name'];
	$_SESSION['SESS_LAST_NAME'] = $member['position'];
	$_SESSION['SESS_ROLE'] = strtolower(trim((string)$member['position'])) === 'admin' ? 'owner' : strtolower(trim((string)$member['position']));
	session_write_close();
	if ($_SESSION['SESS_ROLE'] === 'manager') {
		header("location: manager/index.php");
		exit();
	}
	if ($_SESSION['SESS_ROLE'] === 'stock_manager') {
		header("location: stock/index.php");
		exit();
	}
	if ($_SESSION['SESS_ROLE'] === 'cashier') {
		header("location: cashier/index.php");
		exit();
	}
	header("location: main/index.php");
	exit();
?>