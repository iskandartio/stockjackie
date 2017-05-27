<?php
class Data {
	static function needRefreshData($tbl) {
		if (!isset($_SESSION['data'][$tbl])) return true;
		$updated_at= db::select_single($tbl, 'max(updated_at) v');
		
		if ($updated_at>$_SESSION['data'][$tbl]['read_time']) return true; 
		return false;
	}
	static function refreshData($tbl, $res='') {
		if (Data::needRefreshData($tbl)) {	
			if ($res=='') $res=db::select($tbl,'*');
			$data=array();
			$random=array();
			foreach ($res as $rs) {
				while (true) {
					$rs['random_key']=shared::random(12);
					if (!isset($random[$rs['random_key']])) {
						$random[$rs['random_key']]=$rs['rowid'];
						break;
					}
				}
				$data[$rs['rowid']]=$rs;
			}
			
			$_SESSION['data'][$tbl]['read_time']=date('Y-m-d H:i:s');
			$_SESSION['data'][$tbl]['data']=$data;
			$_SESSION['data'][$tbl]['random_key']=$random;
			
		}
		
	}
	static function getData($tbl, $id) {
		if (!isset($_SESSION['data'][$tbl]['data'][$id])) return '';
		
		return $_SESSION['data'][$tbl]['data'][$id];
	}
	static function getName($tbl, $id) {
		$rs=Data::getData($tbl, $id);
		if ($rs=='') return '';
		$tbl = str_replace('m_','',$tbl);
		return $rs[$tbl."_name"];
	}
	static function getRandomKey($tbl, $id) {
		$rs=Data::getData($tbl, $id);
		if ($rs=='') return '';
		return $rs['random_key'];
	}
	static function getId($tbl, $id) {
		if ($id=='') return '';
		if (!isset($_SESSION['data'][$tbl]['random_key'][$id])) return '';
		return $_SESSION['data'][$tbl]['random_key'][$id];
	}
	static function getChoice($tbl, $label, $tag='') {
		$choice=array();
		if ($tag=='') {
			foreach ($_SESSION['data'][$tbl]['data'] as $key=>$val) {
				array_push($choice, array('label'=>$val[$label], 'value'=>$val['random_key']));
			}
		} else {
			foreach ($_SESSION['data'][$tbl]['data'] as $key=>$val) {
				array_push($choice, array('label'=>$val[$label], 'value'=>$val['random_key'], 'tag'=>$val[$tag]));
			}
		}
		return $choice;
	}
	static function getAllData($tbl) {
		return $_SESSION['data'][$tbl]['data'];
	}
}

?>