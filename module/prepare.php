<?php
include('functions.php');
$viz_price=floatval(file_get_contents($site_root.'/viz_price.txt'));//в долларах
$replace['title']='Начни знакомство с VIZ!';
$replace['description']='Пройди регистрацию и получи аккаунт для взаимодействия с экосистемой VIZ.';

$replace['css_change_time']=$css_change_time;
$replace['script_change_time']=$script_change_time;

$admin=false;
$user=array();
if(isset($_COOKIE['start_vizplus_login'])){
	if($_COOKIE['start_vizplus_password']==md5($users_arr[$_COOKIE['start_vizplus_login']])){
		$admin=true;
		$user=$_COOKIE['start_vizplus_login'];
	}
}