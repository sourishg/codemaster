<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	include('includes/pagination.php');
	if (!isset($_SESSION['username'])){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
?>
<?php
	if (isset($_POST['submit'])){
		$result = ""; $updation = true;
		$name = addslashes(trim($_POST['name']));
		$email = addslashes(htmlspecialchars(trim($_POST['email'])));
		$interests = addslashes(htmlentities(trim($_POST['interests'])));
		$bio = addslashes(htmlentities(trim($_POST['bio'])));
		$pw1 = $_POST['pw1'];
		$pw2 = $_POST['pw2'];
		if (!$name || !$email){
			$result = "&quot;Name&quot; &amp; &quot;Email&quot; cannot be left blank.";
			$updation = false;
		} else {
			$name_pattern = "/^[a-z A-Z]+$/";
			if (!preg_match($name_pattern, $name)){
				$result = "Please enter a valid name.";
				$updation = false;
			} else {
				if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email)){
					$result = "Please enter a valid email.";
					$updation = false;
				}
			}
		}
		if (strlen($name) > 80 || strlen($email) > 65){
			$result = "&quot;Name&quot; or &quot;Email&quot; is too long.";
			$updation = false;
		}
		if (!empty($pw1) || !empty($pw2)){
			if (strlen($pw1) < 6 || strlen($pw2) < 6){
				$result = "Your password must be at least 8 characters long.";
				$updation = false;
			} else {
				if ($pw1 != $pw2){
					$result = "Both the passwords must be matching.";
					$updation = false;
				}
			}
		}
		if ($updation){
			$sql = "UPDATE users SET name = '$name'";
			if ($pw1){
				$sql .= ", password = '".md5($pw1)."'";
			}
			$sql .= ", email = '$email'";
			if (!empty($interests)){
				$sql .= ", interests = '$interests'";
			} else if (empty($interests)){
				$sql .= ", interests = ''";
			}
			if (!empty($bio)){
				$sql .= ", bio = '$bio'";
			} else if (empty($bio)){
				$sql .= ", bio = ''";
			}
			$sql .= " WHERE username = '$_SESSION[username]'";
			$res = mysqli_query($con, $sql);
			if ($res){
				$result = "Profile update successfully.";
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Edit Profile | <?php echo SITE_TITLE; ?></title>
<script src="<?php echo SITE_NAME . "/js/code_rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script type="text/javascript">
	function showPM(val){
		val.style.display = 'none';
		document.getElementById('pm-area').style.display = 'block';
	}
</script>
<script type="text/javascript">
	function confirmDelete(value){
		var conf = confirm("Are you sure you want to delete this entry?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value;
		}
	}
	function confirmStashDelete(value){
		var conf = confirm("Are you sure you want to delete this entry?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value + "&ref=stash";
		}
	}
	function confirmDpDelete(value){
		var conf = confirm("Are you sure you want to delete your picture?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value + "&ref=dp";
		}
	}
</script>
<script type="text/javascript" src="<?php echo SITE_NAME; ?>/js/message.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
</head>

<body onload="iFrameOn();">
<div id="lightbox">
	<h1>Your message</h1><a href="#" onclick="closePM()">Close Dialog</a>
    <textarea id="pmessage" name="pmessage"></textarea><br />
    <input type="submit" name="submit" id="psubmit" value="Send" onclick="validatePM();" class="inp-submit" style="margin-left:0;" />
    <div id="pmRes"></div>
</div>
<div id="container">
    <div id="content">
    	<div id="logo">
    	<a href="<?php echo SITE_NAME; ?>" class="logo-link"><img src="<?php echo SITE_NAME; ?>/assets/logo.png" /></a>
        <?php if (isset($_SESSION['username'])) { echo "<a href=\"".SITE_NAME."/post.php\" class=\"npbtn\">New Post / Upload Files</a>"; } ?>
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
				if (isset($_SESSION['username'])){
        			echo "<li><a href=\"".SITE_NAME."/logout.php\">Logout</a></li>";
				}
			?>
		</ul>
        </div>
        
        <div id="displayContent">
        <table width="100%" border="0" style="border-collapse:collapse; border-top:1px solid #333;">
        	<tr>
            	<td width="200px" valign="top" id="left-bar">
                    <center><img src="<?php echo SITE_NAME; ?>/assets/logo2.png" /></center>
                    <p style="padding:10px; color:#eee; font-family:Verdana, Geneva, sans-serif;">
                    <?php
						if (isset($_SESSION['username'])){
							echo "Welcome, $_SESSION[username].<br /><b>YOUR STATS:</b><br />";
							$sql = "SELECT id FROM code_snippets WHERE user = '$_SESSION[username]'";
							$res = mysqli_query($con, $sql);
							$total_posts = mysqli_num_rows($res);
							$sql = "SELECT id FROM code_stash WHERE user = '$_SESSION[username]'";
							$res = mysqli_query($con, $sql);
							$total_stash = mysqli_num_rows($res);
							$sql = "SELECT id FROM code_comments WHERE user = '$_SESSION[username]'";
							$res = mysqli_query($con, $sql);
							$total_comments = mysqli_num_rows($res);
							echo "<span style=\"color:#999; font-size:8pt; text-transform:uppercase;\">Posts:</span> <b>$total_posts</b><br />";
							echo "<span style=\"color:#999; font-size:8pt; text-transform:uppercase;\">Files in stash:</span> <b>$total_stash</b><br />";
							echo "<span style=\"color:#999; font-size:8pt; text-transform:uppercase;\">Comments:</span> <b>$total_comments</b><br />";
						} else {
					?>
							Welcome to CodeMaster. Share your code with the world.
					<?php
						}
					?>
                    </p>
                    <h3>Pick a Language</h3>
                    <ul id="main-nav">
						<?php 
                            $sql = "SELECT * FROM code_subjects";
                            $res = mysqli_query($con, $sql);
                            while ($row = mysqli_fetch_assoc($res)){
                            	echo "<li><a href=\"".SITE_NAME."/subject/".$row['permalink']."\">".$row['title']."</a></li>";
                            }
                        ?>
                    </ul>
                </td>
                <td valign="top" id="right-bar">
                    <h2><?php echo "Edit your profile"; ?></h2>
                    <?php
                    	if (!empty($result)) { echo "<p class=\"success\" style=\"font-size:13pt; color:#f00;\">$result</p>"; }
					?>
                    <?php
                    	$sql = "SELECT * FROM users WHERE username = '$_SESSION[username]'";
						$res = mysqli_query($con, $sql);
						$row = mysqli_fetch_assoc($res);
					?>
                    <form action="edit.php" method="post">
                    <table width="600" border="0" id="reg">
                    	<tr>
                        	<td class="label">Name:</td>
                            <td><input name="name" type="text" class="field" value="<?php if (!empty($row['name'])) echo stripslashes($row['name']) ?>" /></td>
                        </tr>
                        <tr>
                        	<td class="label">Email:</td>
                            <td><input name="email" type="text"  class="field" value="<?php if (!empty($row['email'])) echo stripslashes($row['email']) ?>" /></td>
                        </tr>
                        <tr>
                        	<td class="label">Interests:</td>
                            <td><input name="interests" type="text" class="field" value="<?php if (!empty($row['interests'])) echo stripslashes($row['interests']) ?>" /></td>
                        </tr>
                        <tr>
                        	<td class="label">Bio:</td>
                            <td><textarea name="bio"><?php if (!empty($row['bio'])) echo stripslashes($row['bio']) ?></textarea></td>
                        </tr>
                        <tr>
                        	<td class="label">New Password:</td>
                            <td><input name="pw1" type="password" class="field" /></td>
                        </tr>
                        <tr>
                        	<td class="label">Repeat Password:</td>
                            <td><input name="pw2" type="password" class="field" /></td>
                        </tr>
                        <tr>
                        	<td></td>
                            <td><input name="submit" type="submit" value="UPDATE" class="inp-submit" style="margin-left:0;" /></td>
                        </tr>
                    </table>
                    </form>
                </td>
            </tr>
        </table>        
        </div>
    </div>
</div>
</body>
</html>