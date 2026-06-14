<?php
// configuration
include('../connect.php');

// new data
$id = $_POST['memi'];
$a = $_POST['code'];
$z = $_POST['gen'];
$b = $_POST['name'];
$c = $_POST['exdate'];
$d = $_POST['price'];
$e = $_POST['supplier'];
$f = $_POST['qty'];
$g = $_POST['o_price'];
$h = $_POST['profit'];
$i = $_POST['date_arrival'];
$j = $_POST['sold'];

$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? $_POST['category_id'] : null;
$unit_type = isset($_POST['unit_type']) && $_POST['unit_type'] !== '' ? $_POST['unit_type'] : 'piece';
$min_stock_level = isset($_POST['min_stock_level']) && $_POST['min_stock_level'] !== '' ? $_POST['min_stock_level'] : 2;

// query
$sql = "UPDATE products 
        SET product_code=?, gen_name=?, product_name=?, expiry_date=?, price=?, supplier=?, qty=?, o_price=?, profit=?, date_arrival=?, qty_sold=?, category_id=?, unit_type=?, min_stock_level=?
		WHERE product_id=?";
$q = $db->prepare($sql);
$q->execute(array($a,$z,$b,$c,$d,$e,$f,$g,$h,$i,$j,$category_id,$unit_type,$min_stock_level,$id));
header("location: products.php");

?>