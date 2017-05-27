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
			$where.=" and b.buy_date >= ?";
			array_push($params, $buy_from_date);		
		}
		if ($buy_to_date!='') {
			$where.=" and b.buy_date <= ?";
			array_push($params, $buy_to_date);		
		}
		if ($filter_supplier_name!='') {
			$where.=" and c.supplier_name=?";
			array_push($params, $filter_supplier_name);		
		}
		if ($where=="1=1") $where = "1=0";
		if ($tab=='complete') {
			$con=db::beginTrans();
			$sql="select a.buy_id, a.qty, a.price, c.supplier_name, d.stock_name, b.buy_date, b.tempo from buy_detail a
				left join buy b on b.rowid=a.buy_id";
			if ($filter_stock_name!='') {
				$sql_temp="create temporary table temp_buy select distinct a.buy_id from buy_detail a 
					left join m_stock b on a.stock_id=b.rowid
					where b.stock_name like ?";
				db::execMe($sql_temp, array("%".$filter_stock_name."%"), $con);
				$sql.=" inner join temp_buy b2 on b2.buy_id=b.rowid";
			}
			$sql.="	left join m_supplier c on c.rowid=b.supplier_id
				left join m_stock d on d.rowid=a.stock_id where $where 
				order by c.supplier_name, b.buy_date";
			
			$res = db::DoQuery($sql, $params, $con);
			db::rollbackTrans($con);
			$z = array();
			
			foreach ($res as $rs) {
				$z[$rs['buy_id']]['supplier_name']=$rs['supplier_name'];
				$z[$rs['buy_id']]['buy_date']=$rs['buy_date'];
				$z[$rs['buy_id']]['tempo']=$rs['tempo'];
				shared::setArr($z[$rs['buy_id']]['data'], $rs);
				
			}
			$result="";
			
			foreach ($z as $key=>$val) {
				$result.="<h2>".$val['supplier_name']." ".formatDate($val['buy_date'])." (".$val['tempo'].")</h2>";
				$result.="<table class='tbl'><thead><tr><th>Stock Name</th><th>QTY</th><th>Price</th><th>Total</th></tr></thead><tbody>";
				$total=0;
				foreach ($val['data'] as $rs) {
					$result.="<tr><td>".$rs['stock_name']."</td><td align='right'>".$rs['qty']."</td><td align='right'>".$rs['price']."</td><td align='right'>".formatNumber($rs['qty']*$rs['price'],4)."</td></tr>";
					$total+=$rs['qty']*$rs['price'];
				}
				$result.="<tr><td colspan='3' align='right'>Total</td><td align='right'>".formatNumber($total,4)."</td></tr></tbody></table>";
			}
		} else if ($tab=='summary') {
			$result="";
			$con=db::beginTrans();
			$sql="select sum(a.qty*a.price) total, a.buy_id, c.supplier_name, b.buy_date from buy_detail a
				left join buy b on b.rowid=a.buy_id";
			if ($filter_stock_name!='') {
				$sql_temp="create temporary table temp_buy select distinct a.buy_id from buy_detail a 
					left join m_stock b on a.stock_id=b.rowid
					where b.stock_name like ?";
				
				db::execMe($sql_temp, array("%".$filter_stock_name."%"), $con);
				$sql.=" inner join temp_buy b2 on b2.buy_id=b.rowid";
			}
			$sql.="	left join m_supplier c on c.rowid=b.supplier_id
				left join m_stock d on d.rowid=a.stock_id where $where 
				group by a.buy_id";
			$res = db::DoQuery($sql, $params, $con);
			db::rollbackTrans($con);
			$z = array();
			
			
			
			$result.="<table class='tbl'><thead><tr><th>Supplier Name</th><th>Buy Date</th><th>Total</th></tr></thead><tbody>";
			$total=0;
			foreach ($res as $rs) {
				$result.="<tr><td>".$rs['supplier_name']."</td><td>".formatDate($rs['buy_date'])."</td>
					<td align='right'>".formatNumber($rs['total'],4)."</td></td></tr>";
				$total+=$rs['total'];
			}
			$result.="<tr><td colspan='2' align='right'>Total</td><td align='right'>".formatNumber($total,4)."</td></tr></tbody></table>";
			
		} else if ($tab=='detail') {
			if ($filter_stock_name!='') {
				$where.=" and d.stock_name like ?";
				array_push($params, "%$filter_stock_name%");		
			}
			$sql="select a.qty*a.price total, a.qty, a.price, c.supplier_name, b.buy_date, d.stock_name from buy_detail a
				left join buy b on b.rowid=a.buy_id
				left join m_supplier c on c.rowid=b.supplier_id
				left join m_stock d on d.rowid=a.stock_id where $where 
				group by c.supplier_name, b.buy_date, a.rowid";

			$res = db::DoQuery($sql, $params);
			$z = array();
			
			$result="";
			
			$result.="<table class='tbl'><thead><tr><th>Supplier Name</th><th>Buy Date</th><th>Stock Name</th>
				<th>QTY</th><th>Price</th><th>Total</th></tr></thead><tbody>";
			$total=0;
			foreach ($res as $rs) {
				$result.="<tr><td>".$rs['supplier_name']."</td><td>".formatDate($rs['buy_date'])."</td>
					<td>".$rs['stock_name']."</td>
					<td align='right'>".formatNumber($rs['qty'],4)."</td>
					<td align='right'>".formatNumber($rs['price'],4)."</td>
					<td align='right'>".formatNumber($rs['total'],4)."</td></td></tr>";
				$total+=$rs['total'];
			}
			$result.="<tr><td colspan='5' align='right'>Total</td><td align='right'>".formatNumber($total,4)."</td></tr></tbody></table>";
			
		}
		
		$data['result']=$result;
	
		die(json_encode($data));
	}
?>