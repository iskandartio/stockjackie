<?php
if ($type=='search') {
	$where="1=1";
	$params=array();
	if ($trans_from_date!='') {
		$where.=" and a.disburstment_date >= ?";
		array_push($params, $trans_from_date);		
	}
	if ($trans_to_date!='') {
		$where.=" and a.disburstment_date <= ?";
		array_push($params, $trans_to_date);		
	}
	Data::refreshData('m_supplier');
	$con=db::beginTrans();
	$sql="select a.*, b.supplier_id from payment a";
	$sql.=" left join pelunasan b on b.rowid=a.pelunasan_id";
	$sql.=" where $where and length(a.no_giro)>2 order by a.disburstment_date";
	$res = db::DoQuery($sql, $params, $con);
	shared::setId('payment', 'rowid', $res);
	$result="";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Trans Date</th><th>Supplier Name</th><th>No Giro</th><th>Nominal</th><th></th></tr></thead><tbody>";
	foreach ($res as $rs) {
		$result.="<tr><td>".$rs['id']."</td><td>".formatDate($rs['disburstment_date'])."</td>
			<td>".Data::getName('m_supplier', $rs['supplier_id'])."</td>
			<td>".$rs['no_giro']."</td>
			<td align='right'>".formatNumber($rs['nominal'])."</td>";
		$result.="<td>".getImageTags(['edit'])."</td></tr>";
	}
	$result.="</tbody></table>";
	$data['result']=$result;
	
	die(json_encode($data));
}
if ($type=='save') {
	$_POST['rowid']=shared::getId('payment', $_POST['rowid']);
	db::updateEasy('payment', $_POST);
	die;
}

?>