<?php
// Partial: task_checklist_item.php
// Reusable audit toggle-card renderer (PHP 5.x compatible)
// Include this file once, then call renderChecklistItem() as needed

function renderChecklistItem($id, $title, $subtitle, $fieldName, $landlordClaim = '') {
    $id_esc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    $fn_esc = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
    $title_esc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $sub_esc = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
    
    echo '<div class="checklist-card" id="card_' . $id_esc . '">';
    echo '  <div class="checklist-card-row">';
    echo '    <div class="checklist-card-info">';
    echo '      <div class="fa-checklist-title">' . $title_esc . '</div>';
    if ($sub_esc !== '') {
        echo '      <div class="fa-checklist-subtitle">' . $sub_esc . '</div>';
    }
    if (!empty($landlordClaim)) {
        echo '      <div class="fa-landlord-claim-box">';
        echo '        <span class="fa-landlord-claim-tag">' . (function_exists('fa_icon') ? fa_icon('user', '', 11) : '') . ' Landlord Assigned Details:</span>';
        echo '        <span class="fa-landlord-claim-text" id="claim_' . $id_esc . '">' . htmlspecialchars($landlordClaim, ENT_QUOTES, 'UTF-8') . '</span>';
        echo '      </div>';
    }
    echo '    </div>';
    echo '    <div class="segmented-control">';
    echo '      <button type="button" class="segmented-btn btn-match" id="btn_match_' . $id_esc . '" onclick="setAuditSegment(\'' . $id_esc . '\', true)">' . (function_exists('fa_icon') ? fa_icon('check', '', 14) : '&#10003;') . ' Verified Match</button>';
    echo '      <button type="button" class="segmented-btn btn-issue" id="btn_issue_' . $id_esc . '" onclick="setAuditSegment(\'' . $id_esc . '\', false)">' . (function_exists('fa_icon') ? fa_icon('x', '', 14) : '&#10005;') . ' Issue Found</button>';
    echo '    </div>';
    echo '    <input type="hidden" id="input_' . $id_esc . '" name="' . $fn_esc . '" value="">';
    echo '  </div>';
    echo '  <div id="' . $id_esc . '_reason_container" class="checklist-reason-box">';
    echo '    <label class="form-label form-label--required text-error fa-checklist-label-error">Log ' . $title_esc . ' Discrepancy Note</label>';
    echo '    <textarea class="textarea-styled fa-checklist-textarea" id="' . $id_esc . '_reason" name="' . $fn_esc . '_reason" oninput="if(typeof checkSubmitReadiness===\'function\') checkSubmitReadiness();" placeholder="Document any discrepancies found against landlord declared details..."></textarea>';
    echo '  </div>';
    echo '</div>';
}

function renderChecklistItemReadOnly($title, $subtitle, $isMatch, $note = '', $landlordClaim = '') {
    echo '<div class="checklist-card fa-checklist-card-ro">';
    echo '  <div class="fa-checklist-card-ro-header">';
    echo '    <div>';
    echo '      <div class="fa-checklist-title">' . htmlspecialchars($title) . '</div>';
    if (!empty($subtitle)) {
        echo '      <div class="fa-checklist-subtitle">' . htmlspecialchars($subtitle) . '</div>';
    }
    if (!empty($landlordClaim)) {
        echo '      <div class="fa-landlord-claim-box" style="margin-top:6px;">';
        echo '        <span class="fa-landlord-claim-tag">Landlord Stated:</span>';
        echo '        <span class="fa-landlord-claim-text">' . htmlspecialchars($landlordClaim) . '</span>';
        echo '      </div>';
    }
    echo '    </div>';
    if ($isMatch === null || $isMatch === '') {
        echo '    <span class="fa-checklist-badge-unrecorded">' . fa_icon('dot-gray') . 'Not recorded</span>';
    } elseif ((int)$isMatch === 1) {
        echo '    <span class="fa-checklist-badge-match">' . fa_icon('check', '', 12) . ' Verified Match</span>';
    } else {
        echo '    <span class="fa-checklist-badge-issue">' . fa_icon('x', '', 12) . ' Issue Flagged</span>';
    }
    echo '  </div>';
    if ($isMatch !== null && $isMatch !== '' && (int)$isMatch === 0 && !empty($note)) {
        echo '  <div class="fa-checklist-note-box">';
        echo '    <strong>Discrepancy Note:</strong> ' . htmlspecialchars($note);
        echo '  </div>';
    }
    echo '</div>';
}
