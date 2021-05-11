<?php
ob_start();
if('admin'==$path_array[1]){
	if($admin){
		$replace['title']='Администрирование';
		print '<div class="cards-view">';
		print '<div class="cards-container"><div class="card">';
		if('codes'==$path_array[2]){
			if('download'==$path_array[3]){
				if(isset($_GET['amount'])){
					$amount=$_GET['amount'];
					$amount=str_replace(',','.',$amount);
					$amount_str=str_replace('.','_',$amount);
					$filename='start_vizplus_code_'.$amount_str.'.txt';
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename='.$filename);
					header('Content-Transfer-Encoding: binary');
					header('Connection: Keep-Alive');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					ob_end_clean();
					$q=$db->sql("SELECT `code` FROM `codes` WHERE `amount`='".$db->prepare($amount)."' AND `status`=0 ORDER BY `id` ASC");
					while($m=$db->row($q)){
						$data.=$m['code'].PHP_EOL;
					}
					$data=trim($data,PHP_EOL);
					header('Content-Length: '.strlen($data));
					print $data;
					exit;
				}
				print '<a class="right" href="/admin/codes/">&larr;</a>';
				print '<h3 class="captions">Загрузить неиспользованные коды</h3>';
				print '<p>Выберите тип:</p>';
				$num=0;
				$q=$db->sql("SELECT DISTINCT(`amount`) as `amount` FROM `codes` WHERE `status`=0 ORDER BY `amount` ASC");
				while($m=$db->row($q)){
					$num++;
					$count=$db->table_count('codes',"WHERE `amount`='".$m['amount']."' AND `status`=0");
					print '<p><a href="/admin/codes/download/?amount='.$m['amount'].'">Список кодов по $'.$m['amount'].', количество: '.$count.'</a></p>';
				}
				if(0==$num){
					print '<p>Видимо, все коды использованы, <a href="/admin/generate-codes/">сгенерируйте новые</a>.</p>';
				}
			}
			elseif('claimed'==$path_array[3]){
				print '<a class="right" href="/admin/codes/">&larr;</a>';
				print '<h3 class="captions">Обзор использованных кодов</h3>';
				$q=$db->sql("SELECT * FROM `codes` WHERE `status`=1 ORDER BY `id` DESC LIMIT 1000");
				while($m=$db->row($q)){
					print '<p style="font-family:arial !important;font-size:12px;margin-bottom:2px;padding-bottom:2px;border-bottom:1px solid #ccc;">#'.$m['code'].' with $'.$m['amount'].' amount, '.$m['viz_claimed'].' VIZ (price $'.$m['viz_price'].' per 1.000 VIZ) claimed '.date('d.m.Y H:i:s',$m['claimed']).', user '.$m['user'].', <span title="'.htmlspecialchars($m['tx']).'">tx</span></p>';
				}
				print '<p>Лимитировано 1000 записями</p>';
			}
			else{
				print '<a class="right" href="/admin/">&larr;</a>';
				print '<h3 class="captions">Обзор кодов</h3>';
				print '<p><a href="/admin/codes/download/">Загрузить неиспользованные</a></p>';
				print '<p><a href="/admin/codes/claimed/">Показать последние использованные</a></p>';
			}
		}
		else
		if('generate-codes'==$path_array[2]){
			if($_POST['count']){
				if($_POST['amount']){
					$amount=$_POST['amount'];
					$amount=str_replace(',','.',$amount);
					$amount=floatval($amount);
					for($i=1;$i<=(int)$_POST['count'];$i++){
						$code=md5('Random'.$time.mt_rand(1,10000).mt_rand(1,10000).mt_rand(1,10000).mt_rand(1,10000)).md5(mt_rand(1,10000).mt_rand(1,10000).'reg-code'.$time.mt_rand(1,10000));
						$code=substr($code,2,8).'-'.substr($code,12,8).'-'.substr($code,22,8).'-'.substr($code,32,8);
						if(0==$db->table_count('codes',"WHERE `code`='".$db->prepare($code)."'")){
							$db->sql("INSERT INTO `codes` (`code`,`amount`,`created`) VALUES ('".$db->prepare($code)."','".$db->prepare($amount)."','".$time."')");
						}
						else{
							$i--;
						}
					}
					header('location:/admin/generate-codes/?caption=ok');
					exit;
				}
			}
			print '<a class="right" href="/admin/">&larr;</a>';
			print '<h3 class="captions">Генерация кодов</h3>';
			if('ok'==$_GET['caption'])
			print '<p class="green">Коды созданы</p>';
			print '<p>Заполните форму для генерации кодов:</p>';
			print '<form action="?" method="POST">';
			print '<input type="text" name="count" value="100"> &mdash; количество кодов<br>';
			print '<input type="text" name="amount" value="10"> &mdash; стоимость кода в долларах<br>';
			print '<input type="submit" value="Сгенерировать">';
			print '</form>';
		}
		else
		if('viz-price'==$path_array[2]){
			if($_POST['amount']){
				$amount=$_POST['amount'];
				$amount=str_replace(',','.',$amount);
				$amount=floatval($amount);

				file_put_contents($site_root.'/viz_price.txt',$amount);
				header('location:/admin/viz-price/?caption=ok');
				exit;
			}
			print '<a class="right" href="/admin/">&larr;</a>';
			print '<h3 class="captions">Установить новую цену VIZ</h3>';
			if('ok'==$_GET['caption'])
			print '<p class="green">Цена изменена</p>';

			print '<p>Заполните форму для изменения:</p>';
			print '<form action="?" method="POST">';
			print '<input type="text" name="amount" value="'.$viz_price.'"> &mdash; стоимость кода в долларах<br>';
			print '<input type="submit" value="Сохранить">';
			print '</form>';
		}
		else{
			print '<h3 class="captions">Администрирование</h3>';
			print '<p>Привет, '.$user.', ваш IP: '.$ip.'</p>';
			print '<p><a href="/admin/viz-price/">Установить новую цену VIZ (текущая цена $'.$viz_price.')</a></p>';
			print '<p><a href="/admin/codes/">Обзор кодов</a></p>';
			print '<p><a href="/admin/generate-codes/">Генерация кодов</a></p>';
		}
		print '</div></div></div>';
		print '</div>';
	}
}
else
if('login'==$path_array[1]){
	$error=false;
	if(isset($_POST['login'])){
		if(isset($users_arr[$_POST['login']])){
			if($_POST['password']==$users_arr[$_POST['login']]){
				@setcookie('start_vizplus_login',$_POST['login'],time()+8*3600,'/');
				@setcookie('start_vizplus_password',md5($_POST['password']),time()+8*3600,'/');
				header('location:/admin/');
				exit;
			}
			else{
				$error='Пароль не подходит';
			}
		}
		else{
			$error='Пользователь не найден';
		}
	}
	print '<div class="cards-view">';
	print '<div class="cards-container"><div class="card">';
	print '<h3 class="captions">Вход</h3>';
	print '<form action="?" method="POST">';
	if($error)
	print '<p class="red">Ошибка: '.$error.'</p>';
	print '<p><input type="text" name="login"> &mdash; логин</p>';
	print '<p><input type="password" name="password"> &mdash; пароль</p>';
	print '<p><input type="submit" value="Выполнить вход"></p>';
	print '</form>';
	print '</div></div></div>';
	print '</div>';
}
else
if(''==$path_array[1]){
	if($maintenance){
		print '
		<div class="cards-view">
			<div class="cards-container">
				<div class="card" data-index="descr">
					<h3>'.$ltmp_arr['maintenance']['title'].'</h3>
					<p>'.$ltmp_arr['maintenance']['description'].'</p>
				</div>
			</div>
		</div>
		';
	}
	else{
	print '
		<div class="cards-view">
			<div class="cards-container">
				<div class="card" data-index="descr">
					<h3>'.$ltmp_arr['index']['intro_title'].'</h3>
					'.$ltmp_arr['index']['intro_text'].'
				</div>

				<div class="card" data-index="7">
					<p><center><strong>7</strong></center></p>
					'.$ltmp_arr['index']['section_7'].'
					<p><label class="check captions">'.$ltmp_arr['index']['section_7_check_caption'].'<input type="checkbox" data-target="6"><span class="mark"></span></label></p>
				</div>

				<div class="card" data-index="6">
					<div class="check-next">
						<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
						<div class="check-opened">
							<p><center><strong>6</strong></center></p>
							'.$ltmp_arr['index']['section_6'].'
							<p><label class="check captions">'.$ltmp_arr['index']['section_6_check_caption'].'<input type="checkbox" data-target="5"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>

				<div class="card hidden" data-index="5">
					<div class="check-next">
						<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
						<div class="check-opened">
							<p><center><strong>5</strong></center></p>
							'.$ltmp_arr['index']['section_5'].'
							<p><label class="check captions">'.$ltmp_arr['index']['section_5_check_caption'].'<input type="checkbox" data-target="4"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>

				<div class="card hidden" data-index="4">
					<div class="check-next">
						<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
						<div class="check-opened">
							<p><center><strong>4</strong></center></p>
							'.$ltmp_arr['index']['section_4'].'
							<p><label class="check captions">'.$ltmp_arr['index']['section_4_check_caption'].'<input type="checkbox" data-target="3"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>

				<div class="card hidden" data-index="3">
					<div class="check-next">
						<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
						<div class="check-opened">
							<p><center><strong>3</strong></center></p>
							'.$ltmp_arr['index']['section_3'].'
							<p><label class="check captions">'.$ltmp_arr['index']['section_3_check_caption'].'<input type="checkbox" data-target="ask"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>

				<div class="card hidden" data-index="ask">
					<div class="check-next">
						<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
						<div class="check-opened">
							<p><strong>'.$ltmp_arr['index']['section_ask_invite'].'</strong></p>
							<p><label class="radio captions">'.$ltmp_arr['index']['section_ask_invite_yes'].'<input type="radio" name="ask" value="yes"><span class="mark"></span></label></p>
							<p><label class="radio captions">'.$ltmp_arr['index']['section_ask_invite_no'].'<input type="radio" name="ask" value="no"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>

				<div class="radio-option radio-ask radio-ask-yes">
					<div class="card" data-index="alt-2">
						<div class="check-next">
							<div class="check-opened">
								<p><center><strong>2</strong></center></p>
								'.$ltmp_arr['index']['section_2_alt'].'
								<p><label class="check captions">'.$ltmp_arr['index']['section_2_alt_check_caption'].'<input type="checkbox" data-target="alt-1"><span class="mark"></span></label></p>
							</div>
						</div>
					</div>
					<div class="card hidden" data-index="alt-1">
						<div class="check-next">
							<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
							<div class="check-opened">
								<p><center><strong>1</strong></center></p>
								'.$ltmp_arr['index']['section_1_alt'].'
								<p><label class="check captions">'.$ltmp_arr['index']['section_1_alt_check_caption'].'<input type="checkbox" data-target="alt-reg"><span class="mark"></span></label></p>
							</div>
						</div>
					</div>
					<div class="card alt-reg-form hidden">
						'.$ltmp_arr['index']['alt_form_text'].'
						'.$ltmp_arr['index']['alt_form_login'].'
						<p><input type="text" class="single-text" value="" name="alt-create-account-login" data-available="alt-create-account-available" placeholder="'.$ltmp_arr['index']['alt_form_login_placeholder'].'" onPaste="return false;" onDrag="return false" onDrop="return false" autocomplete="off"></p>

						'.$ltmp_arr['index']['alt_form_invite'].'
						<p><input type="text" class="single-text" value="" name="alt-create-account-invite" placeholder="'.$ltmp_arr['index']['alt_form_invite_placeholder'].'" autocomplete="off"></p>

						<p class="red alt-create-account-available"></p>
						<p class="red alt-create-account-error"></p>

						<p><input type="button" class="submit-button" name="alt-create-account" value="'.$ltmp_arr['index']['alt_form_button'].'"><span class="submit-button-ring" rel="alt-create-account"></span></p>

						<div class="alt-account-keys hidden">
							'.$ltmp_arr['index']['keys_title'].'

							<p>'.$ltmp_arr['index']['keys_account'].'<span class="green account-login"></span></p>
							<p>'.$ltmp_arr['index']['keys_invite_balance'].'<span class="green invite-balance"></span></p>

							<p>'.$ltmp_arr['index']['keys_text'].'</p>

							<p><span class="master-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_master'].'</p>
							<p><span class="active-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_active'].'</p>
							<p><span class="regular-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_regular'].'</p>
							<p><span class="memo-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_memo'].'</p>

							<p>'.$ltmp_arr['index']['keys_attention'].'</p>
						</div>
					</div>
				</div>

				<div class="radio-option radio-ask radio-ask-no">
					<div class="card" data-index="2">
						<div class="check-next">
							<div class="check-opened">
								<p><center><strong>2</strong></center></p>
								'.$ltmp_arr['index']['section_2'].'
								<p><label class="check captions">'.$ltmp_arr['index']['section_2_check_caption'].'<input type="checkbox" data-target="1"><span class="mark"></span></label></p>
							</div>
						</div>
					</div>

					<div class="card hidden" data-index="1">
						<div class="check-next">
							<div class="check-waiting"><p>'.$ltmp_arr['index']['waiting_caption'].'</p></div>
							<div class="check-opened">
								<p><center><strong>1</strong></center></p>
								'.$ltmp_arr['index']['section_1'].'
								<p><div id="hcaptcha" class="h-captcha" data-sitekey="'.$hcaptcha_sitekey.'" data-callback="hcaptcha_callback" data-expired-callback="hcaptcha_expire_callback"></div></p>
							</div>
						</div>
					</div>
					<div class="card reg-form hidden">
						'.$ltmp_arr['index']['form_text'].'
						'.$ltmp_arr['index']['form_login'].'
						<p><input type="text" class="single-text" value="" name="create-account-login" data-available="create-account-available" placeholder="'.$ltmp_arr['index']['form_login_placeholder'].'" onPaste="return false;" onDrag="return false" onDrop="return false" autocomplete="off"></p>
						<p class="red create-account-available"></p>
						<p class="red create-account-error"></p>
						<p><input type="button" class="submit-button" name="create-account" value="'.$ltmp_arr['index']['form_button'].'"><span class="submit-button-ring" rel="create-account"></span></p>

						<div class="account-keys hidden">
							'.$ltmp_arr['index']['keys_title'].'

							<p>'.$ltmp_arr['index']['keys_account'].'<span class="green account-login"></span></p>

							<p>'.$ltmp_arr['index']['keys_text'].'</p>

							<p><span class="master-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_master'].'</p>
							<p><span class="active-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_active'].'</p>
							<p><span class="regular-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_regular'].'</p>
							<p><span class="memo-key captions">&hellip;</span>'.$ltmp_arr['index']['keys_memo'].'</p>

							<p>'.$ltmp_arr['index']['keys_attention'].'</p>
						</div>
					</div>
				</div>
			</div>
		</div>';
	}
}
$content=ob_get_contents();
ob_end_clean();