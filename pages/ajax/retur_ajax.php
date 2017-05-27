<?php
if ($type=='search') {
	$where="";
	$params=array();
	if ($filter_supplier_name!='') {
		$where.="b.supplier_name like ?";
		array_push($params, '%'.$filter_supplier_name.'%');
	}
	$res=db::select('retur a left join m_supplier b on a.supplier_id=b.rowid','a.*',$where,'trans_date, supplier_name',$params);
	shared::setId('retur', 'rowid', $res);
	
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Date</th><th>Supplier Name</th><th>Description</th><th>Value</th><th></th></tr></thead><tbody>";
	foreach ($res as $rs) {
		$result.="<tr><td>".$rs['id']."</td><td>".formatDate($rs['trans_date'])."</td><td><span class='supplier hidden'>".Data::getRandomKey('m_supplier', $rs['supplier_id'])."</span>
				<span class='supplier_name'>".Data::getName('m_supplier', $rs['supplier_id'])."</span></td><td>".$rs['description']."</td><td>".$rs['value']."</td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</tbody></table>";
	Data::refreshData('m_supplier');
	$data['supplier_choice']=Data::getChoice('m_supplier','supplier_name');
	$adder="<tr><td></td><td>"._t2("trans_date","",8)."</td><td>"._t2("supplier")."</td>
		<td>"._t2("description")."</td><td>"._t2("value","",8)."</td>
		<td>".getImageTags(['save','delete'])."</td></tr>";
	$data['result']=$result;
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='delete') {
	$rowid=shared::getId('retur', $rowid);
	
	try {
		db::delete('retur','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
if ($type=='save') {
	
	$supplier=$_POST['supplier'];
	unset($_POST['supplier']);
	$_POST['rowid']=shared::getId('retur', $rowid);
	$_POST['supplier_id']=Data::getId('m_supplier', $supplier);
	$rowid=db::saveSimpleTrans('retur', $_POST);
	$random_key=shared::random(12);
	$_SESSION['retur'][$random_key]=$rowid;
	die($random_key);
}
?>