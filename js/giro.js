function Giro(div, adder, next_giro, choices, sort_giro, in_key) {
	var self=this;
	var f;
	var add_status=0;
	self.start=function() {
		if (sort_giro) {
			f=generate_assoc(['rowid','trans_date','ket','in','out','btn']);
		} else {
			f=generate_assoc(['rowid','trans_date','ket','in','out','balance','btn']);
		}
		self.bindAll();
	}
	self.bindAll=function() {
		hideColumns('tbl');
		bindDiv('.btn_add',div, 'click', self.Add);
		bindDiv('.btn_edit',div, 'click', self.Edit);
		bindDiv('.btn_delete',div, 'click', self.Delete);
		bindDiv('.btn_save',div, 'click', self.Save);
		bindDiv('.btn_cancel',div, 'click', self.Cancel);
		bindDiv('.btn_detail',div, 'click', self.Detail);
		bindDiv('.in', div, 'keydown', self.InOutEnter);
		bindDiv('.out', div,'keydown', self.InOutEnter);
		
		setDatePicker();
		autoSelect();
		autoComplete('.ket', null, choices);
	}
	self.InOutEnter=function(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			self.Save2($(this).closest('tr'));
		}
	}
	self.Add=function() {
		div.find('table tbody:eq(0)').prepend(adder);
		var par=$('#tbl tbody tr:eq(0)');
		add_status=1;
		self.bindAll();
		$('.ket', par).data("id", in_key);
	}

	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"trans_date":5, "in":5, "out":5}, f);
		labelToAutoComplete(par, ['ket'], f);
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
		textToDefaultLabel(par, ['trans_date','in','out'], f);
		autoCompleteToDefaultLabel(par, ['ket'], f);
		btnChange(par, ['edit','delete'], f);
		self.start();
	}
	self.Save=function() {
		var par=$(this).closest('tr');
		self.Save2(par);
		
	}
	self.Save2=function(par) {
		var data={}
		data['type']='save';
		data=prepareDataMultiInput(data, ['trans_date'], par);
		data=prepareDataAutoComplete(data, ['ket'], par,f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		var nilai=getChild(par, 'in', f)-getChild(par, 'out', f);
		data['nilai']=nilai;
		var success=function(msg) {
			setHtmlText(par, 'rowid', msg, f);
			textToLabel(par, ['trans_date','in','out'], f);
			autoCompleteToLabel(par, ['ket'], f);
			btnChange(par, ['edit','delete'], f);
			self.start();
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
}