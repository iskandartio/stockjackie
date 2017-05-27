<?php
if ($type=='get_supplier_choice') {
	Data::refreshData('m_supplier');
	$data['supplier_choice']=shared::getSupplierChoice();
	die(json_encode($data));
}
if ($type=='get_stock_choice') {
	Data::refreshData('m_stock');
	$data['stock_choice']=shared::getstockChoice();
	die(json_encode($data));
}
if ($type=='search') {
	$where="1=1";
	$params=array();
	
	if ($buy_from_date!='') {
		$where.=" and a.buy_date >= ?";
		array_push($params, $buy_from_date);		
	}
	if ($buy_to_date!='') {
		$where.=" and a.buy_date <= ?";
		array_push($params, $buy_to_date);		
	}
	if ($filter_supplier_name!='') {
		$where.=" and b.supplier_name=?";
		array_push($params, $filter_supplier_name);		
	}
	
	Data::refreshData('m_supplier');
	$con=db::beginTrans();
	$sql="select a.* from buy a";
	$sql.=" left join m_supplier b on b.rowid=a.supplier_id";
	if ($filter_stock_name!='') {
		$sql_temp="create temporary table temp_buy select distinct a.buy_id from buy_detail a 
			left join m_stock b on a.stock_id=b.rowid
			where b.stock_name=?";
		db::execMe($sql_temp, array($filter_stock_name), $con);
		$sql.=" inner join temp_buy b2 on b2.buy_id=a.rowid";
	}
	$sql.=" where $where";
	$res = db::DoQuery($sql, $params, $con);
	shared::setId('buy', 'rowid', $res);
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Buy Date</th><th>Supplier Name</th><th>Tempo</th><th>Detail</th><th></th></tr></thead><tbody>";
	foreach ($res as $rs) {
		$result.="<tr><td>".$rs['id']."</td><td>".formatDate($rs['buy_date'])."</td>
			<td><span class='supplier hidden'>".Data::getRandomKey('m_supplier', $rs['supplier_id'])."</span>
				<span class='supplier_name'>".Data::getName('m_supplier', $rs['supplier_id'])."</span>
			</td><td>".$rs['tempo']."</td>";
		$result.="<td><span class='span_show_detail'><button class='btn_detail button_link'>Detail</button></span>
					<span class='span_hide_detail'></span></td>";
		$result.="<td>".getImageTags(['edit','delete'])."</td></tr>";
	}
	$result.="</tbody></table>";
	$data['supplier_choice']=Data::getChoice('m_supplier','supplier_name','tempo');
	$data['result']=$result;
	
	$adder="<tr><td></td><td>"._t2("buy_date")."</td><td>"._t2("supplier")."</td><td>"._t2("tempo","",8)."</td><td></td><td>".getImageTags(['save','delete'])."</td></tr>";
	$data['adder']=$adder;
	die(json_encode($data));
}
if ($type=='delete') {
	$rowid=shared::getId('buy', $rowid);
	try {
		db::delete('buy','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
if ($type=='save') {
	$supplier=$_POST['supplier'];
	unset($_POST['supplier']);
	$_POST['supplier_id']=Data::getId('m_supplier', $supplier);
	$con=db::beginTrans();
	if ($_POST['supplier_id']=='') {
		$_POST['supplier_id']=db::select_single('m_supplier', 'rowid v', 'supplier_name=?','', array($supplier_name), $con);
		
		if ($_POST['supplier_id']==null) {
			$_POST['supplier_id']=db::insert('m_supplier', 'supplier_name', array($supplier_name), $con);
			
		}
	}
	$_POST['rowid']=shared::getId('buy', $_POST['rowid']);
	unset($_POST['supplier_name']);
	
	
	$rowid=db::saveSimpleTrans('buy', $_POST, $con);
	db::commitTrans($con);
	$random_key=shared::random(12);
	$_SESSION['buy'][$random_key]=$rowid;
	die($random_key);
}
if  ($type=='detail') {
	Data::refreshData('m_stock');
	$buy_id=shared::getId('buy', $rowid);
	
	$res=db::select('buy_detail','*','buy_id=?', 'rowid', array($buy_id));
	shared::setId('buy_detail', 'rowid', $res);
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
	$data['supplier']=$supplier;
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
	$_POST['rowid']=shared::getId('buy_detail', $_POST['rowid']);
	
	unset($_POST['stock_name']);
	$_POST['buy_id']=shared::getId('buy', $buy_id);
	$rowid=db::saveSimpleTrans('buy_detail', $_POST, $con);
	db::commitTrans($con);
	$random_key=shared::random(12);
	$_SESSION['buy_detail'][$random_key]=$rowid;
	die($random_key);
}
if ($type=='delete_detail') {
	$rowid=shared::getId('buy_detail', $rowid);
	try {
		db::delete('buy_detail','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;

}

if ($type=='get_last_price') {
	$stock=$_POST['stock'];
	unset($_POST['stock']);
	$stock_id=Data::getId('m_stock', $stock);
	$supplier_id=Data::getId('m_supplier', $_POST['supplier']);
	$sql="select a.price v from buy_detail a";
	$sql.=" left join buy b on a.buy_id=b.rowid";
	$sql.=" where a.stock_id=? and b.supplier_id=? order by b.buy_date desc, a.rowid desc limit 1";
	
	$price= db::DoQuerySingle($sql, array($stock_id, $supplier_id));
	die($price);
}
?>