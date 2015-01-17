<?php
	session_start();
	include('../includes/constants.php');
	include('../includes/connect.php');
	include('../includes/code_functions.php');
	include('../includes/pagination.php');
	$locate = SITE_NAME . "/404.php";
	if (!isset($_SESSION['username']) || !isset($_GET['id']) || !messageOnlyForMe($con, $_GET['id'], $_SESSION['username'])){
		header("location:$locate");
	}
	updateSeen($con, $_GET['id']);
	$success = false;
	$fail = false;
	$id = $_GET['id'];
    $sql = "SELECT id, sender, receiver, message, date_format(date, '%D %M, %Y - %r') as time, seen FROM pm WHERE receiver = '$_SESSION[username]' AND id = $id";
	$res = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($res);
?>
<?php 
	if (isset($_POST['submit'])){		
		$content = addslashes(trim($_POST['message']));
		if (!$content){
			$fail = true;
		} else {
			$sender = $_SESSION['username'];
			$receiver =  $row['sender'];
			$sql = "INSERT INTO pm VALUES('', '".$sender."', '".$receiver."', '".addslashes($content)."', NOW(), '0')";
			$res = mysqli_query($con, $sql);
			if ($res){
				$success = true;
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>INBOX | <?php echo SITE_TITLE; ?></title>
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
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value + '&ref=msg';
		}
	}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
</head>

<body onload="iFrameOn();">
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
                	<?php
						$logged_in = mysqli_real_escape_string($con, $_SESSION['username']);
						if (is_numeric($_GET['id'])){
							$id = $_GET['id'];
						}
                    	$sql = "SELECT id, sender, receiver, message, date_format(date, '%D %M, %Y - %r') as time, seen FROM pm WHERE receiver = '$logged_in' AND id = '$id'";
						$res = mysqli_query($con, $sql);
						$row = mysqli_fetch_assoc($res);
						$content = htmlentities($row['message']);
						$content = nl2br($content);
						$dp = getDpThumb($con, $row['sender']);
                    	echo "<h2>Message from ".getUserName($con, $row['sender'])."</h2>";
						if ($success){
							echo "<p class=\"success\" style=\"font-size:13pt; color:#294D2E;\">Your reply has been sent successfully.</p>";
						} else if ($fail){
							echo "<p class=\"success\" style=\"font-size:13pt; color:#f00;\">Please enter some content.</p>";
						}
						echo "<table border=\"0\" style=\"padding-left:10px; padding-top:10px;\">";
						echo "<tr>";
						if (!empty($dp)){
							echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[sender]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
						}
						echo "<td valign=\"top\">";
						echo "<p style=\"padding:0;\">".stripslashes($content)."<br /><br /><span style=\"color:#666; font-size:8pt;\">".$row['time']."</span></p>";
						echo "</td>";
						echo "</table>";
						echo "<div id=\"pmCont\">";
						echo "<ul>";
						echo "<li><a href=\"#\" onclick=\"confirmDelete($row[id]);\">Delete Message</a></li>";
						echo "<li><a href=\"".SITE_NAME."/inbox/\">Back to Inbox</li>";
						echo "<li><a href=\"#reply\" onclick=\"showReply()\">Reply</a></li>";
						echo "</ul>";
						echo "</div>";
					?>
                    <div id="pmReply">
                    	<form action="<?php echo SITE_NAME . "/inbox/viewmsg.php?id=$_GET[id]" ?>" method="post">
                        <p><b>MESSAGE:</b><br />
                        <?php
                    		print date('D, d M Y');
                		?>
                        </p>
                        <a name="reply"></a>
                        <p style="padding-bottom:0;"><textarea name="message" id="message" placeholder="Your message..."></textarea></p>
                        <input type="submit" value="Send Message" name="submit" class="inp-submit" id="pmSubmit" />
                        </form>
                    </div>                   
                </td>
            </tr>
        </table>        
        </div>
    </div>
</div>
<script type="text/javascript">
	function showReply(){
		if (document.getElementById("pmReply").style.display == "block"){
			document.getElementById("pmReply").style.display = "none";
		} else {
			document.getElementById("pmReply").style.display = "block";
		}
	}
</script>
</body>
</html>