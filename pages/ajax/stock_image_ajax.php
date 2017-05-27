<?php
if ($type=='save') {
	try {
		db::update('m_stock_image', 'price', 'stock_name=?', array($_POST['price'], $_POST['stock_name']));
		die;
	} catch (Exception $e) {
		echo $e;
	}
	
}

?>