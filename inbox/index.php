<?php
	session_start();
	include('../includes/constants.php');
	include('../includes/connect.php');
	include('../includes/code_functions.php');
	include('../includes/pagination.php');
	$locate = SITE_NAME . "/404.php";
	if (!isset($_SESSION['username'])){
		header("location:$locate");
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
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value;
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
                    <h2><?php echo "Your Inbox"; ?></h2>
                    <?php 
						$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
						$per_page = 10;
						$sqla = "SELECT COUNT(*) FROM pm WHERE receiver = '$_SESSION[username]'";
						$resa = mysqli_query($con, $sqla);
						$rowa = mysqli_fetch_array($resa);
						$total_count = array_shift($rowa);
						
						$pagination = new Pagination($page, $per_page, $total_count);
					
						$sql = "SELECT id, sender, receiver, message, date_format(date, '%d, %M') as time, seen FROM pm WHERE receiver = '$_SESSION[username]' ORDER BY seen ASC, id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
						$res = mysqli_query($con, $sql);
						if (mysqli_num_rows($res) > 0){
							$sql2 = "SELECT seen FROM pm WHERE receiver = '$_SESSION[username]' AND seen = 0";
							$res2 = mysqli_query($con, $sql2);
							$unread = mysqli_num_rows($res2);
							echo "<p style=\"font-family:Tahoma; font-size:12pt;\">You have $unread unread messages.</p>"; 
														
							echo "<table id=\"pmArea\" style=\"border-collapse:collapse;\">";
							while ($row = mysqli_fetch_assoc($res)){
								$content = substr(trim($row['message']), 0, 50) . " ...";
								if ($row['seen'] == 0){
									echo "<tr class=\"unread\">";
									echo "<td class=\"sender\"><a href=\"".SITE_NAME."/coder/$row[sender]\">".getUserName($con, $row['sender'])."</a></td>";
									echo "<td class=\"pmContent\"><a href=\"".SITE_NAME."/inbox/viewmsg.php?id=$row[id]\" style=\"color:#000; font-weight:bold;\">".htmlentities(stripslashes($content))."</a></td>";
									echo "<td align=\"right\" class=\"pmTime\">".$row['time']."</td>";
									echo "</tr>";
								} else {
									echo "<tr class=\"read\">";
									echo "<td class=\"sender\"><a href=\"".SITE_NAME."/coder/$row[sender]\">".getUserName($con, $row['sender'])."</a></td>";
									echo "<td class=\"pmContent\"><a href=\"".SITE_NAME."/inbox/viewmsg.php?id=$row[id]\">".htmlentities(stripslashes($content))."</a></td>";
									echo "<td align=\"right\" class=\"pmTime\">".$row['time']."</td>";
									echo "</tr>";
								}								
							}
							echo "</table>";
						} else {
							echo "<h3 style=\"padding:10px;\">You don't have any messages in your inbox!</h2>";
						}
					?>                    
                </td>
            </tr>
        </table>        
        </div>
    </div>
</div>
</body>
</html>