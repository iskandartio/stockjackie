<?php
	if ($type=='fix') {	
		$sql="insert into buy_detail(buy_id, stock_id, qty)
select a.rowid, b.* from buy a, (
select a.stock_id, -sum(qty) from (
select a.stock_id, a.buy_id, buy_date, qty from buy_detail a
left join buy b on a.buy_id=b.rowid
left join m_supplier c on b.supplier_id=c.rowid
where c.supplier_name='Jackie Jaya'
union all
select a.stock_id, 0, sell_date, -qty qty from sell_detail a
left join sell b on a.sell_id=b.rowid
left join m_customer c on b.customer_id=c.rowid
where c.customer_name ='Jackie Jaya') a
group by a.stock_id having sum(qty)<0) b
where a.buy_date ='2000-01-01'";
		
		$i=db::ExecMe($sql);
		if ($i==0) die("Already fixed");
		die("Success");
	}
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
		$con=db::beginTrans();
		$inner_table="";
		db::ExecMe("drop table if exists temp_stock");
		
		if ($filter_stock!='') {
			$sql="create table temp_stock select rowid from m_stock where stock_name like ?";
			db::ExecMe($sql, array('%'.$filter_stock.'%'), $con);
			$inner_table=" inner join temp_stock a2 on a.stock_id=a2.rowid";
		}
		
		$sql="select a.stock_id, b.stock_name, a.qty from (
select a.stock_id, sum(qty) qty from (
select a.stock_id, a.buy_id, buy_date, qty from buy_detail a
$inner_table
left join buy b on a.buy_id=b.rowid
left join m_supplier c on b.supplier_id=c.rowid
where c.supplier_name='Jackie Jaya'
union all
select a.stock_id, 0, sell_date, -qty qty from sell_detail a
$inner_table
left join sell b on a.sell_id=b.rowid
left join m_customer c on b.customer_id=c.rowid
where c.customer_name ='Jackie Jaya') a
group by a.stock_id) a
left join m_stock b on a.stock_id=b.rowid 
order by b.stock_name";
		
		$res=db::DoQuery($sql, array(), $con);
		shared::setId('stock_report', 'stock_id', $res);
		$result="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>Stock Name</th><th>QTY</th><th></th></tr></thead>";
		foreach ($res as $rs) {
			$result.="<tr><td>".$rs['id']."</td><td>".$rs['stock_name']."</td><td>".$rs['qty']."</td><td>".getImageTags(['detail'])."<div class='div_detail'></div></td></tr>";
		}
		$result.="</table>";
		$data['result']=$result;
		db::rollbackTrans($con);
		die(json_encode($data));
	}
	
	if ($type=='detail') {
		$rowid=shared::getId('stock_report', $rowid);
		$where="a.stock_id=$rowid";
		$where2=$where;
		$params=array();
		if ($from_date!='') {
			$where.=" and b.buy_date >= ?";
			array_push($params, $from_date);		
		}
		if ($to_date!='') {
			$where.=" and b.buy_date <= ?";
			array_push($params, $to_date);		
		}
		if ($from_date!='') {
			$where2.=" and b.sell_date >= ?";
			array_push($params, $from_date);		
		}
		if ($to_date!='') {
			$where2.=" and b.sell_date <= ?";
			array_push($params, $to_date);		
		}
		$sql="select b.buy_date trans_date, a.qty from buy_detail a 
			left join buy b on a.buy_id=b.rowid
			inner join m_supplier c on b.supplier_id=c.rowid and c.supplier_name='Jackie Jaya'
			where ".$where;
		$sql.=" union all select b.sell_date trans_date, -a.qty from sell_detail a 
			left join sell b on a.sell_id=b.rowid
			inner join m_customer c on b.customer_id=c.rowid and c.customer_name='Jackie Jaya'
			where ".$where2;
		
		$res=db::DoQuery($sql, $params);
		$result="<table id='tbl' class='tbl'><thead><tr><th>Date</th><th>QTY</th></tr></thead>";
		foreach ($res as $rs) {
			$result.="<tr><td>".formatDate($rs['trans_date'])."</td><td>".$rs['qty']."</td></tr>";
		}
		$result.="</table>";
		$data['result']=$result;
		die(json_encode($data));
	}

?>