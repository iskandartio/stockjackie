function ChangePaymentDate(div) {
	var self=this;
	var f=generate_assoc(['rowid','disburstment_date','supplier','no_giro','nominal','btn']);
	self.start=function() {
		self.bindAll();
	}
	self.bindAll=function() {
		hideColumns('tbl');
		bindDiv('.btn_edit', div, 'click', self.Edit);
		bindDiv('.btn_save',div, 'click', self.Save);
		bindDiv('.btn_cancel',div, 'click', self.Cancel);
		setDatePicker();
	}
	

	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"disburstment_date":8}, f);
		btnChange(par, ['save','cancel'], f);
		self.bindAll();
	}
	self.Cancel=function() {
		var par=$(this).closest("tr");
		textToDefaultLabel(par, ['disburstment_date'], f);
		btnChange(par, ['edit'], f);
		self.start();
	}
	self.Save=function() {
		var par=$(this).closest("tr");
		var data={}
		data['type']='save';
		data=prepareDataText(data, ['disburstment_date'], par, f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		var success=function(msg) {
			textToLabel(par, ['disburstment_date'], f);
			btnChange(par, ['edit'], f);
			self.start();
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
}
