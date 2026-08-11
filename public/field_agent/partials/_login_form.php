<?php
// Partial: _login_form.php
// Field agent login card
// Expects: $error (string), $success_msg (string)
?>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo-wrapper">
            <div class="auth-title">BoardNest</div>
            <div class="auth-subtitle">Field Agent Portal</div>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="auth-success">✅ <?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="auth-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="auth-form-group">
                <label class="auth-form-label">Email Address</label>
                <input type="email" name="email" class="auth-form-input" placeholder="Enter email address" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="auth-form-group">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <label class="auth-form-label" style="margin-bottom:0;">Password</label>
                    <a href="forgot_password.php" style="font-size:12px;font-weight:700;color:#A4856D;text-decoration:none;">Forgot Password?</a>
                </div>
                <div style="position:relative;">
                    <input type="password" id="loginPassword" name="password" class="auth-form-input" placeholder="Enter password" required style="padding-right:44px;">
                    <button type="button" onclick="togglePasswordVisibility('loginPassword','loginEyeIcon')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#8C7B74;padding:4px;display:flex;align-items:center;">
                        <svg id="loginEyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth-submit">Sign In</button>
        </form>
    </div>
</div>
