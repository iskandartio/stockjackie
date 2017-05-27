<?php
	
?>
<script src='js/giro.js'></script>
<script>
	var ajaxPage='giro_ajax';
	$(function() {
		setDatePicker();		
		$('#trans_from_date').val($.datepicker.formatDate('dd-mm-yy', dateAdd(new Date(),-5)));
		bind('#btn_search','click', Search);
		
	});
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['trans_from_date','trans_to_date','filter_description']);
		data=prepareDataCheckBox(data, ['sort_giro']);
		
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			var div='#div_result';
			$(div).html(d['result']);
			
			var a=new Giro($('#div_result'), d['adder'], d['next_giro'], d['ket_choices'], $('#sort_giro').prop('checked'), d['in_key']);
		}
		ajax(ajaxPage, data, success);
	}
</script>
Date : <?php _t("trans_from_date")?> - <?php _t("trans_to_date")?> &nbsp; Description : <?php _t("filter_description") ?>
<input type='checkbox' id='sort_giro'><label for='sort_giro'>Sort Giro</label>
<button id='btn_search' class='button_link'>Search</button>
<div id='div_result'></div>