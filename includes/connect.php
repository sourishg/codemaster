<?php
	
	ini_set('display_errors', '1');

	$services_json = json_decode(getenv('VCAP_SERVICES'),true);
	$mySql = $services_json["mysql-5.5"][0]["credentials"];
	// Extract the VCAP_SERVICES variables for MySQL connection.  
	$myDbUsername = $mySql["username"];
	$myDbPassword = $mySql["password"];
	$myDbHost = $mySql["hostname"];
	$myDbName = $mySql["name"];
	$myDbPort = $mySql["port"];

	$con = mysqli_connect($myDbHost,$myDbUsername,$myDbPassword, $myDbName, $myDbPort);

	if (mysqli_connect_errno()) {
		throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
	}
	
	function mss($con, $value){
		return mysqli_real_escape_string($con, trim(htmlentities($value)));
	}
	function hasUser($con, $user){
		$found = false;
		$sql = "SELECT id FROM users WHERE username = '".addslashes($user)."'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){ $found = true; }
		return $found;
	}
	function checkJID($con, $id){
		$found = false;
		$sql = "SELECT id FROM j_posts WHERE id = '".$id."'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){ $found = true; }
		return $found;
	}
	function checkJTitle($con, $title){ $title = addslashes($title);
		$found = false;
		$sql = "SELECT id FROM j_posts WHERE permalink = '".addslashes($title)."'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){ $found = true; }
		return $found;
	}
	function userHasEntries($con, $user){
		$found = false;
		$sql = "SELECT user FROM j_posts WHERE user = '".addslashes($user)."'";
		$res = mysqli_query($con, $sql);
		if (mysql_num_rows($res) > 0){ $found = true; }
		return $found;
	}
	function userHasSpecificEntry($con, $user, $id, $title){
		$found = false;
		$sql = "SELECT id FROM j_posts WHERE user = '".addslashes($user)."' AND id = '".$id."' AND permalink = '".addslashes($title)."'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){ $found = true; }
		return $found;
	}
	function getLastJID($con){
		$sql = "SELECT id FROM j_posts WHERE user = '".addslashes($_SESSION['username'])."' ORDER BY id DESC LIMIT 1";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res); return $row['id'];
	}
	function hasPrevPost($con, $id, $user){
		$sql = "SELECT id, title FROM j_posts WHERE user = '".addslashes($user)."' AND id < '".$id."'";
		$res = mysqli_query($con, $sql);
		$found = false;
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function getPrevPostID($con, $id, $user){
		$sql = "SELECT id, title FROM j_posts WHERE user = '".addslashes($user)."' AND id < '".$id."' ORDER BY id DESC";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			while ($row = mysqli_fetch_assoc($res)){
				$prev_id = $row['id'];
				break; 
			}
		}
		return $prev_id;
	}
	function getPrevPostTitle($con, $id, $user){
		$sql = "SELECT id, title FROM j_posts WHERE user = '".addslashes($user)."' AND id < '".$id."' ORDER BY id DESC";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			while ($row = mysqli_fetch_assoc($res)){
				$prev_title = stripslashes($row['title']);
				break; 
			}
		}
		return $prev_title;
	}
	
	function hasNextPost($con, $id, $user){
		$sql = "SELECT id, title FROM j_posts WHERE user = '".addslashes($user)."' AND id > '".$id."'";
		$res = mysqli_query($con, $sql);
		$found = false;
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function getNextPostID($con, $id, $user){
		$sql = "SELECT id, title FROM j_posts WHERE user = '".addslashes($user)."' AND id > '".$id."' ORDER BY id ASC";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			while ($row = mysql_fetch_assoc($res)){
				$next_id = $row['id'];
				break; 
			}
		}
		return $next_id;
	}
	function getNextPostTitle($con, $id, $user){
		$sql = "SELECT id, title FROM j_posts WHERE user = '".addslashes($user)."' AND id > '".$id."' ORDER BY id ASC";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			while ($row = mysqli_fetch_assoc($res)){
				$next_title = stripslashes($row['title']);
				break; 
			}
		}
		return $next_title;
	}
	function isEmpty($value){
		$check = false;
		$value = str_replace(" ", "", $value);
		if (empty($value)){
			$check = true;
		}
		return $check;
	}
	function createCleanTitle($string){
		//Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
		$string = strtolower($string);
		$string = str_replace("C++", "cpp", $string);
		$string = str_replace("C#", "c-sharp", $string);
		//Strip any unwanted characters
		$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
		//Clean multiple dashes or whitespaces
		$string = preg_replace("/[\s-]+/", " ", $string);
		//Convert whitespaces and underscore to dash
		$string = preg_replace("/[\s_]/", "-", $string);
		return $string;
	}
	function getJournalPostTitle($con, $permalink){
		$sql = "SELECT title FROM j_posts WHERE permalink = '$permalink'";
		$res = mysqli_query($con, $sql);
		$row = mysql_fetch_assoc($res);
		return stripslashes($row['title']);
	}
	function getUserName($con, $user){
		$sql = "SELECT name FROM users WHERE username = '".addslashes($user)."'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return $row['name'];
	}
	function mainFTopicExists($con, $cat){
		$found = false;
		$sql = "SELECT name FROM main_categories WHERE permalink = '$cat'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function mainFIDExists($con, $id, $cat){
		$found = false;
		$sql = "SELECT name FROM main_categories WHERE id = $id AND permalink = '$cat'";
		$res = mysqli_query($con, $sql);
		if (mysqli_num_rows($res) > 0){
			$found = true;
		}
		return $found;
	}
	function getForumCategoryName($con, $cat){
		$sql = "SELECT name FROM main_categories WHERE permalink = '$cat'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return stripslashes($row['name']);
	}
	function getCategoryPermalink($con, $title){
		$sql = "SELECT permalink FROM main_categories WHERE name = '$title'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return stripslashes($row['permalink']);
	}
	function get_image_sizes($sourceImageFilePath, $maxResizeWidth, $maxResizeHeight) {
	
	  // Get width and height of original image
	  $size = getimagesize($sourceImageFilePath);
	  if($size === FALSE) return FALSE; // Error
	  $origWidth = $size[0];
	  $origHeight = $size[1];
	
	  // Change dimensions to fit maximum width and height
	  $resizedWidth = $origWidth;
	  $resizedHeight = $origHeight;
	  if ($resizedWidth > $maxResizeWidth) {
		$aspectRatio = $maxResizeWidth / $resizedWidth;
		$resizedWidth = round($aspectRatio * $resizedWidth);
		$resizedHeight = round($aspectRatio * $resizedHeight);
	  }
	  if ($resizedHeight > $maxResizeHeight) {
		$aspectRatio = $maxResizeHeight / $resizedHeight;
		$resizedWidth = round($aspectRatio * $resizedWidth);
		$resizedHeight = round($aspectRatio * $resizedHeight);
	  }
	  
	  // Return an array with the original and resized dimensions
	  return array($origWidth, $origHeight, $resizedWidth, $resizedHeight);
	}
	function generateDpThumb($sourceImageFilePath, $thumbName){
		$sizes = get_image_sizes($sourceImageFilePath, 40, 40);
		$origWidth = $sizes[0];
		$origHeight = $sizes[1];
		$resizedWidth = $sizes[2];
		$resizedHeight = $sizes[3];
			
		// Create the resized image 
		$imageOutput = imagecreatetruecolor($resizedWidth, $resizedHeight);
			
		// Load the source image
		$imageSource = imagecreatefromjpeg($sourceImageFilePath);
				
		$result = imagecopyresampled($imageOutput, $imageSource, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $origWidth, $origHeight);
					
		// Write out the JPEG file with the highest quality value
		$result = imagejpeg($imageOutput, "dp_thumbs/".$thumbName, 100);
		return "/dp_thumbs/" . $thumbName;
	}
	function getDpThumb($con, $user){
		$sql = "SELECT dp_thumb FROM users WHERE username = '$user'";
		$res = mysqli_query($con, $sql);
		$row = mysqli_fetch_assoc($res);
		return $row['dp_thumb'];
	}
	function curPageURL() {
	 	$pageURL = 'http';
	 	$pageURL .= "://";
	 	if ($_SERVER["SERVER_PORT"] != "80") {
	  	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	 	} else {
	  	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 	}
	 	return $pageURL;
	}
?>