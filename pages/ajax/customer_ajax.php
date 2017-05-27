<?php
if ($type=='search') {
	$where="";
	$params=array();
	if ($filter_customer_name!='') {
		$where.="customer_name like ?";
		array_push($params, '%'.$filter_customer_name.'%');
	}
	$res=db::select('m_customer','rowid',$where,'customer_name',$params);
	
	Data::refreshData('m_customer');
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Customer Name</th><th>Customer Address</th><th>Customer Phone</th><th></th></tr></thead>";
	foreach ($res as $rs) {
		$rs=Data::getData('m_customer', $rs['rowid']);
		
		$result.="<tr><td>".$rs['random_key']."</td><td>".$rs['customer_name']."</td><td>".$rs['customer_address']."</td><td>".$rs['customer_phone']."</td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</table>";
	$adder="<tr><td></td><td>"._t2("customer_name")."</td>
		<td>"._t2("customer_address")."</td><td>"._t2("customer_phone")."</td>
		<td>".getImageTags(['save','delete'])."</td></tr>";
	
	$data['result']=$result;
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='delete') {
	$rowid=Data::getId('m_customer', $rowid);
	try {
		db::delete('m_customer','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
if ($type=='save') {
	die(db::saveSimple('m_customer', $_POST));
}
?>