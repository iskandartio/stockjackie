function Pelunasan(div, adder) {
	var self=this;
	var f;
	var f2;
	var f3;
	var ajaxPage='pelunasan_ajax';
	
	self.start=function() {
		f=generate_assoc(['rowid','payment_method_id','no_giro','disburstment_date','nominal','btn']);
		f2=generate_assoc(['buy_id','buy_lunas_id','buy_date','total','remaining','paid']);
		f3=generate_assoc(['rowid','description','value','btn']);
		self.bindAll();
	}
	self.bindAll=function() {
		
		bindDiv('#btn_new',div,'click', self.New);
		bindDiv('#btn_add',div,'click', self.Add);
		bindDiv('#btn_calculate',div,'click', self.Calculate);
		bindDiv('#btn_save_data',div,'click', self.SaveData);
		bindDiv('.btn_edit',div,'click', self.Edit);
		bindDiv('.btn_delete',div,'click', self.Delete);
		bindDiv('.btn_save',div,'click', self.Save);
		bindDiv('.btn_cancel',div,'click', self.Cancel);
		bindDiv('.btn_delete_retur', div, 'click', self.DeleteRetur);
		hideColumns('tbl_payment');
		fixSelect();
		setDatePicker();
		autoSelect();
	}
	self.New=function() {
		var data={}
		data['supplier_id']=$('#supplier').data("id");
		data['type']='load_data_new';
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			var div='#div_update';
			var f2=generate_assoc(['buy_id','buy_lunas_id','buy_date','total','remaining','paid']);
			var adder=d['adder'].replace('@@PaymentMethod', payment_method_choice);
			var result=d['result'].replace('@@Adder', adder);
			$(div).html(result);
			fixSelect();
			setDatePicker();
			var a=new Pelunasan($('#div_update'), adder);
			hideColumns('tbl_payment');
			hideColumnsArr('tbl_detail_nota', ['buy_id','buy_lunas_id'] , f2);
			hideColumns('tbl_retur');
			pelunasan_id="";
		}
		ajax(ajaxPage, data, success);
	}
	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"nominal":8, "no_giro":4, "disburstment_date":8 }, f);
		labelToSelect(getChildObj(par, 'payment_method_id', f), payment_method_choice);
		btnChange(par, ['save','cancel'], f);
		self.bindAll();
	}
	self.Save=function() {
		var par=$(this).closest("tr");
		var data={}
		data['type']='save_payment';
		data['supplier_id']=$('#supplier').data('id');
		data['pelunasan_id']=pelunasan_id;
		data=prepareDataText(data, ['payment_method_id','no_giro','disburstment_date'], par, f);
		data=prepareDataDecimal(data, ['nominal'], par, f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			pelunasan_id=d['pelunasan_id'];
			setHtmlText(par, 'rowid', d['rowid'], f);
			textToLabel(par, ['disburstment_date', 'nominal','no_giro'], f);
			selectedToLabel(par, ['payment_method_id'], f);
			btnChange(par, ['edit','delete'], f);
			self.bindAll();
			
		}
		ajax(ajaxPage, data, success);
	}
	
	self.Add=function() {
		div.find('#tbl_payment tbody:eq(0)').prepend(adder);
		self.bindAll();
	}
	self.Calculate=function() {
		var z=$('#tbl_payment>tbody>tr');
		var total=0;
		z.each(function(idx) {
			total+=getChild($(this), 'nominal', f).replace(',','')*1;
		});
		z=$('#tbl_retur>tbody>tr');
		z.each(function(idx) {
			total+=getChild($(this), 'value', f3).replace(',','')*1;
		});
		
		z=$('#tbl_detail_nota>tbody>tr');
		z.each(function(idx) {
			if (getChildObj($(this), 'paid', f2).html()!='-') {
				if (total<=0) {
					getChildObj($(this), 'paid', f2).html('');
				} else {
					var remaining=getChild($(this), 'remaining', f2).replace(',','');
					var paid=0;
					if (total<remaining) {
						paid=total;
					} else {
						paid=remaining;
					}
					total-=paid;
					getChildObj($(this), 'paid', f2).html(paid);
				}	
			}
			
		});
	}
	self.SaveData=function() {
		var z=$('#tbl_detail_nota>tbody>tr');
		var data={}
		data['type']='save_data';
		var arr_buy_id=new Array();
		var arr_buy_lunas_id=new Array();
		var arr_paid=new Array();
		z.each(function(idx) {
			var paid=getChild($(this), 'paid', f2).replace(',','');
			var buy_lunas_id=getChild($(this), 'buy_lunas_id', f2);
			var buy_id=getChild($(this), 'buy_id', f2);
			
			if (paid!='' || buy_lunas_id!='') {
				arr_buy_id.push(buy_id);
				arr_buy_lunas_id.push(buy_lunas_id);
				arr_paid.push(paid);
			}
		});
		var arr_retur=new Array();
		var z=$('#tbl_retur>tbody>tr');
		z.each(function(idx) {
			arr_retur.push(getChild($(this), 'rowid', f3));
		});
		data['arr_retur']=arr_retur;
		data['arr_buy_id']=arr_buy_id;
		data['arr_buy_lunas_id']=arr_buy_lunas_id;
		data['arr_paid']=arr_paid;
		data['pelunasan_id']=pelunasan_id;
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			var z=$('#tbl_detail_nota>tbody>tr');
			z.each(function(idx) {
				var buy_id=getChild($(this), 'buy_id', f2);
				var buy_lunas_id=d['buy_lunas_data'][buy_id];
				if (buy_lunas_id) {
					if (buy_lunas_id==' ') buy_lunas_id='';
					getChildObj($(this), 'buy_lunas_id', f2).html(buy_lunas_id);
				}
			});
		}
		ajax(ajaxPage, data, success);
	}
	self.Delete=function() {
		var par=$(this).closest("tr");
		if ($(this).closest('table').attr('id')=='tbl_detail_nota') {
			getChildObj(par, 'paid', f2).html("-");
			return;
		}
		var data={}
		data['type']='delete_payment';
		data=prepareDataText(data, ['rowid'], par, f);
		data['pelunasan_id']=pelunasan_id;
		var success=function(msg) {
			if (msg!='fail') {
				par.remove();

			} else {
				alert(msg);
			}
		}
		ajax(ajaxPage, data, success);
	}
	self.DeleteRetur=function() {
		$(this).closest('tr').remove();
	}
	self.Cancel=function() {
		var par=$(this).closest("tr");
		textToDefaultLabel(par, ['disburstment_date','nominal','no_giro'], f);
		selectedToDefaultLabel(par, ['payment_method_id'], f);
		
		btnChange(par, ['edit','delete'], f);
		self.start();
	}
	self.start();
}

