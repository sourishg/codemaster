<?php
	include('includes/code_functions.php');
	include('includes/constants.php');
	include('includes/connect.php');
	$q = trim($_GET['k']);
	$terms = explode(" ", $q);
	$query = "SELECT id, sid, title, permalink, privacy, content, user, date_format(date, '%d %M, %Y - %T') as time FROM code_snippets WHERE ";
	$i = 0;
	foreach ($terms as $term){
		$i++;
		
		if ($i == 1){
			$query .= "title LIKE '%$term%' ";		
		} else {
			$query .= "OR title LIKE '%$term%' ";
		}
	}
	$query .= "AND privacy = 0 ORDER BY views DESC";
	$res = mysqli_query($con, $query) or die(mysqli_error());
	if (mysqli_num_rows($res) > 0){
		echo "<center><h3>Top Results (".mysqli_num_rows($res).")</h3></center>";
		while ($row = mysqli_fetch_assoc($res)){
			echo "<a href=\"".SITE_NAME."/topic/".getSubjectPermalink($con, $row['sid'])."/".$row['id']."/".$row['permalink']."\" >".stripslashes($row['title'])."</a>";
		}
	} else {
		echo "<p>No results found, or there maybe private codes which you may not see.</p>";
	}
?>