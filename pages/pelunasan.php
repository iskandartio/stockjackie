<?php
?>
<script src='js/pelunasan.js'></script>
<script>
var ajaxPage='pelunasan_ajax';
var tabs=['update','list'];
var payment_method_choice='';
var pelunasan_id='';

$(function() {
	var data={}
	data['type']='get_support_data';
	prepareTabs(tabs, 'pelunasan');
	LoadData();
	
	var success=function(msg) {
		var d=jQuery.parseJSON(msg);
		autoComplete('.supplier', LoadData, d['supplier_choice']);
		payment_method_choice=d['payment_method'];
		
	}
	ajax(ajaxPage, data, success);
	
});

function LoadData() {
	var act=$( "#tabs" ).tabs( "option", "active");
	
	
	for (i = 0; i < tabs.length; i++) { 
		$('#div_'+tabs[i]).html('');
	}
	pelunasan_id='';
	
	load(act);
	
}

function load(active) {
	var data={}
	var div='#div_'+tabs[active];
	if ($(div).html()!='') return;
	if (!$('#supplier').data("id")) return;
	data['supplier_id']=$('#supplier').data("id");
	if (tabs[active]=='update') {
		data['type']='load_data_new';
	} else {
		data['type']='load_data';
	}
	
	
	var success=function(msg) {
		var d=jQuery.parseJSON(msg);
		var div='#div_'+tabs[active];
		var f2=generate_assoc(['buy_id','buy_lunas_id','buy_date','total','remaining','paid']);
		if (tabs[active]=='update') {
			var adder=d['adder'].replace('@@PaymentMethod', payment_method_choice);
			var result=d['result'].replace('@@Adder', adder);
			$(div).html(result);
			fixSelect();
			setDatePicker();
			var a=new Pelunasan($('#div_'+tabs[active]), adder);
			hideColumns('tbl_payment');
			hideColumnsArr('tbl_detail_nota', ['buy_id','buy_lunas_id'] , f2);
			hideColumns('tbl_retur');
		} else {
			$(div).html(d['result']);
			var a=new PelunasanList($('#div_'+tabs[active]));
			setDatePicker();
		}
	}
	ajax(ajaxPage, data, success);
	
}
</script>
Supplier : <?php _t("supplier") ?>
<div id='tabs'></div>