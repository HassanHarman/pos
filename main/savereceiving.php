<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$variant_id = isset($_POST['variant_id']) ? $_POST['variant_id'] : '';
	$qty = isset($_POST['qty']) ? (float)$_POST['qty'] : 0;
	$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
	$created_by = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

	if ($product_id === '' || $qty <= 0) {
		header('location: receiving.php?err=' . urlencode('Missing product or invalid quantity'));
		exit();
	}

	try {
		if ($variant_id !== '') {
			$q = $db->prepare("SELECT current_stock FROM product_variants WHERE variant_id = :vid AND product_id = :pid LIMIT 1");
			$q->execute(array(':vid' => $variant_id, ':pid' => $product_id));
			$row = $q->fetch(PDO::FETCH_ASSOC);
			if (!$row) {
				header('location: receiving.php?err=' . urlencode('Variant not found'));
				exit();
			}
			$prev = (float)$row['current_stock'];
			$u = $db->prepare("UPDATE product_variants SET current_stock = current_stock + :q WHERE variant_id = :vid");
			$u->execute(array(':q' => $qty, ':vid' => $variant_id));
			$new = $prev + $qty;

			$m = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, :vid, 'purchase', :qty, NULL, 'manual_receive', :prev, :new, :notes, :by)");
			$m->execute(array(':pid' => $product_id, ':vid' => $variant_id, ':qty' => $qty, ':prev' => $prev, ':new' => $new, ':notes' => $notes, ':by' => $created_by));

			header('location: receiving.php?msg=' . urlencode('Variant stock updated successfully'));
			exit();
		}

		$q = $db->prepare("SELECT qty FROM products WHERE product_id = :pid LIMIT 1");
		$q->execute(array(':pid' => $product_id));
		$row = $q->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			header('location: receiving.php?err=' . urlencode('Product not found'));
			exit();
		}
		$prev = (float)$row['qty'];
		$u = $db->prepare("UPDATE products SET qty = qty + :q WHERE product_id = :pid");
		$u->execute(array(':q' => $qty, ':pid' => $product_id));
		$new = $prev + $qty;

		$m = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, NULL, 'purchase', :qty, NULL, 'manual_receive', :prev, :new, :notes, :by)");
		$m->execute(array(':pid' => $product_id, ':qty' => $qty, ':prev' => $prev, ':new' => $new, ':notes' => $notes, ':by' => $created_by));

		header('location: receiving.php?msg=' . urlencode('Product stock updated successfully'));
		exit();
	} catch (Exception $e) {
		header('location: receiving.php?err=' . urlencode('Failed to save receiving'));
		exit();
	}
?>