function PelunasanList(div) {
	var self=this;
	var f=generate_assoc(['rowid','supplier','date','payment_method','nominal','btn']);
	var f2=generate_assoc(['buy_id','buy_lunas_id','buy_date','total','remaining','paid']);
	var ajaxPage='pelunasan_ajax';
	
	self.start=function() {
		self.bindAll();
	}
	self.bindAll=function() {
		bindDiv('.btn_edit',div,'click', self.Edit);
		bindDiv('.btn_delete',div,'click', self.Delete);
		bindDiv('#btn_search',div,'click', self.Search);
		hideColumns('tbl_list');	
	}
	self.Search=function() {
		var data={}
		data['type']='search';
		data['supplier_id']=$('#supplier').data('id');
		data['from_date']=$('#from_created_date').val();
		data['to_date']=$('#to_created_date').val();
		
		var success=function(msg) {
			$('#divlist').html(msg);
			self.bindAll();
		}
		ajax(ajaxPage, data, success);
	}
	self.Edit=function() {
		var data={}
		var par=$(this).closest("tr");
		data['type']='edit';
		data=prepareDataText(data, ['rowid'], par, f);
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			var adder=d['adder'].replace('@@PaymentMethod', payment_method_choice);
			var result=d['result'].replace('@@Adder', adder);
			pelunasan_id=d['pelunasan_id'];	
			var div='#div_update';
			$(div).html(result);
			fixSelect();
			setDatePicker();
			var a=new Pelunasan($(div), adder);
			hideColumns('tbl_payment');
			hideColumnsArr('tbl_detail_nota', ['buy_id','buy_lunas_id'] , f2);
			hideColumns('tbl_retur');
			$("#tabs").tabs({ active: 0 });	
		}
		ajax(ajaxPage, data, success);
	}
	
	self.Delete=function() {
		var data={}
		var par=$(this).closest("tr");
		data['type']='delete';
		data=prepareDataText(data, ['rowid'], par, f);
		var success=function(msg) {
			
			par.remove();
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
	
}