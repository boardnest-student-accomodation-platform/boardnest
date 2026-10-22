<?php /* VIVA NOTE: Authenticated Landlord page shell: sidebar navigation, sticky header, main content, account controls, and asset references are shared by every protected page. */
/**
 * Shared landlord layout. The controller provides $title, $active, $landlord
 * and $content. Navigation lives here so all views stay small.
 */
/* VIVA: Sidebar is generated from $navigation; CSS makes the header sticky; $content is inserted into <main>. */
$navigation = [
    ['dashboard', '/dashboard', 'Overview', 'home'],
    ['properties', '/properties', 'Properties', 'building'],
    ['rooms', '/rooms', 'Rooms', 'door'],
    ['bookings', '/bookings', 'Bookings', 'calendar'],
    ['occupants', '/students/current', 'Current students', 'users'],
    ['messages', '/messages', 'Messages', 'message'],
    ['complaints', '/complaints', 'Complaints', 'alert'],
    ['verification', '/verification', 'Verification', 'shield'],
    ['announcements', '/announcements', 'Announcements', 'bell'],
    ['subscription', '/subscription', 'Subscription', 'credit'],
    ['profile', '/profile', 'My profile', 'settings'],
];

// Small inline SVG icons keep the UI crisp without third-party dependencies.
$icons = [
    'home' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    'building' => '<path d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16M2 21h20M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3"/>',
    'door' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M15 12h.01"/>',
    'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18m-12 5 2 2 4-4"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m14-13a4 4 0 1 1 0 8m6 5v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>',
    'message' => '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8A8.5 8.5 0 0 1 12.5 20a8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 12.5 3h.5a8.5 8.5 0 0 1 8 8z"/>',
    'alert' => '<path d="M10.3 3.3 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.3a2 2 0 0 0-3.4 0ZM12 9v4m0 4h.01"/>',
    'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
    'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
    'credit' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20"/>',
    'settings' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
];
$svg = static function (string $name) use ($icons): string {
    return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'
        . ($icons[$name] ?? '') . '</svg>';
};
$initial = mb_strtoupper(mb_substr(trim((string)$landlord['full_name']), 0, 1));
$photoPath = (string)($landlord['profile_photo'] ?? '');
$avatarHtml = $photoPath !== ''
    ? '<img class="avatar-image" src="'.e(url('/'.ltrim($photoPath, '/'))).'" alt="" width="38" height="38">'
    : e($initial);
require __DIR__ . '/../../help/topics.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4a3f3b">
    <title><?= e($title) ?> | BoardNest</title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css', ['v' => '20261010-booking-help-1'])) ?>">
    <script src="<?= e(url('/assets/js/app.js', ['v' => '20261010-booking-help-1'])) ?>" defer></script>
</head>
<body class="dashboard-body">
<a class="skip-link" href="#main-content">Skip to content</a>
<div class="sidebar-overlay" id="sidebar-overlay" hidden></div>

<aside class="sidebar" id="sidebar" aria-label="Landlord navigation">
    <div class="sidebar-brand">
        <a class="logo" href="<?= e(url('/dashboard')) ?>" aria-label="BoardNest dashboard">
            <span class="logo-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-8h6v8"/></svg>
            </span>
            <span>Board<span class="brand-accent">Nest</span></span>
        </a>
        <button type="button" class="sidebar-close" id="menu-close" aria-label="Close navigation">&times;</button>
    </div>
    <div class="nav-caption">WORKSPACE</div>
    <nav class="sidebar-nav" aria-label="Main navigation">
        <?php foreach ($navigation as [$key, $path, $label, $icon]): ?>
            <a class="nav-link <?= $active === $key ? 'active' : '' ?>"
               href="<?= e(url($path)) ?>"
               <?= $active === $key ? 'aria-current="page"' : '' ?>>
                <?= $svg($icon) ?>
                <span><?= e($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="user-chip">
            <span class="avatar" aria-hidden="true"><?= $avatarHtml ?></span>
            <div class="user-info">
                <strong><?= e($landlord['full_name']) ?></strong>
                <small><?= e(ucfirst((string)$landlord['subsc_tier'])) ?> plan</small>
            </div>
        </div>
        <form method="post" action="<?= e(url('/logout')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="logout">Sign out <span aria-hidden="true">→</span></button>
        </form>
    </div>
</aside>

<div class="app-container">
    <header class="app-topbar">
        <div class="topbar-start">
            <button class="mobile-menu" id="menu-toggle" type="button"
                    aria-expanded="false" aria-controls="sidebar" aria-label="Open navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="topbar-titles">
                <h1><?= e($title) ?></h1>
            </div>
        </div>
        <div class="topbar-actions">
            <button type="button" id="context-help-open" class="topbar-icon-link help-trigger"
                    aria-label="Help for <?=e($help[0])?>" aria-haspopup="dialog" aria-controls="landlord-help-dialog"
                    title="Help for <?=e($help[0])?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.3a2.7 2.7 0 0 1 5.2 1c0 1.8-2.7 2-2.7 4"/><path d="M12 17.5h.01"/></svg>
            </button>
            <a class="topbar-icon-link" href="<?= e(url('/announcements')) ?>" aria-label="Announcements" title="Announcements">
                <?= $svg('bell') ?>
            </a>
            <a class="topbar-account" href="<?= e(url('/profile')) ?>" title="My profile" aria-label="My profile">
                <span class="avatar" aria-hidden="true"><?= $avatarHtml ?></span>
            </a>
        </div>
    </header>
    <main id="main-content" class="main-content" tabindex="-1">
        <?php if ($flash): ?>
            <div role="alert" class="alert <?= e($flash[0]) ?>"><?= e($flash[1]) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
</div>
<!-- Context-aware help: a single reusable dialog, populated from the current route. -->
<dialog id="landlord-help-dialog" class="help-dialog" aria-labelledby="landlord-help-title" aria-describedby="landlord-help-subtitle">
    <div class="help-dialog-head">
        <div><p class="help-kicker">BOARDNEST LANDLORD GUIDE · <?=e($help[0])?></p>
        <h2 id="landlord-help-title"><?=e($help[1])?></h2>
        <p id="landlord-help-subtitle">Instructions for this section only.</p></div>
        <button type="button" id="context-help-close" class="help-dialog-close" aria-label="Close help">&times;</button>
    </div>
    <div class="help-dialog-body"><ol class="help-steps">
    <?php foreach ($help[2] as $step): ?><li><?=e($step)?></li><?php endforeach; ?>
    </ol><div class="help-tip"><strong>Remember:</strong> <?=e($help[3])?></div></div>
    <div class="help-dialog-foot"><button type="button" id="context-help-done" class="btn">Got it</button></div>
</dialog>
</body>
</html>
