<?php /* VIVA NOTE: Creates a pending landlord account; password is hashed on the server. */ $title='Become a landlord';require dirname(__DIR__,3).'/shared/views/layouts/auth_start.php'; ?>
<span class="eyebrow">CREATE YOUR ACCOUNT</span><h1>Start hosting on BoardNest.</h1><p class="subtle">Create an account and verify your identity. Once approved, you can list your property.</p>
<form method="post" action="<?=e(url('/register'))?>" class="stack">
<?=csrf_field()?>
<label>Full name<input name="name" maxlength="100" minlength="3" required autocomplete="name"></label>
<label>Email address<input name="email" type="email" maxlength="100" required autocomplete="email"></label>
<label>Mobile number<input name="phone" maxlength="15" required autocomplete="tel"></label>
<label>Password (8–72 bytes)<input name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password"></label>
<label class="check-row"><input type="checkbox" name="consent" value="1" required><span>I agree to the landlord registration requirements.</span></label>
<button class="btn wide">Create account →</button>
</form><p class="form-footer">Already registered? <a href="<?=e(url('/login'))?>">Sign in</a></p>
<?php require dirname(__DIR__,3).'/shared/views/layouts/auth_end.php'; ?>
