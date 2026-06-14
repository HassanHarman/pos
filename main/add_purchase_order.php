<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="save_purchase_order.php" method="post">
<center><h4><i class="icon-plus-sign icon-large"></i> Create Purchase Order</h4></center>
<hr>
<div id="ac">
	<span>PO Number:</span><input type="text" style="width:265px; height:30px;" name="po_number" required /><br>
	<span>Supplier:</span>
	<select name="supplier_id" style="width:265px; height:30px; margin-left:-5px;" required>
		<option value="">-- select --</option>
		<?php
			$s = $db->prepare("SELECT suplier_id, suplier_name FROM supliers WHERE is_active = 1 ORDER BY suplier_name ASC");
			$s->execute();
			for($i=0; $row = $s->fetch(); $i++){
		?>
			<option value="<?php echo $row['suplier_id']; ?>"><?php echo $row['suplier_name']; ?></option>
		<?php
			}
		?>
	</select><br>
	<span>Expected Delivery:</span><input type="date" style="width:265px; height:30px;" name="expected_delivery" /><br>
	<span>Notes:</span><input type="text" style="width:265px; height:30px;" name="notes" /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="create" />
</div>
</form>
