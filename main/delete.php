<?php
	include('../connect.php');
	$id=$_GET['id'];
	$c=$_GET['invoice'];
	$sdsd=$_GET['dle'];
	$qty=$_GET['qty'];
	$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
	$variant_id = isset($_GET['variant_id']) ? $_GET['variant_id'] : '';
	//edit qty
	if ($variant_id !== '' && $variant_id !== '0') {
		$sql = "UPDATE product_variants SET current_stock=current_stock+? WHERE variant_id=?";
		$q = $db->prepare($sql);
		$q->execute(array($qty,$variant_id));
	} else {
		$sql = "UPDATE products 
				SET qty=qty+?
				WHERE product_id=?";
		$q = $db->prepare($sql);
		$q->execute(array($qty,$product_id));
	}

	$result = $db->prepare("DELETE FROM sales_order WHERE transaction_id= :memid");
	$result->execute(array(':memid' => $id));
	header("location: sales.php?id=$sdsd&invoice=$c");
?>