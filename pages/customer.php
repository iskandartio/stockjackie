<?php
?>
<script src='js/customer.js'></script>
<script>
	var ajaxPage='customer_ajax';
	$(function() {
		bind('#btn_search','click', Search);
		bind('#filter_customer_name','keydown',FilterEnter);
	});
	function FilterEnter(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			Search();
		}
	}
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['filter_customer_name']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			var a=new Customer($('#div_result'), d['adder']);
		}
		ajax(ajaxPage, data, success);
	}
</script>
Customer Name <?php _t("filter_customer_name")?> <button id='btn_search' class='button_link'>Search</button>
<div id='div_result'></div>