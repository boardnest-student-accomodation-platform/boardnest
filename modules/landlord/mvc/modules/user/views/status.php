<?php /* VIVA NOTE: Pending/approved identity status and the next registration action. */ $title = 'Account verification'; require dirname(__DIR__,3).'/shared/views/layouts/auth_start.php'; ?>
<span class="eyebrow">ACCOUNT VERIFICATION</span>
<h1><?= $landlord['identity_status'] === 'submitted' ? 'Your documents are under review' : ($landlord['identity_status'] === 'rejected' ? 'Verification needs attention' : 'Complete identity verification') ?></h1>
<p class="subtle">
<?php if ($landlord['identity_status'] === 'submitted'): ?>
    Your identity documents have been received. You will be able to add properties and rooms after an administrator approves your account.
<?php elseif ($landlord['identity_status'] === 'rejected'): ?>
    Your identity could not be approved. Review your information and submit your documents again.
<?php else: ?>
    Verify your identity to unlock property and room management. Your account must be approved first.
<?php endif; ?>
</p>
<section class="approval-summary" aria-label="Verification progress">
    <div class="approval-step complete"><span class="step-marker">1</span><div><strong>Account created</strong><small>Registration complete</small></div></div>
    <div class="approval-step <?= $landlord['identity_status'] === 'submitted' || $landlord['identity_status'] === 'approved' ? 'complete' : '' ?>"><span class="step-marker">2</span><div><strong>Identity documents</strong><small><?=e(pill($landlord['identity_status']))?></small></div></div>
    <div class="approval-step"><span class="step-marker">3</span><div><strong>Administrator approval</strong><small>Required before adding a property</small></div></div>
</section>
<?php if (in_array($landlord['identity_status'], ['not_submitted','rejected'], true)): ?>
<a class="btn wide" href="<?=e(url('/registration/identity'))?>"><?= $landlord['identity_status'] === 'rejected' ? 'Resubmit documents' : 'Verify identity' ?> <span>→</span></a>
<?php endif; ?>
<form action="<?=e(url('/logout'))?>" method="post" class="form-footer"><?=csrf_field()?><button class="link-button" type="submit">Sign out</button></form>
<?php require dirname(__DIR__,3).'/shared/views/layouts/auth_end.php'; ?>
