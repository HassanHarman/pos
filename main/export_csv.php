<?php
	require_once('auth.php');
	require_role(array('owner'));
	include('../connect.php');

	$type = isset($_GET['type']) ? $_GET['type'] : '';
	$filename = $type . '_' . date('Y-m-d_His') . '.csv';

	header('Content-Type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');

	$out = fopen('php://output', 'w');

	if ($type === 'sales') {
		fputcsv($out, array('transaction_id','invoice_number','cashier','user_id','customer_name','customer_phone','sale_type','delivery_status','subtotal','vat_amount','total_amount','amount_paid','change_amount','created_at'));
		$q = $db->prepare("SELECT transaction_id, invoice_number, cashier, user_id, customer_name, customer_phone, sale_type, delivery_status, subtotal, vat_amount, total_amount, amount_paid, change_amount, created_at FROM sales ORDER BY transaction_id DESC");
		$q->execute();
		while($row = $q->fetch(PDO::FETCH_ASSOC)) {
			fputcsv($out, $row);
		}
		fclose($out);
		exit();
	}

	if ($type === 'sales_lines') {
		fputcsv($out, array('transaction_id','invoice','product_id','variant_id','product_code','name','qty','unit_price','discount','amount','profit','cost_price_at_sale','date'));
		$q = $db->prepare("SELECT transaction_id, invoice, product_id, variant_id, product_code, name, qty, unit_price, discount, amount, profit, cost_price_at_sale, date FROM sales_order ORDER BY transaction_id DESC");
		$q->execute();
		while($row = $q->fetch(PDO::FETCH_ASSOC)) {
			fputcsv($out, $row);
		}
		fclose($out);
		exit();
	}

	if ($type === 'products') {
		fputcsv($out, array('product_id','product_code','product_name','category_id','unit_type','cost','price','qty','min_stock_level','is_active','created_at'));
		$q = $db->prepare("SELECT product_id, product_code, product_name, category_id, unit_type, cost, price, qty, min_stock_level, is_active, created_at FROM products ORDER BY product_id DESC");
		$q->execute();
		while($row = $q->fetch(PDO::FETCH_ASSOC)) {
			fputcsv($out, $row);
		}
		fclose($out);
		exit();
	}

	if ($type === 'variants') {
		fputcsv($out, array('variant_id','product_id','variant_name','price','cost','current_stock','min_stock_level','is_active','created_at'));
		$q = $db->prepare("SELECT variant_id, product_id, variant_name, price, cost, current_stock, min_stock_level, is_active, created_at FROM product_variants ORDER BY variant_id DESC");
		$q->execute();
		while($row = $q->fetch(PDO::FETCH_ASSOC)) {
			fputcsv($out, $row);
		}
		fclose($out);
		exit();
	}

	if ($type === 'stock_movements') {
		fputcsv($out, array('movement_id','product_id','variant_id','movement_type','quantity','reference_id','reference_type','previous_stock','new_stock','notes','created_by','created_at'));
		$q = $db->prepare("SELECT movement_id, product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by, created_at FROM stock_movements ORDER BY movement_id DESC");
		$q->execute();
		while($row = $q->fetch(PDO::FETCH_ASSOC)) {
			fputcsv($out, $row);
		}
		fclose($out);
		exit();
	}

	fputcsv($out, array('Invalid export type'));
	fclose($out);
	exit();
?>
