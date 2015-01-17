<?php

	function codeExists($con, $permalink, $snipId){
		$found = false;
		$sql = "SELECT id FROM code_snippets WHERE permalink = '$permalink' AND id = $snipId";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function subjectExists($con, $stitle){
		$found = false;
		$sql = "SELECT id FROM code_subjects WHERE permalink = '$stitle'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function getSid($con, $stitle){
		$sql = "SELECT id FROM code_subjects WHERE permalink = '$stitle'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return $row['id'];
	}
	function getSubjectName($con, $stitle){
		$sql = "SELECT title FROM code_subjects WHERE permalink = '$stitle'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return $row['title'];
	}
	function getTopicName($con, $permalink, $sid){
		$sql = "SELECT title FROM code_snippets WHERE sid = $sid AND permalink = '$permalink'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return $row['title'];
	}
	function topicExists($con, $title, $sid){
		$found = false;
		$sql = "SELECT id FROM code_snippets WHERE sid = $sid AND title = '$title'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function allCodesPrivate($con, $sid){
		$found = false;
		$sql = "SELECT privacy FROM code_snippets WHERE sid = $sid";
		$res = mysqli_query($con, $sql);
		$total = mysqli_num_rows($res); $privacy = 0;
		while ($row = mysqli_fetch_assoc($res)){
			$privacy += $row['privacy'];
		}
		$found = ($total == $privacy)?true:false;
		return $found;
	}
	function allCodesPrivateByUser($con, $user){
		$found = false;
		$sql = "SELECT privacy FROM code_snippets WHERE user = '$user'";
		$res = mysqli_query($con, $sql);
		$total = mysqli_num_rows($res); $privacy = 0;
		while ($row = mysqli_fetch_assoc($res)){
			$privacy += $row['privacy'];
		}
		$found = ($total == $privacy)?true:false;
		return $found;
	}
	function getSubjectPermalink($con, $sid){
		$sql = "SELECT permalink FROM code_subjects WHERE id = $sid";
		$res = mysqli_query($con, $sql) or die(mysql_error());
		$row = mysqli_fetch_assoc($res);
		return $row['permalink'];
	}
	function getSubjectPermalinkByTitle($con, $title){
		$title = addslashes($title);
		$sql = "SELECT permalink FROM code_subjects WHERE title = '$title'";
		$res = mysqli_query($con, $sql) or die(mysql_error());
		$row = mysqli_fetch_assoc($res);
		return $row['permalink'];
	}
	function tagCloud($con, $maximum=0){
		$query = "SELECT id FROM code_subjects";
		$result = mysqli_query($con, $query);
		while ($row = mysqli_fetch_assoc($result)){
			$query2 = "SELECT id FROM code_snippets WHERE sid = $row[id]";
			$result2 = mysqli_query($con, $query2);
			$check = mysqli_num_rows($result2);
			if ($check > $maximum) $maximum = $check;
		}
		
		$cats = array();
		$sql = "SELECT * FROM code_subjects ORDER BY id ASC";
		$res = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($res)){
			$cat = $row['title'];
			
			$sql2 = "SELECT id FROM code_snippets WHERE sid = $row[id]";
			$res2 = mysqli_query($con, $sql2);
			$counter = mysqli_num_rows($res2);
			if ($counter> $maximum) $maximum = $counter;

			$percent = floor(($counter / $maximum) * 100);
			if ($percent <20) {
				$class = 'smallest';
			}
			elseif ($percent>= 20 and $percent <40) {
				$class = 'small';
			}
			elseif ($percent>= 40 and $percent <60) {
				$class = 'medium';
			}
			elseif ($percent>= 60 and $percent <80) {
				$class = 'large';
			}
			else {
				$class = 'largest';
			}
			$cats[] = array('term' => $cat, 'class' => $class);
		}
		return $cats;
	}
	function isPrivate($con, $permalink, $snippet_id){
		$privacy = true;
		$sql = "SELECT privacy, user FROM code_snippets WHERE permalink = '$permalink' AND id = $snippet_id";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		if ($row['privacy'] == 0){
			$privacy = false;
		} else {
			if (isset($_SESSION['username'])){
				if ($_SESSION['username'] == $row['user']){
					$privacy = false;
				}
			}
		}
		return $privacy;
	}
	function hasPrivateCodes($con, $stitle){
		$privacy = false;
		$sql = "SELECT privacy FROM code_snippets WHERE sid = " . getSid($con, $stitle);
		$res = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($res)){
			if ($row['privacy'] == 1){
				$privacy = true;
				break;
			}
		}
		return $privacy;
	}
	function hasPrivateCodesByUser($con, $user){
		$privacy = false;
		$sql = "SELECT privacy FROM code_snippets WHERE user = '$user'";
		$res = mysqli_query($con, $sql);
		while ($row = mysqli_fetch_assoc($res)){
			if ($row['privacy'] == 1){
				$privacy = true;
				break;
			}
		}
		return $privacy;
	}
	function generateRandomFileName($filename){
		$name = substr($filename, 0, strrpos($filename, '.'));
		$ext = substr($filename, strrpos($filename, '.'), (strlen($filename)-1));
		$random_digit=sha1(rand(000000000,999999999));
		$new_file_name = $random_digit.$ext;
		return $new_file_name;
	}
	function showPopularity($con){
		$sql = "SELECT id FROM code_snippets";
		$res = mysqli_query($con, $sql);
		$total = mysqli_num_rows($res);
		
		echo "<ul id=\"chart\">";
		$sql2 = "SELECT * FROM code_subjects";
		$res2 = mysqli_query($con, $sql2);
		$sum = 0;
		while ($row2 = mysqli_fetch_assoc($res2)){
			$sql3 = "SELECT * FROM code_snippets WHERE sid = $row2[id]";
			$res3 = mysqli_query($con, $sql3);
			$c = mysqli_num_rows($res3);
			$sum += $c;
			if ($total != 0){
				$percent = ($sum / $total) * 100;
				$percent = round($percent, 2);
				if ($percent > 0){
					echo "<li title=\"$percent\" class=\"red\">";
					echo "<span class=\"bar\"></span><span class=\"name\">$row2[title]</span>";
					echo "<span class=\"percent\"></span>";
					echo "</li>";
				}
				$sum = 0;
			}
		}
		echo "</ul>";
	}
	function messageOnlyForMe($con, $id, $user){
		$found = false;
		$sql = "SELECT * FROM pm WHERE id = $id AND receiver = '$user'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function updateSeen($con, $id){
		$sql = "UPDATE pm SET seen = 1 WHERE id = $id";
		$res = mysqli_query($con, $sql);
	}
?>