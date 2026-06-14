<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_GET['po_id']) ? $_GET['po_id'] : '';
	$h = $db->prepare("SELECT po.*, s.suplier_name, s.suplier_address, s.suplier_contact, s.contact_person, s.email FROM purchase_orders po LEFT JOIN supliers s ON s.suplier_id = po.supplier_id WHERE po.po_id = :id");
	$h->execute(array(':id' => $po_id));
	$po = $h->fetch(PDO::FETCH_ASSOC);
	if (!$po) {
		echo 'PO not found';
		exit();
	}

	function money($v){ return number_format((float)$v, 2); }
?>
<!DOCTYPE html>
<html>
<head>
	<title>Purchase Order</title>
	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
	<script language="javascript">
	function Clickheretoprint(){
		var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting+="scrollbars=yes,width=900, height=650, left=100, top=25";
		var content_vlue = document.getElementById("content").innerHTML;
		var docprint=window.open("","",disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 900px; font-size:12px; font-family:arial; font-weight:normal;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();
	}
	</script>
</head>
<body>
<div class="container" style="margin-top:20px;">
	<button class="btn btn-success" onclick="Clickheretoprint()"><i class="icon-print"></i> Print</button>
	<a href="po_view.php?po_id=<?php echo $po_id; ?>"><button class="btn btn-default"><i class="icon-arrow-left"></i> Back</button></a>
	<hr>
	<div id="content">
		<div style="text-align:center; font:bold 22px 'Aleo';">Purchase Order</div>
		<div style="text-align:center; margin-bottom:15px;">PO Number: <strong><?php echo $po['po_number']; ?></strong></div>

		<table class="table table-bordered" style="width:100%;">
			<tr><th style="width:200px;">Supplier</th><td><?php echo $po['suplier_name']; ?></td></tr>
			<tr><th>Contact Person</th><td><?php echo $po['contact_person']; ?></td></tr>
			<tr><th>Phone</th><td><?php echo $po['suplier_contact']; ?></td></tr>
			<tr><th>Email</th><td><?php echo $po['email']; ?></td></tr>
			<tr><th>Address</th><td><?php echo $po['suplier_address']; ?></td></tr>
			<tr><th>Order Date</th><td><?php echo $po['order_date']; ?></td></tr>
			<tr><th>Expected Delivery</th><td><?php echo $po['expected_delivery']; ?></td></tr>
			<tr><th>Status</th><td><?php echo $po['status']; ?></td></tr>
			<tr><th>Notes</th><td><?php echo $po['notes']; ?></td></tr>
		</table>

		<table class="table table-bordered" style="width:100%;">
			<thead>
				<tr>
					<th>Item</th>
					<th style="width:120px;">Qty</th>
					<th style="width:150px;">Unit Price</th>
					<th style="width:150px;">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$items = $db->prepare("SELECT i.*, p.product_name, v.variant_name FROM purchase_order_items i INNER JOIN products p ON p.product_id=i.product_id LEFT JOIN product_variants v ON v.variant_id = i.variant_id WHERE i.po_id = :id ORDER BY i.poi_id ASC");
					$items->execute(array(':id' => $po_id));
					$sum = 0;
					for($i=0; $row = $items->fetch(); $i++){
						$sum += (float)$row['total_price'];
				?>
				<tr>
					<td><?php echo $row['product_name']; ?><?php echo ($row['variant_name'] ? ' - ' . $row['variant_name'] : ''); ?></td>
					<td><?php echo $row['quantity']; ?></td>
					<td><?php echo money($row['unit_price']); ?></td>
					<td><?php echo money($row['total_price']); ?></td>
				</tr>
				<?php } ?>
			</tbody>
			<thead>
				<tr>
					<th colspan="3" style="text-align:right;">Total</th>
					<th><?php echo money($sum); ?></th>
				</tr>
			</thead>
		</table>
	</div>
</div>
</body>
</html>
