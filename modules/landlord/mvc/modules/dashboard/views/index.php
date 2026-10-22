<?php /* VIVA NOTE: Overview dashboard: figures and recent data were prepared by DashboardController. */
$firstName = explode(' ', trim((string) $landlord['full_name']))[0] ?: 'there';
$figures = [
    ['Properties', (int) ($stats['properties'] ?? 0)],
    ['Rooms', (int) ($stats['rooms'] ?? 0)],
    ['Pending bookings', (int) ($stats['bookings'] ?? 0)],
    ['Current students', (int) ($stats['occupants'] ?? 0)],
];
?>
<section class="hero" aria-label="Dashboard introduction">
    <div>
        <span class="eyebrow">OVERVIEW</span>
        <h2>Welcome back, <?= e($firstName) ?>.</h2>
        <p>Manage properties, room availability and booking requests in one place.</p>
    </div>
    <a class="btn" href="<?= e(url('/property/new')) ?>">Add property <span aria-hidden="true">→</span></a>
</section>

<section class="metrics" aria-label="Portfolio overview">
    <?php foreach ($figures as [$label, $value]): ?>
        <div class="metric">
            <span class="metric-top"><?= e($label) ?></span>
            <strong><?= e($value) ?></strong>
        </div>
    <?php endforeach; ?>
</section>

<div class="two-panel">
    <section class="panel">
        <div class="panel-title">
            <h3>Recent bookings</h3>
            <a href="<?= e(url('/bookings')) ?>">View all →</a>
        </div>
        <?php if (!$bookings): ?>
            <p class="empty">No booking requests yet.</p>
        <?php else: ?>
            <div class="table-wrap" tabindex="0" aria-label="Recent bookings, scroll horizontally if needed">
                <table>
                    <thead><tr><th>Student</th><th>Property</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><strong><?= e($booking['student_name']) ?></strong></td>
                            <td><?= e($booking['property_title']) ?></td>
                            <td><span class="status"><?= e(pill($booking['status'])) ?></span></td>
                            <td><a href="<?= e(url('/booking/show', ['id' => $booking['booking_id']])) ?>">Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel-title">
            <h3>Announcements</h3>
            <a href="<?= e(url('/announcements')) ?>">View all →</a>
        </div>
        <?php if (!$announcements): ?>
            <p class="empty">No announcements right now.</p>
        <?php else: ?>
            <?php foreach ($announcements as $announcement): ?>
                <article class="notice">
                    <span class="eyebrow"><?= e(pill($announcement['priority'])) ?></span>
                    <strong><?= e($announcement['title']) ?></strong>
                    <p><?= e(mb_strimwidth($announcement['message'], 0, 160, '…')) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
