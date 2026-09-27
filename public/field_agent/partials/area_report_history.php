<?php
// Partial: area_report_history.php
// Right column: submitted area reports feed
// Expects: $reports (array)
?>
<div class="history-card">
    <h2 class="fa-history-title">Regional Audit History</h2>

    <?php if (empty($reports)): ?>
        <div class="fa-history-empty-box">
            <div class="fa-history-empty-icon">📝</div>
            <div class="fa-history-empty-title">No Audit History Yet</div>
            <div class="fa-history-empty-desc">Submit your first area observation report using the form.</div>
        </div>
    <?php else: ?>
        <?php foreach ($reports as $rep): ?>
        <div class="feed-item-card">
            <div class="fa-checklist-card-ro-header">
                <span class="fa-history-date">
                    📅 <?php echo date('M d, Y', strtotime($rep['submitted_at'])); ?>
                </span>
                <?php
                $statusBg  = $rep['status'] === 'approved' ? 'rgba(39,174,96,0.15)' : ($rep['status'] === 'rejected' ? 'rgba(192,57,43,0.15)' : 'rgba(200,121,65,0.15)');
                $statusClr = $rep['status'] === 'approved' ? '#27AE60' : ($rep['status'] === 'rejected' ? '#C0392B' : '#A4856D');
                ?>
                <span class="fa-history-badge" style="background:<?php echo $statusBg; ?>;color:<?php echo $statusClr; ?>;">
                    <?php echo htmlspecialchars(ucfirst($rep['status'])); ?>
                </span>
            </div>

            <!-- Transport Section -->
            <div class="fa-mb-12">
                <div class="fa-history-section-title">🚍 Transport &amp; Access</div>
                <div class="fa-history-tags-wrap">
                    <?php
                    $tItems = array_filter(array_map('trim', explode('.', $rep['transport_details'])));
                    foreach ($tItems as $tItem): if ($tItem === '') continue; ?>
                        <span class="fa-history-tag">
                            <?php echo htmlspecialchars($tItem); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Amenities Section -->
            <div class="fa-mb-12">
                <div class="fa-history-section-title">🛒 Student Amenities</div>
                <div class="fa-history-tags-wrap">
                    <?php
                    $aItems = array_filter(array_map('trim', explode('.', $rep['amenities_details'])));
                    foreach ($aItems as $aItem): if ($aItem === '') continue; ?>
                        <span class="fa-history-tag">
                            <?php echo htmlspecialchars($aItem); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Safety Section -->
            <div>
                <div class="fa-history-section-title">🛡️ Safety &amp; Lighting</div>
                <div class="fa-history-tags-wrap">
                    <?php
                    $sItems = array_filter(array_map('trim', explode('.', $rep['safety_details'])));
                    foreach ($sItems as $sItem): if ($sItem === '') continue; ?>
                        <span class="fa-history-tag">
                            <?php echo htmlspecialchars($sItem); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
