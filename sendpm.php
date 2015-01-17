<?php 
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	if (isset($_POST['message']) && isset($_POST['receiver']) && isset($_SESSION['username'])){
		$message = addslashes(trim($_POST['message']));
		$receiver = $_POST['receiver'];
		$sender = $_SESSION['username'];
		$sql = "INSERT INTO pm VALUES('', '".mysqli_real_escape_string($con, $sender)."', '".mysqli_real_escape_string($con, $receiver)."', '".mysqli_real_escape_string($con, $message)."', NOW(), '0')";
		$res = mysqli_query($con, $sql);
		if ($res){
			echo "<p>Your message has been sent.</p>";
		} else {
			echo "<p>Oops! Message was not delivered.</p>";
		}
	}
?>