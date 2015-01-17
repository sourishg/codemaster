<?php
	session_start();
	include('../includes/constants.php');
	include('../includes/connect.php');
	include('../includes/pagination.php');
	if (!isset($_GET['q'])) header("location:../404.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Search Forum</title>
<link rel="stylesheet" type="text/css" href="../css/main.css" />
<script src="../js/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="../js/webforms2.js" type="text/javascript"></script>
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
        <div id="mainContent">
        <h3>Search Forum</h3>
        <form action="search.php" method="get"><input placeholder="Search here..." type="text" name="q" id="search" required="required"/></form>
        <div id="list">
        <?php
            if (isEmpty($_GET['q'])){
                echo "<p>Please enter someting to search.</p>";
			} else {
            	$q = trim($_GET['q']);
				$terms = explode(" ", $q);
				
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$per_page = 16;
				$sqla = "SELECT COUNT(*) FROM topics WHERE ";
				$i = 0;
				foreach ($terms as $term){
					$i++;
					
					if ($i == 1){
						$sqla .= "title LIKE '%$term%' ";		
					} else {
						$sqla .= "OR title LIKE '%$term%' ";
					}
				}
				$resa = mysqli_query($con, $sqla);
				$rowa = mysqli_fetch_array($resa);
				$total_count = array_shift($rowa);
						
				$pagination = new Pagination($page, $per_page, $total_count);
				
				$query = "SELECT id, cid, category, permalink, user, title, content, date_format(date, '%d %M, %Y') as time FROM topics WHERE ";
				$i = 0;
				foreach ($terms as $term){
					$i++;
					
					if ($i == 1){
						$query .= "title LIKE '%$term%' ";		
					} else {
						$query .= "OR title LIKE '%$term%' ";
					}
				}
				$query .= "ORDER BY id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
				$res = mysqli_query($con, $query);
				if (mysqli_num_rows($res) > 0){
					echo "<p>Results found for <b>&quot;" .$q. "&quot;</b>: <b>" .number_format(mysqli_num_rows($res)). "</b><span class=\"small-text\" style=\"float:right;\"><a href=\"".SITE_NAME."/forum/\">Back to Forums</a></span></p><br />";
		?>
        		<table width="100%" border="0" style="border-collapse:collapse;">
                    <tr>
                        <td width="60%" style="padding:8px;">TOPIC</td>
                        <td width="17%" align="left" valign="middle" style="padding:8px;">FORUM</td>
                        <td width="17%" align="left" valign="middle" style="padding:8px;">STARTED BY</td>
                        <td width="6%" align="right" >REPLIES</td>
                    </tr>
                    <?php
                        while ($row = mysqli_fetch_assoc($res)){
                            $title = stripslashes($row['title']);
                            $category = stripslashes($row['category']);
							$user = stripslashes($row['user']);
							if (strlen(trim($row['content'])) > 60){
								$desc = trim(substr($row['content'], 0, 60)) . "...";
							} else {
								$desc = trim($row['content']);
							}
							$dp_thumb = getDpThumb($con, $user);
                            
                            echo "<tr class=\"list-cat\">";
                            echo "<td width=\"60%\" class=\"left-curve\"><img src=\"".SITE_NAME."$dp_thumb\" style=\"float:left; padding-right:10px;\" /><span class=\"big-text\"><a href=\"".SITE_NAME."/forum/topic/".$row['id']."/" .getCategoryPermalink($con, $category). "/" .urlencode($row['permalink']). "\">$title</a></span><br />".$desc."</td>";
                            echo "<td width=\"17%\" valign=\"middle\"><a href=\"".SITE_NAME."/forum/category/$row[cid]/".getCategoryPermalink($con, $category)."\" class=\"small-link\">" . $category . "</a></td>";
                            echo "<td width=\"17%\" valign=\"middle\"><a href=\"".SITE_NAME."/coder/$user\" class=\"small-link\">" .$user. "</a><br />on $row[time]</td>";
                            echo "<td width=\"6%\" align=\"right\" class=\"right-curve\">";
							$sql2 = "SELECT id FROM replies WHERE tid = $row[id]";
							$res2 = mysqli_query($con, $sql2);
							$count = mysqli_num_rows($res2);
							echo "$count</td>";
                            echo "</tr>";
                        }
                    ?>
                </table>
                <?php
					if ($pagination->total_pages() > 1){
						echo "<center>";
						echo "<div id=\"pagination\">";
						if ($pagination->has_previous_page()){
							echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/forum/search.php?q=$_GET[q]" . "&page=";
							echo $pagination->previous_page();
							echo "\" class=\"movers-left\">&laquo; Newer Threads</a></span>";
						}
						if ($pagination->has_next_page()){
							echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/forum/search.php?q=$_GET[q]" . "&page=";
							echo $pagination->next_page();
							echo "\" class=\"movers-right\">Older Threads &raquo;</a></span>";
						}
						echo "</div>";
						echo "</center>";
					}
				?>
        <?php
				} else {
					echo "<div id=\"notFound\">Sorry, but what you're looking for isn't here!<br /><span class=\"big-text\"><a href=\"index.php\">Back to Forums</a></span></div>";
				}
			}
        ?>
        </div>
        </div>
    </div>
</div>
</body>
</html>