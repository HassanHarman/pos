<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$po_id = isset($_GET['po_id']) ? $_GET['po_id'] : '';
	$h = $db->prepare("SELECT po.*, s.suplier_name FROM purchase_orders po LEFT JOIN supliers s ON s.suplier_id = po.supplier_id WHERE po.po_id = :id");
	$h->execute(array(':id' => $po_id));
	$po = $h->fetch(PDO::FETCH_ASSOC);
	if (!$po) {
		echo 'PO not found';
		exit();
	}

	function money($v){ return number_format((float)$v, 2); }
?>
<html>
<head>
<title>
POS
</title>
 <link href="css/bootstrap.css" rel="stylesheet">
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
<script src="lib/jquery.js" type="text/javascript"></script>
<script src="src/facebox.js" type="text/javascript"></script>
<script type="text/javascript">
  jQuery(document).ready(function($) {
    $('a[rel*=facebox]').facebox({
      loadingImage : 'src/loading.gif',
      closeImage   : 'src/closelabel.png'
    })
  })
</script>
</head>
<body>
<?php include('navfixed.php');?>
<div class="container-fluid">
  <div class="row-fluid">
    <div class="span2">
      <div class="well sidebar-nav">
        <ul class="nav nav-list">
          <li><a href="index.php"><i class="icon-dashboard icon-2x"></i> Dashboard</a></li>
          <li class="active"><a href="purchase_orders.php"><i class="icon-truck icon-2x"></i> Purchase Orders</a></li>
        </ul>
      </div>
    </div>
    <div class="span10">
      <div class="contentheader"><i class="icon-truck"></i> PO: <?php echo $po['po_number']; ?></div>
      <ul class="breadcrumb">
        <li><a href="purchase_orders.php">Purchase Orders</a></li> /
        <li class="active">View</li>
      </ul>

      <div style="margin-top:-10px; margin-bottom: 15px;">
        <a href="purchase_orders.php"><button class="btn btn-default"><i class="icon-arrow-left"></i> Back</button></a>
        <a href="po_print.php?po_id=<?php echo $po_id; ?>" target="_blank"><button class="btn btn-success"><i class="icon-print"></i> Print</button></a>
        <?php if($po['status']=='pending') { ?>
        <a rel="facebox" href="po_add_item.php?po_id=<?php echo $po_id; ?>"><button class="btn btn-info"><i class="icon-plus"></i> Add Item</button></a>
        <a href="po_receive.php?po_id=<?php echo $po_id; ?>"><button class="btn btn-success"><i class="icon-ok"></i> Receive</button></a>
        <?php } ?>
      </div>

      <table class="table table-bordered" style="width:100%;">
        <tr><th>Supplier</th><td><?php echo $po['suplier_name']; ?></td></tr>
        <tr><th>Status</th><td><?php echo $po['status']; ?></td></tr>
        <tr><th>Expected Delivery</th><td><?php echo $po['expected_delivery']; ?></td></tr>
        <tr><th>Notes</th><td><?php echo $po['notes']; ?></td></tr>
      </table>

      <table class="table table-bordered" id="resultTable" data-responsive="table" style="text-align: left;">
        <thead>
          <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
            <th width="120">Action</th>
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
          <tr class="record">
            <td><?php echo $row['product_name']; ?><?php echo ($row['variant_name'] ? ' - ' . $row['variant_name'] : ''); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td><?php echo money($row['unit_price']); ?></td>
            <td><?php echo money($row['total_price']); ?></td>
            <td>
              <?php if($po['status']=='pending') { ?>
              <a href="po_delete_item.php?poi_id=<?php echo $row['poi_id']; ?>&po_id=<?php echo $po_id; ?>" onclick="return confirm('Remove item?')"><button class="btn btn-mini btn-danger"><i class="icon-trash"></i> Remove</button></a>
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
        <thead>
          <tr>
            <th colspan="3" style="text-align:right;">Total:</th>
            <th><?php echo money($sum); ?></th>
            <th></th>
          </tr>
        </thead>
      </table>

      <?php
        $upd = $db->prepare("UPDATE purchase_orders SET total_amount = :t WHERE po_id = :id");
        $upd->execute(array(':t' => $sum, ':id' => $po_id));
      ?>

    </div>
  </div>
</div>
</body>
<?php include('footer.php');?>
</html>
