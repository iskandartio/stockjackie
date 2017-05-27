function Retur(div, choices, adder) {
	var self=this;
	var f=generate_assoc(['rowid','trans_date','supplier','description','value','btn']);
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
		autoComplete('.supplier', null, choices);
		setDatePicker();
	}
	self.Add=function() {
		div.find('table tbody').prepend(adder);
		self.bindAll();
	}
	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"trans_date":8, "description":0, "value":8}, f);
		labelToAutoComplete(par, ['supplier'], f);
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
		textToDefaultLabel(par, ['trans_date', 'description','value'], f);
		autoCompleteToDefaultLabel(par, ['supplier'], f);
		btnChange(par, ['edit','delete'], f);
		self.start();
	}
	self.Save=function() {
		var par=$(this).closest("tr");
		var data={}
		data['type']='save';
		data=prepareDataText(data, ['trans_date', 'description'], par, f);
		data=prepareDataAutoComplete(data, ['supplier'], par,f);
		data=prepareDataDecimal(data, ['value'], par, f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		var success=function(msg) {
			setHtmlText(par, 'rowid', msg, f);
			textToLabel(par, ['trans_date', 'description','value'], f);
			autoCompleteToLabel(par, ['supplier'], f);
			btnChange(par, ['edit','delete'], f);
			self.start();
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
}