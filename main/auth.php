<?php
	// prevent "headers already sent" issues if any page/include outputs whitespace
	if (!function_exists('pwa_head_snippet')) {
		function pwa_head_snippet() {
			return "\n    <!-- PWA + Favicon -->\n" .
				"    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"/pos/favicon/apple-touch-icon.png\">\n" .
				"    <link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/pos/favicon/favicon-32x32.png\">\n" .
				"    <link rel=\"icon\" type=\"image/png\" sizes=\"16x16\" href=\"/pos/favicon/favicon-16x16.png\">\n" .
				"    <link rel=\"shortcut icon\" href=\"/pos/favicon/favicon.ico\">\n" .
				"    <link rel=\"manifest\" href=\"/pos/manifest.webmanifest\">\n" .
				"    <meta name=\"theme-color\" content=\"#8936FF\">\n" .
				"    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\">\n" .
				"    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"default\">\n" .
				"    <meta name=\"apple-mobile-web-app-title\" content=\"Real Sisters POS\">\n" .
				"    <script defer src=\"/pos/pwa.js\"></script>\n";
		}
	}

	if (!function_exists('pwa_inject_head')) {
		function pwa_inject_head($buffer) {
			if (stripos($buffer, '</head>') === false) {
				return $buffer;
			}
			if (stripos($buffer, 'manifest.webmanifest') !== false) {
				return $buffer;
			}
			return preg_replace('~</head>~i', pwa_head_snippet() . "</head>", $buffer, 1);
		}
	}

	if (!defined('PWA_HEAD_BUFFERED')) {
		define('PWA_HEAD_BUFFERED', true);
		ob_start('pwa_inject_head');
	}

	//Start session
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	
	//Check whether the session variable SESS_MEMBER_ID is present or not
	if(!isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) == '')) {
		header("location: ../index.php");
		exit();
	}

	function normalize_role($role) {
		$role = strtolower(trim((string)$role));
		$role = str_replace(' ', '_', $role);
		if ($role === 'admin') {
			return 'owner';
		}
		if ($role === 'stock_manager') {
			return 'manager';
		}
		return $role;
	}

	function current_role() {
		if (isset($_SESSION['SESS_ROLE'])) {
			return normalize_role($_SESSION['SESS_ROLE']);
		}
		if (isset($_SESSION['SESS_LAST_NAME'])) {
			return normalize_role($_SESSION['SESS_LAST_NAME']);
		}
		return '';
	}

	function require_role($roles) {
		$role = current_role();
		$roles = (array)$roles;
		foreach ($roles as $r) {
			if ($role === normalize_role($r)) {
				return;
			}
		}
		header("location: ../index.php");
		exit();
	}
?>