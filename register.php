<?php
	session_start();
	include('includes/code_functions.php');
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/pagination.php');
	if (isset($_SESSION['username'])){
		header("location:index.php");
	}
?>
<?php
	if (isset($_POST['submit'])){
		$msg = "";
		$validate = true;
		$fname = mss($_POST['fname']);
		$lname = mss($_POST['lname']);
		$username = mysqli_real_escape_string($con, $_POST['username']);
		$email = mysqli_real_escape_string($con, $_POST['email']);
		$pw1 = $_POST['pw1'];
		$pw2 = $_POST['pw2'];
		if (!$fname || !$lname || !$username || !$email || !$pw1 || !$pw2){
			$msg = "All fields must be filled.";
			$validate = false;
		} else {
			$name = $fname . " " . $lname;
			if (!preg_match("/^[a-zA-Z]+$/", $fname) || !preg_match("/^[a-zA-Z]+$/", $lname) || strlen($name) > 80){
				$msg = "Please enter a proper name.";
				$validate = false;
			} else {
				if (!preg_match("/^[a-zA-Z0-9]+$/", $username) || strlen($username) < 6 || strlen($username) > 30){
					$msg = "Your username can only contain digits and alphabets. It must be at least 6 characters long and be a maximum of 30 characters.";
					$validate = false;
				} else {
					if (strlen($pw1) < 6 || strlen($pw2) < 6 || $pw1 != $pw2){
						$msg = "Your passwords must be matching and at least 6 characters long.";
						$validate = false;
					} else {
						$sql = "SELECT username FROM users";
						$res = mysqli_query($con, $sql);
						while ($row = mysqli_fetch_assoc($res)){
							if ($username == $row['username']){
								$msg = "Username already exists. Please use a different one.";
								$validate = false;
								break;
							}
						}
					}
				}
			}
		}
		if ($validate){
			$name = $fname . " " . $lname;
			$sql = "INSERT INTO users VALUES('', '".addslashes($name)."', '".$username."', '".md5($pw1)."', '".$email."', '', '', '', '')";
			$res = mysqli_query($con, $sql);
			if ($res){
				$_SESSION['username'] = $username;
				$location = SITE_NAME . "/coder/$username";
				header("location:$location");
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Register Account | <?php echo SITE_TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="css/extra.css" />
<link rel="stylesheet" type="text/css" href="css/code.css" />
</head>

<body>
<div id="container">
    <div id="content">
    	<div id="logo" style="border-bottom:2px solid #fff;">
        <a href="<?php echo SITE_NAME; ?>" class="logo-link"><img src="assets/logo.png" /></a>
        <?php if (isset($_SESSION['username'])) { echo "<a href=\"post.php\" class=\"npbtn\">New Post / Upload Files</a>"; } ?>
        <ul id="main-menu">
        	<?php
				if (!isset($_SESSION['username'])){
					echo "<li><a href=\"".SITE_NAME."/login.php?r=".urlencode(curPageURL())."\">Login</a></li>";
					echo "<li><a href=\"".SITE_NAME."/register.php\">Create Account</a></li>";
				}
				if (isset($_SESSION['username'])){
					echo "<li><a href=\"".SITE_NAME."/coder/$_SESSION[username]\">Profile</a></li>";
					echo "<li><a href=\"".SITE_NAME."/inbox/\">Inbox</a></li>";
				}
        		echo "<li><a href=\"".SITE_NAME."/forum/\">Forum</a></li>";
			?>
		</ul>
        </div>

		<div id="login_panel">
            <table width="100%" border="0">
            	<tr>
                	<td valign="middle"><img src="<?php echo SITE_NAME . "/assets/logo2.png"; ?>" /></td>
                	<td valign="top">
                    	<form action="register.php" method="post">
                        <div id="lpanel">
                        <h1 style="color:#666; font-size:18pt;">Register New Account</h1>
                        <?php 
							if (!empty($msg)){
								echo "<p class=\"failure\">$msg</p>";
							}
						?>
                        <p class="label">First Name</p>
                        <input type="text" name="fname" class="field" value="<?php if (isset($_POST['submit'])) { echo $_POST['fname']; } ?>" autocomplete="off" />
                        <p class="label">Last Name</p>
                        <input type="text" name="lname" class="field" value="<?php if (isset($_POST['submit'])) { echo $_POST['lname']; } ?>" autocomplete="off" />
                        <p class="label">Username</p>
                        <input type="text" name="username" class="field" value="<?php if (isset($_POST['submit'])) { echo $_POST['username']; } ?>" autocomplete="off" />
                        <p class="label">Email</p>
                        <input type="email" name="email" class="field" value="<?php if (isset($_POST['submit'])) { echo $_POST['email']; } ?>" autocomplete="off" />
                        <p class="label">Password</p>
                        <input type="password" name="pw1" class="field" />
                        <p class="label">Repeat Password</p>
                        <input type="password" name="pw2" class="field" /><br />
                        <input type="submit" name="submit" value="Register" class="lsubmit" />
                        </div>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
   </div>
</div>
</body>
</html>