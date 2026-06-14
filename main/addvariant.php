<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="savevariant.php" method="post">
<center><h4><i class="icon-plus-sign icon-large"></i> Add Variant</h4></center>
<hr>
<div style="text-align:left;">
<div id="ac">
	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
	<span>Variant Name: </span><input type="text" style="width:265px; height:30px;" name="variant_name" placeholder="e.g., 1L, 5L, 20L" required /><br>
	<span>Cost: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="cost" required /><br>
	<span>Price: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="price" required /><br>
	<span>Stock: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="current_stock" required /><br>
	<span>Min Stock Level: </span><input type="number" step="0.01" style="width:265px; height:30px;" name="min_stock_level" value="2" required /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="save" />
</div>
</div>
</form>
