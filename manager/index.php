<?php
	require_once('../main/auth.php');
	require_role(array('manager','owner'));
?>
<!DOCTYPE html>
<html>
<head>
	<title>Manager Dashboard</title>
	<link href="../main/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="../main/css/DT_bootstrap.css">
	<link rel="stylesheet" href="../main/css/font-awesome.min.css">
	<link href="../main/css/bootstrap-responsive.css" rel="stylesheet">
	<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
	<?php include('../main/navfixed.php');?>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="contentheader">
					<i class="icon-dashboard"></i> Manager Dashboard
				</div>
				<ul class="breadcrumb">
					<li class="active">Manager</li>
				</ul>
				<div id="mainmain">
					<a href="../main/products.php"><i class="icon-list-alt icon-2x"></i><br> Products</a>
					<a href="../main/supplier.php"><i class="icon-group icon-2x"></i><br> Suppliers</a>
					<a href="../main/purchases.php"><i class="icon-truck icon-2x"></i><br> Purchases</a>
					<a href="../main/salesreport.php?d1=0&d2=0"><i class="icon-bar-chart icon-2x"></i><br> Reports</a>
					<a href="../index.php"><font color="red"><i class="icon-off icon-2x"></i></font><br> Logout</a>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
