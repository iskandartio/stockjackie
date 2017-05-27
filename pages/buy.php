<?php

?>
<script src='js/buy.js'></script>
<script>
	var ajaxPage='buy_ajax';
	
	$(function() {
		
		setDatePicker();
		bind('#btn_search','click', Search);
		bind('#buy_from_date','keydown', BuyFromEnter);
		bind('#buy_to_date','keydown',FilterEnter);
		bind('#filter_supplier_name','keydown',FilterEnter);
		bind('#filter_stock_name','keydown',FilterEnter);
		var data={}
		data['type']='get_supplier_choice';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			supplier_choice=d['supplier_choice'];
			autoComplete('.filter_supplier_name', null, supplier_choice);
		}
		ajax(ajaxPage, data, success);
		data['type']='get_stock_choice';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			stock_choice=d['stock_choice'];
			autoComplete('.filter_stock_name', null, stock_choice);
		}
		ajax(ajaxPage, data, success);
		
		$('#buy_from_date').val($.datepicker.formatDate('dd-mm-yy', new Date()));
	});
	function FilterEnter(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			Search();
		}
	}
	function BuyFromEnter(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			$('#buy_to_date').val($('#buy_from_date').val());
			$('#buy_to_date').select();
		}
	}
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['buy_from_date','buy_to_date','filter_supplier_name','filter_stock_name']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			var a=new Buy($('#div_result'), d['supplier_choice'], d['adder']);
			
		}
		ajax(ajaxPage, data, success);
	}
</script>

Buy Date <?php _t("buy_from_date")?> <?php _t("buy_to_date")?> Supplier <?php _t("filter_supplier_name") ?> Stock <?php _t("filter_stock_name") ?> <button id='btn_search' class='button_link'>Search</button>
<div id='div_result'>
</div>