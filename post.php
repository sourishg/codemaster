<?php
	session_start();
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/code_functions.php');
	if (!isset($_SESSION['username'])){
		$locate = SITE_NAME . "/login.php";
		header("location:$locate");
	}
?>
<?php
	if (isset($_POST['submit'])){
		$message2 = "";
		$upload_errors = array(
			UPLOAD_ERR_OK 			=> "Upload was successful.",
			UPLOAD_ERR_INI_SIZE  	=> "Larger than upload_max_filesize.",
			UPLOAD_ERR_FORM_SIZE 	=> "File size must be less than 2MB.",
			UPLOAD_ERR_PARTIAL 		=> "Partial upload.",
			UPLOAD_ERR_NO_FILE 		=> "No file.",
			UPLOAD_ERR_NO_TMP_DIR 	=> "No temporary directory.",
			UPLOAD_ERR_CANT_WRITE 	=> "Can't write to disk.",
			UPLOAD_ERR_EXTENSION 	=> "File upload stopped by extension."
		);
		
		$tmp_file = $_FILES['file_upload']['tmp_name'];
		$target_file = basename($_FILES['file_upload']['name']);
		$upload_dir = "stash";
		$checkfile = "stash/$target_file";
		$newFileName = generateRandomFileName($target_file);
		while (file_exists($newFileName)){
			$newFileName = generateRandomFileName($target_file);
		}
		
		$title = mysqli_real_escape_string($con, trim(htmlentities($_POST['title'])));
		if (!file_exists($newFileName)){
			if(move_uploaded_file($tmp_file, $upload_dir."/".$newFileName)) {
				$location = "/stash/$newFileName";
					if (!$title){
						$message2 = "Please enter a title.";
					} else {
						if (strlen($title) > 80){
							$message2 = "Your title should be less than 80 characters.";
						} else {
							$command = "INSERT INTO code_stash VALUES('', '".$title."', '".$location."', '".$_SESSION['username']."')";
							$result = mysqli_query($con, $command);
							if ($result){
								$message2 = "File added to Stash successfully.";
							} else {
								$message2 = "lol";
							}
						}
					}
				} else {
					$error = $_FILES['file_upload']['error'];
					$message2 = $upload_errors[$error];
				}
			
		} else {
			$message2 = "File already exists. Choose a different file name.";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CodeMaster Editor</title>
<script src="<?php echo SITE_NAME . "/js/code_rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script type="text/javascript">
	var xmlhttp;
	var url;
	function ajax(){
		if (window.ActiveXObject){
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} else if (window.XMLHttpRequest){
			xmlhttp = new XMLHttpRequest();
		}
	}
	function submit_form(){
		if (pvalidate()){
			ajax();
			document.getElementById('results').style.display = 'block';
			document.getElementById('results').innerHTML = "Posting the code...";
			var ptitle = "ptitle=" + encodeURIComponent(document.getElementById('ptitle').value);
			var cont = document.getElementById('content').value;
			cont = window.frames['richTextField'].document.body.innerHTML;
			var content = "content=" + encodeURIComponent(cont);
			var pl = "pl=" + encodeURIComponent(document.getElementById('planguage').value);
			var privacy = "privacy=" + document.getElementById("pr").value;
			if (document.getElementById('pr').checked){
				privacy = "privacy=1";
			} else {
				privacy = "privacy=0";
			}
			function stateChanged(){
				if (xmlhttp.readyState == 4){
					document.getElementById('results').innerHTML = xmlhttp.responseText;
				}
			}
			var vars = ptitle + "&" + content + "&" + pl + "&" + privacy;
			url = "addpost.php";
			xmlhttp.onreadystatechange = stateChanged;
			xmlhttp.open("POST", url, true);
			xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xmlhttp.send(vars);
		}
	}
	function pvalidate(){
		var val = true;
		var ptitle = document.getElementById('ptitle').value;
		var content = document.getElementById('content').value;
		content = window.frames['richTextField'].document.body.innerHTML;
		var pl = document.getElementById('planguage').value;
		if (ptitle.trim() == ""){
			val = false;
			document.getElementById('ptitle').style.borderColor = "#f00";
		} else {
			if (content.trim() == ""){
				val = false;
				document.getElementById('richTextField').style.borderColor = "#f00";
			} else {
				if (pl == 0){
					val = false;
					document.getElementById("planguage").style.borderColor = "#f00";
				}
			}
		}
		return val;
	}
	function confirmStashDelete(value){
		var conf = confirm("Are you sure you want to delete this entry?");
		if (conf){ 
			location.href='<?php echo SITE_NAME; ?>/delete.php?id=' + value + "&ref=stash";
		}
	}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
</head>

<body onload="iFrameOn();">
<div id="container">
    <div id="content">
    	<div id="logo">
    	<a href="<?php echo SITE_NAME; ?>" class="logo-link"><img src="<?php echo SITE_NAME; ?>/assets/logo.png" /></a>
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
							Hi there stranger. Welcome to CodeMaster. If you don't have an account, you can always register for free.
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
                    <h2>Make a new post or upload files</h2>
                    <?php
						if (isset($_SESSION['username'])){
					?>
                    <div id="editor-area">
                    	<div id="results">
                        </div>
                        <?php
                        	if (!empty($message2)) { echo "<p class=\"success\" style=\"font-size:13pt; color:#f00;\">$message2</p>"; }
						?>
                        <h3 style="padding-bottom:0;">Code Editor</h3>                  
                        <p style="padding:5px 10px; display:inline-block;">Title:</p>
                        <select id="planguage">
                        	<option value="0">Select Language</option>
                        	<?php
                            	$sql = "SELECT * FROM code_subjects";
								$res = mysqli_query($con, $sql);
								while ($row = mysqli_fetch_assoc($res)){
									echo "<option value=\"".$row['id']."\">".stripslashes($row['title'])."</option>";
								}
							?>               
                        </select>
                        <br />
                        <input type="text" placeholder="Your title here..." name="ptitle" style="padding:5px; margin-left:10px; width:490px; border:1px solid #999; outline:none; margin-bottom:5px;" required="required" id="ptitle" /><br />
                        <div id="cp" style="padding:0;">
                            <input type="button" onclick="iBold()" value="B" />
                            <input type="button" onclick="iUnderline()" value="U" />
                            <input type="button" onclick="iItalic()" value="I" />
                            
                            <select id="fonts" onChange="iFont(this[this.selectedIndex].value)">
                                <option value="Verdana">Verdana</option>
                                <option value="Arial">Arial</option>
                                <option value="Comic Sans MS">Comic Sans MS</option>                
                                <option value="Courier New">Courier New</option>                
                                <option value="Monotype Corsiva">Monotype</option>                
                                <option value="Tahoma">Tahoma</option>                
                                <option value="Times">Times</option>                
                            </select>
                            <input type="button" onclick="inimg()" value="Y" />
                            <input type="button" onclick="iFontSize()" value="Size" />
                            <input type="button" onclick="iForeColor()" value="Color" />
                            <input type="button" onclick="iHorizontalRule()" value="Line" />
                        	<input type="checkbox" value="1" style="margin-left:10px;" name="pr" id="pr" /> Private
                        </div>                        
                        <textarea style="display:none;" name="content" id="content" required="required"></textarea>
                        <iframe name="richTextField" id="richTextField" style="width:500px; height:300px;" src =""></iframe><br />
                        <input name="submit" type="submit" value="Post" onclick="javascript:submit_form();" class="inp-submit"/>
                        <div id="stash-area">
                        	<h3 style="padding:10px 0; display:inline-block;">Your Stash</h3>
                            <?php
                            	$sql = "SELECT id FROM code_stash WHERE user = '$_SESSION[username]'";
								$res = mysqli_query($con, $sql);
								$countFiles = mysqli_num_rows($res);
							?>
                            <span style="float:right; padding-top:10px;">Total Files (<?php echo number_format($countFiles); ?>) &bull; <a href="<?php echo SITE_NAME . "/coder/$_SESSION[username]#stash"; ?>">View Stash</a></span>
                        	<p style="padding:5px 0;">File Name:</p>
                            <form action="post.php" method="post" enctype="multipart/form-data">
                            <input type="text" name="title" placeholder="File name..." style="width:490px; padding:5px; border:1px solid #666; outline:none;" id="title" /><br />
                            <input type="hidden" name="MAX_FILE_SIZE" value="2000000" /><input type="file" name="file_upload" style="margin:0; border:1px solid #666; padding:3px;" required="required" id="file_upload"/><br />
                            <input type="submit" value="Upload" name="submit" class="inp-submit" style="margin:0;" />
                        	</form>
                        </div>      
                    </div>
                    <?php 
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