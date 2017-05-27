<script>
	var ajaxPage='stock_image_ajax';
	$(function() {
		bind('.btn_image','click', ShowImage);
		bind('.txt_price','keydown', KeyDown);
		bind('.btn_save','click', Save);
	});
	function KeyDown(e) {
		var code = e.keyCode || e.which;
		if(code == 13) {
			var par=$(this).closest('tr');
			$('.btn_save', par).trigger('click');
		}
	}
	function Save() {
		var par=$(this).closest('tr');
		var price=$('.txt_price', par).val();
		
		var data={}
		data['type']='save';
		data['price']=price;
		data['stock_name']=$('.stock_name', par).html();
		var success=function(msg) {
			if (msg=='-1') alert('Failed');
		}
		ajax(ajaxPage, data, success);
	}
	function ShowImage(){
		var par=$(this).closest('tr');
		var stock_name=$('.stock_name', par).html();
		var result="<a target='_blank' href='Camera/"+stock_name+"'><img style='width:200px;height:200px' src='show_picture_ajax?file_name=Camera/"+stock_name+"'/></a></div>";
		$('#img', par).html(result);
	}
</script>
<?php
$dir    = 'Camera';
$files = scandir($dir);
$con=db::beginTrans();
foreach ($files as $file) {
	if ($file!='.' && $file!='..') {
		db::insert('m_stock_image', 'stock_name', array($file), $con);
		
	}
}
db::commitTrans($con);
$created_at=db::select_single('m_stock_image', 'max(created_at) v');
$res=db::select('m_stock_image' ,'stock_name,price');
$res_array=array();

foreach ($res as $rs) {
	if (!isset($res_array[$rs['price']])) {
		$res_array[$rs['price']]="<h2>Harga ".$rs['price']."</h2>";
		$res_array[$rs['price']].="<table id='tbl_detail' class='tbl'><thead><tr><th>Stock Name</th><th>Image</th><th>Price</th><th></th></tr></thead><tbody>";
	} 
	$result="<tr><td class='stock_name'>".$rs['stock_name']."</td>";
	$result.="<td><div id='btn'><button class='btn_image'>Image</button></div><div id='img'></div></td>";
	$result.="<td>"._t2('price', $rs['price'], 3, 'text', 'txt_price')."</td><td>".getImageTags(['save'])."</td></tr>";	
	$res_array[$rs['price']].=$result;		
	
}
ksort($res_array);
foreach ($res_array as $res) {
	echo $res."</table>";	
}

?>
