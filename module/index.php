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
						$code=md5('Random'.$time.mt_rand(1,10000).mt_rand(1,10000).mt_rand(1,10000).mt_rand(1,10000)).md5(mt_rand(1,10000).mt_rand(1,10000).'Generator'.$time.mt_rand(1,10000));
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
print '
	<div class="cards-view">
		<div class="cards-container">
			<div class="card" data-index="descr">
				<h3>Ваш первый аккаунт в ВИЗе</h3>
				<p><strong>На этой странице вы создадите свой первый аккаунт, добавите на него немного социального капитала (по желанию) и найдёте ссылки на некоторые приложения и сайты из экосистемы ВИЗ.</strong></p>
                                <p>Для создания аккаунта необходимо прочитать пояснения ниже и поставить галочки под некоторыми пунктами. Это займёт несколько минут, но избавит вас от ненужных проблем и вопросов.</p>
				<p>Аккаунты в ВИЗе отличаются от аккаунтов в социальных сетях, в электронной почте, на сайтах и т.п. Ваш аккаунт - это ваш личный счёт в блокчейне VIZ, на нём вы храните свой социальный капитал - главную ценность в экосистеме.</p>
				<p>С помощью аккаунта, социального капитала и токенов viz вы будете награждать людей и получать награды от них, оформлять подписки, голосовать, а также делать другие интересные вещи в интернете и в «реальной жизни».</p>
				<p>Пожалуйста, обратите внимание на особенности аккаунтов ВИЗа и подтвердите, что вы с ними ознакомились:</p>
			</div>

			<div class="card" data-index="7">
				<p><center><strong>7</strong></center></p>
				<p>Изначально ваш аккаунт полностью анонимен. Помогая вам его создать, мы не запрашиваем никаких персональных данных: ни имени, ни электронной почты, ни телефона. Вы просто создаёте аккаунт одной кнопкой и начинаете им пользоваться. В дальнейшем для удобства его можно привязать к нику, имени, аккаунту в социальной сети, телефону и т.п. при помощи различных приложений в экосистеме. А можно и не привязывать.</p>
				<p><label class="check captions">Отлично!<input type="checkbox" data-target="6"><span class="mark"></span></label></p>
			</div>

			<div class="card" data-index="6">
				<div class="check-next">
					<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
					<div class="check-opened">
						<p><center><strong>6</strong></center></p>
						<p>Несмотря на то, что при создании аккаунта вы получаете ключи от него (об этом ниже), мы их не знаем. Ключи генерируются прямо в вашем браузере и не скачиваются на наши серверы. До тех пор, пока вы сами не сообщите ваши ключи от аккаунта кому-нибудь, никто во всём мире, кроме вас, их не узнает. Это очень безопасно, но также это означает, что при потере ключей никто не сможет их вам «напомнить», «восстановить» или создать заново. Будьте внимательны и предусмотрительны! Храните копии ключей в нескольких безопасных местах!</p>
						<p><label class="check captions">Понятно!<input type="checkbox" data-target="5"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="card hidden" data-index="5">
				<div class="check-next">
					<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
					<div class="check-opened">
						<p><center><strong>5</strong></center></p>
						<p>Практически все действия вашего аккаунта в ВИЗе (награды, переводы, голосования) будут видны любопытным и технически грамотным людям. Блокчейн VIZ - открытого типа, это не ошибка, это так и задумано. Ведите себя прилично, даже если вы анонимны. Рано или поздно кто-то наверняка узнает, кому принадлежит ваш аккаунт, и посмотрит всю историю ваших действий. Или следите за анонимностью и постарайтесь не раскрыть свою личность.</p>
						<p><label class="check captions">Буду вести себя прилично! (Но это неточно).<input type="checkbox" data-target="4"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="card hidden" data-index="4">
				<div class="check-next">
					<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
					<div class="check-opened">
						<p><center><strong>4</strong></center></p>
						<p>Ваш капитал и ликвидные монеты viz хранятся в электронном виде в блокчейне. Никто кроме вас (или того, кто знает ваши ключи) не может с ними ничего сделать: не только куда-то перевести, но и «заморозить», «заблокировать», «потерять», «не отдать» и т.д., как иногда делают банки или правоохренители. В то же время, если плохой человек узнает ваш ключ от аккаунта и украдёт монеты, никто их вам не вернёт. Блокчейн - не банк, вы полностью контролируете свои активы и полностью отвечаете за них. Жаловаться некому.</p>
						<p><label class="check captions">Жёстко, но справедливо!<input type="checkbox" data-target="3"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="card hidden" data-index="3">
				<div class="check-next">
					<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
					<div class="check-opened">
						<p><center><strong>3</strong></center></p>
						<p>Очень коротко про ключи. Ключ с т.з. пользователя - это что-то вроде пароля, но с ограничениями по способам использования. При создании аккаунта вы получите три разных ключа (вообще, в ВИЗе их больше, но остальные нужны только очень продвинутым пользователям, а не новичкам). Ключи самостоятельно придумать нельзя, они генерируются специальным алгоритмом, выглядят как очень длинные строки из букв и цифр и начинаются на 5.</p>
						<p><strong>Обычный ключ</strong> (regular key) позволяет награждать других пользователей, записывать информацию в блокчейн, менять данные аккаунта и голосовать за заявки в Фонде развития ВИЗа. Это самый безобидный ключ, с его помощью невозможно нанести большой ущерб. Поэтому именно он обычно используется в приложениях.</p>
						<p><strong>Активный ключ</strong> (active key) делает всё то же, что регулярный, но кроме того, позволяет управлять вашими средствами - монетами viz в кошельке и социальным капиталом (а также голосовать за делегатов блокчейна VIZ). Злоумышленник, узнав ваш активный ключ, сможет быстро и безвозвратно украсть монеты из вашего кошелька и включить понижение социального капитала. Этот ключ можно вводить только в очень небольшое количество приложений, которым вы максимально доверяете.</p>
						<p><strong>Ключ владельца</strong> (master) - главный ключ, позволяет делать с вашим аккаунтом что угодно, а также менять другие ключи в случае их потери или компрометации (или просто так, для профилактики). Мастер-ключ лучше всего практически никогда не использовать и хранить в нескольких копиях в очень надёжных местах. Он нужен только в экстремальных случаях - когда требуется сменить другие ключи после их компрометации или при передаче аккаунта другому человеку.</p>
						<p><label class="check captions">За обычный не переживаю, за активным слежу, мастер храню в сейфе!<input type="checkbox" data-target="ask"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="card hidden" data-index="ask">
				<div class="check-next">
					<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
					<div class="check-opened">
						<p><strong>У вас есть приглашение (инвайт)?</strong></p>
						<p><label class="radio captions">Да<input type="radio" name="ask" value="yes"><span class="mark"></span></label></p>
						<p><label class="radio captions">Нет<input type="radio" name="ask" value="no"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="radio-option radio-ask radio-ask-yes">
				<div class="card" data-index="alt-2">
					<div class="check-next">
						<div class="check-opened">
							<p><center><strong>2</strong></center></p>
							<p>Сумма с вашего инвайт-чека перейдёт в капитал нового аккаунта. Это позволит вам сразу получить полноценный аккаунт. Чтобы увеличить капитал, можно купить токены viz или начать получать награды от других участников экосистемы.</p>
							<p><label class="check captions">Без капитала - никуда&hellip;<input type="checkbox" data-target="alt-1"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>
				<div class="card hidden" data-index="alt-1">
					<div class="check-next">
						<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
						<div class="check-opened">
							<p><center><strong>1</strong></center></p>
							<p>В дальнейшем вы сможете регистрировать любое количество новых аккаунтов и субаккаунтов ВИЗ за свой счёт без инвайтов и без помощи нашего регистратора. Для этого потребуется передать новому аккаунту 1 viz в капитал или делегировать 10 viz.</p>
							<p><label class="check captions">Пригодится!<input type="checkbox" data-target="alt-reg"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>
				<div class="card alt-reg-form hidden">
					<p>А теперь - к делу!</p>
					<p>Введите желаемое имя аккаунта (маленькие латинские буквы и цифры, больше 2 знаков, первый знак должен быть буквой):</p>
					<p><input type="text" class="single-text" value="" name="alt-create-account-login" data-available="alt-create-account-available" placeholder="— введите имя аккаунта" onPaste="return false;" onDrag="return false" onDrop="return false" autocomplete="off"></p>

					<p>Введите код инвайта:</p>
					<p><input type="text" class="single-text" value="" name="alt-create-account-invite" placeholder="— введите код" autocomplete="off"></p>

					<p class="red alt-create-account-available"></p>
					<p class="red alt-create-account-error"></p>

					<p><input type="button" class="submit-button" name="alt-create-account" value="Использовать код"><span class="submit-button-ring" rel="alt-create-account"></span></p>

					<div class="alt-account-keys hidden">
						<h3>Поздравляем!</h3>

						<p>Ваш аккаунт: <span class="green account-login"></span></p>
						<p>В капитал переведено: <span class="green invite-balance"></span></p>

						<p>Ваши ключи:</p>

						<p><span class="master-key captions">&hellip;</span> &mdash; master или главный ключ</p>
						<p><span class="active-key captions">&hellip;</span> &mdash; active или активный ключ</p>
						<p><span class="regular-key captions">&hellip;</span> &mdash; regular или обычный ключ</p>
						<p>Сохраните эти ключи в нескольких разных местах прямо сейчас!</p>
						<p><label class="check captions">Подтверждаю сохранение ключей<input type="checkbox" data-target="reg-complete" data-hidden-target="1"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="radio-option radio-ask radio-ask-no">
				<div class="card" data-index="2">
					<div class="check-next">
						<div class="check-opened">
							<p><center><strong>2</strong></center></p>
							<p>Ваш первый аккаунт будет абсолютно пустым, то есть без социального капитала. Это значит, что он не сможет выполнять никакие действия, кроме получения наград от других аккаунтов. Чтобы стать полноценным участником экосистемы ВИЗ, надо накопить хотя бы небольшой социальный капитал или купить его (это быстрее). Про оба способа мы расскажем после создания вашего аккаунта.</p>
							<p><label class="check captions">Без капитала - никуда&hellip;<input type="checkbox" data-target="1"><span class="mark"></span></label></p>
						</div>
					</div>
				</div>

				<div class="card hidden" data-index="1">
					<div class="check-next">
						<div class="check-waiting"><p>Ждем когда вы поставите галочку&hellip;</p></div>
						<div class="check-opened">
							<p><center><strong>1</strong></center></p>
							<p>Создание аккаунта в ВИЗе - дело платное. Но сайт готов заплатить за ваш первый аккаунт («первая доза - бесплатно»), так как мы хотим, чтобы экосистема ВИЗ привлекла много людей. Поэтому вам придётся подтвердить, что вы - человек, а не бот для массовой регистрации. В дальнейшем вы сможете создать любое количество аккаунтов за свой счёт.</p>

							<p><div class="g-recaptcha" data-callback="recaptcha_callback" data-sitekey="6LfHcb8UAAAAAKy_yT6B4XPmjUGJX1jlCPl3JVH9"></div></p>
						</div>
					</div>
				</div>
				<div class="card reg-form hidden">
					<p>А теперь - к делу!</p>
					<p>У вас есть 100 секунд, чтобы ввести имя аккаунта. Если не успеете, поставьте галочку в капче ещё раз.</p>
					<p>Введите желаемое имя аккаунта (маленькие латинские буквы и цифры, больше 2 знаков, первый знак должен быть буквой):</p>
					<p><input type="text" class="single-text" value="" name="create-account-login" data-available="create-account-available" placeholder="— введите имя аккаунта" onPaste="return false;" onDrag="return false" onDrop="return false" autocomplete="off"></p>
					<p class="red create-account-available"></p>
					<p class="red create-account-error"></p>
					<p><input type="button" class="submit-button" name="create-account" value="Создать"><span class="submit-button-ring" rel="create-account"></span></p>

					<div class="account-keys hidden">
						<h3 class="captions">Поздравляем!</h3>

						<p>Ваш аккаунт: <span class="green account-login"></span></p>

						<p>Ваши ключи:</p>

						<p><span class="master-key captions">&hellip;</span> &mdash; master или главный ключ</p>
						<p><span class="active-key captions">&hellip;</span> &mdash; active или активный ключ</p>
						<p><span class="regular-key captions">&hellip;</span> &mdash; regular или обычный ключ</p>

						<p>Сохраните эти ключи в нескольких разных местах прямо сейчас!</p>
						<p><label class="check captions">Подтверждаю сохранение ключей<input type="checkbox" data-target="reg-complete" data-hidden-target="1"><span class="mark"></span></label></p>
					</div>
				</div>
			</div>

			<div class="card hidden" data-index="reg-complete">
				<h3 class="captions">Ваш первый социальный капитал</h3>
				<p>Накопление и использование <strong>социального капитала</strong> - это то, ради чего мы все здесь собрались. Социальный капитал - это сохранённая на специальном «счёте» вашего аккаунта сумма полученных от других участников ВИЗа наград или монет viz.</p>
				<p>Социальный капитал можно получить в виде наград от других участников или купить монеты viz у них за деньги и перевести в капитал. Покупка - это совершенно нормально, так как, покупая viz’ы, вы показываете другим участникам реальную стоимость их накоплений и помогаете им получить дополнительную выгоду от той пользы, что они приносят другим людям.</p>
				<p>Если у вас есть социальный капитал, вы можете награждать других людей за пользу или удовольствие, которые они вам приносят. <strong>Обратите внимание: награждая других, вы не уменьшаете ваш социальный капитал!</strong> Вы лишь указываете блокчейну, на какую величину надо увеличить социальный капитал другого человека. Размер награды напрямую зависит от размера вашего социального капитала и некоторых ограничений по его использованию (т.н. энергии).</p>
				<p>Купите стартовый социальный капитал:</p>
				<ol>
					<li>Оплатите покупку специального кода картой, Яндекс.Деньгами или криптовалютой;</li>
					<li>Введите код и имя вашего аккаунта в форму ниже;</li>
					<li>Примерно через минуту вы получите ваш первый социальный капитал в экосистеме ВИЗ на выбранную сумму.</li>
				</ol>

				<div class="addon">
					<h3 class="captions">ПРЕДУПРЕЖДЕНИЕ</h3>
					<p>Курс, по которому мы продаём токены VIZ, скорее всего, будет заметно хуже биржевого из-за сложностей в продаже токенов за обычные деньги, комиссий посредников (и за это тоже мы не любим банки) и других причин. Поэтому мы рекомендуем выбрать сумму, не слишком эффективное использование которой вас не смутит. Покупки на более крупные суммы лучше проводить на <a href="https://wallet.bitshares.org/#/market/XCHNG.VIZ_BTS" target="_blank">децентрализованной бирже.</a></p>
				</div>

				<p><label class="radio captions">Куплю немного сейчас<input type="radio" checked="checked" name="buy" value="now"><span class="mark"></span></label></p>
				<p><label class="radio captions">Куплю позже на бирже<input type="radio" name="buy" value="later"><span class="mark"></span></label></p>

				<div class="radio-option radio-option-default radio-buy radio-buy-now">
					<p>Ссылки на покупку кода: https://...
					</p>
					<div class="addon">
						<h3 class="captions">Активация кода</h3>
						<p><input type="text" class="single-text" value="" name="claim-login" placeholder="— введите имя аккаунта" autocomplete="off"></p>
						<p><input type="text" class="single-text" value="" name="claim-code" placeholder="— введите код" autocomplete="off"></p>
						<p class="red claim-action-error"></p>
						<p class="green claim-action-success"></p>

						<p><input type="button" class="submit-button" name="claim-action" value="Получить"><span class="submit-button-ring" rel="claim-action"></span></p>
					</div>
				</div>
				<div class="radio-option radio-buy radio-buy-later">
				</div>
			</div>

			<div class="card hidden" data-index="reg-complete">
				<h3 class="captions">Ваши первые приложения в ВИЗе</h3>
				<div class="columns-view">
					<div class="column column-2 shadow with-buttons">
						<h4 class="captions">Телеграм-бот для награждения</h4>
						<p>Если бот присутствует в Телеграм-группе, то после подключения к нему своего аккаунта вам достаточно начать ответ на чьё-либо сообщение со знака «+», чтобы наградить этого человека. Точно так же и вы будете получать награды. Бот имеет массу настроек, поэтому сначала обязательно прочитайте /help.</p>
						<div class="buttons captions"><a class="inline-button" href="https://t.me/viz_social_bot" target="_blank">Адрес</a><a class="inline-button" href="https://vk.com/@vizworld-kak-polzovatsya-telegram-botom" target="_blank">Описание</a></div>
					</div>
					<div class="column column-2 shadow with-buttons">
						<h4 class="captions">Телеграм-бот &mdash; «шпион»</h4>
						<p>С помощью этого бота можно следить за действиями других участников ВИЗа и узнавать информацию об аккаунтах. Или контролировать собственную активность. В боте не очень простая система фильтров, поэтому потренируйтесь в её настройке.</p>
						<div class="buttons captions"><a class="inline-button" href="https://t.me/viznotifybot" target="_blank">Адрес</a><a class="inline-button" href="https://golos.in/ru--bot/@jackvote/khochu-predstavit-novogo-bota-dlya-blokcheina-viz" target="_blank">Описание</a></div>
					</div>
				</div>

				<div class="columns-view">
					<div class="column column-2 shadow with-buttons">
						<h4 class="captions">Телеграм-бот &mdash; кошелёк VIZ</h4>
						<p>Простой кошелёк: можно переводить токены с аккаунта на аккаунт, увеличивать и уменьшать свой социальный капитал, а также делегировать его. По заявлению автора, бот не сохраняет команды ключи пользователей.</p>
						<div class="buttons captions"><a class="inline-button" href="https://t.me/vizmoney_bot" target="_blank">Адрес</a></div>
					</div>
					<div class="column column-2 shadow with-buttons">
						<h4 class="captions">Игра «Дикий Запад»</h4>
						<p>Онлайн-игра, в которой стандартные операции блокчейна VIZ поданы в виде активности в стиле Дикого Запада: поиск золотых самородков, игра в казино и т.п. Игра продолжает развиваться, в ней периодически появляются новые развлечения.</p>
						<div class="buttons captions"><a class="inline-button" href="https://wildviz.top/" target="_blank">Адрес</a><a class="inline-button" href="https://golos.in/viz/@xchng/wildviz-dikii-zapad-na-blokcheine" target="_blank">Описание</a></div>
					</div>
				</div>

				<div class="columns-view">
					<div class="column column-2 shadow with-buttons">
						<h4 class="captions">Шлюз на криптобиржу BitShares</h4>
						<p>Монеты viz торгуются на децентрализованной криптобирже BitShares. Торговля viz не требует сложной регистрации, она анонимна и удобна. Но чтобы завести монеты на биржу и вывести их оттуда, нужен посредник - т.н. «шлюз». В экосистеме ВИЗ пока есть один такой посредник - сервис XCHNG. Будьте внимательны и тщательно изучите инструкцию перед его использованием!</p>
						<div class="buttons captions"><a class="inline-button" href="https://golos.in/bitshares/@xchng/pravila-raboty-avtomaticheskogo-shlyuza-xchng-viz" target="_blank">Описание</a></div>
					</div>
				</div>

			</div>
		</div>
	</div>';
}
$content=ob_get_contents();
ob_end_clean();