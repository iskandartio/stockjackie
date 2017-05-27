<?php
	if ($type=='get_supplier_choice') {
		Data::refreshData('m_supplier');
		$data['supplier_choice']=shared::getSupplierChoice();
		die(json_encode($data));
	}
	if ($type=='search') {
		$where="";
		$params=array();
		
		if ($filter_supplier_name!='') {
			$z=explode(" ",$filter_supplier_name);
			$where="1=1";
			foreach ($z as $s) {
				$where.=" and supplier_name like ?";
				array_push($params, '%'.$s.'%');
			}
			
		}
		$res=db::select('m_supplier','rowid',$where,'supplier_name',$params);
		Data::refreshData('m_supplier');
		$result="";
		$result.="<button id='btn_merge' class='button_link'>Merge</button>";
		$result.=_t2("supplier_merge_to");
		$result.="<table id='tbl' class='tbl'><thead><tr><th>Supplier Name</th></tr></thead>";
		foreach ($res as $rs) {
			$rs=Data::getData('m_supplier', $rs['rowid']);
			$result.="<tr><td>".shared::create_checkbox('supplier_name',$rs['supplier_name'],'',$rs['random_key'])."</td>";
			$result.="</tr>";
		}
		$result.="</table>";
		$data['result']=$result;
		die(json_encode($data));
	}
	if ($type=='merge') {
		$result="";
		$to=Data::getId('m_supplier', $supplier_id[0]);
		$con=db::beginTrans();
		for($i=1;$i<count($supplier_id);$i++) {
			$id=$supplier_id[$i];
			$from=Data::getId('m_supplier', $id);
			db::update('buy','supplier_id','supplier_id=?',array($to, $from),$con);
			db::delete('m_supplier','rowid=?', array($from),$con);
		}
		db::update('m_supplier','supplier_name','rowid=?', array($supplier_merge_to, $to),$con);
		db::commitTrans($con);
		die('Success');
		
	}
?>