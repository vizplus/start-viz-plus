<?php
if('check-login-available'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$api=new viz_jsonrpc_web($config['jsonrpc_node']);
	$account_login=$_POST['account_login'];
	$account_login=preg_replace('~[^a-z0-9\-]~','',$account_login);
	$user_arr=$api->execute_method('get_accounts',array(array($account_login)))[0];
	if($user_arr['name']){
		print '{"result":"failure"}';
	}
	else{
		print '{"result":"success"}';
	}
	exit;
}
if('claim-code'==$path_array[2]){
	header("Access-Control-Allow-Origin:*");
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$api=new viz_jsonrpc_web($config['jsonrpc_node']);

	$account_login=$_POST['account_login'];
	$account_login=preg_replace('~[^a-z0-9\-\.]~','',$account_login);
	$code=$_POST['code'];

	$attempts=$db->table_count('bruteforce_check',"WHERE `ip`='".$db->prepare($ip)."' AND `time`>'".($time - 300)."'");
	if($attempts>=5){
		print '{"result":"too much attempts"}';
	}
	else{
		if(0==$db->table_count('codes',"WHERE `code`='".$db->prepare($code)."'")){
			$db->sql("INSERT INTO `bruteforce_check` (`time`,`ip`) VALUES ('".$time."','".$db->prepare($ip)."')");
			print '{"result":"incorrect code"}';
		}
		else{
			$code_arr=$db->sql_row("SELECT * FROM `codes` WHERE `code`='".$db->prepare($code)."'");
			if(0==$code_arr['status']){
				$viz_amount=$code_arr['amount']/$viz_price;
				$viz_amount=$viz_amount+0.001;
				$viz_amount=number_format($viz_amount,3,'.','');
				$viz_amount_fixed=number_format($viz_amount,3,'.','').' VIZ';
				$tx=build_transfer_to_vesting_tx($claim_wif,$claim_login,$account_login,$viz_amount_fixed);
				if($tx){
					$result=$api->execute_method('broadcast_transaction',$tx);
					if(false!==$result){
						$db->sql("UPDATE `codes` SET `viz_price`='".(float)$viz_price."', `viz_claimed`='".(float)$viz_amount."', `claimed`='".time()."', `status`=1, `user`='".$db->prepare($account_login)."', `tx`='".$db->prepare($tx)."' WHERE `id`='".$code_arr['id']."'");
						print '{"result":"success"}';
					}
					else{
						print '{"result":"broadcast error"}';
					}
				}
				else{
					print '{"result":"broadcast error"}';
				}
			}
			else{
				print '{"result":"claimed code"}';
			}
		}
	}
	exit;
}
if('claim-code-balance'==$path_array[2]){
	header("Access-Control-Allow-Origin:*");
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$api=new viz_jsonrpc_web($config['jsonrpc_node']);

	$account_login=$_POST['account_login'];
	$account_login=preg_replace('~[^a-z0-9\-\.]~','',$account_login);
	$code=$_POST['code'];

	$attempts=$db->table_count('bruteforce_check',"WHERE `ip`='".$db->prepare($ip)."' AND `time`>'".($time - 300)."'");
	if($attempts>=5){
		print '{"result":"too much attempts"}';
	}
	else{
		if(0==$db->table_count('codes',"WHERE `code`='".$db->prepare($code)."'")){
			$db->sql("INSERT INTO `bruteforce_check` (`time`,`ip`) VALUES ('".$time."','".$db->prepare($ip)."')");
			print '{"result":"incorrect code"}';
		}
		else{
			$code_arr=$db->sql_row("SELECT * FROM `codes` WHERE `code`='".$db->prepare($code)."'");
			if(0==$code_arr['status']){
				$viz_amount=$code_arr['amount']/$viz_price;
				$viz_amount=$viz_amount+0.001;
				$viz_amount=number_format($viz_amount,3,'.','');
				$viz_amount_fixed=number_format($viz_amount,3,'.','').' VIZ';
				$tx=build_transfer_tx($claim_wif,$claim_login,$account_login,$viz_amount_fixed,'code from viz.plus');
				if($tx){
					$result=$api->execute_method('broadcast_transaction',$tx);
					if(false!==$result){
						$db->sql("UPDATE `codes` SET `viz_price`='".(float)$viz_price."', `viz_claimed`='".(float)$viz_amount."', `claimed`='".time()."', `status`=1, `user`='".$db->prepare($account_login)."', `tx`='".$db->prepare($tx)."', `in_tokens`=1 WHERE `id`='".$code_arr['id']."'");
						print '{"result":"success"}';
					}
					else{
						print '{"result":"broadcast error"}';
					}
				}
				else{
					print '{"result":"broadcast error"}';
				}
			}
			else{
				print '{"result":"claimed code"}';
			}
		}
	}
	exit;
}
if('account-create'==$path_array[2]){
	header("Content-type:text/html; charset=UTF-8");
	header('HTTP/1.1 200 Ok');
	$api=new viz_jsonrpc_web($config['jsonrpc_node']);
	$recaptcha_response=$_POST['recaptcha_response'];

	$post_data=http_build_query(
		array(
			'secret'=>$recaptcha_secret,
			'response'=>$recaptcha_response
		)
	);
	$opts=array('http'=>
		array(
			'method'=>'POST',
			'header'=>'Content-type: application/x-www-form-urlencoded',
			'content'=>$post_data
		)
	);
	$context=stream_context_create($opts);
	$response=file_get_contents('https://www.google.com/recaptcha/api/siteverify',false,$context);
	$result=json_decode($response,true);

	if(true==$result['success']){
		$account_login=$_POST['account_login'];
		$account_login=preg_replace('~[^a-z0-9\-]~','',$account_login);
		$user_arr=$api->execute_method('get_accounts',array(array($account_login)))[0];
		if($user_arr['name']){
			print '{"result":"login not available"}';
		}
		else{
			$public_master=$_POST['public_master'];
			$public_active=$_POST['public_active'];
			$public_regular=$_POST['public_regular'];

			$chain_properties=$api->execute_method('get_chain_properties',array());
			$delegation=floatval($chain_properties['account_creation_fee'])*intval($chain_properties['create_account_delegation_ratio']);
			$delegation=''.number_format($delegation,6,'.','').' SHARES';

			$public_memo='VIZ1111111111111111111111111111111114T1Anm';
			$tx1=build_account_create_tx($reg_wif,'0.000 VIZ',$delegation,$reg_login,$account_login,$public_master,$public_active,$public_regular,$public_memo,'','');
			if($tx1){
				$result=$api->execute_method('broadcast_transaction',$tx1);
				if(false!==$result){
					$tx2=build_delegate_vesting_shares_tx($reg_wif,$reg_login,$account_login,'0.000000 SHARES');
					if($tx2){
						$result=$api->execute_method('broadcast_transaction',$tx2);
						if(false!==$result){
							print '{"result":"success"}';
						}
						else{
							print '{"result":"success"}';//не отозвалось делегирование!?
						}
					}
				}
				else{
					print '{"result":"broadcast error"}';
				}
			}
		}
	}
	else{
		print '{"result":"failed recaptcha"}';
	}
	exit;
}