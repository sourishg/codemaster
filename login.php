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
		$user = mss($con, $_POST['username']);
		$pass = md5($_POST['password']);
		$message = "";
		if ($user && $pass){
			$query = "SELECT username FROM users WHERE username = '".$user."'";
			$res = mysqli_query($con, $query);
			if (mysqli_num_rows($res) > 0){
				$query2 = "SELECT username FROM users WHERE username = '".$user."' AND password = '".$pass."'";
				$res2 = mysqli_query($con, $query2);
				if (mysqli_num_rows($res2) > 0){
					$row = mysqli_fetch_assoc($res2);
					$_SESSION['username'] = $row['username'];
					if (isset($_GET['r'])){
						$r = $_GET['r'];
						echo "<script type=\"text/javascript\">location.href=\"".$r."\";</script>";
					} else {
						echo "<script type=\"text/javascript\">location.href=\"index.php\";</script>";
					}
				} else {
					$message = "The username and password do not match.";
				}
			} else {
				$message = "The username you supplied does not exist.";
			}
		} else {
			$message = "Please enter both the username and password.";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login | <?php echo SITE_TITLE; ?></title>
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
                    	<?php
							if (isset($_GET['r'])){
								echo "<form method=\"post\" action=\"login.php?r=".urlencode($_GET['r'])."\">";
							} else {
								echo "<form method=\"post\" action=\"login.php\">";
							}
						?>
                        <div id="lpanel">
                        <h1 style="color:#666; font-size:18pt;">Sign in</h1>
                        <?php 
							if (!empty($message)){
								echo "<p class=\"failure\">$message</p>";
							}
						?>
                        <p class="label">Username</p>
                        <input type="text" name="username" class="field" value="<?php if (isset($_POST['submit'])) { echo $_POST['username']; } ?>" />
                        <p class="label">Password</p>
                        <input type="password" name="password" class="field" /><br />
                        <input type="submit" name="submit" value="Login" class="lsubmit" />
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