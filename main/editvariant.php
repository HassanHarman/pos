<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
	$q = $db->prepare("SELECT variant_id, product_id, variant_name, cost, price, current_stock, min_stock_level FROM product_variants WHERE variant_id = :id");
	$q->execute(array(':id' => $id));
	$row = $q->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		echo 'Variant not found';
		exit();
	}
	if ($product_id === '') {
		$product_id = $row['product_id'];
	}
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="saveeditvariant.php" method="post">
<center><h4><i class="icon-edit icon-large"></i> Edit Variant</h4></center>
<hr>
<div style="text-align:left;">
<div id="ac">
	<input type="hidden" name="id" value="<?php echo $row['variant_id']; ?>" />
	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
	<span>Variant Name: </span><input type="text" style="width:265px; height:30px;" name="variant_name" value="<?php echo $row['variant_name']; ?>" required /><br>
	<span>Cost: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="cost" value="<?php echo $row['cost']; ?>" required /><br>
	<span>Price: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="price" value="<?php echo $row['price']; ?>" required /><br>
	<span>Stock: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="current_stock" value="<?php echo $row['current_stock']; ?>" required /><br>
	<span>Min Stock Level: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="min_stock_level" value="<?php echo $row['min_stock_level']; ?>" required /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="save" />
</div>
</div>
</form>
