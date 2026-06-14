<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_POST['po_id']) && $_POST['po_id'] !== '' ? (int)$_POST['po_id'] : null;
	$supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';
	$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$variant_id = isset($_POST['variant_id']) ? $_POST['variant_id'] : '';
	$qty = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
	$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
	$created_by = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

	if ($supplier_id === '' || $product_id === '' || $qty <= 0 || $reason === '') {
		header('location: returns.php?err=' . urlencode('Invalid supplier return data'));
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
			if ($prev < $qty) {
				$db->rollBack();
				header('location: returns.php?err=' . urlencode('Insufficient variant stock to return to supplier'));
				exit();
			}
			$u = $db->prepare("UPDATE product_variants SET current_stock = current_stock - :q WHERE variant_id = :vid AND current_stock >= :q");
			$u->execute(array(':q' => $qty, ':vid' => $variant_id));
			$new = $prev - $qty;
		} else {
			$s = $db->prepare("SELECT qty FROM products WHERE product_id = :pid LIMIT 1");
			$s->execute(array(':pid' => $product_id));
			$r = $s->fetch(PDO::FETCH_ASSOC);
			$prev = $r ? (float)$r['qty'] : 0;
			if ($prev < $qty) {
				$db->rollBack();
				header('location: returns.php?err=' . urlencode('Insufficient product stock to return to supplier'));
				exit();
			}
			$u = $db->prepare("UPDATE products SET qty = qty - :q WHERE product_id = :pid AND qty >= :q");
			$u->execute(array(':q' => $qty, ':pid' => $product_id));
			$new = $prev - $qty;
		}

		$ins = $db->prepare("INSERT INTO supplier_returns (po_id, supplier_id, product_id, variant_id, quantity, reason) VALUES (:po, :sid, :pid, :vid, :q, :rsn)");
		$ins->execute(array(
			':po' => $po_id,
			':sid' => $supplier_id,
			':pid' => $product_id,
			':vid' => ($variant_id !== '' ? $variant_id : null),
			':q' => $qty,
			':rsn' => $reason
		));
		$returnId = $db->lastInsertId();

		$m = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, :vid, 'supplier_return', :q, :rid, 'supplier_return', :prev, :new, :n, :by)");
		$m->execute(array(
			':pid' => $product_id,
			':vid' => ($variant_id !== '' ? $variant_id : null),
			':q' => $qty,
			':rid' => $returnId,
			':prev' => $prev,
			':new' => $new,
			':n' => ($po_id ? ('PO ' . $po_id . ' - ') : '') . $reason,
			':by' => $created_by
		));

		$db->commit();
		header('location: returns.php?msg=' . urlencode('Supplier return saved'));
		exit();
	} catch (Exception $e) {
		if ($db->inTransaction()) {
			$db->rollBack();
		}
		header('location: returns.php?err=' . urlencode('Failed to save supplier return'));
		exit();
	}
?>
