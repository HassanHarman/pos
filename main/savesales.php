<?php
session_start();
include('../connect.php');
$a = $_POST['invoice'];
$b = $_POST['cashier'];
$c = $_POST['date'];
$d = $_POST['ptype'];
$e = $_POST['amount'];
$z = $_POST['profit'];
$cname = $_POST['cname'];

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$subtotal = isset($_POST['subtotal']) ? $_POST['subtotal'] : null;
$vat_amount = isset($_POST['vat_amount']) ? $_POST['vat_amount'] : null;
$total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : null;
$amount_paid = null;
$change_amount = null;
$sale_type = isset($_POST['sale_type']) ? $_POST['sale_type'] : 'counter';
$customer_phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : null;
$delivery_address = isset($_POST['delivery_address']) ? $_POST['delivery_address'] : null;
$delivery_status = ($sale_type === 'delivery') ? 'pending' : 'delivered';

function log_sale_stock_movements($db, $invoice, $created_by) {
	try {
		$saleId = null;
		$sq = $db->prepare("SELECT transaction_id FROM sales WHERE invoice_number = :inv ORDER BY transaction_id DESC LIMIT 1");
		$sq->execute(array(':inv' => $invoice));
		$srow = $sq->fetch(PDO::FETCH_ASSOC);
		if ($srow && isset($srow['transaction_id'])) {
			$saleId = $srow['transaction_id'];
		}

		$lines = $db->prepare("SELECT product_id, variant_id, qty FROM sales_order WHERE invoice = :inv");
		$lines->execute(array(':inv' => $invoice));
		while ($line = $lines->fetch(PDO::FETCH_ASSOC)) {
			$productId = isset($line['product_id']) ? $line['product_id'] : null;
			$variantId = isset($line['variant_id']) ? $line['variant_id'] : null;
			if ($productId === null || $productId === '' || (int)$productId === 0) {
				continue;
			}

			$qty = isset($line['qty']) ? (float)$line['qty'] : 0;
			if ($qty == 0) {
				continue;
			}

			$current = null;
			if ($variantId !== null && $variantId !== '' && (int)$variantId !== 0) {
				$st = $db->prepare("SELECT current_stock FROM product_variants WHERE variant_id = :vid LIMIT 1");
				$st->execute(array(':vid' => $variantId));
				$prow = $st->fetch(PDO::FETCH_ASSOC);
				$current = $prow && isset($prow['current_stock']) ? (float)$prow['current_stock'] : null;
			} else {
				$st = $db->prepare("SELECT qty FROM products WHERE product_id = :pid LIMIT 1");
				$st->execute(array(':pid' => $productId));
				$prow = $st->fetch(PDO::FETCH_ASSOC);
				$current = $prow && isset($prow['qty']) ? (float)$prow['qty'] : null;
			}

			$prev = $current;
			$new = $current;
			if ($current !== null) {
				$prev = $current + $qty;
				$new = $current;
			}

			$ins = $db->prepare("INSERT INTO stock_movements (product_id, variant_id, movement_type, quantity, reference_id, reference_type, previous_stock, new_stock, notes, created_by) VALUES (:pid, :vid, 'sale', :qty, :rid, 'sale', :prev, :new, :notes, :by)");
			$ins->execute(array(
				':pid' => $productId,
				':vid' => ($variantId !== null && $variantId !== '' && (int)$variantId !== 0) ? $variantId : null,
				':qty' => $qty,
				':rid' => $saleId,
				':prev' => $prev,
				':new' => $new,
				':notes' => 'Invoice ' . $invoice,
				':by' => $created_by
			));
		}
	} catch (Exception $e) {
		// best-effort only
	}
}
if($d=='credit') {
$f = $_POST['due'];

$sql = "INSERT INTO sales (invoice_number,cashier,user_id,date,type,amount,profit,due_date,name,customer_phone,sale_type,delivery_address,delivery_status,subtotal,vat_amount,total_amount,amount_paid,change_amount) VALUES (:a,:b,:uid,:c,:d,:e,:z,:f,:g,:phone,:stype,:addr,:dstatus,:sub,:vat,:tot,:paid,:chg)";
$q = $db->prepare($sql);
$q->execute(array(
	':a'=>$a,
	':b'=>$b,
	':uid'=>$user_id,
	':c'=>$c,
	':d'=>$d,
	':e'=>$e,
	':z'=>$z,
	':f'=>$f,
	':g'=>$cname,
	':phone'=>$customer_phone,
	':stype'=>$sale_type,
	':addr'=>$delivery_address,
	':dstatus'=>$delivery_status,
	':sub'=>$subtotal,
	':vat'=>$vat_amount,
	':tot'=>$total_amount,
	':paid'=>$amount_paid,
	':chg'=>$change_amount
));

log_sale_stock_movements($db, $a, $user_id);
header("location: preview.php?invoice=$a");
exit();
}
if($d=='cash') {
$f = $_POST['cash'];

$amount_paid = $f;
$change_amount = null;
if ($total_amount !== null) {
	$change_amount = (float)$amount_paid - (float)$total_amount;
}

$sql = "INSERT INTO sales (invoice_number,cashier,user_id,date,type,amount,profit,due_date,name,customer_phone,sale_type,delivery_address,delivery_status,subtotal,vat_amount,total_amount,amount_paid,change_amount) VALUES (:a,:b,:uid,:c,:d,:e,:z,:f,:g,:phone,:stype,:addr,:dstatus,:sub,:vat,:tot,:paid,:chg)";
$q = $db->prepare($sql);
$q->execute(array(
	':a'=>$a,
	':b'=>$b,
	':uid'=>$user_id,
	':c'=>$c,
	':d'=>$d,
	':e'=>$e,
	':z'=>$z,
	':f'=>$f,
	':g'=>$cname,
	':phone'=>$customer_phone,
	':stype'=>$sale_type,
	':addr'=>$delivery_address,
	':dstatus'=>$delivery_status,
	':sub'=>$subtotal,
	':vat'=>$vat_amount,
	':tot'=>$total_amount,
	':paid'=>$amount_paid,
	':chg'=>$change_amount
));

log_sale_stock_movements($db, $a, $user_id);
header("location: preview.php?invoice=$a");
exit();
}
// query
?>