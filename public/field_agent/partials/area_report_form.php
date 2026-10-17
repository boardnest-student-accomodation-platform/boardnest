<?php
// Partial: area_report_form.php
// Area observation submission form (Transport, Amenities, Safety)
// Expects: $city (string)
?>
<form id="areaReportForm" action="actions/submit_area_report.php" method="POST" onsubmit="return compileReportData();">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <input type="hidden" id="transport_details" name="transport_details">
    <input type="hidden" id="amenities_details" name="amenities_details">
    <input type="hidden" id="safety_details"    name="safety_details">

    <!-- Step 1: Transport -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-icon-badge"><?php echo fa_icon('bus', '', 18); ?></div>
            <div>
                <h3 class="step-title">01. Transport &amp; Mobility Access</h3>
                <p class="step-desc">Select transport modes available in this area.</p>
            </div>
        </div>

        <div class="fa-checkbox-grid-flex">
            <label class="fa-checkbox-label fa-checkbox-label-sm">
                <input type="checkbox" id="chk_bus" onchange="toggleBusRoutes()" class="fa-accent-primary"> <?php echo fa_icon('bus', '', 13); ?> Bus Transport
            </label>
            <label class="fa-checkbox-label fa-checkbox-label-sm">
                <input type="checkbox" id="chk_train" class="fa-accent-primary"> <?php echo fa_icon('train', '', 13); ?> Railway Station
            </label>
            <label class="fa-checkbox-label fa-checkbox-label-sm">
                <input type="checkbox" id="chk_walk" class="fa-accent-primary"> <?php echo fa_icon('walk', '', 13); ?> Walking to Campus
            </label>
            <label class="fa-checkbox-label fa-checkbox-label-sm">
                <input type="checkbox" id="chk_tuk" class="fa-accent-primary"> <?php echo fa_icon('rideshare', '', 13); ?> Tuk / Rideshare
            </label>
        </div>

        <!-- Dynamic Bus Routes -->
        <div id="busRoutesFilterBox" class="fa-bus-filter-box" class="fa-d-none">
            <div class="fa-bus-filter-title"><?php echo fa_icon('bus', '', 14); ?> Select Active Regional Bus Routes:</div>
            <div class="fa-bus-grid">
                <label class="fa-bus-label">
                    <input type="checkbox" class="bus_route_chk fa-accent-secondary" value="Route 100 (Panadura - Pettah)"> Route 100 (Panadura - Pettah)
                </label>
                <label class="fa-bus-label">
                    <input type="checkbox" class="bus_route_chk fa-accent-secondary" value="Route 138 (Maharagama - Pettah)"> Route 138 (Maharagama - Pettah)
                </label>
                <label class="fa-bus-label">
                    <input type="checkbox" class="bus_route_chk fa-accent-secondary" value="Route 255 (Kottawa - Mount Lavinia)"> Route 255 (Kottawa - Mt. Lavinia)
                </label>
                <label class="fa-bus-label">
                    <input type="checkbox" class="bus_route_chk fa-accent-secondary" value="Route 400 (Galle Road Express)"> Route 400 (Galle Road Express)
                </label>
            </div>
        </div>

        <div class="fa-textarea-label">Additional Transport Notes (Optional):</div>
        <textarea id="transport_notes" class="textarea-styled" placeholder="Type any specific bus stop names, peak transit times, or extra details..."></textarea>
    </div>

    <!-- Step 2: Student Amenities -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-icon-badge"><?php echo fa_icon('cart', '', 18); ?></div>
            <div>
                <h3 class="step-title">02. Student Amenities &amp; Convenience</h3>
                <p class="step-desc">Check available services in this area.</p>
            </div>
        </div>

        <div class="fa-checkbox-grid">
            <label class="fa-checkbox-label">
                <input type="checkbox" class="amenity_chk fa-accent-primary" value="Supermarket (Keells / Cargills / Arpico within 500m)"> <?php echo fa_icon('cart', '', 13); ?> Supermarket (500m)
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="amenity_chk fa-accent-primary" value="24/7 Pharmacy in walking distance"> <?php echo fa_icon('pharmacy', '', 13); ?> 24/7 Pharmacy
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="amenity_chk fa-accent-primary" value="Cheap Student Food Spots &amp; Kade nearby"> <?php echo fa_icon('food', '', 13); ?> Student Food Spots
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="amenity_chk fa-accent-primary" value="Self-service Laundromat nearby"> <?php echo fa_icon('laundry', '', 13); ?> Laundromat
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="amenity_chk fa-accent-primary" value="Bank ATMs (Commercial / Sampath / HNB)"> <?php echo fa_icon('bank', '', 13); ?> Bank ATMs
            </label>
        </div>

        <div class="fa-textarea-label">Additional Amenities Notes (Optional):</div>
        <textarea id="amenities_notes" class="textarea-styled" placeholder="Type any specific market names or landmark details..."></textarea>
    </div>

    <!-- Step 3: Safety -->
    <div class="step-card">
        <div class="step-header">
            <div class="step-icon-badge"><?php echo fa_icon('shield', '', 18); ?></div>
            <div>
                <h3 class="step-title">03. Neighborhood Safety &amp; Conditions</h3>
                <p class="step-desc">Select security conditions for this neighborhood.</p>
            </div>
        </div>

        <div class="fa-checkbox-grid">
            <label class="fa-checkbox-label">
                <input type="checkbox" class="safety_chk fa-accent-primary" value="Well-lit main roads with working streetlights"> <?php echo fa_icon('lightbulb', '', 13); ?> Well-lit Main Roads
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="safety_chk fa-accent-primary" value="Regular night police patrols"> <?php echo fa_icon('shield', '', 13); ?> Police Patrols
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="safety_chk fa-accent-primary" value="Safe student residential zone"> <?php echo fa_icon('check-circle', '', 13, '#0f5132'); ?> Safe Residential Zone
            </label>
            <label class="fa-checkbox-label">
                <input type="checkbox" class="safety_chk fa-accent-danger" value="Dark side lanes require caution after 10 PM"> <?php echo fa_icon('alert', '', 13, '#c0392b'); ?> Caution after 10 PM
            </label>
        </div>

        <div class="fa-textarea-label">Additional Safety Notes (Optional):</div>
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
