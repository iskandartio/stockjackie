<script>
	var ajaxPage="outstanding_ajax";
	$(function() {
		bind('#btn_search', 'click', SearchClick);
	});
	function SearchClick() {
		var data={}
		data['type']='search';
		data['day']=$('#day').val();
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			$('#div_result').html(d['result']);
		}
		ajax(ajaxPage, data, success);
	}
</script>
<?php _t("day","3","1") ?>
<button class='button_link' id='btn_search'>Search</button>
<div id='div_result'></div>