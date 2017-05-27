function Buy(div, choices, adder) {
	var self=this;
	var f=generate_assoc(['rowid','buy_date','supplier','tempo','detail','btn']);
	self.start=function() {
		self.bindAll();
	}
	self.bindAll=function() {
		hideColumns('tbl');
		bindDiv('.btn_add',div, 'click', self.Add);
		bindDiv('.btn_edit', div, 'click', self.Edit);
		bindDiv('.btn_delete',div, 'click', self.Delete);
		bindDiv('.btn_save',div, 'click', self.Save);
		bindDiv('.btn_cancel',div, 'click', self.Cancel);
		bindDiv('.btn_detail',div, 'click', self.Detail);
		bindDiv('.tempo',div, 'keyup', self.TempoEnter);
		setDatePicker();
		autoComplete('.supplier', self.SetTempo, choices);
		autoSelect();

	}
	self.SetTempo=function(e, ui) {
		var par=$(e).closest('tr');
		$('.tempo', par).val(ui.item.tag);
	}
	self.TempoEnter=function(e) {
		var code = e.keyCode || e.which;
		if(code == 13) {
			var par=$(this).closest("tr");
			$('.btn_save', par).trigger( "click" );
		}
	}
	
	self.Add=function() {
		div.find('table tbody:eq(0)').prepend(adder);
		var par=$('#tbl tbody tr:eq(0)');
		getChildObjArr(par, 'buy_date', f, 'buy_date').val($.datepicker.formatDate('dd-mm-yy', new Date()));
		
		self.bindAll();
	}

	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"buy_date":8, "tempo":8}, f);
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
		textToDefaultLabel(par, ['buy_date','tempo'], f);
		autoCompleteToDefaultLabel(par, ['supplier'], f);
		btnChange(par, ['edit','delete'], f);
		self.start();
	}
	self.Save=function() {
		var par=$(this).closest("tr");
		var data={}
		data['type']='save';
		data=prepareDataText(data, ['buy_date','tempo'], par, f);
		data=prepareDataAutoComplete(data, ['supplier'], par,f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		data['supplier_name']=getChild(par, 'supplier', f);
		var success=function(msg) {
			setHtmlText(par, 'rowid', msg, f);
			textToLabel(par, ['buy_date','tempo'], f);
			setHtmlText(par, 'detail', "<span class='span_show_detail'><button class='btn_detail button_link'>Detail</button></span><span class='span_hide_detail'></span>", f);
			autoCompleteToLabel(par, ['supplier'], f);
			btnChange(par, ['edit','delete'], f);
			self.start();
		}
		ajax(ajaxPage, data, success);
	}
	self.Detail=function() {
		var par=$(this).closest("tr");
		var td=$(this).closest("td");
		var data={}
		data['type']='detail';
		data=prepareDataHtml(data, ['rowid'], par, f);
		data['supplier']=$('.supplier', par).html();
		
		var success=function(msg) {
			var d=jQuery.parseJSON(msg);
			$('.span_show_detail', td).hide();
			$('.span_hide_detail', td).html(d['result']);
			var a=new BuyDetail(td, d);
		}
		ajax(ajaxPage, data, success);
	}
	self.start();
}
function BuyDetail(div, d) {
	var choices=d['stock_choice'];
	var adder=d['adder'];
	var supplier=d['supplier'];
	var self=this;
	var f=generate_assoc(['rowid','stock','qty','price','total','btn']);
	self.start=function() {
		self.bindAll();
	}
	self.bindAll=function() {
		hideColumns('tbl_detail');
		bindDiv('.btn_add',div, 'click', self.Add);
		bindDiv('.btn_edit',div, 'click', self.Edit);
		bindDiv('.btn_delete',div, 'click', self.Delete);
		bindDiv('.btn_save',div, 'click', self.Save);
		bindDiv('.btn_cancel',div, 'click', self.Cancel);
		bindDiv('.btn_hide',div, 'click', self.Hide);
		bindDiv('.stock', div, 'keyup', self.StockKeyDown);
		bindDiv('.qty', div, 'keydown', self.KeyDown);
		bindDiv('.price', div, 'keydown', self.PriceKeyDown);
		autoComplete('.stock', self.ajaxGetLastPrice, choices);
	}
	
	self.ajaxGetLastPrice=function(o) {
		var par=$(o).closest("tr");
		var data={}
		data['type']='get_last_price';
		data['stock']=getChildHtml(par, 'stock', f);
		data['supplier']=supplier;
		var success=function(msg) {
			getChildObj(par, 'price', f).children().val(msg);
			goNextInput(o);
		}
		ajax(ajaxPage, data, success);
		
	}
	self.StockKeyDown=function(e) {
		$(this).data('id','');
		self.KeyDown(e);
	}
	self.KeyDown=function(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			goNextInput(this);
		}
	}
	self.PriceKeyDown=function(e) {
		var code = e.keyCode || e.which;
		if(code == 13) {
			var par=$(this).closest("tr");
			$('.btn_save', par).trigger( "click" );
		}
	}
	self.Add=function() {
		div.find('table tbody').prepend(adder);
		self.bindAll();
	}
	self.Edit=function() {
		var par=$(this).closest("tr");
		labelToText(par, {"qty":5,"price":8}, f);
		labelToAutoComplete(par, ['stock'], f);
		btnChange(par, ['save','cancel'], f);
		self.bindAll();
	}
	self.Delete=function() {
		var data={}
		var par=$(this).closest("tr");
		data['type']='delete_detail';
		data=prepareDataText(data, ['rowid'], par, f);
		var success=function(msg) {
			if (msg!='fail') {
				par.remove();
			} else {
				alert(msg);
			}
			self.countTotal();
		}
		ajax(ajaxPage, data, success);
	}
	self.Cancel=function() {
		var par=$(this).closest("tr");
		textToDefaultLabel(par, ['qty','price'], f);
		autoCompleteToDefaultLabel(par, ['stock'], f);
		btnChange(par, ['edit','delete'], f);
		self.start();
	}
	self.Save=function() {
		var par=$(this).closest("tr");
		var data={}
		data['type']='save_detail';
		data=prepareDataDecimal(data, ['qty','price'], par, f);
		data=prepareDataAutoComplete(data, ['stock'], par,f);
		data['stock_name']=getChild(par, 'stock', f);
		data=prepareDataHtml(data, ['rowid'], par, f);
		data['buy_id']=par.closest("td").closest("tr").children("td:eq(0)").html();
		var success=function(msg) {
			setHtmlText(par, 'rowid', msg, f);
			textToLabel(par, ['price','qty'], f);
			autoCompleteToLabel(par, ['stock'], f);
			btnChange(par, ['edit','delete'], f);
			qty=getChild(par, 'qty', f);
			price=getChild(par, 'price', f);
			total=qty*price;
			getChildObj(par, 'total', f).html(total);
			self.start();
			self.countTotal();
		}
		ajax(ajaxPage, data, success);
	}
	self.Hide=function() {
		$('.span_hide_detail', div).html('');
		$('.span_show_detail', div).show();
	}
	self.countTotal=function() {
		var grandTotal=0;
		div.find('table>tbody>tr').each(function(idx) {
			qty=getChild($(this), 'qty', f);
			price=getChild($(this), 'price', f);
			total=qty*price;
			grandTotal+=total;
		});
		var footer=div.find('table>tfoot>tr');
		$(footer).children("td:eq(1)").html(grandTotal);
	}
	self.start();
}