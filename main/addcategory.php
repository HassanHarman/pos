<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="savecategory.php" method="post">
<center><h4><i class="icon-plus-sign icon-large"></i> Add Category</h4></center>
<hr>
<div style="text-align:left;">
<div id="ac">
	<span>Category Name: </span><input type="text" style="width:265px; height:30px;" name="category_name" required /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="save" />
</div>
</div>
</form>
