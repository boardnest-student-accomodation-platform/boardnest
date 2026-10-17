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

        <form method="POST" action="register.php" enctype="multipart/form-data">
            <div class="auth-form-grid">
                <div class="auth-form-group full-width" style="background:#FFF8F5;border:1.5px solid #E8DDD4;border-radius:12px;padding:12px 14px;box-sizing:border-box;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-weight:700;color:#3B3330;font-size:13px;line-height:1.4;">
                        <input type="checkbox" name="is_senior_student_applicant" id="chk_senior_student" value="1" style="accent-color:#6F4E37;margin-top:3px;transform:scale(1.2);" <?php echo !empty($_POST['is_senior_student_applicant']) ? 'checked' : ''; ?>>
                        <div>
                            <div style="font-weight:800;color:#6F4E37;font-size:13.5px;">Senior Student Applicant</div>
                            <div style="font-weight:500;color:#8C7B74;font-size:12px;margin-top:2px;">
                                I am an enrolled student with 2+ years of registered studentship. (Eligibility is automatically validated against your registered date).
                            </div>
                        </div>
                    </label>
                </div>

                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Full Name <span style="color:#E74C3C;">*</span></label>
                    <input type="text" name="full_name" class="auth-form-input" placeholder="Enter your full name" required
                           value="<?php echo htmlspecialchars(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>">
                </div>
                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Email Address <span style="color:#E74C3C;">*</span></label>
                    <input type="email" name="email" class="auth-form-input" placeholder="Enter email address" required
                           value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>">
                </div>
                <div class="auth-form-group full-width">
                    <label class="auth-form-label">Password <span style="color:#E74C3C;">*</span></label>
                    <div class="fa-password-wrapper">
                        <input type="password" id="regPassword" name="password" class="auth-form-input fa-input-password" placeholder="Create a strong password (min 8 chars)" required>
                        <button type="button" onclick="togglePasswordVisibility('regPassword','regEyeIcon')" class="fa-btn-toggle-pwd">
                            <svg id="regEyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>
                <div class="auth-form-group">
                    <label class="auth-form-label">NIC Number <span style="color:#E74C3C;">*</span></label>
                    <input type="text" name="nic_number" class="auth-form-input" placeholder="e.g. 199812345678" required
                           value="<?php echo htmlspecialchars(isset($_POST['nic_number']) ? $_POST['nic_number'] : ''); ?>">
                </div>
                <div class="auth-form-group">
                    <label class="auth-form-label">Mobile Number <span style="color:#E74C3C;">*</span></label>
                    <input type="text" name="mobile" class="auth-form-input" placeholder="e.g. 0771234567" required
                           value="<?php echo htmlspecialchars(isset($_POST['mobile']) ? $_POST['mobile'] : ''); ?>">
                </div>

                <div class="auth-form-group">
                    <label class="auth-form-label">NIC Card — Front Image <span style="color:#E74C3C;">*</span></label>
                    <div class="fa-nic-upload-box" onclick="document.getElementById('nic_front_input').click()">
                        <input type="file" name="nic_front" id="nic_front_input" style="display:none;" accept="image/jpeg,image/png,image/webp" required onchange="previewNIC(this, 'nic_front_preview')">
                        <div id="nic_front_preview" class="fa-nic-preview-text">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6F4E37" stroke-width="2" style="display:block;margin:0 auto 4px auto;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <span>Upload NIC Front (JPG, PNG, WebP)</span>
                        </div>
                    </div>
                </div>

                <div class="auth-form-group">
                    <label class="auth-form-label">NIC Card — Back Image <span style="color:#E74C3C;">*</span></label>
                    <div class="fa-nic-upload-box" onclick="document.getElementById('nic_back_input').click()">
                        <input type="file" name="nic_back" id="nic_back_input" style="display:none;" accept="image/jpeg,image/png,image/webp" required onchange="previewNIC(this, 'nic_back_preview')">
                        <div id="nic_back_preview" class="fa-nic-preview-text">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6F4E37" stroke-width="2" style="display:block;margin:0 auto 4px auto;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <span>Upload NIC Back (JPG, PNG, WebP)</span>
                        </div>
                    </div>
                </div>

                <?php
                $districts = array('Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Galle', 'Matara', 'Kurunegala', 'Jaffna');
                $selected_district = isset($_POST['district']) ? $_POST['district'] : '';
                $selected_city     = isset($_POST['city'])     ? $_POST['city']     : '';
                ?>
                <div class="auth-form-group">
                    <label class="auth-form-label">Operating District <span style="color:#E74C3C;">*</span></label>
                    <select name="district" id="regDistrict" class="fa-select-input" required onchange="onDistrictChange(this.value, '')">
                        <option value="">Select District</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo ($selected_district === $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="auth-form-group">
                    <label class="auth-form-label">Operating City <span style="color:#E74C3C;">*</span></label>
                    <select name="city" id="regCity" class="fa-select-input" required <?php echo empty($selected_district) ? 'disabled' : ''; ?>>
                        <option value=""><?php echo empty($selected_district) ? '-- Select district first --' : 'Select city in ' . htmlspecialchars($selected_district); ?></option>
                        <?php
                        if (!empty($selected_district)) {
                            $known_cities = array(
                                'Colombo' => array('Colombo (Central)', 'Battaramulla', 'Dehiwala', 'Homagama', 'Kaduwela', 'Kesbewa', 'Kolonnawa', 'Maharagama', 'Malabe', 'Moratuwa', 'Mount Lavinia', 'Nugegoda', 'Piliyandala', 'Rajagiriya', 'Sri Jayawardenepura Kotte'),
                                'Gampaha' => array('Gampaha', 'Kelaniya', 'Kiribathgoda', 'Kadawatha', 'Negombo', 'Ragama', 'Wattala', 'Ja-Ela', 'Minuwangoda'),
                                'Kalutara' => array('Kalutara', 'Panadura', 'Horana', 'Beruwala', 'Wadduwa', 'Bandaragama'),
                                'Kandy' => array('Kandy', 'Peradeniya', 'Katugastota', 'Kundasale', 'Gampola'),
                                'Galle' => array('Galle', 'Karapitiya', 'Hikkaduwa', 'Ambalangoda'),
                                'Matara' => array('Matara', 'Weligama', 'Mirissa'),
                                'Kurunegala' => array('Kurunegala', 'Kuliyapitiya', 'Narammala'),
                                'Jaffna' => array('Jaffna', 'Chavakachcheri', 'Point Pedro')
                            );
                            if (isset($known_cities[$selected_district])) {
                                foreach ($known_cities[$selected_district] as $kc) {
                                    $sel = ($selected_city === $kc) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($kc) . '" ' . $sel . '>' . htmlspecialchars($kc) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="auth-form-group full-width" style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:10px;padding:12px;margin-top:4px;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:12.5px;color:#3B3330;line-height:1.45;font-weight:600;">
                        <input type="checkbox" name="agree_terms" id="chk_agree_terms" value="1" required style="accent-color:#6F4E37;margin-top:2px;transform:scale(1.2);" <?php echo !empty($_POST['agree_terms']) ? 'checked' : ''; ?>>
                        <span>
                            I confirm that all submitted details and uploaded NIC documents are genuine. I agree to adhere to BoardNest's Field Agent Code of Conduct, audit integrity rules, and verification terms. <span style="color:#E74C3C;">*</span>
                        </span>
                    </label>
                </div>
            </div>
            <div style="font-size:11.5px;color:#8C7B74;margin-top:10px;text-align:center;">
                <span style="color:#E74C3C;font-weight:bold;">*</span> All fields and document uploads are strictly required to submit your application.
            </div>
            <button type="submit" class="btn-auth-submit" style="margin-top:10px;">Submit Application</button>
        </form>

        <div class="fa-auth-footer" style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--fa-text-secondary);">
            <a href="../../index.html" class="fa-auth-link" style="text-decoration: none;">← Back to Home</a>
            <span style="margin: 0 10px; opacity: 0.5;">|</span>
            <span>Already registered? <a href="login.php" class="fa-auth-link" style="text-decoration: none;">Sign In</a></span>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dist = document.getElementById('regDistrict');
    if (dist && dist.value) {
        var selectedCity = <?php echo json_encode($selected_city); ?>;
        onDistrictChange(dist.value, selectedCity);
    }
});
</script>
