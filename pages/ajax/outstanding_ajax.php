<?php
if ($type=='search') {
	Data::refreshData('m_supplier');
	$sql="select a.*, c.supplier_name, b.supplier_id, b.buy_date, DATE_ADD(b.buy_date, INTERVAL ifnull(b.tempo,30) DAY) due_date from (
select a.buy_id, a.total, a.total-ifnull(b.lunas,0) sisa from (
select buy_id, sum(qty*price) total from buy_detail group by buy_id) a
left join (
select buy_id, sum(value) lunas from buy_lunas group by buy_id) b on a.buy_id=b.buy_id
where a.total>ifnull(b.lunas,0)+5) a
left join buy b on a.buy_id=b.rowid
left join m_supplier c on c.rowid=b.supplier_id
where DATE_ADD(b.buy_date, INTERVAL ifnull(b.tempo,30)-$day DAY)<=curdate() 
order by c.supplier_name, DATE_ADD(b.buy_date, INTERVAL ifnull(b.tempo,30) DAY)";

	$res=db::DoQuery($sql);
	$result="";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>Supplier</th><th>Buy Date</th><th>Total</th><th>Sisa</th><th>Due Date</th></tr></thead><tbody>";
	foreach ($res as $rs) {
		$result.="<tr><td>".$rs['supplier_name']."</td>
					<td>".formatDate($rs['buy_date'])."</td>
					<td align='right'>".formatNumber($rs['total'])."</td>
					<td align='right'>".formatNumber($rs['sisa'])."</td>
					<td>".formatDate($rs['due_date'])."</td></tr>";
	}
	$result.="</tbody></table>";
	$data=array();
	$data['result']=$result;
	die(json_encode($data));
}