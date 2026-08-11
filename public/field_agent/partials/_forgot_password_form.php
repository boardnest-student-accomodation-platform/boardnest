<?php
// Partial: _forgot_password_form.php
// Password reset card (Email + NIC verification)
// Expects: $error (string)
?>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo-wrapper">
            <div class="auth-title">BoardNest</div>
            <div class="auth-subtitle">Reset Password</div>
        </div>

        <p style="font-size:13px;color:#8C7B74;text-align:center;margin:-8px 0 20px 0;line-height:1.5;">
            Enter your registered email and NIC number to verify your identity and reset your password.
        </p>

        <?php if (!empty($error)): ?>
            <div class="auth-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="auth-form-group">
                <label class="auth-form-label">Registered Email Address</label>
                <input type="email" name="email" class="auth-form-input" placeholder="Enter email address" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="auth-form-group">
                <label class="auth-form-label">NIC Number (Identity Verification)</label>
                <input type="text" name="nic_number" class="auth-form-input" placeholder="Enter your 12-digit NIC" required
                       value="<?php echo isset($_POST['nic_number']) ? htmlspecialchars($_POST['nic_number']) : ''; ?>">
            </div>

            <div class="auth-form-group">
                <label class="auth-form-label">New Password</label>
                <div style="position:relative;">
                    <input type="password" id="resetPassword" name="new_password" class="auth-form-input" placeholder="Enter new strong password" required style="padding-right:44px;">
                    <button type="button" onclick="togglePasswordVisibility('resetPassword','resetEyeIcon')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#8C7B74;padding:4px;display:flex;align-items:center;">
                        <svg id="resetEyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <div class="auth-form-group">
                <label class="auth-form-label">Confirm New Password</label>
                <div style="position:relative;">
                    <input type="password" id="confirmPassword" name="confirm_password" class="auth-form-input" placeholder="Re-enter new password to confirm" required style="padding-right:44px;">
                    <button type="button" onclick="togglePasswordVisibility('confirmPassword','confirmEyeIcon')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#8C7B74;padding:4px;display:flex;align-items:center;">
                        <svg id="confirmEyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth-submit">Reset My Password</button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:13px;color:#8C7B74;">
            <a href="login.php" style="color:#A4856D;font-weight:700;text-decoration:none;">← Back to Sign In</a>
        </div>
    </div>
</div>
