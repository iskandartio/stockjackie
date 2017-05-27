function StockReport(div, from_date, to_date) {
	var self=this;
	var f=generate_assoc(['rowid','stock_name','qty','detail']);
	self.start=function() {
		self.bindAll();
	}
	self.bindAll=function() {
		bindDiv('.btn_detail',div, 'click', self.Detail);
	}
	self.Detail=function() {
		var par=$(this).closest("tr");
		var td=$(this).closest("td");
		var data={}
		data['type']='detail';
		data=prepareDataHtml(data, ['rowid'], par, f);
		data['from_date']=from_date;
		data['to_date']=to_date;
		
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			$('.div_detail', td).html(d['result']);
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
}
