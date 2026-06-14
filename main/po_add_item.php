<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_GET['po_id']) ? $_GET['po_id'] : '';
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="po_save_item.php" method="post">
<center><h4><i class="icon-plus-sign icon-large"></i> Add PO Item</h4></center>
<hr>
<div id="ac">
	<input type="hidden" name="po_id" value="<?php echo $po_id; ?>" />
	<span>Product:</span><br>
	<select name="product_id" id="product_id" style="width:265px; height:30px;" required>
		<option value="">-- select --</option>
		<?php
			$p = $db->prepare("SELECT product_id, product_name FROM products WHERE is_active = 1 ORDER BY product_name ASC");
			$p->execute();
			for($i=0; $row = $p->fetch(); $i++){
		?>
			<option value="<?php echo $row['product_id']; ?>"><?php echo $row['product_name']; ?></option>
		<?php } ?>
	</select><br>

	<span>Variant (optional):</span><br>
	<select name="variant_id" id="variant_id" style="width:265px; height:30px;">
		<option value="">-- none --</option>
	</select><br>

	<span>Quantity:</span><input type="number" step="0.01" min="0" name="quantity" style="width:265px; height:30px;" required /><br>
	<span>Unit Price:</span><input type="number" step="0.01" min="0" name="unit_price" style="width:265px; height:30px;" required /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="add" />
</div>
</form>
<script src="lib/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
$(function(){
	$('#product_id').on('change', function(){
		var pid = $(this).val();
		if (!pid) {
			$('#variant_id').html('<option value="">-- none --</option>');
			return;
		}
		$('#variant_id').load('get_variants.php?product_id=' + encodeURIComponent(pid), function(){
			$('#variant_id').prepend('<option value="">-- none --</option>');
		});
	});
});
</script>
