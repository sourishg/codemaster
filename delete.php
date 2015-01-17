<?php
	session_start();
	include('includes/connect.php');
	include('includes/constants.php');
	if (!isset($_SESSION['username']) || !isset($_GET['id'])){ 
		$locate = SITE_NAME; 
		header("location:$locate"); 
	}
	if (isset($_GET['ref']) && $_GET['ref'] == "msg"){
		$sql = "SELECT receiver FROM pm WHERE id = $_GET[id]";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$row = mysqli_fetch_assoc($res);
			if ($_SESSION['username'] == $row['receiver']){
				$sql = "DELETE FROM pm WHERE id = $_GET[id] AND receiver = '$row[receiver]'";
				$res = mysqli_query($con, $sql);
			}
		}
		$locate = SITE_NAME . "/inbox/";
		header("location:$locate");
	} else if ($_GET['ref'] && $_GET['ref'] == "stash"){
		$sql = "SELECT user, location FROM code_stash WHERE id = $_GET[id]";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$row = mysqli_fetch_assoc($res);
			if ($_SESSION['username'] == $row['user']){
				$file_location = $row['location'];
				$file = substr($file_location, 1, strlen($file_location));
				unlink($file);
				$sql = "DELETE FROM code_stash WHERE id = $_GET[id] AND user = '$row[user]'";
				$res = mysqli_query($con, $sql);
			}
		}
		$locate = SITE_NAME . "/coder/$_SESSION[username]";
		header("location:$locate");
	} else if ($_GET['ref'] && $_GET['ref'] == "dp"){
		$sql = "SELECT username, dp, dp_thumb FROM users WHERE id = $_GET[id]";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$row = mysqli_fetch_assoc($res);
			if ($_SESSION['username'] == $row['username']){
				$file_location = $row['dp'];
				$file_location2 = $row['dp_thumb'];
				$file = substr($file_location, 1, strlen($file_location));
				unlink($file);
				$file2 = substr($file_location2, 1, strlen($file_location2));
				unlink($file2);
				$null = "";
				$sql = "UPDATE users SET dp = NULL, dp_thumb = NULL WHERE id = $_GET[id]";
				$res = mysqli_query($con, $sql) or die(mysqli_error());
			}
		}
		if ($res){
		$locate = SITE_NAME . "/coder/$_SESSION[username]";
		header("location:$locate");
		}
	} else {
		$sql = "SELECT user FROM code_snippets WHERE id = $_GET[id]";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$row = mysqli_fetch_assoc($res);
			if ($_SESSION['username'] == $row['user']){
				$sql = "DELETE FROM code_snippets WHERE id = $_GET[id] AND user = '$row[user]'";
				$res = mysqli_query($con, $sql);
			}
		}
		$locate = SITE_NAME . "/coder/$_SESSION[username]";
		header("location:$locate");
	}
?>