<?php
?>
<script src='js/sell.js'></script>
<script>
	var ajaxPage='sell_ajax';
	
	$(function() {
		setDatePicker();
		bind('#btn_search','click', Search);
		bind('#sell_from_date','keydown',FilterEnter);
		bind('#sell_to_date','keydown',FilterEnter);
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
		data=prepareDataText(data, ['sell_from_date','sell_to_date']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			var a=new Sell($('#div_result'), d['customer_choice'], d['adder']);
			
		}
		ajax(ajaxPage, data, success);
	}
</script>

Sell Date <?php _t("sell_from_date")?> <?php _t("sell_to_date")?> <button id='btn_search' class='button_link'>Search</button>
<div id='div_result'></div>