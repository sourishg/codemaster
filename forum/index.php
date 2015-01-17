<?php
	session_start();
	include('../includes/constants.php');
	include('../includes/connect.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Forum | <?php echo SITE_TITLE; ?></title>
<?php include('../includes/f-head-connect.php'); ?>
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
        <h3>Forum</h3>
        <form action="search.php" method="get"><input placeholder="Search here..." type="text" name="q" id="search" required="required"/></form>
        <div id="list">
        <table width="100%" border="0" style="border-collapse:collapse;">
            <tr>
                <td width="40%" style="padding:8px;"></td>
                <td width="15%" align="center" valign="middle" style="padding:8px;">TOPICS</td>
                <td width="15%" align="center" valign="middle" style="padding:8px;">POSTS</td>
                <td width="1%" style="background:#E3EBCA;"></td>
                <td width="29%" valign="middle" style="padding:8px;">LATEST THREADS</td>
            </tr>
            <?php
                $sql = "SELECT * FROM main_categories";						
                $res = mysqli_query($con, $sql) or die(mysqli_error());
                while ($row = mysqli_fetch_assoc($res)){
                    $name = stripslashes($row['name']);
                    $desc = stripslashes($row['desc']);
                    $sql2 = "SELECT * FROM topics WHERE category = '$row[name]'";
                    
                    $res2 = mysqli_query($con, $sql2) or die(mysqli_error());
                    $topics = mysqli_num_rows($res2);
                    
                    echo "<tr class=\"list-cat\">";
                    echo "<td width=\"40%\" class=\"left-curve\"><span class=\"big-text\"><a href=\"".SITE_NAME."/forum/category/$row[id]/" . $row['permalink'] . "\" title=\"View all topics under &quot;$name&quot;\">$name</a></span><br />$desc</td>";
                    echo "<td width=\"15%\" align=\"center\" valign=\"middle\">" . number_format($topics) . "</td>";
					
					$posts = 0;
					$sql2 = "SELECT * FROM topics WHERE category = '$name'";
					$res2 = mysqli_query($con, $sql2);
					while ($row2 = mysqli_fetch_assoc($res2)){
						$sql3 = "SELECT * FROM replies WHERE topic = '$row2[permalink]'";
						$res3 = mysqli_query($con, $sql3) or die(mysqli_error());
						$posts += mysqli_num_rows($res3);
					}
					
					$sql4 = "SELECT id, title, permalink, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM topics WHERE category = '$name' ORDER BY id DESC LIMIT 1";
					$res4 = mysqli_query($con, $sql4);
					$row4 = mysqli_fetch_assoc($res4);
					$row4count = mysqli_num_rows($res4);
					
                    echo "<td width=\"15%\" align=\"center\" valign=\"middle\" class=\"right-curve\">".number_format($posts)."</td>";
                    echo "<td width=\"1%\" style=\"background:#E3EBCA;\"></td>";
                    echo "<td class=\"full-curve\" width=\"29%\">";
					if ($row4count > 0){
					echo "<span class=\"small-text\"><a href=\"".SITE_NAME."/forum/topic/$row4[id]/".getCategoryPermalink($con, $name)."/".$row4['permalink']."\">".stripslashes($row4['title'])."</a></span><br /><a href=\"".SITE_NAME."/coder/$row4[user]\" class=\"small-link\">".$row4['user']."</a>, ".$row4['time']."</td>"; 
					} else {
						echo "No threads yet.";
					}
                    echo "</tr>";
                }
            ?>
        </table>
        </div>
        </div>
    </div>
</div>
</body>
</html>