<?php
?>
<script src='js/sell_report.js'></script>
<script>
	var tabs=['complete','summary','detail'];
	var ajaxPage='sell_report_ajax';
	var customer_choice;
	$(function() {
		var a="<ul>";
		for (i in tabs) {
			a+="<li><a href='#div_"+tabs[i]+"'>"+toggleCase(tabs[i])+"</a></li>";
		}
		a+="</ul>";
		for (i in tabs) {
			a+="<div id='div_"+tabs[i]+"'></div>";
		}
		$('#tabs').html(a);
		prepareTabs(tabs,'sell_report');
		setDatePicker();
		bind('#btn_search','click', Search);
		bind('#sell_from_date','keydown',FilterEnter);
		bind('#sell_to_date','keydown',FilterEnter);
		bind('#filter_customer_name','keydown',FilterEnter);
		bind('#filter_stock_name','keydown',FilterEnter);
		var data={}
		data['type']='get_customer_choice';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			customer_choice=d['customer_choice'];
			autoComplete('.filter_customer_name', null, customer_choice);
		}
		ajax(ajaxPage, data, success);
		data['type']='get_stock_choice';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			stock_choice=d['stock_choice'];
			autoComplete('.filter_stock_name', null, stock_choice);
		}
		ajax(ajaxPage, data, success);
	});
	function FilterEnter(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			Search();
		}
	}
	function Search() {
		var active=$("#tabs").tabs('option', 'active');
		$('#div_complete').html("");
		$('#div_summary').html("");
		$('#div_detail').html("");
		load(active);
	}
	function load(active) {
		if ($('#div_'+tabs[active]).html()!='') return;
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['sell_from_date','sell_to_date','filter_customer_name','filter_stock_name']);
		data['tab']=tabs[active];
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			var div='#div_'+tabs[active];
			$(div).html(d['result']);
			
		}
		ajax(ajaxPage, data, success);
	}

</script>
Sell Date <?php _t("sell_from_date")?> <?php _t("sell_to_date")?>  
Customer <?php _t("filter_customer_name") ?> 
Stock <?php _t("filter_stock_name") ?> 
<button id='btn_search' class='button_link'>Search</button>
<div id='tabs'></div>