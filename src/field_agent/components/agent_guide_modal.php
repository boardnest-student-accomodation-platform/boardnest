<!-- Interactive Field Agent Onboarding Protocol Guide Modal -->
<div id="agentGuideModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(8px); padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 20px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; padding: 28px; box-sizing: border-box; border: 1.5px solid #E8DDD4; box-shadow: 0 20px 40px rgba(0,0,0,0.3); position: relative;">
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; border-bottom: 1px solid #E8DDD4; padding-bottom: 14px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #A4856D; text-transform: uppercase; letter-spacing: 1px;">First-Time Agent Onboarding Protocol</div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 900; color: #6F4E37; margin: 2px 0 0 0;">👋 Field Agent Property Audit Guide</h2>
            </div>
            <button type="button" onclick="closeAgentGuide()" style="background: #FAF7F2; border: 1px solid #E8DDD4; color: #3B3330; font-weight: 800; border-radius: 50px; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>

        <p style="font-size: 13px; color: #8C7B74; margin-top: 0; margin-bottom: 20px; line-height: 1.5;">
            Welcome to the BoardNest Field Agent Network! Follow these <strong>4 mandatory steps</strong> during every property verification visit to ensure complete student safety and platform trustworthiness:
        </p>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            <!-- Step 1 -->
            <div style="background: #FAF7F2; border: 1px solid #E8DDD4; border-radius: 12px; padding: 14px; display: flex; gap: 14px; align-items: flex-start;">
                <div style="background: #6F4E37; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">1</div>
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #3B3330;">📍 Step 1: On-Site GPS Geofence Verification</div>
                    <div style="font-size: 12px; color: #8C7B74; margin-top: 4px; line-height: 1.4;">
                        Travel physically to the property address. Tap <strong>"Verify My GPS Location"</strong>. Your device GPS coordinates must match property coordinates (within 100 meters) to unlock the audit protocol form.
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div style="background: #FAF7F2; border: 1px solid #E8DDD4; border-radius: 12px; padding: 14px; display: flex; gap: 14px; align-items: flex-start;">
                <div style="background: #6F4E37; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">2</div>
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #3B3330;">🖼️ Step 2: Landlord Photo Cross-Reference</div>
                    <div style="font-size: 12px; color: #8C7B74; margin-top: 4px; line-height: 1.4;">
                        Inspect the landlord's uploaded listing photos in the left sidebar. Cross-check physical rooms on-site and toggle <strong>"✓ Photo Verified"</strong> or <strong>"✕ Photo Discrepancy"</strong>.
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div style="background: #FAF7F2; border: 1px solid #E8DDD4; border-radius: 12px; padding: 14px; display: flex; gap: 14px; align-items: flex-start;">
                <div style="background: #6F4E37; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">3</div>
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #3B3330;">📋 Step 3: Compliance Audit Protocol</div>
                    <div style="font-size: 12px; color: #8C7B74; margin-top: 4px; line-height: 1.4;">
                        Evaluate 7 key property compliance items (Structural integrity, Electrical wiring, Fire exit pathways, Wi-Fi signal, Rent & key money deposit). If an issue is found, document discrepancy notes.
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div style="background: #FAF7F2; border: 1px solid #E8DDD4; border-radius: 12px; padding: 14px; display: flex; gap: 14px; align-items: flex-start;">
                <div style="background: #6F4E37; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">4</div>
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #3B3330;">📸 Step 4: Live Hardware Camera Capture</div>
                    <div style="font-size: 12px; color: #8C7B74; margin-top: 4px; line-height: 1.4;">
                        Click <strong>"📸 Open Live Camera"</strong> to capture mandatory Entrance & Room photos. Every photo automatically receives a permanent <strong>Date, Time & Location Watermark Stamp</strong>. Use <strong>"➕ Capture Additional Proof Photo"</strong> if extra proof is needed!
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; text-align: center;">
            <button type="button" onclick="closeAgentGuide()" style="background: #6F4E37; color: #FFFFFF; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; width: 100%;">
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
