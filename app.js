viz.config.set('websocket','https://node.viz.plus/');
function pass_gen(length=100,to_wif=true){
	let charset='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+-=_:;.,@!^&*$';
	let ret='';
	for (var i=0,n=charset.length;i<length;++i){
		ret+=charset.charAt(Math.floor(Math.random()*n));
	}
	if(!to_wif){
		return ret;
	}
	let wif=viz.auth.toWif('',ret,'');
	return wif;
}

var keys=[];
keys=viz.auth.getPrivateKeys('',pass_gen(100),['master','active','regular','memo']);

var check_login_timer=0;

$(window).on('hashchange',function(e){
	e.preventDefault();
	if(''!=window.location.hash){
		if($(window.location.hash).length>0){
			$('body,html').animate({scrollTop:parseInt($('.index[data-index='+window.location.hash+']').offset().top) - 64 - 10},1000);
		}
	}
	else{
		$(window).scrollTop(0);
	}
});

function app_keyboard(e){
	if(!e)e=window.event;
	var key=(e.charCode)?e.charCode:((e.keyCode)?e.keyCode:((e.which)?e.which:0));
	if(key==27){
		e.preventDefault();
	}
}

function setCaretPosition(elem,caretPos) {
	let range;
	if (elem.createTextRange) {
		range = elem.createTextRange();
		range.move('character', caretPos);
		range.select();
	} else {
		elem.focus();
		if (elem.selectionStart !== undefined) {
			elem.setSelectionRange(caretPos, caretPos);
		}
	}
}

function download(filename, text) {
	var link = document.createElement('a');
	link.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
	link.setAttribute('download', filename);

	if (document.createEvent) {
		var event = document.createEvent('MouseEvents');
		event.initEvent('click', true, true);
		link.dispatchEvent(event);
	}
	else {
		link.click();
	}
}

var hcaptcha_response=false;
function hcaptcha_callback(response){
	console.log(response);
	hcaptcha_response=response;
	$('.reg-form').css('display','block');
}
function hcaptcha_expire_callback(response){
	console.log(response);
	hcaptcha_response='';
	$('.reg-form').css('display','block');
}

