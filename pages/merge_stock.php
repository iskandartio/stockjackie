<script>
	var ajaxPage='merge_stock_ajax';
	
	$(function() {
		var data={}
		data['type']='get_stock_choice';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			stock_choice=d['stock_choice'];
			autoComplete('.filter_stock_name', null, stock_choice);
		}
		ajax(ajaxPage, data, success);
		bind('#btn_search','click', Search);
	});
	function Search() {
		var data={}
		data['type']='search';
		data=prepareDataText(data, ['filter_stock_name']);
		var success=function(msg) {		
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
			bind('.stock_name','click', ChkClick);
			bind('#btn_merge','click', MergeClick);
		}
		ajax(ajaxPage, data, success);
	}
	function ChkClick() {
		var par=$(this).closest('tr');
		$('#stock_merge_to').val($(this).parent().children('span').html());
	}
	function MergeClick() {
		var data={}
		var stock_id= new Array();
		var par=$(this).closest('tr');
		$('.stock_name').each(function(idx) {
			if ($(this).prop('checked')) {
				stock_id.push($(this).attr('value'));		
			}
		});
		data['type']='merge';
		data['stock_id']=stock_id;
		data=prepareDataText(data, ['stock_merge_to']);
		var success=function(msg) {
			alert(msg);
		}
		ajax(ajaxPage, data, success);
	}
</script>
<?php
	_t("filter_stock_name");
?>
<button id='btn_search' class='button_link'>Search</button>
<div id='div_result'></div>