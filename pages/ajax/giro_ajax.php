<?php
if ($type=='search') {
	$where="1=1";
	$params=array();
	$need_balance=1;
	if ($trans_from_date!='') {
		$where.=" and trans_date >= ?";
		array_push($params, $trans_from_date);		
	}
	if ($trans_to_date!='') {
		$where.=" and trans_date <= ?";
		array_push($params, $trans_to_date);
	}
	if ($filter_description!='') {
		$where.=" and ket like ?";
		array_push($params, "%".$filter_description."%");
		$need_balance=0;
	}
	if ($sort_giro==1) $need_balance=0;
	if ($need_balance==1) {
		$trans_date_available=db::select_single('giro_saldo','max(trans_date) v','trans_date<=?','',array($trans_from_date));
		$saldo=db::select_single('giro_saldo', 'saldo v', 'trans_date=?','', array($trans_date_available));
		$trans=db::select_single('giro a
inner join giro_description b on a.giro_desc_id=b.rowid','sum(a.nilai) v', 'a.trans_date>=? and a.trans_date<?', '', array($trans_date_available, $trans_from_date));
		$saldo+=$trans;
		$trans=db::select_single("payment a
left join pelunasan b on b.rowid=a.pelunasan_id
inner join payment_method d on d.rowid=a.payment_method_id and d.payment_method_name='Giro'"
, '-sum(a.nominal)/1000 v', 'a.disburstment_date>=? and a.disburstment_date<?', '', array($trans_date_available, $trans_from_date));
		$saldo+=$trans;
	}
	Data::refreshData('giro_description');
	$res=db::select("(
select '***' no_giro, trans_date, a.giro_desc_id ket, nilai, a.updated_at, a.rowid from giro a
inner join giro_description b on a.giro_desc_id=b.rowid
 union all
select no_giro, disburstment_date,c.supplier_name, -nominal/1000, a.updated_at, a.rowid from payment a
left join pelunasan b on b.rowid=a.pelunasan_id
left join m_supplier c on c.rowid=b.supplier_id
inner join payment_method d on d.rowid=a.payment_method_id and d.payment_method_name='Giro') a"
,'*',$where, $sort_giro==1 ? 'a.no_giro, a.updated_at' : 'a.trans_date, case when a.nilai>0 then 1 else 0 end, a.updated_at',$params);
	
	shared::setId('giro', 'rowid', $res);
	$result="<button class='button_link btn_add'>Add</button>";
	$result.="<table id='tbl' class='tbl'><thead><tr><th>id</th><th>No Giro<br>Trans Date</th><th>Description</th><th>In</th><th>Out</th>";
	if ($need_balance==1) $result.="<th>Balance</th>";
	$result.="<th></th></tr></thead>";
	if ($need_balance==1) {
		$result.="<tr>
			<td></td>
			<td colspan='5' align='right'>".round($saldo,3)."</td>
			<td></td></tr>";
	}
	$result.="<tbody>";
	$i=0;
	if (count($res)>0) {
		$last=$res[0]['trans_date'];
		foreach ($res as $rs) {
			$i++;
			if ($need_balance==1) $saldo+=$rs['nilai'];
			$result.="<tr><td>".$rs['id']."</td><td>";
			if ($rs['no_giro']!='***') {
				$result.=$rs['no_giro']."<br>";
			}
			$result.=formatDate($rs['trans_date'])."</td>";
			if ($rs['no_giro']=='***') {
				$result.="<td><span class='ket hidden'>".Data::getRandomKey('giro_description', $rs['ket'])."</span>
					<span class='ket_name'>".Data::getName('giro_description', $rs['ket'])."</span>
					</td>";
			} else {
				$result.="<td>".$rs['ket']."</td>";
			}
				
			if ($rs['nilai']>0) {
				$result.="<td align='right'>".round($rs['nilai'],3)."</td><td></td>";
			} else {
				$result.="<td></td><td align='right'>".-round($rs['nilai'],3)."</td>";
			}
			if ($need_balance==1) {
				$result.="<td align='right'>";
				
				if ($i==count($res)) {
					$result.=round($saldo,3);
				} else if ($res[$i]['trans_date']!=$last) {
					$result.=round($saldo,3);
					$last=$res[$i]['trans_date'];
				}
				
				$result.="</td>";
			}
			$result.="<td>";
			if ($rs['no_giro']=='***') {
				$result.=getImageTags(['edit','delete']);
				
			}
			$result.="</td></tr>";
		}
	}
	$result.="</tbody></table>";
	$data['result']=$result;
	$adder="<tr><td></td><td>"._t2("trans_date")."</td><td>"._t2("ket","in",5)."</td>
		<td align='right'>"._t2("in","",3)."</td><td align='right'>"._t2("out","",3)."</td>";
	if ($need_balance==1) $adder.="<td align='right'></td>";
	$adder.="<td>".getImageTags(['save','delete'])."</td></tr>";
	$data['adder']=$adder;
	
	$max_row_id=db::select_single('giro', 'max(rowid) v', 'nilai<0','');
	$sql="select * from giro_description order by giro_description_name";
	$res=db::DoQuery($sql);
	Data::refreshData('giro_description', $res);
	$data['ket_choices']=Data::getChoice('giro_description','giro_description_name');
	$data['in_key']=Data::getRandomKey('giro_description', db::select_single('giro_description', 'rowid v', "giro_description_name='in'") );
	die(json_encode($data));
	
}
if ($type=='save') {
	$con=db::beginTrans();
	$_POST['rowid']=shared::getId('giro', $_POST['rowid']);
	$_POST['giro_desc_id']=Data::getId('giro_description', $_POST['ket']);
	unset($_POST['ket']);
	$rowid=db::saveSimpleTrans('giro', $_POST, $con);
	db::commitTrans($con);
	$random_key=shared::random(12);
	$_SESSION['giro'][$random_key]=$rowid;
	die($random_key);
}
if ($type=='delete') {
	$rowid=shared::getId('giro', $rowid);
	try {
		db::delete('giro','rowid=?', array($rowid));
	} catch (Exception  $e) {
		die("fail");
	}
	die;
}
?>