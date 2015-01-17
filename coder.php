<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	include('includes/pagination.php');
	if (!isset($_GET['coder'])){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
	if (!hasUser($con, $_GET['coder'])){
		$locate_index = SITE_NAME . "/404.php";
		header("location:$locate_index");
	}
?>
<?php
	if (isset($_POST['submit'])){
		$message = "";
		$upload_errors = array(
			UPLOAD_ERR_OK 				=> "Upload was successful.",
			UPLOAD_ERR_INI_SIZE  		=> "Larger than upload_max_filesize.",
			UPLOAD_ERR_FORM_SIZE 		=> "Larger than form MAX_FILE_SIZE.",
			UPLOAD_ERR_PARTIAL 			=> "Partial upload.",
			UPLOAD_ERR_NO_FILE 			=> "No file.",
			UPLOAD_ERR_NO_TMP_DIR 		=> "No temporary directory.",
			UPLOAD_ERR_CANT_WRITE 		=> "Can't write to disk.",
			UPLOAD_ERR_EXTENSION 		=> "File upload stopped by extension."
		);
		
		$tmp_file = $_FILES['file_upload']['tmp_name'];
		$target_file = basename($_FILES['file_upload']['name']);
		$upload_dir = "dp";
		$newFileName = generateRandomFileName($target_file);
		while (file_exists($newFileName)){
			$newFileName = generateRandomFileName($target_file);
		}
		$image_type = strtolower(substr($newFileName, strpos($newFileName, ".")+1, strlen($newFileName)));
		
		if ($image_type == "jpg"){	
			if (!file_exists($newFileName)){
				if(move_uploaded_file($tmp_file, $upload_dir."/".$newFileName)) {
					
					$sizes = get_image_sizes($upload_dir."/".$newFileName, 200, 300);
					$origWidth = $sizes[0];
					$origHeight = $sizes[1];
					$resizedWidth = $sizes[2];
					$resizedHeight = $sizes[3];
					
					// Create the resized image 
					$imageOutput = imagecreatetruecolor($resizedWidth, $resizedHeight);
					
					// Load the source image
					$imageSource = imagecreatefromjpeg($upload_dir."/".$newFileName);
					
					$result = imagecopyresampled($imageOutput, $imageSource, 
						0, 0, 0, 0, $resizedWidth, $resizedHeight, $origWidth, 
						$origHeight);
					
					// Write out the JPEG file with the highest quality value
					$result = imagejpeg($imageOutput, $upload_dir."/".$newFileName, 100);
					$thumb = generateDpThumb($upload_dir."/".$newFileName, $newFileName);
					
					$location = "/dp/$newFileName";
					$command = "UPDATE users SET dp = '$location' WHERE username = '$_GET[coder]'";
					$result = mysqli_query($con, $command);
					if ($result){
						$command = "UPDATE users SET dp_thumb = '$thumb' WHERE username = '$_GET[coder]'";
						$result = mysqli_query($con, $command);
						$message = "Uploaded successfully.";
					} else {
						$message = "lol";
					}
				} else {
					$error = $_FILES['file_upload']['error'];
					$message = $upload_errors[$error];
				}
			} else {
				$message = "File already exists. Choose a different file name.";
			}
		} else {
			$message = "Only .jpg files can be used as profile pictures.";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo stripslashes(getUserName($con, $_GET['coder'])); ?> | <?php echo SITE_TITLE; ?></title>
<script src="<?php echo SITE_NAME . "/js/code_rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script type="text/javascript">
	function showPM(val){
		val.style.display = 'none';
		document.getElementById('pm-area').style.display = 'block';
	}
</script>
<script type="text/javascript">
	function confirmDelete(value){
		var conf = confirm("Are you sure you want to delete this entry?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value;
		}
	}
	function confirmStashDelete(value){
		var conf = confirm("Are you sure you want to delete this entry?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value + "&ref=stash";
		}
	}
	function confirmDpDelete(value){
		var conf = confirm("Are you sure you want to delete your picture?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value + "&ref=dp";
		}
	}
</script>
<script type="text/javascript" src="<?php echo SITE_NAME; ?>/js/message.js"></script>
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
	function validatePM(){
		var validate = true;
		var content = document.getElementById("pmessage").value;
		if (content.trim() == ""){
			validate = false;
		}
		if (validate == true){
			ajax();
			document.getElementById("pmRes").style.display = "inline-block";
			document.getElementById("pmRes").innerHTML = "Sending...";
			var message = "message=" + encodeURIComponent(document.getElementById("pmessage").value);
			var receiver = "receiver=<?php echo $_GET['coder']; ?>";
			function stateChanged(){
				if (xhr.readyState == 4){
					document.getElementById('pmRes').innerHTML = xhr.responseText;
					setTimeout(function () {document.getElementById('lightbox').style.display='none'}, 3000);
				}
			}
			var vars = message + "&" + receiver;
			url = "<?php echo SITE_NAME; ?>/sendpm.php";
			xhr.onreadystatechange = stateChanged;
			xhr.open("POST", url, true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.send(vars);
		} else {
			alert("Please enter some content.");
		}
	}
	function showDelDp(){
		document.getElementById("deleteDp").style.display = "inline-block";
	}
	function hideDelDp(){
		document.getElementById("deleteDp").style.display = "none";
	}
	function showUpState(){
		document.getElementById('upState').style.display = "block";
	}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
</head>

<body onload="iFrameOn();">
<div id="lightbox">
	<h1>Your message</h1><a href="#" onclick="closePM()">Close Dialog</a>
    <textarea id="pmessage" name="pmessage"></textarea><br />
    <input type="submit" name="submit" id="psubmit" value="Send" onclick="validatePM();" class="inp-submit" style="margin-left:0;" />
    <div id="pmRes"></div>
</div>
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
                            	echo "<li><a href=\"".SITE_NAME."/subject/".$row['permalink']."\">".$row['title']."</a></li>";
                            }
                        ?>
                    </ul>
                </td>
                <td valign="top" id="right-bar">
                    <h2><?php echo "<span style=\"color:#f90;\">" . getUserName($con, $_GET['coder']) . "</span>"; ?></h2>
                    <?php 
						$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
						$per_page = 10;
						$sqla = "SELECT COUNT(*) FROM code_snippets WHERE user = '$_GET[coder]'";
						$resa = mysqli_query($con, $sqla);
						$rowa = mysqli_fetch_array($resa);
						$total_count = array_shift($rowa);
						
						$pagination = new Pagination($page, $per_page, $total_count);
					
						$sql = "SELECT id, sid, title, permalink, privacy, content, user, date_format(date, '%d %M, %Y - %l.%i %p') as time FROM code_snippets WHERE user = '$_GET[coder]' ORDER BY id DESC LIMIT {$per_page} OFFSET {$pagination->offset()}";
						$res = mysqli_query($con, $sql);
						if (mysqli_num_rows($res) > 0){
							$sql2 = "SELECT dp_thumb FROM users WHERE username = '$_GET[coder]'";
							$res2 = mysqli_query($con, $sql2);
							$row2 = mysqli_fetch_assoc($res2);
							$dp_thumb = $row2['dp_thumb'];
							echo "<div id=\"feed-area\">";
							echo "<h3 style=\"padding-bottom:0;\">Code Snippets By $_GET[coder]</h3>";
							if (hasPrivateCodesByUser($con, $_GET['coder'])) { echo "<span style=\"padding-left:10px; color:#666; font-size:8pt;\">(There are private codes here which you may not see)</span><br /><br />"; };
							while ($row = mysqli_fetch_assoc($res)){
								if ($row['privacy'] == 0){
									echo "<div class=\"e-feed\">";
									echo "<table width=\"100%\" border=\"0\">";
									echo "<tr>";
									if (!empty($dp_thumb)){
										echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$_GET[coder]\"><img src=\"".SITE_NAME."$dp_thumb\" /></a></td>";
									}									
									echo "<td valign=\"top\">";
									if (isset($_SESSION['username']) && $_GET['coder'] == $_SESSION['username']){
										echo "<span class=\"del\" style=\"padding:5px;\"><a href=\"#\" onclick=\"confirmDelete($row[id])\">Delete</a></span>";
									}	
									echo "<h3><a href=\"".SITE_NAME."/topic/".getSubjectPermalink($con, $row['sid'])."/".$row['id']."/".$row['permalink']."\" class=\"title\">".stripslashes($row['title'])."</a></h3>";
									echo "<span class=\"e-meta\">By ".$row['user']."; ".$row['time']."</span>";
									echo "</td>";
									echo "</tr>";
									echo "</table>";
									echo "<div class=\"e-content\">".stripslashes($row['content'])."</div>";
									echo "</div>";
								} else if ($row['privacy'] == 1){
									if (isset($_SESSION['username'])){
										if ($_SESSION['username'] == $row['user']){
											echo "<div class=\"e-feed\">";
											echo "<table width=\"100%\" border=\"0\">";
											echo "<tr>";
											if (!empty($dp_thumb)){
												echo "<td width=\"45\"><a href=\"".SITE_NAME."/coder/$_GET[coder]\"><img src=\"".SITE_NAME."$dp_thumb\" /></a></td>";
											}
											echo "<td valign=\"top\">";
											if (isset($_SESSION['username']) && $_GET['coder'] == $_SESSION['username']){
												echo "<span class=\"del\" style=\"padding:5px;\"><a href=\"#\" onclick=\"confirmDelete($row[id])\">Delete</a></span>";
											}
											echo "<h3><a href=\"".SITE_NAME."/topic/".getSubjectPermalink($con, $row['sid'])."/".$row['id']."/".$row['permalink']."\" class=\"title\">".stripslashes($row['title'])."</a></h3>";
											echo "<span class=\"e-meta\">By ".$row['user']."; ".$row['time']."</span>";
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
									echo "<span class=\"movers-left\"><a href=\"".SITE_NAME."/coder/$_GET[coder]&page=";
									echo $pagination->previous_page();
									echo "\" class=\"movers-left\">&laquo; Newer Posts</a></span>";
								}							
								if ($pagination->has_next_page()){
									echo "<span class=\"movers-right\"><a href=\"".SITE_NAME."/coder/$_GET[coder]&page=";
									echo $pagination->next_page();
									echo "\" class=\"movers-right\">Older Posts &raquo;</a></span>";
								}
								echo "</div>";
								echo "</center>";
							}
							echo "</div>";
						} else {
							if (isset($_SESSION['username']) && ($_SESSION['username'] == $_GET['coder'])){
								echo "<div id=\"feed-area\">";
								echo "<h3 style=\"padding-bottom:0; font-size:20pt;\">Welcome to CodeMaster, <span style=\"color:#09f;\">$_GET[coder]</span></h3>";
								echo "<p style=\"font-size:12pt;\">In order to get started, click the button above to make your first post or upload files to your stash.</p>";
								echo "<p style=\"font-size:12pt;\">If you are willing to browse codes posted by others, you can use the left navigation bar.</p>";
								echo "<p style=\"font-size:12pt;\">As you can see there are no files in your stash now, but once you upload your first file, the stash area will show your files which other users or you can download.</p>";
								echo "<p style=\"font-size:12pt;\">Happy coding...</p>";
								echo "</div>";
							} else {
								echo "<div id=\"feed-area\">";
								echo "<p style=\"font-size:12pt;\"><span style=\"color:#09f;\">$_GET[coder]</span> has not yet started posting his codes.</p>";
								echo "</div>";
							}
						}
					?>
                    <div id="editor-area">
                        <div id="stash-area" style="padding-bottom:0; margin-top:0; border-top:none; width:350px;">
                        	<?php
								if (!isset($_POST['submit'])){
							?>
                        	<div id="upState" style="padding:5px; background:#ffffcc; color:#c90; margin-top:10px; border:1px solid #FC3; display:none;">Uploading profile picture...</div>
							<?php
								}
                            ?>
							<?php
								echo "<div id=\"dpArea\">";
								$sql = "SELECT * FROM users WHERE username = '$_GET[coder]'";
								$res = mysqli_query($con, $sql);
								$row = mysqli_fetch_assoc($res);
								$dp = $row['dp'];
								if (!empty($dp)){
									echo "<img src=\"".SITE_NAME."$dp\" style=\"padding-top:10px\" id=\"myDP\" onmouseover=\"showDelDp()\" onmouseout=\"hideDelDp()\" />";
								}
								if (isset($_SESSION['username']) && $_SESSION['username'] == $_GET['coder']){
									echo "<a href=\"#\" id=\"deleteDp\" onmouseover=\"this.style.display = 'inline-block'\" onmouseout=\"this.style.display = 'none'\" onclick=\"confirmDpDelete($row[id]);\">Delete</a>";
									$sql = "SELECT * FROM users WHERE username = '$_GET[coder]'";
									$res = mysqli_query($con, $sql);
									$row = mysqli_fetch_assoc($res);
									$dp = $row['dp'];									
									if (!$dp){
										if (!empty($message)){
											echo $message;
										}
										echo "
											<div style=\"border-bottom:1px solid #ccc; padding-bottom:10px;\">
											<p style=\"padding:10px 0; padding-bottom:5px;\">You do not have a profile picture.</p>
											<form action=\"".SITE_NAME."/coder/$_GET[coder]\" method=\"post\" enctype=\"multipart/form-data\">
												<input type=\"file\" name=\"file_upload\" style=\"margin:0; border:1px solid #999;\" /><br />
												<input type=\"submit\" name=\"submit\" value=\"Upload\" class=\"inp-submit\" style=\"margin-left:0;\" onclick=\"showUpState();\" />
											</form>
											</div>
										";
									}
								}
								echo "</div>";
							?>
                        	<?php
								if (isset($_SESSION['username']) && $_SESSION['username'] != $_GET['coder']){
							?>
                        	<div id="pm">
                            	<input type="submit" value="SEND MESSAGE" class="inp-submit" style="margin-left:0;" onclick="activatePM();" />
                            </div>
							<?php
								}
							?>
							<?php
								if (isset($_SESSION['username']) && $_SESSION['username'] == $_GET['coder']){
									echo "<br /><a href=\"".SITE_NAME."/edit.php\" class=\"inp-submit\" style=\"text-decoration:none; margin-left:0;\">Edit Profile</a>";
								}
                            	$sql = "SELECT interests, bio, email FROM users WHERE username = '$_GET[coder]'";
								$res = mysqli_query($con, $sql);
								$row = mysqli_fetch_assoc($res);
								$interests = stripslashes($row['interests']);
								$bio = stripslashes($row['bio']);
								$email = stripslashes($row['email']);
								if (!empty($email)){
									echo "<h3 style=\"padding:0; padding-top:10px; font-size:12pt; font-weight:bold; color:#d90;\">EMAIL:</h3>";
									echo "<p style=\"padding:0; font-size:9pt;\">".$email."</p>";
								}
								if (!empty($interests)){
									echo "<h3 style=\"padding:0; padding-top:10px; font-size:12pt; font-weight:bold; color:#d90;\">INTERESTS:</h3>";
									echo "<p style=\"padding:0; font-size:9pt;\">".$interests."</p>";
								}
								if (!empty($bio)){
									echo "<h3 style=\"padding:0; padding-top:10px; font-size:12pt; font-weight:bold; color:#d90;\">BIO:</h3>";
									echo "<p style=\"padding:0; font-size:9pt;\">".$bio."</p>";
								}
								$sql = "SELECT * FROM topics WHERE user = '$_GET[coder]' ORDER BY id DESC LIMIT 1";
								$res = mysqli_query($con, $sql) or die(mysqli_error());
								if (mysqli_num_rows($res) == 1){
									$row = mysqli_fetch_assoc($res);
									echo "<h3 style=\"padding:0; padding-top:10px; font-size:12pt; font-weight:bold; color:#666;\">LATEST FORUM THREAD:</h3>";
									echo "<a href=\"".SITE_NAME."/forum/topic/$row[id]/".getCategoryPermalink($con, $row['category'])."/".$row['permalink']."\" style=\"color:#333; padding-bottom:5px;\">".stripslashes($row['title'])."</a>";
									echo "<br />";
								}
							?>
                            <a name="stash"></a>
                        	<h3 style="padding:10px 0;"><?php echo $_GET['coder'] . "'s"; ?> Stash</h3>
                            <?php 
								$sql = "SELECT * FROM code_stash WHERE user = '$_GET[coder]'";
								$res = mysqli_query($con, $sql) or die(mysqli_error());
								if (mysqli_num_rows($res) == 0){
									echo "<p class=\"imp\" style=\"border:1px solid #ccc; text-align:center;\"><b>No files in stash.</b></p>";
								} else {
									echo "<table width=\"350px\" border=\"0\" style=\"border-collapse:collapse;\" id=\"stash\">";
									echo "<tr style=\"background:#333; color:#fff;\">";
									echo "<td width=\"40\" style=\"border:1px solid #666; padding:5px;\">S. No.</td>";
									echo "<td style=\"border:1px solid #666; padding:5px;\" colspan=\"2\">File name</td>";
									echo "</tr>";
									$count = 0;
									while ($row = mysqli_fetch_assoc($res)){
										$count++;
											echo "<tr>";
											echo "<td style=\"padding:5px;\">".$count.".</td>";
											echo "<td><a href=\"".SITE_NAME."$row[location]\">".stripslashes($row['title'])."</a></td>";
											if (isset($_SESSION['username']) && $_SESSION['username'] == $_GET['coder']){
												echo "<td width=\"50\" align=\"center\"><input type=\"button\" onclick=\"confirmStashDelete($row[id])\" value=\"Delete\" class=\"delbtn\" /></td>";
											}
											echo "</tr>";
									}
									echo "</table><br />";
								}
							?>
                        </div>
                    </div>
                    
                </td>
            </tr>
        </table>        
        </div>
    </div>
</div>
</body>
</html>