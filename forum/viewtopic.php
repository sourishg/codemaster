<?php
	session_start();
	include('../includes/constants.php');
	include('../includes/connect.php');
	include('../includes/pagination.php');
	$locate = SITE_NAME . "/404.php";
	if (isset($_GET['category']) && isset($_GET['topic']) && isset($_GET['id'])) { 
		$cat = $_GET['category']; $topic = $_GET['topic']; $id = $_GET['id'];
	} else {
		header("location:$locate"); 
	}
	if (empty($_GET['category']) || empty($_GET['topic']) || empty($id)){
		header("location:$locate");
	}
	$name = getForumCategoryName($con, $cat);
	$sql = "SELECT id, title, permalink FROM topics WHERE id = $id AND category = '$name'";
	$res = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($res);
	if (mysqli_num_rows($res) == 0){
		header("location:$locate");
	} else if (stripslashes($row['permalink']) != $_GET['topic']) {
		header("location:$locate");
	} else {
		$title = stripslashes($row['title']);
	}
?>
<?php
	if (isset($_POST['submit'])){
		$comment = addslashes(trim($_POST['comment']));
		if (!$comment){
			$success = "Please care to write something.";
		} else {
			$sql = "INSERT INTO replies VALUES('', '".$id."', '".$_SESSION['username']."', '".mysqli_real_escape_string($con, $comment)."', '".mysqli_real_escape_string($con, $topic)."', NOW())";
			$res = mysqli_query($con, $sql) or die(mysqli_error());
			if ($res){
				$success = "Your reply has been posted.";
			} else {
				$success = "FAil!";
			}
		} 
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo stripslashes($title) ?> | <?php echo getForumCategoryName($con, $cat) . " Forum"; ?> | <?php echo SITE_TITLE; ?></title>
<?php include('../includes/f-head-connect.php'); ?>
<script type="text/javascript">
	function activateComment(val){
		val.style.height='100px';
		document.getElementById('submitComment').style.visibility='visible';
	}
</script>
</head>

<body>
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
        		echo "<li><a href=\"".SITE_NAME."/\">CodeMaster</a></li>";
				if (isset($_SESSION['username'])){
        			echo "<li><a href=\"".SITE_NAME."/logout.php\">Logout</a></li>";
				}
			?>
		</ul>
        </div>
        <?php
        	if (!empty($success)){
            	echo "<div id=\"notice-board\">";
            	echo "<p>$success</p>";
            	echo "</div>";
			}
        ?>
        <div id="mainContent">
        <table width="100%" border="0" style="border-collapse:collapse">
            <tr>
                <td align="left" valign="top">
                    <h3><?php echo stripslashes($title); ?></h3>
                    
                    <?php 
						$cat1 = getForumCategoryName($con, $_GET['category']);
						$sql = "SELECT id FROM main_categories WHERE name = '".addslashes($cat1)."'";
						$res = mysqli_query($con, $sql); $row = mysqli_fetch_assoc($res); $cid = $row['id'];
					?>
                    
                    <span><a href="<?php echo SITE_NAME . "/forum"; ?>" class="normal-link">Forums</a> &gt; <a href="<?php echo SITE_NAME . "/forum/category/$cid/" . urlencode($cat); ?>" class="normal-link"><?php echo getForumCategoryName($con, $cat); ?></a></span>
                </td>
                <td align="right" valign="top">
                    <form action="<?php echo SITE_NAME . "/forum/search.php"; ?>" method="get"><input placeholder="Search here..." type="text" name="q" id="search" required="required"/></form>
                </td>
            </tr>
        </table>
        <div id="repliesArea">
        	<?php
            	$sql = "SELECT id, category, user, title, content, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM topics WHERE id = $id AND category = '$name'";
				$res = mysqli_query($con, $sql) or die(mysqli_error());
				$row = mysqli_fetch_assoc($res);
				$user = $row['user'];
				$header = stripslashes($row['content']);
				$header = str_replace("\n", "<br />", $header);
				$dp = getDpThumb($con, $row['user']);
				
				echo "<table width=\"100%\" border=\"0\">";
				echo "<tr class=\"reply\">";
				if (!empty($dp)){
					echo "<td width=\"45\" style=\"padding-top:10px\"><a href=\"".SITE_NAME."/coder/$user\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
				} else {
					echo "<td width=\"45\"></td>";
				}
				echo "<td valign=\"top\" class=\"reply-container\">";
				echo "<h2><a href=\"".SITE_NAME."/coder/$user\" class=\"big-link\">$user</a></h2>";
				echo "<p><span style=\"color:#777; font-size:8pt;\">on $row[time]</span><br /><br /></p>";
				echo "<p>$header</p>";
				echo "</td>";
				echo "</tr>";
				echo "</table>";
			?>
            <?php 
				if (isset($_SESSION['username'])){
			?>
            <center><h1 style="padding:20px; font-size:18pt; font-weight:bold;">Comments (Add yours)</h1></center>
            <?php 
				} else {
					echo "<center><h1 style=\"padding:20px; font-size:18pt; font-weight:bold;\">Login to comment!</h1></center>";
				}
			?>
            <?php
			
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$per_page = 14;
				$sqla = "SELECT COUNT(*) FROM replies WHERE tid = $id";
				$resa = mysqli_query($con, $sqla);
				$rowa = mysqli_fetch_array($resa);
				$total_count = array_shift($rowa);
				
				$pagination = new Pagination($page, $per_page, $total_count);
			
            	$sql = "SELECT id, user, content, topic, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM replies WHERE tid = $id ORDER BY id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
				$res = mysqli_query($con, $sql);
				if (mysqli_num_rows($res) > 0){
					while ($row = mysqli_fetch_assoc($res)){
						$user = stripslashes($row['user']);
						$content = htmlentities($row['content']);
						$content = nl2br($content);
						$dp = getDpThumb($con, $row['user']);
						
						echo "<table width=\"100%\" border=\"0\">";
						echo "<tr class=\"reply\">";
						echo "<tr class=\"reply\">";
						if (!empty($dp)){
							echo "<td width=\"45\" style=\"padding-top:10px\"><a href=\"".SITE_NAME."/coder/$user\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
						} else {
							echo "<td width=\"45\"></td>";
						}
						echo "<td valign=\"top\" class=\"reply-container\">";
						echo "<h2><a href=\"".SITE_NAME."/coder/$user\" class=\"big-link\">$user</a></h2>";
						echo "<p><span style=\"color:#777; font-size:8pt;\">on $row[time]</span><br /><br /></p>";
						echo "<p>".stripslashes($content)."</p>";
						echo "</td>";
						echo "</tr>";
						echo "</table><br />"; 
					}
				}
			?>
            <?php
            	if ($pagination->total_pages() > 1){
					echo "<center>";
					echo "<div id=\"pagination\">";
					if ($pagination->has_previous_page()){
						echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/forum/topic/" . $_GET['id'] ."/" .urlencode($_GET['category']). "/" .urlencode($_GET['topic']). "&page=";
						echo $pagination->previous_page();
						echo "\" class=\"movers-left\">&laquo; Newer Posts</a></span>";
					}
					
					
					
					if ($pagination->has_next_page()){
						echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/forum/topic/" . $_GET['id'] ."/" .urlencode($_GET['category']). "/" .urlencode($_GET['topic']). "&page=";
						echo $pagination->next_page();
						echo "\" class=\"movers-right\">Older Posts &raquo;</a></span>";
					}
					echo "</div>";
					echo "</center>";
				}
			?>
            
            <br /><br />
            <?php 
				if (isset($_SESSION['username'])){
			?>
            <form action="<?php echo SITE_NAME . "/forum/topic/$id/".urlencode($cat)."/$_GET[topic]"; ?>" method="post">
            <table width="100%" border="0">
            	<tr>
                    <td style="padding-left:10px;"><?php echo "<span style=\"color:#000\">" . $_SESSION['username'] . "</span> - Add a comment:"; ?></td>
                </tr>
            	<tr class="reply">
                    <td valign="top" class="comment-adder">
                    <textarea placeholder="Your comment here..." name="comment" class="commenter" onfocus="activateComment(this);" required="required"></textarea>
                    </td>
                </tr>
                <tr id="submitComment">
                    <td><input type="submit" value="Add Reply" name="submit" class="inp-submit" /><input type="hidden" name="topic" value="<?php echo $topic; ?>" /></td>
                </tr>
            </table>
            </form>
            <?php } ?>
        </div>
        </div>
	</div>
</div>
</body>
</html>