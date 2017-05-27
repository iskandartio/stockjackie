<?php

if ($type=='login') {
	
	$data['err']='';
	
	$res=db::select_one('m_user','rowid, user_name, pwd', 'user_name=? and pwd=?','',array($_POST['email'], $_POST['password']));
	
	if ($res==null || count($res)==0) {
		$data['err']='Wrong User Name or Password or Not Activated!';			
		die (json_encode($data));
	}
	$_SESSION['uid']=$res['rowid'];
	
	$res=db::DoQuery('select b.role_name from m_user_role a
	left join m_role b on a.role_id=b.rowid
	where a.user_id=?',array($_SESSION['uid']));
	$_SESSION['role_name']=array();
	foreach ($res as $rs) {
		array_push($_SESSION['role_name'], $rs['role_name']);
	}
	$res=db::DoQuery('select distinct c.module_name, c.module_description, c.sub_module, d.category_name from m_user_role a 
		inner join m_role_module b on a.role_id=b.role_id
		inner join m_module c on c.rowid=b.module_id
		inner join m_category d on d.rowid=c.category_id
		where a.user_id=? order by d.sort_id, c.sort_id, c.module_description', array($_SESSION['uid']));	
	$_SESSION['create_menu']=shared::create_menu($res);
	$allowed=array();
	foreach ($res as $rs) {
		$allowed[$rs['module_name']][$rs['module_description']]=$rs['sub_module'];
	}
	$_SESSION['allowed_module']=$allowed;
	
	$url="";
	$flag=0;
	if (isset($_COOKIE['url'])) {
		$url=$_COOKIE['url'];
		$p=str_replace("_ajax","",$url);
		if (isset($_SESSION['allowed_module'][$p])) {				
			$flag=1;
		}
	}
	
	if (count($_SESSION['allowed_module'])>0 && $flag==0) {
		reset($_SESSION['allowed_module']);
		$url=key($_SESSION['allowed_module']);
	}
	
	
	$data['url']=$url;
	die (json_encode($data));
} 

?>