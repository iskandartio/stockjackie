<?php
	if ($type=='get_stock_choice') {
		Data::refreshData('m_stock');
		$data['stock_choice']=shared::getStockChoice();
		die(json_encode($data));
	}
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
		$result="";
		$result.="<button id='btn_merge' class='button_link'>Merge</button>";
		$result.=_t2("stock_merge_to");
		$result.="<table id='tbl' class='tbl'><thead><tr><th>Stock Name</th></tr></thead>";
		foreach ($res as $rs) {
			$rs=Data::getData('m_stock', $rs['rowid']);
			$result.="<tr><td>".shared::create_checkbox('stock_name',$rs['stock_name'],'',$rs['random_key'])."</td>";
			$result.="</tr>";
		}
		$result.="</table>";
		$data['result']=$result;
		die(json_encode($data));
	}
	if ($type=='merge') {
		$result="";
		$to=Data::getId('m_stock', $stock_id[0]);
		$con=db::beginTrans();
		for($i=1;$i<count($stock_id);$i++) {
			$id=$stock_id[$i];
			$from=Data::getId('m_stock', $id);
			db::update('buy_detail','stock_id','stock_id=?',array($to, $from),$con);
			db::update('sell_detail','stock_id','stock_id=?',array($to, $from),$con);
			db::delete('m_stock','rowid=?', array($from),$con);
		}
		db::update('m_stock','stock_name','rowid=?', array($stock_merge_to, $to),$con);
		db::commitTrans($con);
		die('Success');
		
	}
?>