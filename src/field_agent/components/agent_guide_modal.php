<!-- Interactive Field Agent Onboarding Protocol Guide Modal -->
<div id="agentGuideModal" class="fa-modal-overlay">
    <div class="fa-modal-container large">
        
        <div class="fa-modal-header bordered">
            <div>
                <div class="fa-guide-subtitle">First-Time Agent Onboarding Protocol</div>
                <h2 class="fa-guide-title">👋 Field Agent Property Audit Guide</h2>
            </div>
            <button type="button" onclick="closeAgentGuide()" class="fa-btn-close alt">✕</button>
        </div>

        <p class="fa-guide-desc">
            Welcome to the BoardNest Field Agent Network! Follow these <strong>4 mandatory steps</strong> during every property verification visit to ensure complete student safety and platform trustworthiness:
        </p>

        <div class="fa-step-list">
            <!-- Step 1 -->
            <div class="fa-step-container">
                <div class="fa-step-number">1</div>
                <div>
                    <div class="fa-step-title">📍 Step 1: On-Site GPS Geofence Verification</div>
                    <div class="fa-step-desc">
                        Travel physically to the property address. Tap <strong>"Verify My GPS Location"</strong>. Your device GPS coordinates must match property coordinates (within 100 meters) to unlock the audit protocol form.
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="fa-step-container">
                <div class="fa-step-number">2</div>
                <div>
                    <div class="fa-step-title">🖼️ Step 2: Landlord Photo Cross-Reference</div>
                    <div class="fa-step-desc">
                        Inspect the landlord's uploaded listing photos in the left sidebar. Cross-check physical rooms on-site and toggle <strong>"✓ Photo Verified"</strong> or <strong>"✕ Photo Discrepancy"</strong>.
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="fa-step-container">
                <div class="fa-step-number">3</div>
                <div>
                    <div class="fa-step-title">📋 Step 3: Compliance Audit Protocol</div>
                    <div class="fa-step-desc">
                        Evaluate 7 key property compliance items (Structural integrity, Electrical wiring, Fire exit pathways, Wi-Fi signal, Rent & key money deposit). If an issue is found, document discrepancy notes.
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="fa-step-container">
                <div class="fa-step-number">4</div>
                <div>
                    <div class="fa-step-title">📸 Step 4: Live Hardware Camera Capture</div>
                    <div class="fa-step-desc">
                        Click <strong>"📸 Open Live Camera"</strong> to capture mandatory Entrance & Room photos. Every photo automatically receives a permanent <strong>Date, Time & Location Watermark Stamp</strong>. Use <strong>"➕ Capture Additional Proof Photo"</strong> if extra proof is needed!
                    </div>
                </div>
            </div>
        </div>

        <div class="fa-guide-footer">
            <button type="button" onclick="closeAgentGuide()" class="fa-btn-primary full">
                Got It! Proceed to Property Audit
            </button>
        </div>
    </div>
</div>

<script>
    function openAgentGuide() {
        const modal = document.getElementById('agentGuideModal');
        if (modal) modal.style.display = 'flex';
    }
    function closeAgentGuide() {
        const modal = document.getElementById('agentGuideModal');
        if (modal) modal.style.display = 'none';
        localStorage.setItem('boardnest_agent_guide_seen', 'true');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('boardnest_agent_guide_seen')) {
            setTimeout(openAgentGuide, 500);
        }
    });
</script>
