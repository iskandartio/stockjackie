<?php
	if (isset($_GET['table'])) {
		$fields="*";
		$where="";
		$order="";
		if (isset($_GET['fields'])) {
			$fields=$_GET['fields'];
		}
		if (isset($_GET['where'])) {
			$where=$_GET['where'];
		}
		
		if (isset($_GET['order'])) {
			$order=$_GET['order'];
		}
		$result=db::select($_GET['table'], $fields, $where, $order);
		die(json_encode($result));
	}
	if (isset($_GET['refresh_date'])) {
		$tables=$_GET['tables'];
		$data=array();
		$refresh_date=$_GET['refresh_date'];
		$table=explode(',',$tables);
		foreach ($table as $tbl) {
			$tbl_name=$tbl;
			if ($tbl=='giro') {
				$field='rowid, no_giro, trans_date, giro_desc_id, nilai';
			} else if ($tbl=='giro_saldo') {
				$field='rowid, trans_date, saldo';
			} else if ($tbl=='stock') {
				$tbl_name='m_stock';
				$field='rowid, stock_name';
			} else if ($tbl=='supplier') {
				$tbl_name='m_supplier';
				$field='rowid, supplier_name, tempo, supplier_phone';
			} else if ($tbl=='buy') {
				$field='rowid, buy_date, supplier_id, tempo';
			} else if ($tbl=='buy_detail') {
				$field='rowid, buy_id, stock_id, qty, price';
			} else if ($tbl=='buy_lunas') {
				$field='rowid, buy_id, pelunasan_id, value';
			} else if ($tbl=='payment') {
				$field='rowid, pelunasan_id, payment_method_id, disburstment_date, nominal, no_giro';
			} else if ($tbl=='payment_method') {
				$field='rowid, payment_method_name';
			} else if ($tbl=='pelunasan') {
				$field='rowid, supplier_id';
			} else if ($tbl=='retur') {
				$field='rowid, supplier_id, description, trans_date, value, pelunasan_id';
			} else if ($tbl=='giro_description') {
				$field='rowid, giro_description_name';
			}
			$res=db::select($tbl_name, $field, 'ifnull(updated_at,1)>=?','', array($refresh_date));
			$data[$tbl]=json_encode($res);
			$data[$tbl.'_c']=db::select_with_count($tbl_name);
		}
		
		die(json_encode($data));
	}
?>