/*
function recaptcha_callback(){
	let recaptcha_response=grecaptcha.getResponse();
	if(''!=recaptcha_response){
		$('.reg-form').css('display','block');
	}
}
*/
$(document).ready(function(){
	var hash_load=window.location.hash;
	if(''!=hash_load){
		hash_load=hash_load.substr(1);
		if(0<$('.index[data-index='+hash_load+']').length){
			$('body,html').animate({scrollTop:parseInt($('.index[data-index='+hash_load+']').offset().top) - 64 - 10},1000);
		}
	}
	document.addEventListener('keyup', app_keyboard, false);

	if(0<$('.cards-nav').length){
		$('.cards-nav li').bind('click',function(){
			if($('.index[data-index='+$(this).attr('data-index')+']').length>0){
				$('body,html').animate({scrollTop:parseInt($('.index[data-index='+$(this).attr('data-index')+']').offset().top) - 64 - 10},1000);
				window.location.hash=$(this).attr('data-index');
			}
		});
	}

	if(0<$('.check').length){
		$('.check input[type=checkbox]').change(function(){
			if($(this).prop('checked')){
				if('alt-reg'==$(this).attr('data-target')){
					$(this).attr('disabled','disabled');
					$('.alt-reg-form').css('display','block');
				}
				if('reg-complete'==$(this).attr('data-target')){
					$(this).attr('disabled','disabled');
					$('.card[data-index='+$(this).attr('data-target')+']').css('display','block');
				}
				else{
					let check_target=$('.card[data-index='+$(this).attr('data-target')+']').find('.check-next');
					check_target.children('.check-waiting').css('display','none');
					check_target.children('.check-opened').css('display','block');
					let hidden_check_next=check_target.find('.check input[type=checkbox]').attr('data-hidden-target');
					if(!hidden_check_next){
						let check_next=check_target.find('.check input[type=checkbox]').attr('data-target');
						if(check_next){
							$('.card[data-index='+check_next+']').css('display','block');
						}
					}
				}
			}
			else{
				let check_target=$('.card[data-index='+$(this).attr('data-target')+']').find('.check-next');
				check_target.children('.check-opened').css('display','none');
				check_target.children('.check-waiting').css('display','block');
				let hidden_check_next=check_target.find('.check input[type=checkbox]').attr('data-hidden-target');
				if(!hidden_check_next){
					let check_next=check_target.find('.check input[type=checkbox]').attr('data-target');
					if(check_next){
						$('.card[data-index='+check_next+']').css('display','none');
					}
				}
			}
		});
	}

	if(0<$('.radio').length){
		$('.radio input[type=radio]').change(function(){
			$('.radio-option.radio-'+$(this).attr('name')).css('display','none');
			$('.radio-option.radio-'+$(this).attr('name')+'-'+$(this).val()).css('display','block');
			if('ask'==$(this).attr('name')){
				if('yes'==$(this).val()){
					let check_target=$('.card[data-index=alt-2]').find('.check-next');
					check_target.children('.check-waiting').css('display','none');
					check_target.children('.check-opened').css('display','block');
					let hidden_check_next=check_target.find('.check input[type=checkbox]').attr('data-hidden-target');
					if(!hidden_check_next){
						let check_next=check_target.find('.check input[type=checkbox]').attr('data-target');
						if(check_next){
								$('.card[data-index='+check_next+']').css('display','block');
						}
					}
				}
				if('no'==$(this).val()){
					let check_target=$('.card[data-index=2]').find('.check-next');
					check_target.children('.check-waiting').css('display','none');
					check_target.children('.check-opened').css('display','block');
					let hidden_check_next=check_target.find('.check input[type=checkbox]').attr('data-hidden-target');
					if(!hidden_check_next){
						let check_next=check_target.find('.check input[type=checkbox]').attr('data-target');
						if(check_next){
								$('.card[data-index='+check_next+']').css('display','block');
						}
					}
				}
			}
		});
	}

	$(window).bind('beforeunload', function(e){
		e.preventDefault();
		if($('.check input[type=checkbox]')[0].checked){
			if($('.check input[type=checkbox]')[1].checked){
				return;
			}
			else{
				e.returnValue='';
				return e.returnValue;
			}
		}
		return;
	});

	if(0<$('input[name=create-account]').length){
		$('input[name=create-account]').click(function(){
			$('.create-account-error').html('');
			if(''!=$('.create-account-available').html()){//логин занят
				console.log('just return');
				return;
			}
			//var captcha_response=grecaptcha.getResponse();
			var captcha_response=hcaptcha_response;
			var error=false;
			if(''!=captcha_response){
				var account_login=$('input[name=create-account-login]').val().trim();
				account_login=account_login.toLowerCase();
				$('input[name=create-account-login]').val(account_login);

				if(account_login.length<3){
					error=ltmp_arr.errors.account_length_less;
					$('.create-account-available').html(error);
					$('.create-account-error').html('');
					return;
				}
				else{
					var last_char=account_login.substr(-1,1);
					if(!/^([a-z0-9])$/.test(last_char)){
						error=ltmp_arr.errors.account_last_symbol;
						$('.create-account-available').html(error);
						$('.create-account-error').html('');
						return;
					}
				}
				if(!error){
					$('input[name=create-account]').attr('disabled','disabled');
					$('.submit-button-ring[rel=create-account]').css('display','inline-block');
					$('.create-account-error').html('');
					keys=viz.auth.getPrivateKeys(account_login,pass_gen(100),['master','active','regular','memo']);
					console.log(keys);
					$.ajax({
						type:'POST',
						url:'/ajax/account-create/',
						data:{captcha_response,account_login,'public_master':keys['masterPubkey'],'public_active':keys['activePubkey'],'public_regular':keys['regularPubkey'],'public_memo':keys['memoPubkey']},
						success:function(result){
							$('.submit-button-ring[rel=create-account]').css('display','none');
							result_json=JSON.parse(result);
							if('failed recaptcha'==result_json.result){
								error=ltmp_arr.errors.captcha_invalid;
								grecaptcha.reset();
								$('.create-account-error').html(error);
								$('input[name=create-account]').removeAttr('disabled');
							}
							if('login not available'==result_json.result){
								error=ltmp_arr.errors.account_already_exist;
								$('.create-account-available').html(error);
								$('.create-account-error').html('');
								$('input[name=create-account]').removeAttr('disabled');
							}
							if('broadcast error'==result_json.result){
								error=ltmp_arr.errors.server_error;
								$('.create-account-available').html('');
								$('.create-account-error').html(error);
								$('input[name=create-account]').removeAttr('disabled');
							}
							if('success'==result_json.result){
								$('input[name=create-account-login]').val(account_login);
								$('input[name=create-account-login]').css('border-color','#0db11e');
								$('input[name=create-account-login]').attr('disabled','disabled');
								$('input[name=create-account]').attr('disabled','disabled');
								$('input[name=create-account]').css('display','none');
								$('.create-account-available').html('');
								$('.create-account-error').html('');

								$('.account-keys .account-login').html(account_login);
								$('.account-keys .master-key').html(keys['master']);
								$('.account-keys .active-key').html(keys['active']);
								$('.account-keys .regular-key').html(keys['regular']);
								$('.account-keys .memo-key').html(keys['memo']);
								$('.account-keys').css('display','block');

								$('input[name=claim-login]').val(account_login);
								$('input[name=claim-login]').attr('disabled','disabled');

								download('viz-registration.txt','VIZ.plus registration\r\nAccount login: '+account_login+'\r\nMaster key: '+keys['master']+'\r\nActive key: '+keys['active']+'\r\nRegular key: '+keys['regular']+'\r\nMemo key: '+keys['memo']+'');
								$('.h-captcha').css('display','none');
								//$('.g-recaptcha').css('display','none');
							}
						},
						error:function(xhr,ajaxOptions,thrownError){
							console.log(xhr.statusText);
							console.log(thrownError);
							error=ltmp_arr.errors.server_error;
							$('.create-account-available').html('');
							$('.create-account-error').html(error);
							$('input[name=create-account]').removeAttr('disabled');
						}
					});
				}
			}
			else{
				error=ltmp_arr.errors.captcha_expire;
				$('.create-account-error').html(error);
				return;
			}
		})
	}

	if(0<$('input[name=alt-create-account]').length){
		$('input[name=alt-create-account]').click(function(){
			$('.alt-create-account-error').html('');
			if(''!=$('.alt-create-account-available').html()){//логин занят
				console.log('just return');
				return;
			}
			var error=false;
			var invite=$('input[name=alt-create-account-invite]').val();
			if(!viz.auth.isWif(invite)){
				error=ltmp_arr.errors.invalid_invite;
				$('.alt-create-account-error').html(error);
				return;
			}
			var invite_public=viz.auth.wifToPublic(invite);
			var account=$('input[name=alt-create-account-login]').val();
			if(account.length<3){
				error=ltmp_arr.errors.account_length_less;
				$('.alt-create-account-error').html(error);
				return;
			}
			else{
				var last_char=account.substr(-1,1);
				if(!/^([a-z0-9])$/.test(last_char)){
					error=ltmp_arr.errors.account_last_symbol;
					$('.alt-create-account-available').html(error);
					$('.alt-create-account-error').html('');
					return;
				}
			}
			if(!error){
				$('input[name=alt-create-account]').attr('disabled','disabled');
				$('.submit-button-ring[rel=alt-create-account]').css('display','inline-block');
				$('.alt-create-account-error').html('');
				let private_key=pass_gen(100,true);
				let public_key=viz.auth.wifToPublic(private_key);
				viz.api.getInviteByKey(invite_public,function(err,response){
					if(!err){
						let invite_balance=response.balance;
						viz.broadcast.inviteRegistration('5KcfoRuDfkhrLCxVcE9x51J6KN9aM9fpb78tLrvvFckxVV6FyFW','invite',account,invite,public_key,function(err,result){
							if(!err){
								$('.submit-button-ring[rel=alt-create-account]').css('display','none');
								$('input[name=alt-create-account-login]').val(account);
								$('input[name=alt-create-account-login]').css('border-color','#0db11e');
								$('input[name=alt-create-account-login]').attr('disabled','disabled');
								$('input[name=alt-create-account]').attr('disabled','disabled');
								$('input[name=alt-create-account]').css('display','none');
								$('.alt-create-account-available').html('');
								$('.alt-create-account-error').html('');

								$('.alt-account-keys .account-login').html(account);
								$('.alt-account-keys .invite-balance').html(invite_balance);
								$('.alt-account-keys .master-key').html(private_key);
								$('.alt-account-keys .active-key').html(private_key);
								$('.alt-account-keys .regular-key').html(private_key);
								$('.alt-account-keys .memo-key').html(private_key);
								$('.alt-account-keys').css('display','block');

								$('input[name=claim-login]').val(account);
								$('input[name=claim-login]').attr('disabled','disabled');

								download('viz-registration.txt','VIZ.plus registration\r\nAccount login: '+account+'\r\nMaster key: '+private_key+'\r\nActive key: '+private_key+'\r\nRegular key: '+private_key+'\r\nMemo key: '+keys['memo']+'');
							}
							else{
								$('.submit-button-ring[rel=alt-create-account]').css('display','none');
								error=ltmp_arr.errors.server_error;
								$('.alt-create-account-available').html('');
								$('.alt-create-account-error').html(error);
								$('input[name=alt-create-account]').removeAttr('disabled');
							}
						});
					}
					else{
						$('.submit-button-ring[rel=alt-create-account]').css('display','none');
						error=ltmp_arr.errors.server_error;
						$('.alt-create-account-available').html('');
						$('.alt-create-account-error').html(error);
						$('input[name=alt-create-account]').removeAttr('disabled');
					}
				});
			}
		})
	}

	if(0<$('input[name=claim-action]').length){
		$('input[name=claim-action]').click(function(){
			var error=false;
			var account_login=$('input[name=claim-login]').val().trim();
			account_login=account_login.toLowerCase();
			$('input[name=claim-login]').val(account_login);
			var code=$('input[name=claim-code]').val().trim();
			$('input[name=claim-action]').attr('disabled','disabled');
			$('.submit-button-ring[rel=claim-action]').css('display','inline-block');
			$('.claim-action-error').html('');
			$('.claim-action-success').html('');
			$.ajax({
				type:'POST',
				url:'/ajax/claim-code/',
				data:{account_login,code},
				success:function(result){
					//console.log(result);
					$('.submit-button-ring[rel=claim-action]').css('display','none');
					result_json=JSON.parse(result);
					if('too much attempts'==result_json.result){
						error=ltmp_arr.errors.attempts_limit;
						$('.claim-action-error').html(error);
						$('input[name=claim-action]').removeAttr('disabled');
					}
					if('claimed code'==result_json.result){
						error=ltmp_arr.errors.invite_already_activated;
						$('.claim-action-error').html(error);
						$('input[name=claim-action]').removeAttr('disabled');
					}
					if('incorrect code'==result_json.result){
						error=ltmp_arr.errors.invite_not_founded;
						$('.claim-action-error').html(error);
						$('input[name=claim-action]').removeAttr('disabled');
					}
					if('broadcast error'==result_json.result){
						error=ltmp_arr.errors.server_error;
						$('.claim-action-error').html(error);
						$('input[name=claim-action]').removeAttr('disabled');
					}
					if('success'==result_json.result){
						$('input[name=claim-action]').attr('disabled','disabled');
						$('input[name=claim-action]').css('display','none');
						$('.claim-action-error').html('');
						$('.claim-action-success').html(ltmp_arr.success.invite);
					}
				},
			});
		})
	}
	var check_login=function(el){
		var account_login=el.val();
		if(account_login.length>2){
			var last_char=account_login.substr(-1,1);
			var first_char=account_login.substr(0,1);
			if(!/^([a-z0-9])$/.test(last_char)){
				el.css('border-color','#ef1c1c');
				$('.'+el.attr('data-available')).html(ltmp_arr.errors.account_last_symbol);
			}
			else
				if(!/^([a-z])$/.test(first_char)){
				el.css('border-color','#ef1c1c');
				$('.'+el.attr('data-available')).html(ltmp_arr.errors.account_first_symbol);
			}
			else{
				$.ajax({
					type:'POST',
					url:'/ajax/check-login-available/',
					data:{'account_login':el.val()},
					success:function(result){
						result_json=JSON.parse(result);
						if('success'==result_json.result){
							el.css('border-color','#0db11e');
							$('.'+el.attr('data-available')).html('');
						}
						else{
							el.css('border-color','#ef1c1c');
							$('.'+el.attr('data-available')).html(ltmp_arr.errors.account_already_exist);
						}
					}
				});
			}
		}
		else{
			el.css('border-color','#ccc');
			$('.'+el.attr('data-available')).html('');
		}
	}
	if(0<$('input[name=create-account-login]').length){
		$('input[name=create-account-login]').bind('input',function(e){
			//console.log('input',e);
			if(!e)e=window.event;
			e=e.originalEvent;
			let char=e.data;
			if(null!==char){
				if(char.length>1){
					char=char.slice(-1);
				}
				//more accurate for input char
				let str=$(this).val();
				let caret_position=$(this)[0].selectionStart;
				let left_part=str.substr(0,caret_position-1);
				let right_part=str.substr(caret_position);

				save=true;
				if(/^([A-Z])$/.test(char)){
					//$(this).val(''+$(this).val().slice(0,-char.length));
					//$(this).val(''+$(this).val()+char.toLowerCase());

					$(this).val(left_part+char.toLowerCase()+right_part);
					setCaretPosition($(this)[0],left_part.length+1);
					return;
				}
				if(0==$(this).val().length){
					if(/^([a-z])$/.test(char)){
						save=true;
					}
					else{
						save=false;
					}
				}
				else{
					if(/^([a-z0-9\-])$/.test(char)){
						save=true;
					}
					else{
						save=false;
					}
				}
				//prevent double hyphen
				if(-1!=str.indexOf('--')){
					save=false;
				}
				//prevent login starts on digit or hyphen
				if(/^([0-9\-])$/.test(str.substr(0,1))){
					save=false;
				}
				//prevent login ends on hyphen
				if(/^([\-])$/.test(str.substr(-1))){
					save=false;
				}
				if(!save){
					//$(this).val(''+$(this).val().slice(0,-char.length));
					$(this).val(left_part+right_part);
					setCaretPosition($(this)[0],left_part.length);
				}
			}
		});
		/*
		//old keypress
		$('input[name=create-account-login]').bind('keypress',function(e){
			if(!e)e=window.event;
			let key=(e.charCode)?e.charCode:((e.keyCode)?e.keyCode:((e.which)?e.which:0));
			let char=String.fromCharCode(key);
			if(/^([A-Z])$/.test(char)){
				$(this).val(''+$(this).val()+char.toLowerCase());
				return false;
			}
			if(0==$(this).val().length){
				if(/^([a-z])$/.test(char)){
					return true;
				}
				else{
					return false;
				}
			}
			else{
				if(/^([a-z0-9\-])$/.test(char)){
					return true;
				}
				else{
					return false;
				}
			}
			return true;
		});
		*/
		$('input[name=create-account-login]').bind('keyup',function(e){
			clearTimeout(check_login_timer);
			check_login_timer=setTimeout(check_login,300,$(this));
		});
	}
	if(0<$('input[name=alt-create-account-login]').length){
		$('input[name=alt-create-account-login]').bind('input',function(e){
			if(!e)e=window.event;
			e=e.originalEvent;
			let char=e.data;
			if(null!==char){
				if(char.length>1){
					char=char.slice(-1);
				}
				//more accurate for input char
				let str=$(this).val();
				let caret_position=$(this)[0].selectionStart;
				let left_part=str.substr(0,caret_position-1);
				let right_part=str.substr(caret_position);

				save=true;
				if(/^([A-Z])$/.test(char)){
					//$(this).val(''+$(this).val().slice(0,-char.length));
					//$(this).val(''+$(this).val()+char.toLowerCase());

					$(this).val(left_part+char.toLowerCase()+right_part);
					setCaretPosition($(this)[0],left_part.length+1);
					return;
				}
				if(0==$(this).val().length){
					if(/^([a-z])$/.test(char)){
						save=true;
					}
					else{
						save=false;
					}
				}
				else{
					if(/^([a-z0-9\-])$/.test(char)){
						save=true;
					}
					else{
						save=false;
					}
				}
				//prevent double hyphen
				if(-1!=str.indexOf('--')){
					save=false;
				}
				//prevent login starts on digit or hyphen
				if(/^([0-9\-])$/.test(str.substr(0,1))){
					save=false;
				}
				//prevent login ends on hyphen
				if(/^([\-])$/.test(str.substr(-1))){
					save=false;
				}
				if(!save){
					//$(this).val(''+$(this).val().slice(0,-char.length));
					$(this).val(left_part+right_part);
					setCaretPosition($(this)[0],left_part.length);
				}
			}
		});
		/*
		//old keypress
		$('input[name=alt-create-account-login]').bind('keypress',function(e){
			if(!e)e=window.event;
			let key=(e.charCode)?e.charCode:((e.keyCode)?e.keyCode:((e.which)?e.which:0));
			let char=String.fromCharCode(key);
			if(/^([A-Z])$/.test(char)){
				$(this).val(''+$(this).val()+char.toLowerCase());
				return false;
			}
			if(0==$(this).val().length){
				if(/^([a-z])$/.test(char)){
					return true;
				}
				else{
					return false;
				}
			}
			else{
				if(/^([a-z0-9\-])$/.test(char)){
					return true;
				}
				else{
					return false;
				}
			}
			return true;
		});
		*/
		$('input[name=alt-create-account-login]').bind('keyup',function(e){
			clearTimeout(check_login_timer);
			check_login_timer=setTimeout(check_login,300,$(this));
		});
	}
});