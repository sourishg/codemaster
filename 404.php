<?php
	session_start();
	include('includes/code_functions.php');
	include('includes/constants.php');
	include('includes/connect.php');
	include('includes/pagination.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>404 Not Found</title>
<script src="<?php echo SITE_NAME . "/js/rtf.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/webforms2.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/jquery-1.7.1.min.js"; ?>" type="text/javascript"></script>
<script src="<?php echo SITE_NAME . "/js/charts.js"; ?>" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/code.css"; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo SITE_NAME . "/css/extra.css"; ?>" />
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
</script>
</head>

<body style="background:#E4ECE9;">
<div id="fourofour">
	<table width="100%" border="0">
    	<tr>
        	<td width="300" valign="middle"><img src="<?php echo SITE_NAME . "/assets/oops.png"; ?>" /></td>
            <td valign="middle">
            <p style="padding:10px; font-family:Verdana, Geneva, sans-serif; font-size:9pt;">The requested url was not found.</p>
            <a href="<?php echo SITE_NAME; ?>" id="b2hm">&laquo; Go back to our homepage</a>
            </td>
        </tr>
    </table>
</div>
</body>
</html>