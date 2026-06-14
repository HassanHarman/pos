<?php
	require_once('auth.php');
	require_role(array('owner'));
?>
<link href="../style.css" media="screen" rel="stylesheet" type="text/css" />
<form action="saveuser.php" method="post">
<center><h4><i class="icon-plus-sign icon-large"></i> Add User</h4></center>
<hr>
<div style="text-align:left;">
<div id="ac">
	<span>Full Name: </span><input type="text" style="width:265px; height:30px;" name="name" required /><br>
	<span>Username: </span><input type="text" style="width:265px; height:30px;" name="username" required /><br>
	<span>Password: </span><input type="password" style="width:265px; height:30px;" name="password" required /><br>
	<span>Role: </span>
	<select name="position" style="width:265px; height:30px;" required>
		<option value="manager">manager</option>
		<option value="cashier">cashier</option>
		<option value="owner">owner</option>
	</select><br>
	<span>&nbsp;</span><input id="btn" type="submit" value="save" />
</div>
</div>
</form>
