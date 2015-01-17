<?php
	session_start();
	include('../includes/constants.php');
	include('../includes/connect.php');
	include('../includes/pagination.php');
	$locate = SITE_NAME . "/404.php";
	if (isset($_GET['category']) && isset($_GET['id'])) { $cat = $_GET['category']; $id = $_GET['id']; } else { header("location:$locate"); }
	if (empty($cat) || empty($id)){
		header("location:$locate");
	}
	if (!mainFTopicExists($con, $cat) || !mainFIDExists($con, $id, $cat)){
		header("location:$locate");
	}
		
?>
<?php 
	if (isset($_POST['submit'])){
		$success = "";
		$user = $_SESSION['username'];
		$title_head = $_POST['name']; $permalink = createCleanTitle($title_head); $title_head = addslashes(htmlentities(trim($title_head)));
		$content = $_POST['content']; $content = addslashes(trim($content));
		$cat = mss($con, $_GET['category']);
		$id = $_GET['id'];
		 
		if (!trim($title_head)){
			$fail = "Please enter a title";
		} else {
			if (!$content){
				$fail = "Please enter some content.";
			} else {	
				if (strlen($title_head)> 80){
					$fail = "Your title should be less than 80 characters.";
				} else {
					$sql = "INSERT INTO topics VALUES('', '$_GET[id]', '".mysqli_real_escape_string($con, getForumCategoryName($con, $cat))."', '".$user."', '".mysqli_real_escape_string($con, $title_head)."', '".$permalink."', '".mysqli_real_escape_string($con, $content)."', NOW())";
					$res = mysqli_query($con, $sql) or die(mysqli_error());
					if ($res){
						$success = "Your Forum topic has been successfully created.";
					} else {
						$success = "Fail!";
					}
				}
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo getForumCategoryName($con, $cat) . " Forum"; ?> | <?php echo SITE_TITLE; ?></title>
<?php include('../includes/f-head-connect.php'); ?>
<script type="text/javascript">
	function showCreator(val){
		val.style.display='none';
		document.getElementById('create-topic').style.display='block';
	}
	function hideCreator(){
		document.getElementById('create-topic').style.display='none';
		document.getElementById('stThrd').style.display = 'inline-block';
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
				echo "<p><img src=\"".SITE_NAME."/assets/tick.png\" width=\"20\" height=\"20\" /> $success</p>";
				echo "</div>";
			} else {
				if (!empty($fail)){
					echo "<div id=\"notice-board\" style=\"color:#900;\">";
					echo "<p><u>$fail</u></p>";
					echo "</div>";
				}
			}
		?>
        <div id="mainContent">
        <table width="100%" border="0" style="border-collapse:collapse">
            <tr>                
                <td align="left" valign="top">
                    <h3><?php echo getForumCategoryName($con, $cat) . " Forum"; ?></h3>
                    <span><a href="<?php echo SITE_NAME . "/forum"; ?>" class="normal-link">Forums</a> &gt; <a href="<?php echo SITE_NAME . "/forum/category/$id/" . urlencode($cat); ?>" class="normal-link"><?php echo getForumCategoryName($con, $cat); ?></a></span>
                </td>
                <td align="right" valign="top">
                    <form action="<?php echo SITE_NAME . "/forum/search.php"; ?>" method="get"><input placeholder="Search here..." type="text" name="q" id="search" required="required"/></form>
                </td>
            </tr>
        </table>
        <?php 
			$name = getForumCategoryName($con, $cat);
            $sql = "SELECT * FROM topics WHERE category = '$name'";
            $res = mysqli_query($con, $sql);
            if (mysqli_num_rows($res) > 0) {
        ?>
        <div id="list">
        <table width="100%" border="0" style="border-collapse:collapse;">
            <tr>
                <td width="50%" style="padding:8px;">TOPIC</td>
                <td width="12%" align="left" valign="middle" style="padding:8px;">STARTED BY</td>
                <td width="12%" align="right" valign="middle" style="padding:8px;">REPLIES</td>
                <td width="1%" style="background:#E3EBCA;"></td>
                <td width="25%" valign="middle" style="padding:8px;">LATEST REPLY</td>
            </tr>
                        <?php
						
							$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
							$per_page = 16;
							$sqla = "SELECT COUNT(*) FROM topics WHERE category = '".$cat."' AND cid = '".$id."'";
							$resa = mysqli_query($con, $sqla);
							$rowa = mysqli_fetch_array($resa);
							$total_count = array_shift($rowa);
							
							$pagination = new Pagination($page, $per_page, $total_count);
							
                            $sql = "SELECT id, title, category, user, permalink, content, date_format(date, '%d/%m/%Y - %l.%i %p') as time FROM topics WHERE category = '$name' AND cid = '".$id."' ORDER BY id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
                            $res = mysqli_query($con, $sql);
                            while ($row = mysqli_fetch_assoc($res)){
                                $title = stripslashes($row['title']);
								$permalink = $row['permalink'];
                                $user = stripslashes($row['user']);
								if (strlen(trim($row['content'])) > 60){
									$desc = trim(stripslashes(substr($row['content'], 0, 60))) . "...";
								} else {
									$desc = trim(stripslashes($row['content']));
								}
								
								$sql2 = "SELECT * FROM replies WHERE topic = '".$row['permalink']."'";
								$res2 = mysqli_query($con, $sql2) or die(mysqli_error());
								$row2 = mysqli_fetch_assoc($res2);
								
								$replies = 0;
								$replies = mysqli_num_rows($res2);
								
								
								$sql3 = "SELECT user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM replies WHERE topic = '".$row['permalink']."' ORDER BY id DESC LIMIT 1";
								$res3 = mysqli_query($con, $sql3);
								$row3 = mysqli_fetch_assoc($res3);
								
								$dp_thumb = getDpThumb($con, $user);
                                
                                echo "<tr class=\"list-cat\">";
                                echo "<td width=\"50%\" class=\"left-curve\"><a href=\"".SITE_NAME."/coder/$user\"><img src=\"".SITE_NAME."$dp_thumb\" style=\"float:left; padding-right:10px;\" /></a><span class=\"big-text\"><a href=\"".SITE_NAME."/forum/topic/$row[id]/".urlencode($cat)."/" . urlencode($permalink). "\" title=\"View all replies under &quot;$title&quot;\">$title</a></span><br />".$desc."</td>";
                                echo "<td width=\"12%\" align=\"left\" valign=\"middle\"><a href=\"".SITE_NAME."/coder/$user\" class=\"small-link\">$user</a><br /><span style=\"font-size:10pt;\">on $row[time]</span></td>";
                                echo "<td width=\"12%\" align=\"right\" valign=\"middle\" class=\"right-curve\">".number_format($replies)."</td>";
                                echo "<td style=\"background:#E3EBCA;\" width=\"1%\"></td>";
                                echo "<td class=\"full-curve\" width=\"25%\"><a href=\"".SITE_NAME."/coder/$row3[user]\" class=\"small-link\">$row3[user]</a><br />$row3[time]</td>";
                                echo "</tr>";
                            }
                        ?>
        </table>
        </div>
        <?php
			if ($pagination->total_pages() > 1){
				echo "<center>";
				echo "<div id=\"pagination\">";
				if ($pagination->has_previous_page()){
					echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/forum/category/" . $id ."/" .urlencode($_GET['category']). "&page=";
					echo $pagination->previous_page();
					echo "\" class=\"movers-left\">&laquo; Newer Posts</a></span>";
				}
				if ($pagination->has_next_page()){
					echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/forum/category/" . $id ."/" .urlencode($_GET['category']). "&page=";
					echo $pagination->next_page();
					echo "\" class=\"movers-right\">Older Posts &raquo;</a></span>";
				}
				echo "</div>";
				echo "</center>";
			}
		?>
        <?php
            }
        ?>
        <?php 
            if (isset($_SESSION['username'])){
        ?>
        <div class="start">
        <center><input type="submit" value="Start Thread" name="submit" class="inp-submit" onclick="showCreator(this);" id="stThrd" /></center>
        </div>
        <div id="create-topic">
            <h2>NEW FORUM THREAD</h2>
            <form action="<?php echo SITE_NAME . "/forum/category/$id/" . urlencode($cat); ?>" method="post">
            <table border="0" width="100%">
                <tr>
                    <td width="20%" valign="middle" align="right" style="padding-right:10px;"><b>SUBJECT</b></td>
                    <td width="80%" valign="top"><input type="text" name="name" id="inp-name" required="required"/></td>
                </tr>
                <tr>
                    <td width="20%" valign="top" align="right" style="padding-right:10px; padding-top:10px;"><b>BODY</b></td>
                    <td width="80%" valign="top"><textarea placeholder="Write your content here..." name="content" id="inp-content" required="required"></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td width="80%" valign="top"><input type="submit" name="submit" value="Create" class="inp-submit"/><input type="button" name="submit" value="Cancel" class="inp-submit" onclick="hideCreator();" /></td>
                </tr>
            </table>
            </form>
        </div>
        <?php
            }
        ?>
        </div>
    </div>
</div>
</body>
</html>