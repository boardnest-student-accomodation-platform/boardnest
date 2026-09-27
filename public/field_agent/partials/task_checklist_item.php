<?php
// Partial: task_checklist_item.php
// Reusable audit toggle-card renderer (PHP 5.x compatible)
// Include this file once, then call renderChecklistItem() as needed

function renderChecklistItem($id, $title, $subtitle, $fieldName) {
    echo '<div class="checklist-card">';
    echo '  <div class="checklist-card-row">';
    echo '    <div>';
    echo '      <div class="fa-checklist-title">' . htmlspecialchars($title) . '</div>';
    echo '      <div class="fa-checklist-subtitle">' . htmlspecialchars($subtitle) . '</div>';
    echo '    </div>';
    echo '    <div class="segmented-control">';
    echo '      <button type="button" class="segmented-btn active-match" id="btn_match_' . $id . '" onclick="setAuditSegment(\'' . $id . '\', true)">&#10003; Verified Match</button>';
    echo '      <button type="button" class="segmented-btn" id="btn_issue_' . $id . '" onclick="setAuditSegment(\'' . $id . '\', false)">&#10005; Issue Found</button>';
    echo '    </div>';
    echo '    <input type="hidden" id="input_' . $id . '" name="' . htmlspecialchars($fieldName) . '" value="1">';
    echo '  </div>';
    echo '  <div id="' . $id . '_reason_container" class="checklist-reason-box">';
    echo '    <label class="form-label form-label--required text-error fa-checklist-label-error">Log ' . htmlspecialchars($title) . ' Discrepancy Note</label>';
    echo '    <textarea class="textarea-styled fa-checklist-textarea" id="' . $id . '_reason" placeholder="Document any discrepancies found for: ' . htmlspecialchars($title) . '..."></textarea>';
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
