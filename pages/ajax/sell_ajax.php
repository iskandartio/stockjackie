<?php
if ($type=='search') {
	$where="1=1";
	$params=array();
	if ($sell_from_date!='') {
		$where.=" and sell_date >= ?";
		array_push($params, $sell_from_date);		
	}
	if ($sell_to_date!='') {
		$where.=" and sell_date <= ?";
		array_push($params, $sell_to_date);		
	}
	Data::refreshData('m_customer');
	$res=db::select('sell a','*',$where, 'a.sell_date, a.rowid',$params);
	
	shared::setId('sell', 'rowid', $res);
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Sell Date</th><th>Customer Name</th><th>Detail</th><th></th></tr></thead><tbody>";
	foreach ($res as $rs) {
		$result.="<tr><td>".$rs['id']."</td><td>".formatDate($rs['sell_date'])."</td>
			<td><span class='customer hidden'>".Data::getRandomKey('m_customer', $rs['customer_id'])."</span>
				<span class='customer_name'>".Data::getName('m_customer', $rs['customer_id'])."</span>
			</td>";
		$result.="<td><span class='span_show_detail'><button class='btn_detail button_link'>Detail</button></span>
					<span class='span_hide_detail'></span></td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</tbody></table>";
	$data['result']=$result;
	$data['customer_choice']=Data::getChoice('m_customer','customer_name');
	$adder="<tr><td></td><td>"._t2("sell_date")."</td><td>"._t2("customer")."</td><td></td><td>".getImageTags(['save','delete'])."</td></tr>";
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='delete') {
	$rowid=shared::getId('sell', $rowid);
	try {
		db::delete('sell','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
if ($type=='save') {
	$customer=$_POST['customer'];
	unset($_POST['customer']);
	$_POST['customer_id']=Data::getId('m_customer', $customer);
	$con=db::beginTrans();
	if ($_POST['customer_id']=='') {
		$_POST['customer_id']=db::select_single('m_customer', 'rowid v', 'customer_name=?','', array($customer_name), $con);
		
		if ($_POST['customer_id']==null) {
			$_POST['customer_id']=db::insert('m_customer', 'customer_name', array($customer_name), $con);
			
		}
	}
	$_POST['rowid']=shared::getId('sell', $_POST['rowid']);
	unset($_POST['customer_name']);
	
	
	$rowid=db::saveSimpleTrans('sell', $_POST, $con);
	db::commitTrans($con);
	$random_key=shared::random(12);
	$_SESSION['sell'][$random_key]=$rowid;
	die($random_key);
}
if  ($type=='detail') {
	Data::refreshData('m_stock');
	$sell_id=shared::getId('sell', $rowid);
	
	$res=db::select('sell_detail','*','sell_id=?', 'rowid', array($sell_id));
	shared::setId('sell_detail', 'rowid', $res);
	$grandTotal=0;
	$result="<button class='btn_hide button_link'>Hide</button>";
	$result.="<button class='btn_add button_link'>Add</button>";
	$result.="<table id='tbl_detail' class='tbl_inside'><thead><tr><th>id</th><th>Stock Name</th><th>QTY</th><th>Price</th><th>Total</th><th></th></tr></thead><tbody>";
	foreach ($res as $rs) {
		$total=$rs['qty']*$rs['price'];
		
		$result.="<tr><td>".$rs['id']."</td><td>
				<span class='stock hidden'>".Data::getRandomKey('m_stock', $rs['stock_id'])."</span>
				<span class='stock_name'>".Data::getName('m_stock', $rs['stock_id'])."</span></td>
			<td align='right'>".$rs['qty']."</td><td align='right'>".formatNumber($rs['price'],4)."</td><td align='right'>".formatNumber($total,4)."</td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
		$grandTotal+=$total;
	}
	$result.="</tbody><tfoot><tr><td colspan='3' align='right'>Total</td><td align='right'>".formatNumber($grandTotal,4)."</td><td></td></tr></tfoot></table>";
	
	$stock_choice=array();
	foreach ($_SESSION['data']['m_stock']['data'] as $key=>$val) {
		array_push($stock_choice, array('label'=>$val['stock_name'], 'value'=>$val['random_key']));
	}
	
	$data['result']=$result;
	$data['stock_choice']=$stock_choice;
	$adder="<tr><td></td><td>"._t2("stock")."</td><td align='right'>"._t2("qty","",3)."</td><td align='right'>"._t2("price","",3)."</td><td align='right'></td><td>".getImageTags(['save','delete'])."</td></tr>";
	$data['adder']=$adder;
	die(json_encode($data));
}

if ($type=='save_detail') {
	$stock=$_POST['stock'];
	unset($_POST['stock']);
	$_POST['stock_id']=Data::getId('m_stock', $stock);
	$con=db::beginTrans();
	if ($_POST['stock_id']=='') {
		$_POST['stock_id']=db::select_single('m_stock', 'rowid v', 'stock_name=?','', array($stock_name), $con);
		
		if ($_POST['stock_id']==null) {
		
			$_POST['stock_id']=db::insert('m_stock', 'stock_name', array($stock_name), $con);
			
		}
	} 
	$_POST['rowid']=shared::getId('sell_detail', $_POST['rowid']);
	
	unset($_POST['stock_name']);
	$_POST['sell_id']=shared::getId('sell', $sell_id);
	$rowid=db::saveSimpleTrans('sell_detail', $_POST, $con);
	db::commitTrans($con);
	$random_key=shared::random(12);
	$_SESSION['sell_detail'][$random_key]=$rowid;
	die($random_key);
}
if ($type=='delete_detail') {
	$rowid=shared::getId('sell_detail', $rowid);
	try {
		db::delete('sell_detail','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;

}

if ($type=='get_last_price') {
	$stock=$_POST['stock'];
	unset($_POST['stock']);
	$stock_id=Data::getId('m_stock', $stock);
	
	$sql="select a.price v from sell_detail a";
	$sql.=" left join sell b on a.sell_id=b.rowid";
	$sql.=" where a.stock_id=? order by b.sell_date desc, a.rowid desc limit 1";
	$price= db::DoQuerySingle($sql, array($stock_id));
	die($price);
}
?>