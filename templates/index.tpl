<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title>{title}</title>
	<meta name="description" content="{description}">
	<meta property="og:description" content="{description}">
	<meta name="twitter:description" content="{description}">
	<meta name="viewport" content="width=device-width">

{head_addon}

<!--
	<link href="https://fonts.googleapis.com/css?family=IBM+Plex+Serif&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Scada&display=swap" rel="stylesheet">
-->

	<link rel="stylesheet" href="/app.css?{css_change_time}">

	<!--
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	-->
	<script src="https://hcaptcha.com/1/api.js" async defer></script>
	<script type="text/javascript" src="/viz.min.js"></script>
	<script type="text/javascript" src="/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="/app.js?{script_change_time}"></script>
</head>
<body>
<div class="header shadow unselectable">
	<div class="horizontal-view">
		<div class="logo"><a href="/" class="prefix selected">start.</a><a href="https://viz.plus/"><img src="/logo.svg" alt="VIZ+"></a></div>
		<div class="right captions">
			{select-lang}
			<!--
				<a class="menu-el" href="https://start.viz.plus/"><strong>Регистрация в VIZ</strong></a>
				<a class="menu-el" href="https://user.viz.plus/">Управление аккаунтом</a>
				<a class="menu-el" href="https://dev.viz.plus/">Разработчикам</a>
			-->
		</div>
	</div>
</div>
<div class="horizontal-view vertical-view">
	{content}
</div>
</body>
</html>