<?php
	require_once('auth.php');
	require_role(array('cashier','manager','owner'));
	include('../connect.php');

	$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
	if ($product_id === '') {
		exit();
	}

	header('Content-Type: text/html; charset=UTF-8');

	echo '<option value="">-- select size --</option>';

	$s = $db->prepare("SELECT variant_id, variant_name, current_stock FROM product_variants WHERE product_id = :pid AND is_active = 1 ORDER BY variant_name ASC");
	$s->execute(array(':pid' => $product_id));
	while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
		echo '<option value="' . htmlspecialchars($row['variant_id']) . '">' . htmlspecialchars($row['variant_name']) . ' (Stock ' . htmlspecialchars($row['current_stock']) . ')</option>';
	}
?>
