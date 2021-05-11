<?php
include('functions.php');
include('ltmp_arr.php');
$viz_price=floatval(file_get_contents($site_root.'/viz_price.txt'));//в долларах
$replace['css_change_time']=$css_change_time;
$replace['script_change_time']=$script_change_time;
$replace['head_addon']='';

$admin=false;
$user=array();
if(isset($_COOKIE['start_vizplus_login'])){
	if($_COOKIE['start_vizplus_password']==md5($users_arr[$_COOKIE['start_vizplus_login']])){
		$admin=true;
		$user=$_COOKIE['start_vizplus_login'];
	}
}

$replace['title']=$ltmp_arr['meta']['title'];
$replace['description']=$ltmp_arr['meta']['description'];
$replace['head_addon'].='<script>';
$replace['head_addon'].='var ltmp_arr='.json_encode($ltmp_arr['js']).';';
$replace['head_addon'].='</script>';

$replace['select-lang']='';
/*
$select_lang_arr=[];
foreach($ltmp_base as $lang_el){
	if($lang_el['active']){
		$select_lang_arr[]='<a href="?set_lang='.$lang_el['code2'].'"'.(($lang_el['code2']==$ltmp_current)?' class="current"':'').'>'.$lang_el['local-name'].'</a>';
	}
}
$replace['select-lang']=implode(' / ',$select_lang_arr);
*/