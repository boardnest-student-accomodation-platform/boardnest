<?php
// Partial: _area_report_history.php
// Right column: submitted area reports feed
// Expects: $reports (array)
?>
<div class="history-card">
    <h2 style="font-size:18px;font-weight:800;color:#3B3330;margin:0 0 16px 0;">Regional Audit History</h2>

    <?php if (empty($reports)): ?>
        <div style="text-align:center;padding:32px 16px;background:#FFF8F5;border-radius:14px;border:1px dashed #E8DDD4;">
            <div style="font-size:32px;margin-bottom:8px;">📝</div>
            <div style="font-size:14px;font-weight:700;color:#3B3330;">No Audit History Yet</div>
            <div style="font-size:12px;color:#8C7B74;margin-top:4px;">Submit your first area observation report using the form.</div>
        </div>
    <?php else: ?>
        <?php foreach ($reports as $rep): ?>
        <div class="feed-item-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-size:12px;font-weight:700;color:#8C7B74;">
                    📅 <?php echo date('M d, Y', strtotime($rep['submitted_at'])); ?>
                </span>
                <?php
                $statusBg  = $rep['status'] === 'approved' ? 'rgba(39,174,96,0.15)' : ($rep['status'] === 'rejected' ? 'rgba(192,57,43,0.15)' : 'rgba(200,121,65,0.15)');
                $statusClr = $rep['status'] === 'approved' ? '#27AE60' : ($rep['status'] === 'rejected' ? '#C0392B' : '#A4856D');
                ?>
                <span style="font-size:11px;font-weight:800;padding:2px 10px;border-radius:50px;background:<?php echo $statusBg; ?>;color:<?php echo $statusClr; ?>;">
                    <?php echo htmlspecialchars(ucfirst($rep['status'])); ?>
                </span>
            </div>

            <!-- Transport Section -->
            <div style="margin-bottom:12px;">
                <div style="font-size:11px;font-weight:800;color:#A4856D;text-transform:uppercase;letter-spacing:0.5px;">🚍 Transport &amp; Access</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                    <?php
                    $tItems = array_filter(array_map('trim', explode('.', $rep['transport_details'])));
                    foreach ($tItems as $tItem): if ($tItem === '') continue; ?>
                        <span style="display:inline-block;background:#FFF8F5;border:1px solid #E8DDD4;color:#3B3330;font-size:12px;font-weight:600;padding:4px 10px;border-radius:50px;">
                            <?php echo htmlspecialchars($tItem); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Amenities Section -->
            <div style="margin-bottom:12px;">
                <div style="font-size:11px;font-weight:800;color:#A4856D;text-transform:uppercase;letter-spacing:0.5px;">🛒 Student Amenities</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                    <?php
                    $aItems = array_filter(array_map('trim', explode('.', $rep['amenities_details'])));
                    foreach ($aItems as $aItem): if ($aItem === '') continue; ?>
                        <span style="display:inline-block;background:#FFF8F5;border:1px solid #E8DDD4;color:#3B3330;font-size:12px;font-weight:600;padding:4px 10px;border-radius:50px;">
                            <?php echo htmlspecialchars($aItem); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Safety Section -->
            <div>
                <div style="font-size:11px;font-weight:800;color:#A4856D;text-transform:uppercase;letter-spacing:0.5px;">🛡️ Safety &amp; Lighting</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                    <?php
                    $sItems = array_filter(array_map('trim', explode('.', $rep['safety_details'])));
                    foreach ($sItems as $sItem): if ($sItem === '') continue; ?>
                        <span style="display:inline-block;background:#FFF8F5;border:1px solid #E8DDD4;color:#3B3330;font-size:12px;font-weight:600;padding:4px 10px;border-radius:50px;">
                            <?php echo htmlspecialchars($sItem); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
