<?php
	require("pages/startup.php");
	$input = array(12, 10, 9);
	$result = array_slice($input, 2);
	print_r($result);
	die;
	$res=db::DoQuery("show tables");
	
	foreach ($res as $rs) {	
		$sql="alter table ".$rs[key($rs)]." change column created_at created_at datetime NOT NULL";
		//alter table m_supplier add created_at datetime default now(), add updated_at datetime default now() on update now(), add updated_by varchar(50);
		db::ExecMe($sql);
	}
	
?>