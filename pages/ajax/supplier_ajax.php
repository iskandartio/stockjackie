<?php
if ($type=='search') {
	$where="";
	$params=array();
	if ($filter_supplier_name!='') {
		$where.="supplier_name like ?";
		array_push($params, '%'.$filter_supplier_name.'%');
	}
	$res=db::select('m_supplier','rowid',$where,'supplier_name',$params);
	Data::refreshData('m_supplier');
	
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Supplier Name</th><th>Tempo</th><th>Supplier Address</th><th>Supplier Phone</th><th></th></tr></thead>";
	foreach ($res as $rs) {
		$rs=Data::getData('m_supplier', $rs['rowid']);
		$result.="<tr><td>".$rs['random_key']."</td><td>".$rs['supplier_name']."</td><td>".$rs['tempo']."</td><td>".$rs['supplier_address']."</td><td>".$rs['supplier_phone']."</td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</table>";
	$adder="<tr><td></td><td>"._t2("supplier_name")."</td><td>"._t2("tempo")."</td>
		<td>"._t2("supplier_address")."</td><td>"._t2("supplier_phone")."</td>
		<td>".getImageTags(['save','delete'])."</td></tr>";
	$data['result']=$result;
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='delete') {
	$rowid=Data::getId('m_supplier', $rowid);
	try {
		db::delete('m_supplier','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
if ($type=='save') {
	die(db::saveSimple('m_supplier', $_POST));
}
?>