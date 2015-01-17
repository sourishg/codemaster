<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/pagination.php');
	include('includes/code_functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo SITE_TITLE; ?></title>
<script src="<?php echo SITE_NAME . "/js/rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/charts.js"; ?>" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
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
	function ajaxSearch(){
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
			url = "search.php?k=" + s;
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
	function Reload(){
		var val = document.getElementById("sBy").value;
		window.location.href = "<?php echo SITE_NAME; ?>/?order=" + val;
	}
</script>
</head>

<body>
<div id="container">
    <div id="content">
    	<div id="logo">
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
				if (isset($_SESSION['username'])){
        			echo "<li><a href=\"".SITE_NAME."/logout.php\">Logout</a></li>";
				}
			?>
		</ul>
        </div>
        
        <div id="displayContent">
        <table width="100%" border="0" style="border-collapse:collapse;">
        	<tr>
            	<td width="200px" valign="top" id="left-bar">
                <center><img src="assets/logo2.png" /></center>
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
					$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
					$per_page = 15;
					$sqla = "SELECT COUNT(*) FROM code_snippets";
					$resa = mysqli_query($con, $sqla);
					$rowa = mysqli_fetch_array($resa);
					$total_count = array_shift($rowa);
					
					$pagination = new Pagination($page, $per_page, $total_count);
					
					if (isset($_GET['order']) && $_GET['order'] == "top"){
						$sql = "SELECT id, sid, title, permalink, privacy, views, content, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets ORDER BY views DESC, id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";						
					}
					else {
						$sql = "SELECT id, sid, title, permalink, privacy, views, content, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets ORDER BY id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
					}
					$res = mysqli_query($con, $sql); 
					$total = mysqli_num_rows($res);
					if (mysqli_num_rows($res) > 0){
						echo "<div id=\"feed-area\" style=\"border-left:none; width:685px;\">";
						echo "<h3 style=\"padding-bottom:0; padding-left:15px; display:inline-block;\">Global Feed</h3>";
						echo "<select id=\"sBy\" onchange=\"Reload()\">";
						if (isset($_GET['order']) && $_GET['order'] == "rec"){
							echo "<option value=\"rec\">Recent Posts</option>
							<option value=\"top\">Top Posts</option>";
						} else if (isset($_GET['order']) && $_GET['order'] == "top"){
							echo "<option value=\"top\">Top Posts</option>
							<option value=\"rec\">Recent Posts</option>";
						} else {
							echo "<option value=\"rec\">Recent Posts</option>
							<option value=\"top\">Top Posts</option>";
						}
						echo "</select>";
						echo "<br />";
						while ($row = mysqli_fetch_assoc($res)){
							$stitle = getSubjectPermalink($con, $row['sid']);
							$subject = getSubjectName($con, getSubjectPermalink($con, $row['sid']));
							$dp = getDpThumb($con, $row['user']);
							
							if ($row['privacy'] == 0){
								echo "<div class=\"e-feed\" style=\"padding-left:15px; padding-top:10px;\">";
								echo "<table width=\"100%\" border=\"0\" style=\"border-collapse:collapse;\">";							
								if (!empty($dp)){
									echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
								}
								echo "<td valign=\"top\">";
								
								$sql2 = "SELECT id FROM code_comments WHERE code_title = '$row[permalink]'";
								$res2 = mysqli_query($con, $sql2);
								$comments = mysqli_num_rows($res2);
								
								echo "<h3><a href=\"".SITE_NAME."/topic/".$stitle."/".$row['id']."/".$row['permalink']."\" class=\"title\">".stripslashes($row['title'])."</a></h3>";
								echo "<span class=\"e-meta\">By <a href=\"".SITE_NAME."/coder/".$row['user']."\" class=\"small-link\">".$row['user']."</a>; ".$row['time']." &bull; Views: " .$row['views']. " &bull; Comments:".$comments."</span>";
								echo "</td>";
								echo "<td valign=\"middle\" align=\"right\">";
								echo "<a href=\"".SITE_NAME."/subject/".getSubjectPermalink($con, $row['sid'])."\" class=\"s-link\">" . $subject . "</a>";
								echo "</td>";
								echo "</tr>";
								echo "</table>";
								echo "<div class=\"e-content\">".stripslashes($row['content'])."</div>";
								echo "</div>";
							} else {
								if (isset($_SESSION['username'])){
									if ($_SESSION['username'] == "$row[user]"){
										echo "<div class=\"e-feed\" style=\"padding-left:15px; padding-top:10px;\">";
										echo "<table width=\"100%\" border=\"0\" style=\"border-collapse:collapse;\">";
										if (!empty($dp)){
											echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp\" /></a></td>";
										}
										echo "<td valign=\"top\">";
										echo "<h3><a href=\"".SITE_NAME."/topic/".$stitle."/".$row['id']."/".$row['permalink']."\" class=\"title\">".stripslashes($row['title'])."</a></h3>";
										echo "<span class=\"e-meta\">By <a href=\"".SITE_NAME."/coder/".$row['user']."\" class=\"small-link\">".$row['user']."</a>; ".$row['time']." &bull; Views: " .$row['views']. " &bull; Comments:".$comments."</span>";
										echo "</td>";
										echo "<td valign=\"middle\" align=\"right\">";
										echo "<a href=\"".SITE_NAME."/subject/".getSubjectPermalink($con, $row['sid'])."\" class=\"s-link\">" . $subject . "</a>";
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
								echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/?page=";
								echo $pagination->previous_page();
								echo "\" class=\"movers-left\">&laquo; Newer Posts</a></span>";
							}							
							if ($pagination->has_next_page()){
								echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/?page=";
								echo $pagination->next_page();
								echo "\" class=\"movers-right\">Older Posts &raquo;</a></span>";
							}
							echo "</div>";
							echo "</center>";
						}
						echo "</div>";
						
								
						echo "<div id=\"g-stats\">";
						echo "<h3 style=\"padding-left:0px;\">Popularity Stats</h3>";
						showPopularity($con);
						
						$sql = "SELECT username FROM users";
						$res = mysqli_query($con, $sql);
						if (mysqli_num_rows($res) > 0){
							$max = 0;
							$activeUser = "";
							$max2 = 0;
							$activeUserForum = "";
							$max3 = 0;
							$activeComments = "";
							while ($row = mysqli_fetch_assoc($res)){
								$sql2 = "SELECT user FROM code_snippets WHERE user = '$row[username]'";
								$res2 = mysqli_query($con, $sql2);
								$count = mysqli_num_rows($res2);
								if ($count > $max){
									$max = $count;
									$row2 = mysqli_fetch_assoc($res2);
									$activeUser = $row2['user'];
								}
								$sql3 = "SELECT user FROM topics WHERE user = '$row[username]'";
								$res3 = mysqli_query($con, $sql3);
								$count2 = mysqli_num_rows($res3);
								if ($count2 > $max2){
									$max2 = $count2;
									$row3 = mysqli_fetch_assoc($res3);
									$activeUserForum = $row3['user'];
								}
								$sql4 = "SELECT user FROM code_comments WHERE user = '$row[username]'";
								$res4 = mysqli_query($con, $sql4);
								$count3 = mysqli_num_rows($res4);
								if ($count3 > $max3){
									$max3 = $count3;
									$row4 = mysqli_fetch_assoc($res4);
									$activeComments = $row4['user'];
								} 
							}
							echo "<div id=\"statistics\">";
							echo "<h3>Most active coder:</h3>
								  <p><a href=\"".SITE_NAME."/coder/$activeUser\" class=\"small-link\">".getUserName($con, $activeUser)."</a> ($max posts)</p><br />
								  <h3>Most active forum user:</h3>
								  <p><a href=\"".SITE_NAME."/coder/$activeUserForum\" class=\"small-link\">".getUserName($con, $activeUserForum)."</a> ($max2 threads)</p><br />
								  <h3>Most comments by:</h3>
								  <p><a href=\"".SITE_NAME."/coder/$activeComments\" class=\"small-link\">".getUserName($con, $activeComments)."</a> ($max3 comments)</p>
								 ";
							echo "</div>";
						}
						
						echo "<div id=\"tagcloud\">";
						echo "<h3>Subject Cloud</h3>";
							$tagCloud = tagCloud($con);
							foreach ($tagCloud as $t) {
								$cat = $t['term'];
								$class = $t['class'];
								$permalink = getSubjectPermalinkByTitle($con, $cat);
								echo "<a href=\"".SITE_NAME."/subject/$permalink\" class=\"$class\">$cat</a> ";
							}
						echo "</div>";
						echo "<div id=\"srch\">";
						echo "<h3>Quick Search <span style=\"float:right;\"><a href=\"".SITE_NAME."/advsearch.php\" style=\"display:inline-block; font-size:9pt; margin-top:5px;\">Advanced Search &raquo;</a></span></h3>";
						echo "<input type=\"text\" placeholder=\"Search here...\" name=\"k\" class=\"srch-fld\" id=\"searchField\" /><input type=\"submit\" value=\"Search\" name=\"submit\" onclick=\"ajaxSearch()\" class=\"inp-submit\" id=\"srch-submit\" style=\"border:1px solid #222;\" />";
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