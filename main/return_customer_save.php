<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$invoice = isset($_POST['invoice_number']) ? trim($_POST['invoice_number']) : null;
	$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$variant_id = isset($_POST['variant_id']) ? $_POST['variant_id'] : '';
	$qty = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
	$refund = isset($_POST['refund_amount']) ? (float)$_POST['refund_amount'] : 0;
	$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
	$approved_by = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

	if ($product_id === '' || $qty <= 0 || $refund < 0 || $reason === '') {
		header('location: returns.php?err=' . urlencode('Invalid return data'));
		exit();
	}

	try {
		$db->beginTransaction();

		$prev = null;
		$new = null;

		if ($variant_id !== '') {
			$s = $db->prepare("SELECT current_stock FROM product_variants WHERE variant_id = :vid AND product_id = :pid LIMIT 1");
			$s->execute(array(':vid' => $variant_id, ':pid' => $product_id));
			$r = $s->fetch(PDO::FETCH_ASSOC);
			$prev = $r ? (float)$r['current_stock'] : 0;
			$u = $db->prepare("UPDATE product_variants SET current_stock = current_stock + :q WHERE variant_id = :vid");
			$u->execute(array(':q' => $qty, ':vid' => $variant_id));
			$new = $prev + $qty;
		} else {
			$s = $db->prepare("SELECT qty FROM products WHERE product_id = :pid LIMIT 1");
			$s->execute(array(':pid' => $product_id));
			$r = $s->fetch(PDO::FETCH_ASSOC);
			$prev = $r ? (float)$r['qty'] : 0;
			$u = $db->prepare("UPDATE products SET qty = qty + :q WHERE product_id = :pid");
			$u->execute(array(':q' => $qty, ':pid' => $product_id));
			$new = $prev + $qty;
		}

		$ins = $db->prepare("INSERT INTO returns (sale_id, invoice_number, product_id, variant_id, quantity, refund_amount, reason, approved_by) VALUES (NULL, :inv, :pid, :vid, :q, :rf, :rsn, :ab)");
		$ins->execute(array(
			':inv' => $invoice,
			':pid' => $product_id,
			':vid' => ($variant_id !== '' ? $variant_id : null),
			':q' => $qty,
			':rf' => $refund,
			':rsn' => $reason,
			':ab' => $approved_by
		));
		$returnId = $db->lastInsertId();

		$m = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, :vid, 'return', :q, :rid, 'customer_return', :prev, :new, :n, :by)");
		$m->execute(array(
			':pid' => $product_id,
			':vid' => ($variant_id !== '' ? $variant_id : null),
			':q' => $qty,
			':rid' => $returnId,
			':prev' => $prev,
			':new' => $new,
			':n' => ($invoice ? ('Invoice ' . $invoice . ' - ') : '') . $reason,
			':by' => $approved_by
		));

		$db->commit();
		header('location: returns.php?msg=' . urlencode('Customer return saved'));
		exit();
	} catch (Exception $e) {
		if ($db->inTransaction()) {
			$db->rollBack();
		}
		header('location: returns.php?err=' . urlencode('Failed to save return'));
		exit();
	}
?>
