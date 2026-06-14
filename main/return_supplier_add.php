<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="return_supplier_save.php" method="post">
<center><h4><i class="icon-undo icon-large"></i> Supplier Return</h4></center>
<hr>
<div id="ac">
	<span>PO ID (optional):</span><input type="number" style="width:265px; height:30px;" name="po_id" /><br>

	<span>Supplier:</span><br>
	<select name="supplier_id" style="width:265px; height:30px;" required>
		<option value="">-- select --</option>
		<?php
			$s = $db->prepare("SELECT suplier_id, suplier_name FROM supliers WHERE is_active = 1 ORDER BY suplier_name ASC");
			$s->execute();
			for($i=0; $row = $s->fetch(); $i++){
		?>
		<option value="<?php echo $row['suplier_id']; ?>"><?php echo htmlspecialchars($row['suplier_name']); ?></option>
		<?php } ?>
	</select><br>

	<span>Product:</span><br>
	<select name="product_id" id="product_id" style="width:265px; height:30px;" required>
		<option value="">-- select --</option>
		<?php
			$p = $db->prepare("SELECT product_id, product_name FROM products WHERE is_active = 1 ORDER BY product_name ASC");
			$p->execute();
			for($i=0; $row = $p->fetch(); $i++){
		?>
		<option value="<?php echo $row['product_id']; ?>"><?php echo htmlspecialchars($row['product_name']); ?></option>
		<?php } ?>
	</select><br>

	<span>Variant (optional):</span><br>
	<select name="variant_id" id="variant_id" style="width:265px; height:30px;">
		<option value="">-- none --</option>
	</select><br>

	<span>Quantity Returned:</span><input type="number" step="0.01" min="0" style="width:265px; height:30px;" name="quantity" required /><br>
	<span>Reason:</span><input type="text" style="width:265px; height:30px;" name="reason" required /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="save" />
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
