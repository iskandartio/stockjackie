<script src="js/sha512.js"></script>

<script>
	var ajaxPage='login_ajax';
	$(function() {
		bind('#btn_login','click',Login);
		bind('#password','keydown',PasswordEnter);
		$('#email').val(getCookie('user_id'));
		login2(getCookie('password'));
	});
	function PasswordEnter(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			Login();
		}
	}
	
	function EmailEnter(e) {
		var code = e.keyCode || e.which;
		if(code == 13) { 
			$(this).next().focus();
		}
	}
	function login2(pwd, fromInput) {
		var data={};
		data['type']='login';
		data['password']=pwd;
		data=prepareDataText(data,['email']);		
		var success=function(msg) {
			obj = jQuery.parseJSON(msg);
			$('#freeze').hide();
			if (obj['err']!='')  {
				if (fromInput) {	
					alert(obj['err']);
				}
				return;
			}
			setCookie('user_id',$('#email').val(), 1000);
			setCookie('password',data['password'], 1000);
			location.href=obj['url'];
		}		
		ajax(ajaxPage, data, success);
	}
	function Login() {
		if (!validate_empty(['email','password'])) return;
		$('#freeze').show();
		var hash = CryptoJS.SHA512($('#password').val());
		login2(hash.toString(), true);
		
	}

</script>

<div class='middle_div'>
	<div class='label'>Email</div><div class='textbox'><?php _t("email") ?></div>
	<div id='div_password'><div class='label'>Password</div><div class='textbox'><?php _t("password","","","password") ?></div></div>
	<button class='button_link' id='btn_login'>Login</button>
	
</div>	
