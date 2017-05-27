<?php
?>
<script src='js/change_payment_date.js'></script>
<script>
	var ajaxPage='change_payment_date_ajax';
	$(function() {
		setDatePicker();
		bind('#btn_search','click', Search);
		
	});
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['trans_from_date','trans_to_date']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			var a=new ChangePaymentDate($('#div_result'));
		}
		ajax(ajaxPage, data, success);
	}
</script>
Trans Date <?php _t("trans_from_date")?> <?php _t("trans_to_date")?> <button id='btn_search' class='button_link'>Search</button> 
<div id='div_result'></div>