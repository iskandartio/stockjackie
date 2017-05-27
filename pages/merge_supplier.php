<script>
	var ajaxPage='merge_supplier_ajax';
	
	$(function() {
		var data={}
		data['type']='get_supplier_choice';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			supplier_choice=d['supplier_choice'];
			autoComplete('.filter_supplier_name', null, supplier_choice);
		}
		ajax(ajaxPage, data, success);
		bind('#btn_search','click', Search);
	});
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['filter_supplier_name']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			bind('.supplier_name','click', ChkClick);
			bind('#btn_merge','click', MergeClick);
		}
		ajax(ajaxPage, data, success);
	}
	function ChkClick() {
		var par=$(this).closest('tr');
		$('#supplier_merge_to').val($(this).parent().children('span').html());
	}
	function MergeClick() {
		var data={}
		var supplier_id= new Array();
		var par=$(this).closest('tr');
		$('.supplier_name').each(function(idx) {
			if ($(this).prop('checked')) {
				supplier_id.push($(this).attr('value'));		
			}
		});
		data['type']='merge';
		data['supplier_id']=supplier_id;
		data=prepareDataText(data, ['supplier_merge_to']);
		var success=function(msg) {
			alert(msg);
		}
		ajax(ajaxPage, data, success);
	}
</script>
<?php
	_t("filter_supplier_name");
?>
<button id='btn_search' class='button_link'>Search</button>
<div id='div_result'></div>