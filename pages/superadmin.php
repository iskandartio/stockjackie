<script src='js/superadmin.js'></script>
<script>
	var tabs=['user','role','module', 'category', 'user_role','role_module'];
	$(function() {

		prepareTabs(tabs, 'superadmin');
	});
	var employee_choice;
	function load(active) {
		if ($('#div_'+tabs[active]).html()!='') return;
		var data={}
		data['type']='load';
		data['tbl']=tabs[active];
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			var div='#div_'+tabs[active];
			$(div).html(d['result']);
			var a=new superadmin($('#div_'+tabs[active]), tabs[active]);
			if ($.inArray('adder', d)) {
				a.adder=d['adder'];
			}
			
			if (tabs[active]=='user_role') {
				a.setAutoComplete(d['user_choice']);
			}
			if (tabs[active]=='module') {
				a.category_choice=d['category_choice'];
			}
		}
		ajax("superadmin_ajax", data, success);
	}
</script>

<div id='tabs'></div>
