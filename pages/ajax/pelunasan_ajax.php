<?php
if ($type=='get_support_data') {	
	Data::refreshData('m_supplier');
	$data['supplier_choice']=Data::getChoice('m_supplier','supplier_name');
	$data['payment_method']=shared::select_combo_complete(db::select('payment_method','*'), 'payment_method_id','-Payment Method-','payment_method_name');
	$_SESSION['payment_method_option']=$data['payment_method'];
	die(json_encode($data));
}
if ($type=='load_data') {
	$result="";
	$result.= _t2("from_created_date", date('Y-m-01'))." - "._t2("to_created_date");
	$result.= "<button class='button_link' id='btn_search'>Search</button><div id='divlist'>&nbsp;</div>";
	$data['result']=$result;
	die(json_encode($data));
}
if ($type=='load_data_new') {
	$result="<button class='button_link' id='btn_new'>New</button>";
	$result.="<h1>Pelunasan</h1>";
	$result.="<button class='button_link' id='btn_add'>Add</button>";
	$result.="<table class='tbl' id='tbl_payment'><thead><tr><th></th><th>Payment Method</th><th>No Giro</th><th>Disburstment Date</th><th>Nominal</th><th></th></tr></thead><tbody>";
	$supplier_id=Data::getId('m_supplier', $supplier_id);
	$adder="<tr><td></td><td>@@PaymentMethod</td><td>"._t2("no_giro")."</td><td>"._t2("disburstment_date")."</td><td>"._t2("nominal")."</td>";
	$adder.="<td>".getImageTags(['save','delete'])."</td></tr>";
	$result.="@@Adder";
	$result.="</tbody></table>";
	$result.=Pelunasan::load_detail(0, $supplier_id);
	
	$data['result']=$result;
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='save_payment') {
	$con=db::beginTrans();
	$supplier_id=Data::getId('m_supplier', $supplier_id);
	$_POST['pelunasan_id']=shared::getId('pelunasan', $_POST['pelunasan_id']);
	if ($_POST['pelunasan_id']=='') {
		$_POST['pelunasan_id']=db::insert('pelunasan', 'supplier_id', array($supplier_id));
	}
	unset($_POST['supplier_id']);
	$_POST['rowid']=shared::getId('payment', $_POST['rowid']);
	
	$rowid=db::saveSimpleTrans('payment', $_POST, $con);
	db::commitTrans($con);
	$random_key=shared::random(12);
	$data['rowid']=$random_key;
	$_SESSION['payment'][$random_key]=$rowid;
	$random_key=shared::random(12);
	$data['pelunasan_id']=$random_key;
	$_SESSION['pelunasan'][$random_key]=$_POST['pelunasan_id'];
	die(json_encode($data));
}
if ($type=='save_data') {
	$con=db::beginTrans();
	$pelunasan_id=shared::getId('pelunasan', $_POST['pelunasan_id']);
	$buy_lunas_data=array();
	db::ExecMe("update retur set pelunasan_id=null where pelunasan_id=$pelunasan_id", array(), $con);
	if (isset($arr_retur)) {
		for ($i=0;$i<count($arr_retur);$i++) {
			$id=shared::getId('retur', $arr_retur[$i]);
			db::update('retur', 'pelunasan_id', 'rowid=?', array($pelunasan_id, $id), $con);
		}
	}
	for($i=0;$i<count($arr_buy_id);$i++) {
		$buy_lunas_id=$arr_buy_lunas_id[$i];
		if ($buy_lunas_id!='') {
			$id=shared::getId('buy_lunas', $buy_lunas_id);
			
			if ($arr_paid[$i]=='-' || $arr_paid[$i]=='') {
				db::delete('buy_lunas', 'rowid=?', array($id), $con);
				$buy_lunas_data[$arr_buy_id[$i]]=' ';
			} else {
				db::update('buy_lunas', 'value', 'rowid=?', array($arr_paid[$i], $id), $con);
			}	
			
		} else {
			$buy_id=shared::getId('buy_detail', $arr_buy_id[$i]);
			$buy_lunas_id=db::insert('buy_lunas', 'buy_id, value, pelunasan_id', array($buy_id, $arr_paid[$i], $pelunasan_id), $con); 
			$random_key=shared::random(12);
			$_SESSION['buy_lunas'][$random_key]=$buy_lunas_id;
			$buy_lunas_data[$arr_buy_id[$i]]=$random_key;
		}
	}
	db::commitTrans($con);
	$data['buy_lunas_data']=$buy_lunas_data;
	
	die(json_encode($data));
}
if ($type=='delete_payment') {
	$rowid=shared::getId('payment', $_POST['rowid']);

	$con=db::beginTrans();
	db::delete('payment', 'rowid=?', array($rowid), $con);
	db::commitTrans($con);
	die;
}
if ($type=='delete') {
	$rowid=shared::getId('pelunasan', $_POST['rowid']);
	$con=db::beginTrans();
	db::delete('payment', 'pelunasan_id=?', array($rowid), $con);
	db::delete('buy_lunas', 'pelunasan_id=?', array($rowid), $con);
	db::ExecMe("update retur set pelunasan_id=null where pelunasan_id=$rowid", array(), $con);
	db::delete('pelunasan', 'rowid=?', array($rowid), $con);
	db::commitTrans($con);
	die;
}

