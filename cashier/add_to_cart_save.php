<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$invoice = isset($_POST['invoice']) ? (string)$_POST['invoice'] : '';
	$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
	$variant_id_raw = isset($_POST['variant_id']) ? (string)$_POST['variant_id'] : '';
	$variant_id = $variant_id_raw !== '' ? (int)$variant_id_raw : 0;
	$qty = isset($_POST['qty']) ? (float)$_POST['qty'] : 0;

	if ($invoice === '' || $product_id <= 0 || $qty <= 0) {
		header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Invalid data'));
		exit();
	}

	$discount = 0;
	$date = date('m/d/y');

	$result = $db->prepare("SELECT * FROM products WHERE product_id = :pid LIMIT 1");
	$result->execute(array(':pid' => $product_id));
	$row = $result->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Product not found'));
		exit();
	}

	$asasa = (float)$row['price'];
	$code = $row['product_code'];
	$gen = $row['gen_name'];
	$name = $row['product_name'];
	$p = (float)$row['profit'];
	$unit_price = $asasa;
	$cost_price = isset($row['cost']) ? $row['cost'] : null;

	if ($variant_id > 0) {
		$v = $db->prepare("SELECT variant_id, variant_name, price, cost, current_stock FROM product_variants WHERE variant_id = :vid AND product_id = :pid AND is_active = 1");
		$v->execute(array(':vid' => $variant_id, ':pid' => $product_id));
		$vr = $v->fetch(PDO::FETCH_ASSOC);
		if (!$vr) {
			header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Variant not found'));
			exit();
		}
		if ((float)$vr['current_stock'] < (float)$qty) {
			header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Insufficient stock for selected size. Available: ' . $vr['current_stock']));
			exit();
		}
		$unit_price = (float)$vr['price'];
		$cost_price = $vr['cost'];
		$usql = "UPDATE product_variants SET current_stock=current_stock-? WHERE variant_id=? AND current_stock >= ?";
		$uq = $db->prepare($usql);
		$uq->execute(array($qty, $variant_id, $qty));
		if ($uq->rowCount() === 0) {
			header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Insufficient stock for selected size.'));
			exit();
		}
		$name = $name . ' - ' . $vr['variant_name'];
	} else {
		if ((float)$row['qty'] < (float)$qty) {
			header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Insufficient product stock. Available: ' . $row['qty']));
			exit();
		}
		$sql = "UPDATE products SET qty=qty-? WHERE product_id=? AND qty >= ?";
		$q = $db->prepare($sql);
		$q->execute(array($qty, $product_id, $qty));
		if ($q->rowCount() === 0) {
			header('location: pos.php?invoice=' . urlencode($invoice) . '&err=' . urlencode('Insufficient product stock.'));
			exit();
		}
	}

	$fffffff = $unit_price - $discount;
	$amount = $fffffff * $qty;
	$profit = $p * $qty;

	$sqlIns = "INSERT INTO sales_order (invoice,product,product_id,variant_id,qty,unit_price,discount,amount,name,price,profit,cost_price_at_sale,product_code,gen_name,date) VALUES (:a,:b,:pid,:vid,:c,:up,:disc,:d,:e,:f,:h,:cost,:i,:j,:k)";
	$ins = $db->prepare($sqlIns);
	$ins->execute(array(
		':a' => $invoice,
		':b' => $product_id,
		':pid' => $product_id,
		':vid' => ($variant_id > 0 ? $variant_id : null),
		':c' => $qty,
		':up' => $unit_price,
		':disc' => $discount,
		':d' => $amount,
		':e' => $name,
		':f' => $unit_price,
		':h' => $profit,
		':cost' => $cost_price,
		':i' => $code,
		':j' => $gen,
		':k' => $date
	));

	header('location: pos.php?invoice=' . urlencode($invoice));
	exit();
?>
