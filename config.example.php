<?php
$users_arr=array('admin'=>'TEST_PASSWORD_123');
$config['server_timezone']='Etc/GMT';
$config['db_host']='localhost';
$config['db_login']='login';
$config['db_password']='password';
$config['db_base']='database';
$config['jsonrpc_node']='https://solox.world/';

//Google recaptcha secret
$recaptcha_secret='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

//VIZ credentials for registration (account, private active key)
$reg_login='account';
$reg_wif='5K...';

//VIZ credentials for code-claim transfer in vesting shares (account, private active key)
$claim_login='account';
$claim_wif='5K...';