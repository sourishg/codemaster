<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	$permalink = createCleanTitle($_POST['ptitle']);
	$title = mysqli_real_escape_string($con, trim(htmlentities($_POST['ptitle'])));
	$content = addslashes($_POST['content']);
	$checker = strip_tags(trim(str_replace("&nbsp;", "", $content)));
	$privacy = $_POST['privacy'];
	$sid = $_POST['pl'];
	
	if (!$title){
		echo "Please enter a Snippet Title.";
	} else{
		if (empty($checker)){
			echo "Please enter some content.";
		} else {
			if (strlen($title) > 80){
				echo "Your title should be less than 80 characters";
			} else {
			$sql = "INSERT INTO code_snippets VALUES('', '".$sid."', '".addslashes($title)."', '".$permalink."', '".$privacy."', '".addslashes($content)."', '".$_SESSION['username']."', '0', NOW())";
			$res = mysqli_query($con, $sql) or die(mysqli_error());
			$sql2 = "SELECT id FROM code_snippets WHERE user = '$_SESSION[username]' ORDER BY id DESC LIMIT 1";
			$res2 = mysqli_query($con, $sql2);
			$row2 = mysqli_fetch_assoc($res2);
			if ($res) { echo "Your code snippet has been added successfully. <a href=\"".SITE_NAME."/topic/".getSubjectPermalink($con, $sid)."/".$row2['id']."/".$permalink."\" class=\"title\">View post</a>"; }
			}
		}
	}
?>