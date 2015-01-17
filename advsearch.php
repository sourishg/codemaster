<?php
	session_start();
	include('includes/code_functions.php');
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/pagination.php');
?>
<?php 
	if (isset($_POST['lsubmit'])){
		$location = curPageURL();
		loginUser($location);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Advanced Search | <?php echo SITE_TITLE; ?></title>
<script src="<?php echo SITE_NAME . "/js/rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/charts.js"; ?>" type="text/javascript"></script>
<script type="text/javascript">
	function showList(){
		var l = document.getElementById('filter').value;
		if (l == 2){
			document.getElementById('author').style.display = "none";
			document.getElementById('plang').style.display = "inline-block";
		} else if (l == 3){
			document.getElementById('plang').style.display = "none";
			document.getElementById('author').style.display = "inline-block";
		} else {
			document.getElementById('plang').style.display = "none";
			document.getElementById('author').style.display = "none";
		}
	}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
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
                	<div id="aS">
                		<h1>Advanced Search</h1>
                        <form action="advsearch.php" method="get">
                        	<input type="text" name="k" value="<?php if (isset($_GET['k'])) echo trim($_GET['k']); ?>" /><input type="submit" name="submit" value="Search" class="aS-submit" />
                            <h3 style="padding-left:0;">Add Filters <span style="font-size:10pt;">(Optional)</span></h2>
                            <select name="plang" id="plang">
                            	<option value="0">Select Language</option>
                                <?php
                                	$sql = "SELECT * FROM code_subjects";
									$res = mysqli_query($con, $sql);
									while ($row = mysqli_fetch_assoc($res)){
										echo "<option value=\"$row[id]\">$row[title]</option>";	
									}
								?>
                            </select>
                            <span style="font-size:12pt;">Coder:</span>
                            <input type="text" name="author" id="author" placeholder="Coder username..." value="<?php if (isset($_GET['author'])) { echo $_GET['author']; } ?>" /> <span style="font-size:9pt; color:#666;">(Multiple names to be separated by spaces)</span>
                        </form>
                        <?php
							if (isset($_GET['k']) && trim($_GET['k']) != ""){
								$terms = explode(" ", $_GET['k']);
								$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
								$per_page = 15;
								$sqla = "SELECT COUNT(*) FROM code_snippets WHERE ";
								$i = 0;
								foreach ($terms as $term){
									$i++;
									if ($i == 1){
										$sqla .= "title LIKE '%$term%' ";		
									} else {
										$sqla .= "OR title LIKE '%$term%' ";
									}
								}
								if (isset($_GET['plang']) && $_GET['plang'] != 0){
									$plang = $_GET['plang'];
									$sqla .= "AND sid = $plang ";
								}
								if (isset($_GET['author']) && $_GET['author'] != ""){
									$author = $_GET['author'];
									$terms = explode(" ", $author);
									$i = 0;
									foreach ($terms as $term){
										$i++;
										if ($i == 1){
											$sqla .= "AND user LIKE '%$term%' ";		
										} else {
											$sqla .= "OR user LIKE '%$term%' ";
										}
									}
								}
								
								
								$sqla .= " AND privacy = 0 ";
								$resa = mysqli_query($con, $sqla) or die(mysqli_error());
								$rowa = mysqli_fetch_array($resa);
								$total_count = array_shift($rowa);
								
								$pagination = new Pagination($page, $per_page, $total_count);
								
								$terms = explode(" ", $_GET['k']);
								$query = "SELECT id, sid, title, permalink, views, privacy, content, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets WHERE ";
								$i = 0;
								foreach ($terms as $term){
									$i++;
									if ($i == 1){
										$query .= "title LIKE '%$term%' ";		
									} else {
										$query .= "OR title LIKE '%$term%' ";
									}
								}
								
								if (isset($_GET['plang']) && $_GET['plang'] != 0){
									$plang = $_GET['plang'];
									$query .= "AND sid = $plang ";
								}
								if (isset($_GET['author']) && $_GET['author'] != ""){
									$author = $_GET['author'];
									$terms = explode(" ", $author);
									$i = 0;
									foreach ($terms as $term){
										$i++;
										if ($i == 1){
											$query .= "AND user LIKE '%$term%' ";		
										} else {
											$query .= "OR user LIKE '%$term%' ";
										}
									}
								}
								
								
								
								$query .= " AND privacy = 0 ";
								$query .= "ORDER BY views DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
								$res = mysqli_query($con, $query) or die(mysqli_error());
								if (mysqli_num_rows($res) > 0){
									while ($row = mysqli_fetch_assoc($res)){
										$title = stripslashes($row['title']);
										$content = strip_tags(stripslashes($row['content']));
										$content = substr($content, 0 , 200);
										$views = $row['views'];
										$dp_thumb = getDpThumb($con, $row['user']);
										echo "<div class=\"results\">";
										echo "<a href=\"".SITE_NAME."/coder/$row[user]\"><img src=\"".SITE_NAME."$dp_thumb\" style=\"float:left; padding-right:10px; margin-top:0px;\" /></a>";
										echo "<a href=\"".SITE_NAME."/topic/".getSubjectPermalink($con, $row['sid'])."/$row[id]/$row[permalink]\" class=\"ptitle\">".$title."</a> - <span style=\"font-weight:bold; text-transform:uppercase;\">" . getSubjectName($con, getSubjectPermalink($con, $row['sid'])) . "</span>";
										echo "<span class=\"meta\">By $row[user] &bull; $row[time] &bull; Views: $views</span>";
										echo "<p>".$content."</p>";
										echo "</div>";
									}
								} else {
									echo "<p class=\"error\">No results found.</p>";
								}
							}
							if (isset($_GET['k']) && trim($_GET['k']) != ""){
								if ($pagination->total_pages() > 1){
									echo "<center>";
									echo "<div id=\"pagination\">";
									if ($pagination->has_previous_page()){
										echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/advsearch.php?k=$_GET[k]&submit=Search&plang=$_GET[plang]&author=$_GET[author]";
										echo "&page=";
										echo $pagination->previous_page();
										echo "\" class=\"movers-left\">&laquo; Previous</a></span>";
									}
									for ($i = 1; $i <= $pagination->total_pages(); $i++){
										if ($i == $page){
											echo "<span style=\"padding:0 5px;\">{$i}</span>";
										} else {
											echo "<a href=\"".SITE_NAME."/advsearch.php?k=$_GET[k]&submit=Search&plang=$_GET[plang]&author=$_GET[author]&page={$i}\" style=\"padding:0 5px;\">{$i}</a>";
										}
									}							
									if ($pagination->has_next_page()){
										echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/advsearch.php?k=$_GET[k]&submit=Search&plang=$_GET[plang]&author=$_GET[author]";
										echo "&page=";
										echo $pagination->next_page();
										echo "\" class=\"movers-right\">Next &raquo;</a></span>";
									}
									echo "</div>";
									echo "</center>";
								}
							}
							echo "</div>";
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