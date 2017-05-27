<?php
	if ($type=='get_customer_choice') {
		Data::refreshData('m_customer');
		$data['customer_choice']=shared::getCustomerChoice();
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
		if ($sell_from_date!='') {
			$where.=" and b.sell_date >= ?";
			array_push($params, $sell_from_date);		
		}
		if ($sell_to_date!='') {
			$where.=" and b.sell_date <= ?";
			array_push($params, $sell_to_date);		
		}
		if ($filter_customer_name!='') {
			$where.=" and c.customer_name=?";
			array_push($params, $filter_customer_name);		
		}
		if ($where=="1=1") $where = "1=0";
		if ($tab=='complete') {
			$con=db::beginTrans();
			$sql="select a.sell_id, a.qty, a.price, c.customer_name, d.stock_name, b.sell_date, b.tempo from sell_detail a
				left join sell b on b.rowid=a.sell_id";
			if ($filter_stock_name!='') {
				$sql_temp="create temporary table temp_sell select distinct a.sell_id from sell_detail a 
					left join m_stock b on a.stock_id=b.rowid
					where b.stock_name like ?";
				db::execMe($sql_temp, array("%".$filter_stock_name."%"), $con);
				$sql.=" inner join temp_sell b2 on b2.sell_id=b.rowid";
			}
			$sql.="	left join m_customer c on c.rowid=b.customer_id
				left join m_stock d on d.rowid=a.stock_id where $where 
				order by c.customer_name, b.sell_date";
			
			$res = db::DoQuery($sql, $params, $con);
			db::rollbackTrans($con);
			$z = array();
			
			foreach ($res as $rs) {
				$z[$rs['sell_id']]['customer_name']=$rs['customer_name'];
				$z[$rs['sell_id']]['sell_date']=$rs['sell_date'];
				$z[$rs['sell_id']]['tempo']=$rs['tempo'];
				shared::setArr($z[$rs['sell_id']]['data'], $rs);
				
			}
			$result="";
			
			foreach ($z as $key=>$val) {
				$result.="<h2>".$val['customer_name']." ".formatDate($val['sell_date'])." (".$val['tempo'].")</h2>";
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
			$sql="select sum(a.qty*a.price) total, a.sell_id, c.customer_name, b.sell_date from sell_detail a
				left join sell b on b.rowid=a.sell_id";
			if ($filter_stock_name!='') {
				$sql_temp="create temporary table temp_sell select distinct a.sell_id from sell_detail a 
					left join m_stock b on a.stock_id=b.rowid
					where b.stock_name like ?";
				
				db::execMe($sql_temp, array("%".$filter_stock_name."%"), $con);
				$sql.=" inner join temp_sell b2 on b2.sell_id=b.rowid";
			}
			$sql.="	left join m_customer c on c.rowid=b.customer_id
				left join m_stock d on d.rowid=a.stock_id where $where 
				group by a.sell_id";
			$res = db::DoQuery($sql, $params, $con);
			db::rollbackTrans($con);
			$z = array();
			
			
			
			$result.="<table class='tbl'><thead><tr><th>Customer Name</th><th>Buy Date</th><th>Total</th></tr></thead><tbody>";
			$total=0;
			foreach ($res as $rs) {
				$result.="<tr><td>".$rs['customer_name']."</td><td>".formatDate($rs['sell_date'])."</td>
					<td align='right'>".formatNumber($rs['total'],4)."</td></td></tr>";
				$total+=$rs['total'];
			}
			$result.="<tr><td colspan='2' align='right'>Total</td><td align='right'>".formatNumber($total,4)."</td></tr></tbody></table>";
			
		} else if ($tab=='detail') {
			if ($filter_stock_name!='') {
				$where.=" and d.stock_name like ?";
				array_push($params, "%$filter_stock_name%");		
			}
			$sql="select a.qty*a.price total, a.qty, a.price, c.customer_name, b.sell_date, d.stock_name from sell_detail a
				left join sell b on b.rowid=a.sell_id
				left join m_customer c on c.rowid=b.customer_id
				left join m_stock d on d.rowid=a.stock_id where $where 
				group by c.customer_name, b.sell_date, a.rowid";

			$res = db::DoQuery($sql, $params);
			$z = array();
			
			$result="";
			
			$result.="<table class='tbl'><thead><tr><th>Customer Name</th><th>Buy Date</th><th>Stock Name</th>
				<th>QTY</th><th>Price</th><th>Total</th></tr></thead><tbody>";
			$total=0;
			foreach ($res as $rs) {
				$result.="<tr><td>".$rs['customer_name']."</td><td>".formatDate($rs['sell_date'])."</td>
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