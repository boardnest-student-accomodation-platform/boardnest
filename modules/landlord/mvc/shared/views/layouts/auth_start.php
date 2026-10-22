<?php /* VIVA NOTE: Common opening HTML for login, registration and identity verification pages, which do not use the authenticated dashboard sidebar. */ ?><!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title??'Landlord')?> · BoardNest</title>
<link rel="stylesheet" href="<?=e(url('/assets/css/app.css', ['v' => '20261009-2']))?>"></head>
<body class="auth-body"><main class="auth-shell"><div class="auth-brand"><a class="logo" href="<?=e(url('/login'))?>"><span class="logo-icon">⌂</span> Board<span>Nest</span></a><p>Thoughtful spaces.<br>Better student living.</p><div class="auth-art"><span>⌂</span></div><small>Property management, made simple</small></div><section class="auth-card">
<?php if(isset($flash)&&$flash): ?><div role="alert" class="alert <?=e($flash[0])?>"><?=e($flash[1])?></div><?php endif; ?>
