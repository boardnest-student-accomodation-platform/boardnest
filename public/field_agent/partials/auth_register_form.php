<?php
// Partial: auth_register_form.php
// Field agent registration card
// Expects: $error (string)
?>
<div class="auth-container-wide">
    <div class="auth-card">
        <div class="auth-logo-wrapper">
            <div class="auth-title">BoardNest</div>
            <div class="auth-subtitle">Apply as a Field Agent</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="auth-form-grid">
                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Full Name</label>
                    <input type="text" name="full_name" class="auth-form-input" placeholder="Enter your full name" required
                           value="<?php echo htmlspecialchars(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>">
                </div>
                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Email Address</label>
                    <input type="email" name="email" class="auth-form-input" placeholder="Enter email address" required
                           value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>">
                </div>
                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Password</label>
                    <div class="fa-password-wrapper">
                        <input type="password" id="regPassword" name="password" class="auth-form-input fa-input-password" placeholder="Create a strong password (min 8 chars)" required>
                        <button type="button" onclick="togglePasswordVisibility('regPassword','regEyeIcon')" class="fa-btn-toggle-pwd">
                            <svg id="regEyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>
                <div class="auth-form-group">
                    <label class="auth-form-label">NIC Number</label>
                    <input type="text" name="nic_number" class="auth-form-input" placeholder="e.g. 199812345678" required
                           value="<?php echo htmlspecialchars(isset($_POST['nic_number']) ? $_POST['nic_number'] : ''); ?>">
                </div>
                <div class="auth-form-group">
                    <label class="auth-form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="auth-form-input" placeholder="e.g. 0771234567" required
                           value="<?php echo htmlspecialchars(isset($_POST['mobile']) ? $_POST['mobile'] : ''); ?>">
                </div>
                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Preferred City</label>
                    <select name="city" class="fa-select-input" required>
                        <option value="">Select your operating city</option>
                        <?php
                        $cities = array('Colombo', 'Kandy', 'Moratuwa', 'Jaffna');
                        foreach ($cities as $c):
                            $sel = (isset($_POST['city']) && $_POST['city'] === $c) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $c; ?>" <?php echo $sel; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-auth-submit">Submit Application</button>
        </form>

        <div class="fa-auth-footer">
            Already registered? <a href="login.php" class="fa-auth-link">Sign In</a>
        </div>
    </div>
</div>
