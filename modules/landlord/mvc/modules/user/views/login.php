<?php /* VIVA NOTE: Landlord sign-in form, using the shared auth layout and CSRF token. */ $title='Landlord sign in';require dirname(__DIR__,3).'/shared/views/layouts/auth_start.php'; ?>
<span class="eyebrow">WELCOME BACK</span><h1>Sign in to your space.</h1><p class="subtle">Manage your rooms, bookings and residents in one place.</p>
<form method="post" action="<?=e(url('/login'))?>" class="stack">
<?=csrf_field()?>
<label>Email address<input name="email" type="email" required autocomplete="username" maxlength="100" placeholder="you@example.com"></label>
<label>Password<input name="password" type="password" required autocomplete="current-password" placeholder="Your password"></label>
<button class="btn wide">Sign in <span>→</span></button>
</form><p class="form-footer">New landlord? <a href="<?=e(url('/register'))?>">Create an account</a></p>
<?php require dirname(__DIR__,3).'/shared/views/layouts/auth_end.php'; ?>
