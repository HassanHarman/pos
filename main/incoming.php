<?php
session_start();
include('../connect.php');
$a = $_POST['invoice'];
$b = $_POST['product'];
$variant_id = isset($_POST['variant_id']) ? $_POST['variant_id'] : '';
$c = $_POST['qty'];
$w = $_POST['pt'];
$date = $_POST['date'];
$discount = $_POST['discount'];
$result = $db->prepare("SELECT * FROM products WHERE product_id= :userid");
$result->execute(array(':userid' => $b));

for($i=0; $row = $result->fetch(); $i++){
$asasa=$row['price'];
$code=$row['product_code'];
$gen=$row['gen_name'];
$name=$row['product_name'];
$p=$row['profit'];
}

$unit_price = $asasa;
$cost_price = isset($row['cost']) ? $row['cost'] : null;

if ($variant_id !== '') {
	$v = $db->prepare("SELECT variant_id, variant_name, price, cost, current_stock FROM product_variants WHERE variant_id = :vid AND product_id = :pid AND is_active = 1");
	$v->execute(array(':vid' => $variant_id, ':pid' => $b));
	$vr = $v->fetch(PDO::FETCH_ASSOC);
	if ($vr) {
		if ((float)$vr['current_stock'] < (float)$c) {
			$err = urlencode('Insufficient stock for selected size. Available: ' . $vr['current_stock']);
			header("location: sales.php?id=$w&invoice=$a&err=$err");
			exit();
		}
		$unit_price = $vr['price'];
		$cost_price = $vr['cost'];
		// subtract variant stock
		$usql = "UPDATE product_variants SET current_stock=current_stock-? WHERE variant_id=? AND current_stock >= ?";
		$uq = $db->prepare($usql);
		$uq->execute(array($c, $variant_id, $c));
		if ($uq->rowCount() === 0) {
			$err = urlencode('Insufficient stock for selected size.');
			header("location: sales.php?id=$w&invoice=$a&err=$err");
			exit();
		}
		// make display name include size
		$name = $name . ' - ' . $vr['variant_name'];
		// track sold quantity for parent product
		$soldSql = "UPDATE products SET qty_sold=COALESCE(qty_sold,0)+? WHERE product_id=?";
		$soldStmt = $db->prepare($soldSql);
		$soldStmt->execute(array($c, $b));
	}
}

//edit qty (only when no variant selected)
if ($variant_id === '') {
	if ((float)$row['qty'] < (float)$c) {
		$err = urlencode('Insufficient product stock. Available: ' . $row['qty']);
		header("location: sales.php?id=$w&invoice=$a&err=$err");
		exit();
	}
	$sql = "UPDATE products 
			SET qty=qty-?, qty_sold=COALESCE(qty_sold,0)+?
			WHERE product_id=? AND qty >= ?";
	$q = $db->prepare($sql);
	$q->execute(array($c,$c,$b,$c));

	if ($q->rowCount() === 0) {
		$err = urlencode('Insufficient product stock.');
		header("location: sales.php?id=$w&invoice=$a&err=$err");
		exit();
	}
}

$unit_price_num = (float)$unit_price;
$discount_num = (float)$discount;
$qty_num = (float)$c;
$profit_unit = (float)$p;
$fffffff = $unit_price_num - $discount_num;
$d = $fffffff * $qty_num;
$profit = $profit_unit * $qty_num;

// query
$sql = "INSERT INTO sales_order (invoice,product,product_id,variant_id,qty,unit_price,discount,amount,name,price,profit,cost_price_at_sale,product_code,gen_name,date) VALUES (:a,:b,:pid,:vid,:c,:up,:disc,:d,:e,:f,:h,:cost,:i,:j,:k)";
$q = $db->prepare($sql);
$q->execute(array(
	':a'=>$a,
	':b'=>$b,
	':pid'=>$b,
	':vid'=>($variant_id !== '' ? $variant_id : null),
	':c'=>$c,
	':up'=>$unit_price,
	':disc'=>$discount,
	':d'=>$d,
	':e'=>$name,
	':f'=>$unit_price,
	':h'=>$profit,
	':cost'=>$cost_price,
	':i'=>$code,
	':j'=>$gen,
	':k'=>$date
));
header("location: sales.php?id=$w&invoice=$a");
?>