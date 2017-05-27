<?php
if ($type=='search') {
	$where="";
	$params=array();
	
	if ($filter_stock_name!='') {
		$z=explode(" ",$filter_stock_name);
		$where="1=1";
		foreach ($z as $s) {
			$where.=" and stock_name like ?";
			array_push($params, '%'.$s.'%');
		}
		
	}
	$res=db::select('m_stock','rowid',$where,'stock_name',$params);
	Data::refreshData('m_stock');
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Stock Name</th><th></th></tr></thead>";
	foreach ($res as $rs) {
		$rs=Data::getData('m_stock', $rs['rowid']);
		$result.="<tr><td>".$rs['random_key']."</td><td>".$rs['stock_name']."</td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</table>";
	$adder="<tr><td></td><td>"._t2("stock_name")."</td>
		<td>".getImageTags(['save','delete'])."</td></tr>";
	
	$data['result']=$result;
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='delete') {
	$rowid=Data::getId('m_stock', $rowid);
	try {
		db::delete('m_stock','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
if ($type=='save') {
	die(db::saveSimple('m_stock', $_POST));
}
?>