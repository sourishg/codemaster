<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	include('includes/pagination.php');
	if (!isset($_GET['stitle']) || !isset($_GET['title']) || !isset($_GET['snipId'])){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	} else {
		$permalink = $_GET['title'];
		$sid = getSid($con, $_GET['stitle']);
		$snippet_id = $_GET['snipId'];
	}
	if (!codeExists($con, $permalink, $snippet_id)){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
	if (isPrivate($con, $permalink, $snippet_id)){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
	if (isset($_POST['submit'])){
		$user = $_GET['poster'];
		$content = htmlentities(trim($_POST['content']));
		if (!$content){
			$message = "Please care to write something";
		} else{
			$sql = "INSERT INTO code_comments VALUES('', '".$permalink."', '".$user."', '".addslashes($content)."', NOW())";
			$res = mysqli_query($con, $sql);
		}
	}
	$sql = "SELECT id, sid, title, permalink, content, privacy, views, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets WHERE sid = $sid AND permalink = '$permalink'";
	$res = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($res);
	$views = $row['views'];
	$views++;
	$sql_update = "UPDATE code_snippets SET views = $views WHERE sid = $sid AND permalink = '$permalink'";
	$res_update = mysqli_query($con, $sql_update);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo stripslashes(getTopicName($con, $permalink, $sid)); ?> | <?php echo stripslashes(getSubjectName($con, $_GET['stitle'])); ?> | <?php echo SITE_TITLE; ?></title>
<script src="<?php echo SITE_NAME . "/js/code_rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script type="text/javascript">
	function showPoster(val){
		val.style.height = '100px';
		document.getElementById('poster').style.display = 'block';
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
                                if ($_GET['stitle'] != $row['permalink']){
                                    echo "<li><a href=\"".SITE_NAME."/subject/".$row['permalink']."\">".$row['title']."</a></li>";
                                } else {
                                    echo "<li><a href=\"".SITE_NAME."/subject/".$row['permalink']."\" class=\"selected\">".$row['title']."</a></li>";
                                }
                            }
                        ?>
                    </ul>
                </td>
                <td valign="top" id="right-bar">
                	<h2><?php echo getSubjectName($con, $_GET['stitle']); ?></h2>
					<?php
                    	$sql = "SELECT id, sid, title, permalink, content, privacy, views, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets WHERE sid = $sid AND permalink = '$permalink'";
						$res = mysqli_query($con, $sql);
						$row = mysqli_fetch_assoc($res);
						$dp = getDpThumb($con, $row['user']);
						
						$sql2 = "SELECT id FROM code_comments WHERE code_title = '$row[permalink]'";
						$res2 = mysqli_query($con, $sql2) or die(mysqli_error());
						$comments = mysqli_num_rows($res2);
					?>
                    <div id="feed-area" style="border:none; width:600px;">
                    	<div class="e-feed" style="border-bottom:none;">
                        <table width="100%" border="0" style="border-collapse:collapse">
                        	<tr>
                            	<?php
                            		if (!empty($dp)){
										echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
									}
								?>
                                <td valign="top">
                                <h3 style="font-weight:bold; color:#09f; font-size:25px;"><?php echo stripslashes($row['title']); ?></h3>
                                <span class="e-meta"><?php echo "By <a href=\"".SITE_NAME."/coder/".$row['user']."\" class=\"small-link\">".$row['user']."</a>; ".$row['time'] . " &bull; Views: $row[views]"; ?></span>
                                </td>
                            </tr>
                        </table>
						<div class="e-content"><?php echo stripslashes($row['content']); ?></div>
						</div>
                    </div>
                    <div id="response-area">
                    	<?php
							$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
							$per_page = 15;
							$sqla = "SELECT COUNT(*) FROM code_comments WHERE code_title = '$permalink' ORDER BY id DESC";
							$resa = mysqli_query($con, $sqla);
							$rowa = mysqli_fetch_array($resa);
							$total_count = array_shift($rowa);
							
							$pagination = new Pagination($page, $per_page, $total_count);
						 
							$sql = "SELECT id, code_title, user, content, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_comments WHERE code_title = '$permalink' ORDER BY id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
							$res = mysqli_query($con, $sql) or die(mysqli_error());
							$count = mysqli_num_rows($res);
						?>
                    	<h3 style="padding-left:10px; padding-top:10px;">&laquo; Responses (<?php echo number_format($count); ?>)</h3>
                        <?php if (!empty($message)) { echo "<p style=\"padding-left:0;\">$message</p>"; } ?>
                        <?php 
							if (isset($_SESSION['username'])){
						?>
                        <br />
                        <form action="<?php echo SITE_NAME . "/"; ?>topic/<?php echo $_GET['stitle']; ?>/<?php echo $_GET['snipId']; ?>/<?php echo $permalink; ?>&amp;poster=<?php echo $_SESSION['username']; ?>" method="post">
                        <textarea name="content" placeholder="Your comment..." class="comm-text-box" onfocus="showPoster(this);" required="required" style="border-left:none; border-right:none; width:490px;"></textarea><br />
                        <input type="submit" value="Add" name="submit" class="inp-submit" style="margin-left:0; display:none; padding-left:30px; padding-right:30px;" id="poster"/>
                        </form>
                        <?php
                        	}
						?>
						<?php 
							if ($count == 0){
								echo "<p class=\"imp\" style=\"border:1px solid #ccc;\">No responses yet.";
								if (isset($_SESSION['username'])){
									echo " Be the first to respond.</p>";
								} else {
									echo " Log in to respond.</p>";
								}
							} else {
								while ($row = mysqli_fetch_assoc($res)){
									$dp = getDpThumb($con, $row['user']);
									echo "<div class=\"response\">";
									echo "<table width=\"100%\" border=\"0\" style=\"border-collapse:collapse;\">";
									echo "<tr>";
									if (!empty($dp)){
										echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
									}
									echo "<td valign=\"top\">";
									echo "<span style=\"font-size:12pt;\">" . stripslashes(nl2br($row['content'])) . "</span>";
									echo "<br /><span style=\"color:#666; font-size:8pt;\">by <a href=\"".SITE_NAME."/coder/".$row['user']."\" class=\"small-link\">".$row['user']."</a></span><br /><span style=\"color:#666; font-size:8pt;\">on $row[time]</span>";
									echo "</td>";
									echo "</tr>";									
									echo "</table>";
									echo "</div>";
								}
							}
							if ($pagination->total_pages() > 1){
								echo "<center>";
								echo "<div id=\"pagination\">";
								if ($pagination->has_previous_page()){
									echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/topic/$_GET[stitle]/$_GET[snipId]/$_GET[title]&page=";
									echo $pagination->previous_page();
									echo "\" class=\"movers-left\">&laquo; Newer Comments</a></span>";
								}							
								if ($pagination->has_next_page()){
									echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/topic/$_GET[stitle]/$_GET[snipId]/$_GET[title]&page=";
									echo $pagination->next_page();
									echo "\" class=\"movers-right\">Older Comments &raquo;</a></span>";
								}
								echo "</div>";
								echo "</center>";
							}
						?>
                    </div>
                </td>
            </tr>
        </table>        
        </div>
    </div>
</div>
</body>
</html>