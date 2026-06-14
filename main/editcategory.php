<?php
	require_once('auth.php');
	require_role(array('owner','manager'));
	include('../connect.php');

	$id = isset($_GET['id']) ? $_GET['id'] : '';
	$q = $db->prepare("SELECT category_id, category_name FROM categories WHERE category_id = :id");
	$q->execute(array(':id' => $id));
	$row = $q->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
		echo 'Category not found';
		exit();
	}
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="saveeditcategory.php" method="post">
<center><h4><i class="icon-edit icon-large"></i> Edit Category</h4></center>
<hr>
<div style="text-align:left;">
<div id="ac">
	<input type="hidden" name="id" value="<?php echo $row['category_id']; ?>" />
	<span>Category Name: </span><input type="text" style="width:265px; height:30px;" name="category_name" value="<?php echo $row['category_name']; ?>" required /><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="save" />
</div>
</div>
</form>
