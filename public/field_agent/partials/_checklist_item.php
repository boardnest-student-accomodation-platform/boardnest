<?php
// Partial: _checklist_item.php
// Reusable audit toggle-card renderer (PHP 5.x compatible)
// Include this file once, then call renderChecklistItem() as needed

function renderChecklistItem($id, $title, $subtitle, $fieldName) {
    echo '<div class="checklist-card">';
    echo '  <div class="checklist-card-row">';
    echo '    <div>';
    echo '      <div style="font-weight:800;color:#212529;font-size:14px;">' . htmlspecialchars($title) . '</div>';
    echo '      <div style="font-size:12px;color:#8C7B74;margin-top:2px;">' . htmlspecialchars($subtitle) . '</div>';
    echo '    </div>';
    echo '    <div class="segmented-control">';
    echo '      <button type="button" class="segmented-btn active-match" id="btn_match_' . $id . '" onclick="setAuditSegment(\'' . $id . '\', true)">&#10003; Verified Match</button>';
    echo '      <button type="button" class="segmented-btn" id="btn_issue_' . $id . '" onclick="setAuditSegment(\'' . $id . '\', false)">&#10005; Issue Found</button>';
    echo '    </div>';
    echo '    <input type="hidden" id="input_' . $id . '" name="' . htmlspecialchars($fieldName) . '" value="1">';
    echo '  </div>';
    echo '  <div id="' . $id . '_reason_container" class="checklist-reason-box">';
    echo '    <label class="form-label form-label--required text-error" style="font-size:11px;font-weight:800;margin-bottom:4px;display:block;text-transform:uppercase;">Log ' . htmlspecialchars($title) . ' Discrepancy Note</label>';
    echo '    <textarea class="textarea-styled" id="' . $id . '_reason" style="min-height:60px;font-size:12px;" placeholder="Document any discrepancies found for: ' . htmlspecialchars($title) . '..."></textarea>';
    echo '  </div>';
    echo '</div>';
}

function renderChecklistItemReadOnly($title, $subtitle, $isMatch, $note = '') {
    $match = (int)$isMatch === 1;
    echo '<div class="checklist-card" style="background:#FAF7F2;border:1px solid #E8DDD4;border-radius:12px;padding:16px;margin-bottom:12px;">';
    echo '  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">';
    echo '    <div>';
    echo '      <div style="font-weight:800;color:#212529;font-size:14px;">' . htmlspecialchars($title) . '</div>';
    echo '      <div style="font-size:12px;color:#8C7B74;margin-top:2px;">' . htmlspecialchars($subtitle) . '</div>';
    echo '    </div>';
    if ($match) {
        echo '    <span style="background:rgba(39,174,96,0.12);color:#0F5132;border:1.5px solid #BADBCE;padding:6px 14px;border-radius:50px;font-size:12px;font-weight:800;white-space:nowrap;">✓ Verified Match</span>';
    } else {
        echo '    <span style="background:rgba(220,53,69,0.12);color:#842029;border:1.5px solid #F5C2C7;padding:6px 14px;border-radius:50px;font-size:12px;font-weight:800;white-space:nowrap;">✕ Issue Flagged</span>';
    }
    echo '  </div>';
    if (!$match && !empty($note)) {
        echo '  <div style="margin-top:10px;background:#FFF3CD;border:1px solid #FFECB5;color:#664D03;padding:10px 14px;border-radius:8px;font-size:12px;font-weight:600;">';
        echo '    <strong>Discrepancy Note:</strong> ' . htmlspecialchars($note);
        echo '  </div>';
    }
    echo '</div>';
}
