<?php /* VIVA NOTE: Approved profile screen; profile changes go through a validated POST action. */ ?><div class="toolbar"><p>Manage your profile and contact details. Approved identity information is locked to Admin review.</p></div><div class="detail-grid"><section class="panel"><h3>Personal information</h3><form class="stack" method="post" action="<?=e(url('/profile/save'))?>"><?=csrf_field()?>
<label>Full name<input name="name" required minlength="3" maxlength="100" value="<?=e($landlord['full_name'])?>"></label><label>Email (cannot change here)<input value="<?=e($landlord['email'])?>" disabled></label><label>Mobile<input name="phone" required maxlength="15" value="<?=e($landlord['mobile'])?>"></label><label>Address<textarea name="address" maxlength="1000" rows="3"><?=e($landlord['address']??'')?></textarea></label><button class="btn">Save changes</button></form></section><section class="panel"><h3>Verification status</h3><dl class="details"><dt>Identity</dt><dd><span class="status"><?=e(pill($landlord['identity_status']))?></span></dd><dt>Account</dt><dd><?=e(pill($landlord['user_status']))?></dd><dt>Plan</dt><dd><?=e(pill($landlord['subsc_tier']))?></dd><dt>Landlord ID</dt><dd>#<?=e($landlord['landlord_id'])?></dd></dl></section></div>

<div class="detail-grid profile-security-grid">
<section class="panel">
  <h3>Profile photo</h3>
  <p class="muted">JPEG, PNG or WebP; up to 5 MB. Your picture appears in the sidebar and header.</p>
  <div class="profile-photo-display">
    <?php if (!empty($landlord['profile_photo'])): ?>
      <img class="profile-avatar-large" src="<?=e(url('/'.ltrim((string)$landlord['profile_photo'],'/')))?>" alt="Your profile photo" width="96" height="96">
    <?php else: ?>
      <span class="avatar profile-avatar-large" aria-label="No profile photo uploaded"><?=e(mb_strtoupper(mb_substr((string)$landlord['full_name'],0,1)))?></span>
    <?php endif; ?>
  </div>
  <form class="stack" action="<?=e(url('/profile/photo'))?>" method="post" enctype="multipart/form-data">
    <?=csrf_field()?>
    <label>Choose photo <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
    <button class="btn" type="submit">Upload / replace photo</button>
  </form>
  <?php if (!empty($landlord['profile_photo'])): ?>
    <form action="<?=e(url('/profile/photo/remove'))?>" method="post" class="profile-remove-form">
      <?=csrf_field()?>
      <button type="submit" class="btn btn-secondary">Remove photo</button>
    </form>
  <?php endif; ?>
</section>
<section class="panel">
  <h3>Security — change password</h3>
  <p class="muted">Enter your current password, then a different password of 12–72 bytes.</p>
  <form class="stack" action="<?=e(url('/profile/password'))?>" method="post" autocomplete="on">
    <?=csrf_field()?>
    <label>Current password <input type="password" name="current_password" autocomplete="current-password" required></label>
    <label>New password <input type="password" name="new_password" autocomplete="new-password" minlength="12" maxlength="72" required></label>
    <label>Confirm new password <input type="password" name="confirm_password" autocomplete="new-password" minlength="12" maxlength="72" required></label>
    <button class="btn" type="submit">Change password</button>
  </form>
</section>
</div>
