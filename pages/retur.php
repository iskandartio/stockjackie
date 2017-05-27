<?php
?>
<script src='js/retur.js'></script>
<script>
	var ajaxPage='retur_ajax';
	$(function() {
		bind('#btn_search','click', Search);
		bind('#filter_supplier_name','keydown',FilterEnter);
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
		data=prepareDataText(data, ['filter_supplier_name']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			var a=new Retur($('#div_result'), d['supplier_choice'], d['adder']);
		}
		ajax(ajaxPage, data, success);
	}
</script>
Supplier Name <?php _t("filter_supplier_name")?> <button id='btn_search' class='button_link'>Search</button>
<div id='div_result'></div>