<?php

?>
<script src='js/stock_report.js'></script>
<script>
	var ajaxPage='stock_report_ajax';
	$(function() {
		bind('#btn_fix','click', Fix);
		bind('#btn_search','click', Search);
		setDatePicker();
	});
	function Fix() {
		var data={}
		data['type']='fix';
		var success=function(msg) {
			alert(msg);
		}
		ajax(ajaxPage, data, success);
	}
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['filter_stock']);
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			var a=new StockReport($('#div_result'), $('#from_date').val(), $('#to_date').val());
		}
		ajax(ajaxPage, data, success);
	}
	
	
</script>
Date <?php _t("from_date")?> <?php _t("to_date")?> 
Stock Name : <?php _t("filter_stock") ?>
<button id='btn_search' class='button_link'>Search</button> 
<button id='btn_fix' class='button_link'>Fix</button>
<div id='div_result'></div>