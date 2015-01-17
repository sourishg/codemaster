<?php
	session_start();
	include('../includes/connect.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>My Forum - Admin</title>
<style>
	body{ font-family:Tahoma, Geneva, sans-serif; font-size:11pt; color:#033; padding:0; margin:0; background:#CCC; }
	input { padding:3px; border:1px solid #399; }
	#header { background:#033; color:#999; text-align:center; }
	#header h1 { padding:10px; margin:0; color:#CCCCCC; }
	textarea { width:400px; height:100px; padding:3px; border:1px solid #399; }
	table, tr, td { margin:0; padding:4px; }
	#info { padding:10px; font-size:14pt; color:#990000; background:#99CC99; text-align:center; }
</style>
</head>

<body>
<?php
	$messge = "";

	if (isset($_POST['submit'])){
		$name = mss($_POST['name']);
		$desc = mysqli_real_escape_string(trim(strip_tags($con, $_POST['desc']), '<b><u>'));
		
		if (!$name){
			$message = "Please enter a category name.";
		} else {
			if (!$desc){
				$message = "Please enter a description.";
			} else {
				$sql = "INSERT INTO main_categories VALUES('', '".$name."', '".$desc."')";
				$res = mysql_query($sql) or die(mysql_error());
				if ($res){
					$message = "Main Category added successfully.";
				}
			}
		}
	}
?>
<div id="header">
	<h1>Administrator</h1>
</div>
<?php
    	if (!empty($message)){
			echo "<div id=\"info\">";
			echo "<p>$message</p>";
			echo "</div>";
		}
?>
<div id="content">
    <h3>Add Main Categories</h3>
    <form action="admin.php" method="post">
    <table border="0" cellpadding="3" cellspacing="3" style="border-collapse:collapse;">
        <tr>
            <td valign="top">Category Name</td>
            <td><input type="text" value="" name="name" /></td>
        </tr>
        <tr>
            <td valign="top">Category Description</td>
            <td><textarea name="desc"></textarea></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" value="Add" name="submit" /></td>
        </tr>
    </table>
    </form>
</div>
</body>
</html>