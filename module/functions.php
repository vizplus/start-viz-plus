<?php
function raw_string($str){
	$length=dechex(strlen($str));
	if(strlen($length)%2!=0){
		$length='0'.$length;
	}
	$hex=bin2hex($str);
	$result=$length.$hex;
	return $result;
}
function raw_pub_key($key){
	$pub_key=new viz_keys();
	$pub_key->import_public($key);
	return $pub_key->hex;
}
function raw_asset($str){
	$str_arr=explode(' ',$str);
	$number_arr=explode('.',$str_arr[0]);

	$precision=strlen($number_arr[1]);
	$precision_hex=dechex($precision);
	if(strlen($precision_hex)%2!=0){
		$precision_hex='0'.$precision_hex;
	}

	$number=(int)implode('',$number_arr);
	$number_hex=dechex($number);
	if(strlen($number_hex)%2!=0){
		$number_hex='0'.$number_hex;
	}
	$number_hex=bin2hex(strrev(hex2bin($number_hex)));
	$length=8-(strlen($number_hex)/2);
	if($length>0){
		$number_hex.=str_repeat('00',$length);
	}

	$asset_str=$str_arr[1];
	$asset_hex=bin2hex($asset_str);
	if(strlen($asset_hex)%2!=0){
		$asset_hex='0'.$asset_hex;
	}
	$length=7-(strlen($asset_hex)/2);
	if($length>0){
		$asset_hex.=str_repeat('00',$length);
	}

	$result=$number_hex.$precision_hex.$asset_hex;
	return $result;
}
function raw_int($int,$bytes=1){
	$hex='';
	if($int){
		$hex=dechex($int);
		if(strlen($hex)%2!=0){
			$hex='0'.$hex;
		}
		$hex=bin2hex(strrev(hex2bin($hex)));
	}
	$length=$bytes-(strlen($hex)/2);
	if($length>0){
		$hex.=str_repeat('00',$length);
	}
	return $hex;
}
function build_create_invite_tx($wif,$creator,$balance,$invite_key,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='2b';//43=create_invite
	$raw_tx.=raw_string($creator);
	$raw_tx.=raw_asset($balance);
	$raw_tx.=raw_pub_key($invite_key);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["create_invite",{"creator":"'.$creator.'","balance":"'.$balance.'","invite_key":"'.$invite_key.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_delegate_vesting_shares_tx($wif,$delegator,$delegatee,$vesting_shares,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='13';//19=delegate_vesting_shares
	$raw_tx.=raw_string($delegator);
	$raw_tx.=raw_string($delegatee);
	$raw_tx.=raw_asset($vesting_shares);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["delegate_vesting_shares",{"delegator":"'.$delegator.'","delegatee":"'.$delegatee.'","vesting_shares":"'.$vesting_shares.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_account_create_tx($wif,$fee,$delegation,$creator,$new_account_name,$master,$active,$regular,$memo_key,$json_metadata,$referrer,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='14';//20=account_create

	$raw_tx.=raw_asset($fee);
	$raw_tx.=raw_asset($delegation);

	$raw_tx.=raw_string($creator);
	$raw_tx.=raw_string($new_account_name);

	$raw_tx.='01000000';//weight_threshold=01000000(uint32)
	$raw_tx.='00';//account_auths=[]
	$raw_tx.='01';//key_auths=[[ количество записей в массиве
	$raw_tx.=raw_pub_key($master);
	$raw_tx.='0100';//,1]] = uint16_t weight_type (2 байта)

	$raw_tx.='01000000';//weight_threshold=01000000(uint32)
	$raw_tx.='00';//account_auths=[]
	$raw_tx.='01';//key_auths=[[ количество записей в массиве
	$raw_tx.=raw_pub_key($active);
	$raw_tx.='0100';//,1]] = uint16_t weight_type (2 байта)

	$raw_tx.='01000000';//weight_threshold=01000000(uint32)
	$raw_tx.='00';//account_auths=[]
	$raw_tx.='01';//key_auths=[[ количество записей в массиве
	$raw_tx.=raw_pub_key($regular);
	$raw_tx.='0100';//,1]] = uint16_t weight_type (2 байта)

	$raw_tx.=raw_pub_key($memo_key);

	$raw_tx.=raw_string($json_metadata);
	$raw_tx.=raw_string($referrer);//op referrer
	$raw_tx.='00';//op extensions

	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["account_create",{"fee":"'.$fee.'","delegation":"'.$delegation.'","creator":"'.$creator.'","new_account_name":"'.$new_account_name.'","master":{"weight_threshold":1,"account_auths":[],"key_auths":[["'.$master.'",1]]},"active":{"weight_threshold":1,"account_auths":[],"key_auths":[["'.$active.'",1]]},"regular":{"weight_threshold":1,"account_auths":[],"key_auths":[["'.$regular.'",1]]},"memo_key":"'.$memo_key.'","json_metadata":"'.$json_metadata.'","referrer":"'.$referrer.'","extensions":[]}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_transfer_to_vesting_tx($wif,$from,$to,$amount,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));//!!!
	$raw_tx='01';//op count
	$raw_tx.='03';//03=transfer_to_vesting
	$raw_tx.=raw_string($from);
	$raw_tx.=raw_string($to);
	$raw_tx.=raw_asset($amount);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["transfer_to_vesting",{"from":"'.$from.'","to":"'.$to.'","amount":"'.$amount.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}
function build_transfer_tx($wif,$from,$to,$amount,$memo,$debug=false){
	global $api;
	$chain_id='2040effda178d4fffff5eab7a915d4019879f5205cc5392e4bcced2b6edda0cd';
	$nonce=0;
	$canonical_signature=false;

	$key=new viz_keys();
	$key->import_wif($wif);
	$pub_key=new viz_keys();
	$pub_key->import_wif($wif);
	$pub_key->to_public();

	$dgp=$api->execute_method('get_dynamic_global_properties');
	if(!$dgp['head_block_number']){
		return false;
	}
	$tapos_block_num=$dgp['head_block_number'] - 5;
	$ref_block_num=($tapos_block_num) & 0xFFFF;
	$ref_block_num_hex=dechex($ref_block_num);
	if(strlen($ref_block_num_hex)%2!=0){
		$ref_block_num_hex='0'.$ref_block_num_hex;
	}
	$ref_block_num_bin=bin2hex(strrev(hex2bin($ref_block_num_hex)));
	$tapos_block=$tapos_block_num+1;
	$tapos_block_info=false;
	$api_count=0;
	while(!$tapos_block_info){
		$tapos_block_info=$api->execute_method('get_block_header',array($tapos_block));
		if(!$tapos_block_info){
			$api_count++;
			if($api_count>5){
				return false;
			}
			usleep(100);
		}
	}
	if(!isset($tapos_block_info['previous'])){
		return false;
	}
	$ref_block_prefix_bin=bin2hex(strrev(substr(hex2bin($tapos_block_info['previous']),4,4)));
	$ref_block_prefix=hexdec($ref_block_prefix_bin);
	$ref_block_prefix_bin_nice=bin2hex(strrev(hex2bin($ref_block_prefix_bin)));
	$raw_tx='01';//op count
	$raw_tx.='02';//02=transfer
	$raw_tx.=raw_string($from);
	$raw_tx.=raw_string($to);
	$raw_tx.=raw_asset($amount);
	$raw_tx.=raw_string($memo);
	$tx_extension='00';
	while(!$canonical_signature){
		$expiration_time=time()+600+$nonce;//+10min+nonce
		$expiration=date('Y-m-d\TH:i:s',$expiration_time);
		$expiration_bin=bin2hex(strrev(hex2bin(dechex($expiration_time))));

		$raw_block=$ref_block_num_bin.$ref_block_prefix_bin_nice.$expiration_bin;
		$raw_data=$chain_id.$raw_block.$raw_tx.$tx_extension;

		$raw_data_bin=hex2bin($raw_data);
		$data_signature=$key->sign($raw_data_bin);
		$canonical_signature=ec_check_der($data_signature);
		if($canonical_signature){
			$data_signature_compact=$key->sign_recoverable_compact($raw_data_bin);
			if(!$data_signature_compact){
				$canonical_signature=false;
			}
		}
		else{
			$nonce++;
		}
	}
	if($debug){
		print $raw_data.PHP_EOL;
	}
	$json='{"ref_block_num":'.$ref_block_num.',"ref_block_prefix":'.$ref_block_prefix.',"expiration":"'.$expiration.'","operations":[["transfer",{"from":"'.$from.'","to":"'.$to.'","amount":"'.$amount.'","memo":"'.$memo.'"}]],"extensions":[],"signatures":["'.$data_signature_compact.'"]}';
	return $json;
}