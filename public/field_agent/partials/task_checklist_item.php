<?php
// Partial: task_checklist_item.php
// Reusable audit toggle-card renderer (PHP 5.x compatible)
// Include this file once, then call renderChecklistItem() as needed

function renderChecklistItem($id, $title, $subtitle, $fieldName) {
    $id_esc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    $fn_esc = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
    $title_esc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $sub_esc = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
    
    echo '<div class="checklist-card">';
    echo '  <div class="checklist-card-row">';
    echo '    <div>';
    echo '      <div class="fa-checklist-title">' . $title_esc . '</div>';
    echo '      <div class="fa-checklist-subtitle">' . $sub_esc . '</div>';
    echo '    </div>';
    echo '    <div class="segmented-control">';
    echo '      <button type="button" class="segmented-btn" id="btn_match_' . $id_esc . '" onclick="setAuditSegment(\'' . $id_esc . '\', true)">&#10003; Verified Match</button>';
    echo '      <button type="button" class="segmented-btn" id="btn_issue_' . $id_esc . '" onclick="setAuditSegment(\'' . $id_esc . '\', false)">&#10005; Issue Found</button>';
    echo '    </div>';
    echo '    <input type="hidden" id="input_' . $id_esc . '" name="' . $fn_esc . '" value="">';
    echo '  </div>';
    echo '  <div id="' . $id_esc . '_reason_container" class="checklist-reason-box">';
    echo '    <label class="form-label form-label--required text-error fa-checklist-label-error">Log ' . $title_esc . ' Discrepancy Note</label>';
    echo '    <textarea class="textarea-styled fa-checklist-textarea" id="' . $id_esc . '_reason" name="' . $fn_esc . '_reason" placeholder="Document any discrepancies found for: ' . $title_esc . '..."></textarea>';
    echo '  </div>';
    echo '</div>';
}

function renderChecklistItemReadOnly($title, $subtitle, $isMatch, $note = '') {
    $match = (int)$isMatch === 1;
    echo '<div class="checklist-card fa-checklist-card-ro">';
    echo '  <div class="fa-checklist-card-ro-header">';
    echo '    <div>';
    echo '      <div class="fa-checklist-title">' . htmlspecialchars($title) . '</div>';
    echo '      <div class="fa-checklist-subtitle">' . htmlspecialchars($subtitle) . '</div>';
    echo '    </div>';
    if ($match) {
        echo '    <span class="fa-checklist-badge-match">✓ Verified Match</span>';
    } else {
        echo '    <span class="fa-checklist-badge-issue">✕ Issue Flagged</span>';
    }
    echo '  </div>';
    if (!$match && !empty($note)) {
        echo '  <div class="fa-checklist-note-box">';
        echo '    <strong>Discrepancy Note:</strong> ' . htmlspecialchars($note);
        echo '  </div>';
    }
    echo '</div>';
}
