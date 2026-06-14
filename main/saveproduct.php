<?php
session_start();
include('../connect.php');
$a = $_POST['code'];
$b = $_POST['name'];
$c = $_POST['exdate'];
$d = $_POST['price'];
$e = $_POST['supplier'];
$f = $_POST['qty'];
$g = $_POST['o_price'];
$h = $_POST['profit'];
$i = $_POST['gen'];
$j = $_POST['date_arrival'];
$k = $_POST['qty_sold'];

$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? $_POST['category_id'] : null;
$unit_type = isset($_POST['unit_type']) && $_POST['unit_type'] !== '' ? $_POST['unit_type'] : 'piece';
$min_stock_level = isset($_POST['min_stock_level']) && $_POST['min_stock_level'] !== '' ? $_POST['min_stock_level'] : 2;

// query
$sql = "INSERT INTO products (product_code,product_name,expiry_date,price,supplier,qty,o_price,profit,gen_name,date_arrival,qty_sold,category_id,unit_type,min_stock_level) VALUES (:a,:b,:c,:d,:e,:f,:g,:h,:i,:j,:k,:cat,:unit,:min)";
$q = $db->prepare($sql);
$q->execute(array(':a'=>$a,':b'=>$b,':c'=>$c,':d'=>$d,':e'=>$e,':f'=>$f,':g'=>$g,':h'=>$h,':i'=>$i,':j'=>$j,':k'=>$k,':cat'=>$category_id,':unit'=>$unit_type,':min'=>$min_stock_level));
header("location: products.php");

?>