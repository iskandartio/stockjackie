function Stock(div, adder) {
	var self=this;
	var f=generate_assoc(['rowid','stock_name','btn']);
	self.start=function() {
		self.bindAll();
	}
	self.bindAll=function() {
		hideColumns('tbl');
		bindDiv('.btn_add',div, 'click', self.Add);
		bindDiv('.btn_edit',div, 'click', self.Edit);
		bindDiv('.btn_delete',div, 'click', self.Delete);
		bindDiv('.btn_save',div, 'click', self.Save);
		bindDiv('.btn_cancel',div, 'click', self.Cancel);
	}
	self.Add=function() {
		div.find('table tbody').prepend(adder);
		self.bindAll();
	}
	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"stock_name":0}, f);
		btnChange(par, ['save','cancel'], f);
		self.bindAll();
	}
	self.Delete=function() {
		var data={}
		var par=$(this).closest("tr");
		data['type']='delete';
		data=prepareDataText(data, ['rowid'], par, f);
		var success=function(msg) {
			if (msg!='fail') {
				par.remove();
			} else {
				alert(msg);
			}
		}
		ajax(ajaxPage, data, success);
	}
	self.Cancel=function() {
		var par=$(this).closest("tr");
		textToDefaultLabel(par, ['stock_name'], f);
		btnChange(par, ['edit','delete'], f);
		self.start();
	}
	self.Save=function() {
		var par=$(this).closest("tr");
		var data={}
		data['type']='save';
		data=prepareDataText(data, ['stock_name'], par, f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		var success=function(msg) {
			setHtmlText(par, 'rowid', msg, f);
			textToLabel(par, ['stock_name'], f);
			btnChange(par, ['edit','delete'], f);
			self.start();
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
}