<?php /* VIVA NOTE: NIC verification upload; approval is performed by an administrator, not by this form. */ $title='Identity verification';require dirname(__DIR__,3).'/shared/views/layouts/auth_start.php'; ?>
<span class="eyebrow">IDENTITY VERIFICATION</span><h1>Verify your identity.</h1><p class="subtle">An administrator will review your documents. Property management becomes available after approval.</p>
<form method="post" action="<?=e(url('/registration/identity'))?>" enctype="multipart/form-data" class="stack">
<?=csrf_field()?>
<label>Sri Lankan NIC<input name="nic" value="<?=e($landlord['nic_number'])?>" placeholder="XXXXXXXXXV or 12 digits" required maxlength="12"></label>
<label>Mobile<input name="phone" value="<?=e($landlord['mobile'])?>" required maxlength="15"></label>
<label>NIC photo or PDF (max 5MB)<input name="document" type="file" accept="image/png,image/jpeg,application/pdf" required></label>
<button class="btn wide">Submit identity →</button>
</form><p class="form-footer"><a href="<?=e(url('/registration/status'))?>">Registration status</a></p>
<?php require dirname(__DIR__,3).'/shared/views/layouts/auth_end.php'; ?>
