<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	include('includes/pagination.php');
	if (!isset($_GET['stitle'])){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
	if (!subjectExists($con, $_GET['stitle'])){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
	$sid = getSid($con, $_GET['stitle']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo stripslashes(getSubjectName($con, $_GET['stitle'])); ?> | <?php echo SITE_TITLE; ?></title>
<script src="<?php echo SITE_NAME . "/js/code_rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script type="text/javascript">
	var xhr;
	var url;
	function ajax(){
		if (window.ActiveXObject){
			xhr = new ActiveXObject("Msxml2.XMLHTTP");
		} else if (window.XMLHttpRequest){
			xhr = new XMLHttpRequest();
		}
	}
	function ajaxSearch(sid){
		var ID = sid;
		ajax();
		if (validateSearch()){
			document.getElementById("srch-results").style.display = "block";
			document.getElementById("srch-results").innerHTML = "Loading...";
			var s = document.getElementById("searchField").value;
			function stateChanged(){
				if (xhr.readyState == 4){
					document.getElementById("srch-results").innerHTML = xhr.responseText;
				}
			}
			url = "<?php echo SITE_NAME; ?>/searchsub.php?k=" + s + "&sid=" + ID;
			xhr.onreadystatechange = stateChanged;
			xhr.open("GET", url, true);
			xhr.send(null);
		}
		
	}
	function validateSearch(){
		var validate = true;
		var s = document.getElementById("searchField").value;
		if (s.trim() == null || s.trim() == ""){
			validate = false;
		}
		return validate;
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
					<?php
                        $sql = "SELECT * FROM code_subjects WHERE id = $sid";
                        $res = mysqli_query($con, $sql) or die(mysqli_error());
                        $row = mysqli_fetch_assoc($res);
                    ?>
                    <h2><?php echo "All snippets and codes under <span style=\"color:#f90;\">&lsquo;" . $row['title'] . "&rsquo;</span>"; ?></h2>
                    <?php
						$sql = "SELECT * FROM code_snippets WHERE sid = $sid";
						$res = mysqli_query($con, $sql);
						if (mysqli_num_rows($res) == 0){
                        	echo "<p class=\"imp\">No code/snippets have been written yet.</p>";
						} else {
							$count = mysqli_num_rows($res);
							echo "<p class=\"imp\">Total snippets under this topic: <b>".number_format($count)."</b></p>";
						}
                    ?>
                    <?php 
						$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
						$per_page = 10;
						$sqla = "SELECT COUNT(*) FROM code_snippets WHERE sid = $sid";
						$resa = mysqli_query($con, $sqla);
						$rowa = mysqli_fetch_array($resa);
						$total_count = array_shift($rowa);
						
						$pagination = new Pagination($page, $per_page, $total_count);
					
						$sql = "SELECT id, sid, title, permalink, privacy, views, content, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets WHERE sid = $sid ORDER BY views DESC, id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
						$res = mysqli_query($con, $sql);
						if (mysqli_num_rows($res) > 0){
							echo "<div id=\"feed-area\" style=\"border-left:none; width:700px;\">";
							echo "<h3 style=\"padding-bottom:0;\">Local Feed</h3>";
							if (hasPrivateCodes($con, $_GET['stitle'])) { echo "<span style=\"padding-left:10px; color:#666; font-size:8pt;\">(There are private codes under this subject)</span><br />"; };
							echo "<br />";
							while ($row = mysqli_fetch_assoc($res)){
								$dp = getDpThumb($con, $row['user']);
								
								$sql2 = "SELECT id FROM code_comments WHERE code_title = '$row[permalink]'";
								$res2 = mysqli_query($con, $sql2) or die(mysqli_error());
								$comments = mysqli_num_rows($res2);
								
								if ($row['privacy'] == 0){
									echo "<div class=\"e-feed\">";
									echo "<table border=\"0\">";
									echo "<tr>";
									if (!empty($dp)){
										echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
									}
									echo "<td valign=\"top\">";
									echo "<h3><a href=\"".SITE_NAME."/topic/".$_GET['stitle']."/".$row['id']."/".$row['permalink']."\" class=\"title\">".stripslashes($row['title'])."</a></h3>";
									echo "<span class=\"e-meta\">By <a href=\"".SITE_NAME."/coder/".$row['user']."\" class=\"small-link\">".$row['user']."</a>; ".$row['time']." &bull; Views: $row[views] &bull; Comments: $comments</span>";
									echo "</td>";
									echo "</tr>";
									echo "</table>";
									echo "<div class=\"e-content\">".stripslashes($row['content'])."</div>";
									echo "</div>";
								} else {
									if (isset($_SESSION['username'])){
										if ($_SESSION['username'] == "$row[user]"){
											echo "<div class=\"e-feed\">";
											echo "<table border=\"0\">";
											echo "<tr>";
											if (!empty($dp)){
												echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
											}
											echo "<td valign=\"top\">";
											echo "<h3><a href=\"".SITE_NAME."/topic/".$_GET['stitle']."/".$row['id']."/".$row['permalink']."\" class=\"title\">".stripslashes($row['title'])."</a></h3>";
											echo "<span class=\"e-meta\">By <a href=\"".SITE_NAME."/coder/".$row['user']."\" class=\"small-link\">".$row['user']."</a>; ".$row['time']." &bull; Views: $row[views] &bull; Comments: $comments</span>";
											echo "</td>";
											echo "</tr>";
											echo "</table>";
											echo "<div class=\"e-content\">".stripslashes($row['content'])."</div>";
											echo "</div>";
										}
									}
								}
							}
							if ($pagination->total_pages() > 1){
								echo "<center>";
								echo "<div id=\"pagination\">";
								if ($pagination->has_previous_page()){
									echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/subject/$_GET[stitle]&page=";
									echo $pagination->previous_page();
									echo "\" class=\"movers-left\">&laquo; Newer Posts</a></span>";
								}							
								if ($pagination->has_next_page()){
									echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/subject/$_GET[stitle]&page=";
									echo $pagination->next_page();
									echo "\" class=\"movers-right\">Older Posts &raquo;</a></span>";
								}
								echo "</div>";
								echo "</center>";
							}
							echo "</div>";
							echo "<div id=\"g-stats\">";
							echo "<div id=\"srch\" style=\"border-top:none;\">";
							echo "<h3>Search this subject</h3>";
							echo "<input type=\"text\" placeholder=\"Search here...\" name=\"k\" class=\"srch-fld\" id=\"searchField\" /><input type=\"submit\" value=\"Search\" name=\"submit\" onclick=\"ajaxSearch($sid)\" class=\"inp-submit\" id=\"srch-submit\" style=\"border:1px solid #222;\" />";
							echo "</div>";
							
							echo "<div id=\"srch-results\"></div>";
							echo "</div>";
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