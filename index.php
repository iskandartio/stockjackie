<?php 

require_once("pages/startup.php");

$_SESSION['home_dir']='/php/';


//$_SESSION['home_dir']='/';

if (!isset($_SERVER['REQUEST_SCHEME'])) {
	$scheme="http";
} else {
	$scheme=$_SERVER['REQUEST_SCHEME'];
}

$_SESSION['home']=$scheme."://".$_SERVER['HTTP_HOST'].$_SESSION['home_dir'];


if (isset($_SESSION['home_dir'])) {
	
	$home_dir=$_SESSION['home_dir'];
	$page_name=str_replace($home_dir, "", $_SERVER['REQUEST_URI']);

	if (strpos($page_name,'?')>0) {
		$page_name=substr($page_name,0,strpos($page_name, "?"));	
	}
} else {
	$page_name="";
}
if ($page_name=='logout') {
	setcookie('user_id', null,-1);
	setcookie('password', null,-1);
}
$flag=0;
$title='';

if ($page_name=='') {
	if (!isset($_SERVER['REQUEST_SCHEME'])) {
		$scheme="http";
	} else {
		$scheme=$_SERVER['REQUEST_SCHEME'];
	}

	$_SESSION['home']=$scheme."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$_SESSION['home_dir']=$_SERVER['REQUEST_URI'];
	$page_name="login";

} else {
	
	$general_menu=['activate','index','login','show_picture','captcha','send_email','services','download'];
	
	$p=str_replace("_ajax","",$page_name);
	
	if (!in_array($p, $general_menu)) {
		if (isset($_SESSION['allowed_module']) && count($_SESSION['allowed_module'])>0) {
			
			if (isset($_SESSION['allowed_module'][$p])) {				
				$title=key($_SESSION['allowed_module'][$p]);
			} else {
				header("Location: ".$_SESSION['home']);
				die;
			}
			if ($_SESSION['allowed_module'][$p][$title]==0) {
				setcookie('url', $p, time() + 3600*48); 
			}
		} else {
		
			header("Location: ".$_SESSION['home']);
			die;
		}
	}
}

$maxWidth=0;

if (isset($maxWidthArr[$page_name])) {
	$maxWidth=$maxWidthArr[$page_name];
} else {
	
}


if (endsWith($page_name,'_ajax')) {

	header('Content-Type: text/html; charset=utf-8');
	
	foreach ($_POST as $key=>$value) {
		
		if (startsWith($key,'date')||endsWith($key,'date')) {
			if (!is_array($value)) {
				$$key=dbDate($value);	
				$_POST[$key]=$$key;
				db::Log($key);
			} else {
				foreach ($value as $key2=>$val) {
					$_POST[$key][$key2]=dbDate($val);
				}
				$$key=$_POST[$key];
			}
		} else {
			$$key=$value;
		}					
	}
		
	if (isset($captcha_text)) {
		if ($_SESSION['captcha_text']!=$captcha_text) {
			$data['err']='Wrong captcha text';
			$data['captcha_tag']=shared::get_captcha_text(true);
			$data['focus']='#captcha_text';
			die(json_encode($data));
		}
	}
	
	include("pages/ajax/$page_name.php");
	
	die;
}
if ($page_name=='services') {
	include("pages/$page_name.php");
	die;
}

$_SESSION['page_name']=$page_name;

if ($page_name=='login') {
	unset($_SESSION['uid']);
	unset($_SESSION['data']);
	unset($_SESSION['allowed_module']);
}

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title><?php _p("GIZ HRMS");?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
    <link rel="stylesheet" href="css/jquery-ui.css"/>
	<link rel="stylesheet" href="css/default.css"/>

<link rel="apple-touch-icon" sizes="57x57" href="icon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="icon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="icon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="icon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="icon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="icon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="icon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="icon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="icon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="icon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="icon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">
<link rel="manifest" href="icon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="icon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">

	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	
	<script src="js/general.js"></script>
	<script src="js/numeric.js"></script>
	
	<script>
		$(function() {
			$('#freeze').hide();
			autoSelect();
		});
	</script>
<?php if ($page_name!="login") {?>
	<script src='js/menu.js'></script>
	<script>
		$(function() {
			var a=menu('<?php _p($home_dir)?>', '<?php _p($maxWidth)?>', '<?php _p($page_name)?>');
		});
		
	</script>
<?php }?>
</head>
<body>
	<div id="freeze" style="position: fixed; top: 0px; left: 0px; z-index: 1000; opacity: 0.6; width: 100%; height: 100%; color: white; background-color: black;"></div>
<?php if ($page_name=='login') {		
		include("pages/login.php");
		
	} else {
		_p("<div id='menu' class='menu'>");
		_p(getImageTags(array('hide')));
		_p($_SESSION['create_menu']);
		_p("<ul><li><a href='download?type=app'>Android Application</a></li></ul>");
		_p("</div>");
	}?>
<?php if ($page_name!="login") {?>
	<?php _p(getImageTags(array('menu')))?>
    
	<div id="pagecontent" class='pagecontent'>

		<h3><?php _p($title)?></h3>
		
		<div style="margin:5px">
        <?php
			include("pages/$page_name.php");
        ?>
		</div>
    </div>
<?php }?>

</body>

</html>
