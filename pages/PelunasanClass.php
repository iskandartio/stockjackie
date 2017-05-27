<?php
class Pelunasan {
	static function load_detail($rowid, $supplier_id) {
		$result="";
		$sql="select * from retur where supplier_id=$supplier_id and (pelunasan_id is null or pelunasan_id=$rowid)";
		$res=db::DoQuery($sql);
		shared::setId('retur', 'rowid', $res);
		if (count($res)>0) {
			$result.="<h1>Potongan</h1>";
			$result.="<table class='tbl' id='tbl_retur'><thead><tr><th></th><th>Description</th><th>Value</th><th></th></tr></thead><tbody>";
			foreach($res as $rs) {
				$result.="<tr><td>".$rs['id']."</td><td>".$rs['description']."</td><td>".$rs['value']."</td>
							<td>".getImageTags(['delete'])."</td></tr>";
			}
			$result.="</tbody></table>";
		}
		$sql="select a.buy_id, b.buy_lunas_id, a.buy_date, a.total, a.total-ifnull(b.value_lunas,0) remaining, b.value from (
	select a.buy_id, b.buy_date, sum(qty*price) as total from buy_detail a
	left join buy b on a.buy_id=b.rowid 
	where b.supplier_id=$supplier_id
	group by a.buy_id) a 
	left join (
	select buy_id, max(case when a.pelunasan_id=$rowid then a.rowid else 0 end) buy_lunas_id, sum(case when a.pelunasan_id=$rowid then value else 0 end) value, sum(case when a.pelunasan_id!=$rowid then value else 0 end) value_lunas from buy_lunas a
	left join pelunasan b on a.pelunasan_id=b.rowid
	where b.supplier_id=$supplier_id 
	group by buy_id) b on a.buy_id=b.buy_id
	where a.total-ifnull(b.value_lunas,0)>5  
	order by a.buy_date;
	";
		
		$res=db::DoQuery($sql);
		
		$result.="<h1>Detail Nota</h1>";
		$result.="<button class='button_link' id='btn_calculate'>Calculate</button>";
		$result.="<button class='button_link' id='btn_save_data'>Save Data</button>";
		$result.="<table class='tbl' id='tbl_detail_nota'><thead><tr><th></th><th></th><th>Buy Date</th><th>Total</th><th>Remaining</th><th>Paid</th><th></th></tr></thead><tbody>";
		shared::setId('buy_detail', 'buy_id', $res);
		shared::setId('buy_lunas', 'buy_lunas_id', $res, 'id2');
		foreach ($res as $rs) {
			$result.="<tr><td>".$rs['id']."</td><td>".(empty($rs['id2']) ? '' : $rs['id2'])."</td>
						<td>".formatDate($rs['buy_date'])."</td><td>".formatNumber($rs['total'])."</td>
						<td>".formatNumber($rs['remaining'])."</td>
						<td>".formatNumber($rs['value'])."</td>
						<td>".getImageTags(['delete'])."</td></tr>";
						
		}
		$result.="</tbody></table>";
		return $result;
	}
}