if ($type=='search') {
	Data::refreshData('payment_method');
	Data::refreshData('m_supplier');
	$payment_method=Data::getAllData('payment_method');
	$test_payment=array();
	foreach ($payment_method as $key=>$val) {
		array_push($test_payment, pow(2,$key-1));
	}
	
	$supplier_id=Data::getId('m_supplier', $_POST['supplier_id']);
	$result="";
	
	$sql="select a.rowid, a.supplier_id, min(b.disburstment_date) min_date
, max(b.disburstment_date) max_date
, bit_or(power(2, payment_method_id-1)) sum_payment_method_id 
, sum(nominal) sum_nominal from pelunasan a
left join payment b on b.pelunasan_id=a.rowid
	where a.supplier_id=? group by a.rowid having min_date is null  or (min_date>=? and max_date<=?)";
	if ($to_date=='') $to_date='2100-01-01';
	$res=db::DoQuery($sql, array($supplier_id, $from_date, $to_date));
	$result.="<table class='tbl' id='tbl_list'><thead><th></th><th>Supplier</th><th>Date</th><th>Payment Method</th><th>Nominal</th><th></th></thead>";
	shared::setId('pelunasan', 'rowid', $res);
	foreach ($res as $rs) {
		$result.="<tr><td>".$rs['id']."</td><td>".Data::getName('m_supplier', $rs['supplier_id'])."</td>";
		if ($rs['min_date']==$rs['max_date']) {
			$result.="<td>".formatDate($rs['min_date'])."</td>";
		} else {
			$result.="<td>".formatDate($rs['min_date'])." - ".formatDate($rs['max_date'])."</td>";
		}
		$payment_method="";
		foreach ($test_payment as $test) {
			if (($test & $rs['sum_payment_method_id']) == $test) {
				$payment_method.=", ".Data::getName('payment_method', log($test,2)+1);
			}
		}
		if ($payment_method!="") $payment_method=substr($payment_method, 2);
		$result.="<td>$payment_method</td>";
		$result.="<td align='right'>".formatNumber($rs['sum_nominal'])."</td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td>";
		$result.="</tr>";
	}
	$result.="</table>";
	die($result);
}

if ($type=='edit') {
	$rowid=shared::getId('pelunasan', $_POST['rowid']);
	$supplier_id=db::select_single('pelunasan','supplier_id v','rowid=?','', array($rowid));
	$res=db::select('payment','*','pelunasan_id=?', '', array($rowid));
	$result="<button class='button_link' id='btn_new'>New</button>";
	$result.="<h1>Pelunasan</h1>";
	$result.="<button class='button_link' id='btn_add'>Add</button>";
	$result.="<table class='tbl' id='tbl_payment'><thead><tr><th></th><th>Payment Method</th><th>No Giro</th><th>Disburstment Date</th><th>Nominal</th><th></th></tr></thead><tbody>";
	$adder="<tr><td></td><td>@@PaymentMethod</td><td>"._t2("no_giro")."</td><td>"._t2("disburstment_date")."</td><td>"._t2("nominal")."</td>";
	$adder.="<td>".getImageTags(['save','delete'])."</td></tr>";
	shared::setId('payment', 'rowid', $res);
	foreach($res as $rs) {
		$result.="<tr><td>".$rs['id']."</td>
					<td><span style='display:none'>".$rs['payment_method_id']."</span>".Data::getName('payment_method', $rs['payment_method_id'])."</td>
					<td>".$rs['no_giro']."</td>
					<td>".formatDate($rs['disburstment_date'])."</td><td>".formatNumber($rs['nominal'])."</td>
					<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</tbody></table>";
	$result.=Pelunasan::load_detail($rowid, $supplier_id);
	
	$data['result']=$result;
	$data['adder']=$adder;
	$random_key=shared::random(12);
	$data['pelunasan_id']=$random_key;
	$_SESSION['pelunasan'][$random_key]=$rowid;
	die(json_encode($data));
}
?>