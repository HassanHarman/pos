<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_GET['po_id']) ? $_GET['po_id'] : '';
	$created_by = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

	$h = $db->prepare("SELECT * FROM purchase_orders WHERE po_id = :id");
	$h->execute(array(':id' => $po_id));
	$po = $h->fetch(PDO::FETCH_ASSOC);
	if (!$po) {
		echo 'PO not found';
		exit();
	}
	if ($po['status'] !== 'pending') {
		header('location: po_view.php?po_id=' . $po_id);
		exit();
	}

	$items = $db->prepare("SELECT * FROM purchase_order_items WHERE po_id = :id");
	$items->execute(array(':id' => $po_id));

	try {
		$db->beginTransaction();
		while ($row = $items->fetch(PDO::FETCH_ASSOC)) {
			$pid = $row['product_id'];
			$vid = $row['variant_id'];
			$qty = (float)$row['quantity'];

			if ($vid !== null && $vid !== '' && (int)$vid !== 0) {
				$q = $db->prepare("SELECT current_stock FROM product_variants WHERE variant_id = :vid LIMIT 1");
				$q->execute(array(':vid' => $vid));
				$s = $q->fetch(PDO::FETCH_ASSOC);
				$prev = $s ? (float)$s['current_stock'] : 0;
				$u = $db->prepare("UPDATE product_variants SET current_stock = current_stock + :q WHERE variant_id = :vid");
				$u->execute(array(':q' => $qty, ':vid' => $vid));
				$new = $prev + $qty;
				$m = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, :vid, 'purchase', :qty, :rid, 'purchase_order', :prev, :new, :notes, :by)");
				$m->execute(array(':pid' => $pid, ':vid' => $vid, ':qty' => $qty, ':rid' => $po_id, ':prev' => $prev, ':new' => $new, ':notes' => 'PO ' . $po['po_number'], ':by' => $created_by));
			} else {
				$q = $db->prepare("SELECT qty FROM products WHERE product_id = :pid LIMIT 1");
				$q->execute(array(':pid' => $pid));
				$s = $q->fetch(PDO::FETCH_ASSOC);
				$prev = $s ? (float)$s['qty'] : 0;
				$u = $db->prepare("UPDATE products SET qty = qty + :q WHERE product_id = :pid");
				$u->execute(array(':q' => $qty, ':pid' => $pid));
				$new = $prev + $qty;
				$m = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, NULL, 'purchase', :qty, :rid, 'purchase_order', :prev, :new, :notes, :by)");
				$m->execute(array(':pid' => $pid, ':qty' => $qty, ':rid' => $po_id, ':prev' => $prev, ':new' => $new, ':notes' => 'PO ' . $po['po_number'], ':by' => $created_by));
			}
		}

		$u = $db->prepare("UPDATE purchase_orders SET status='received' WHERE po_id = :id");
		$u->execute(array(':id' => $po_id));
		$db->commit();
		header('location: po_view.php?po_id=' . $po_id);
		exit();
	} catch (Exception $e) {
		if ($db->inTransaction()) {
			$db->rollBack();
		}
		echo 'Failed to receive PO';
		exit();
	}
?>
