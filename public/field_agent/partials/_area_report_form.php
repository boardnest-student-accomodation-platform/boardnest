<?php
// Partial: _area_report_form.php
// Area observation submission form (Transport, Amenities, Safety)
// Expects: $city (string)
?>
<form id="areaReportForm" action="actions/submit_area_report.php" method="POST" onsubmit="return compileReportData();">
    <input type="hidden" id="transport_details" name="transport_details">
    <input type="hidden" id="amenities_details" name="amenities_details">
    <input type="hidden" id="safety_details"    name="safety_details">

    <!-- Step 1: Transport -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-icon-badge">🚍</div>
            <div>
                <h3 class="step-title">01. Transport &amp; Mobility Access</h3>
                <p class="step-desc">Select transport modes available in this area.</p>
            </div>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:8px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" id="chk_bus" onchange="toggleBusRoutes()" style="accent-color:#3B3330;"> 🚌 Bus Transport
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:8px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" id="chk_train" style="accent-color:#3B3330;"> 🚆 Railway Station
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:8px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" id="chk_walk" style="accent-color:#3B3330;"> 🚶 Walking to Campus
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:8px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" id="chk_tuk" style="accent-color:#3B3330;"> 🛺 Tuk / Rideshare
            </label>
        </div>

        <!-- Dynamic Bus Routes -->
        <div id="busRoutesFilterBox" style="display:none;background:#FAF7F2;border:1.5px dashed #A4856D;padding:16px;border-radius:14px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:800;color:#A4856D;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">🚌 Select Active Regional Bus Routes:</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px;">
                <label style="font-size:12px;font-weight:600;color:#3B3330;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" class="bus_route_chk" value="Route 100 (Panadura - Pettah)" style="accent-color:#A4856D;"> Route 100 (Panadura - Pettah)
                </label>
                <label style="font-size:12px;font-weight:600;color:#3B3330;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" class="bus_route_chk" value="Route 138 (Maharagama - Pettah)" style="accent-color:#A4856D;"> Route 138 (Maharagama - Pettah)
                </label>
                <label style="font-size:12px;font-weight:600;color:#3B3330;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" class="bus_route_chk" value="Route 255 (Kottawa - Mount Lavinia)" style="accent-color:#A4856D;"> Route 255 (Kottawa - Mt. Lavinia)
                </label>
                <label style="font-size:12px;font-weight:600;color:#3B3330;cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" class="bus_route_chk" value="Route 400 (Galle Road Express)" style="accent-color:#A4856D;"> Route 400 (Galle Road Express)
                </label>
            </div>
        </div>

        <div style="font-size:12px;font-weight:700;color:#8C7B74;margin-bottom:6px;">Additional Transport Notes (Optional):</div>
        <textarea id="transport_notes" class="textarea-styled" placeholder="Type any specific bus stop names, peak transit times, or extra details..."></textarea>
    </div>

    <!-- Step 2: Student Amenities -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-icon-badge">🛒</div>
            <div>
                <h3 class="step-title">02. Student Amenities &amp; Convenience</h3>
                <p class="step-desc">Check available services in this area.</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="amenity_chk" value="Supermarket (Keells / Cargills / Arpico within 500m)" style="accent-color:#3B3330;"> 🛒 Supermarket (500m)
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="amenity_chk" value="24/7 Pharmacy in walking distance" style="accent-color:#3B3330;"> 💊 24/7 Pharmacy
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="amenity_chk" value="Cheap Student Food Spots &amp; Kade nearby" style="accent-color:#3B3330;"> 🍛 Student Food Spots
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="amenity_chk" value="Self-service Laundromat nearby" style="accent-color:#3B3330;"> 🧺 Laundromat
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="amenity_chk" value="Bank ATMs (Commercial / Sampath / HNB)" style="accent-color:#3B3330;"> 🏧 Bank ATMs
            </label>
        </div>

        <div style="font-size:12px;font-weight:700;color:#8C7B74;margin-bottom:6px;">Additional Amenities Notes (Optional):</div>
        <textarea id="amenities_notes" class="textarea-styled" placeholder="Type any specific market names or landmark details..."></textarea>
    </div>

    <!-- Step 3: Safety -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-icon-badge">🛡️</div>
            <div>
                <h3 class="step-title">03. Neighborhood Safety &amp; Conditions</h3>
                <p class="step-desc">Select security conditions for this neighborhood.</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="safety_chk" value="Well-lit main roads with working streetlights" style="accent-color:#3B3330;"> 💡 Well-lit Main Roads
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="safety_chk" value="Regular night police patrols" style="accent-color:#3B3330;"> 🚓 Police Patrols
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="safety_chk" value="Safe student residential zone" style="accent-color:#3B3330;"> 🟢 Safe Residential Zone
            </label>
            <label style="display:flex;align-items:center;gap:8px;background:#FFF8F5;border:1.5px solid #E8DDD4;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                <input type="checkbox" class="safety_chk" value="Dark side lanes require caution after 10 PM" style="accent-color:#C0392B;"> ⚠️ Caution after 10 PM
            </label>
        </div>

        <div style="font-size:12px;font-weight:700;color:#8C7B74;margin-bottom:6px;">Additional Safety Notes (Optional):</div>
        <textarea id="safety_notes" class="textarea-styled" placeholder="Type any specific security warnings or local observations..."></textarea>
    </div>

    <button type="submit" class="btn-publish">
        <span>Publish Regional Audit Report</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
    </button>
</form>
