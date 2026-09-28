<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../modules/listings/services/search.php';

startSession();

$listingId = (int) ($_GET['id'] ?? 0);
if ($listingId <= 0) {
    header('Location: search.php');
    exit();
}

try {
    $detail = getListingById($pdo, $listingId);
} catch (PDOException $exception) {
    $detail = null;
}

$successMessage = $_SESSION['flash']['success'] ?? '';
$errorMessage = $_SESSION['flash']['error'] ?? '';
unset($_SESSION['flash']['success'], $_SESSION['flash']['error']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$canAccessStudentActions = isset($_SESSION['user_id'])
    && ($_SESSION['role'] ?? '') === 'student';

$isSaved = false;
if ($detail && $canAccessStudentActions) {
    $savedIds = studentSavedListingIds($pdo, (int) $_SESSION['user_id']);
    $isSaved = in_array($listingId, $savedIds, true);
}

$listing = $detail['listing'] ?? null;
$photos = $detail['photos'] ?? [];
$reviews = $detail['reviews'] ?? [];
$occupant = $detail['occupant'] ?? null;
$facilities = $listing && !empty($listing['shared_facilities'])
    ? array_values(array_filter(array_map('trim', explode(',', $listing['shared_facilities']))))
    : [];
$houseRules = $listing && !empty($listing['house_rules'])
    ? array_values(array_filter(array_map('trim', preg_split('/[\r\n.;]+/', $listing['house_rules']))))
    : [];

function listingStars(int $rating): string
{
    return str_repeat('&#9733;', max(0, min(5, $rating)))
        . str_repeat('&#9734;', max(0, 5 - $rating));
}

function listingLabel(?string $value): string
{
    return $value ? ucwords(str_replace('_', ' ', $value)) : 'Not specified';
}

function listingInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials ?: 'BN';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $listing ? htmlspecialchars($listing['title']) . ' | ' : '' ?>BoardNest</title>
    <meta name="description" content="<?= $listing ? htmlspecialchars(mb_strimwidth($listing['description'], 0, 155, '...')) : 'BoardNest listing' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student-listing.css">
</head>
<body data-page="listing-detail">
    <header class="listing-site-header">
        <div class="listing-header-inner">
            <a class="listing-brand" href="../../index.html" aria-label="BoardNest home">
                <span class="listing-brand-mark" aria-hidden="true">B</span>
                <span>BoardNest</span>
            </a>
            <nav class="listing-primary-nav" aria-label="Primary navigation">
                <a class="is-active" href="search.php">Browse</a>
                <a href="../../index.html#how-it-works">How It Works</a>
            </nav>
            <div class="listing-account-actions">
                <?php if ($canAccessStudentActions): ?>
                    <a class="listing-button listing-button--outline listing-button--compact" href="saved.php">Saved</a>
                    <a class="listing-button listing-button--outline listing-button--compact" href="dashboard.php">Dashboard</a>
                    <span class="listing-account-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1))) ?></span>
                <?php else: ?>
                    <a class="listing-button listing-button--outline listing-button--compact" href="../../login.php">Log in</a>
                    <a class="listing-button listing-button--primary listing-button--compact listing-signup" href="register.php">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="listing-breadcrumb-bar">
        <nav class="listing-shell listing-breadcrumb" aria-label="Breadcrumb">
            <a href="../../index.html">Home</a><span aria-hidden="true">/</span>
            <a href="search.php">Browse listings</a>
            <?php if ($listing): ?><span aria-hidden="true">/</span><span aria-current="page"><?= htmlspecialchars($listing['title']) ?></span><?php endif; ?>
        </nav>
    </div>

    <main class="listing-shell listing-page">
        <?php if ($successMessage): ?><div class="listing-alert listing-alert--success" role="status"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="listing-alert listing-alert--error" role="alert"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

        <?php if (!$listing): ?>
            <section class="listing-panel listing-empty-state">
                <span class="listing-empty-mark" aria-hidden="true">?</span>
                <h1>Listing not found</h1>
                <p>This boarding place is unavailable or its catalogue data has not been imported yet.</p>
                <a class="listing-button listing-button--primary" href="search.php">Back to browse</a>
            </section>
        <?php else: ?>
            <section class="listing-title-row">
                <div class="listing-title-copy">
                    <div class="listing-badges">
                        <span class="listing-badge listing-badge--verified"><span aria-hidden="true">&#10003;</span> Field verified</span>
                        <span class="listing-badge"><?= htmlspecialchars(listingLabel($listing['structural_type'])) ?></span>
                        <?php if ((int) $listing['available_slots'] > 0): ?><span class="listing-badge listing-badge--available"><span aria-hidden="true"></span> Available</span><?php endif; ?>
                    </div>
                    <h1><?= htmlspecialchars($listing['title']) ?></h1>
                    <div class="listing-location-line">
                        <span aria-hidden="true">&#9673;</span><span><?= htmlspecialchars($listing['address'] . ', ' . $listing['city']) ?></span>
                        <span class="listing-location-divider" aria-hidden="true"></span><span>Near <?= htmlspecialchars($listing['city']) ?> student campuses</span>
                    </div>
                </div>
                <div class="listing-title-actions">
                    <?php if ($canAccessStudentActions): ?>
                        <form action="actions/save_listing.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="listing_id" value="<?= $listingId ?>">
                            <button class="listing-pill-action<?= $isSaved ? ' is-saved' : '' ?>" type="submit"><span aria-hidden="true">&#9829;</span><span><?= $isSaved ? 'Saved' : 'Save' ?></span></button>
                        </form>
                    <?php endif; ?>
                    <button class="listing-icon-action" type="button" data-share-listing aria-label="Share listing" title="Share listing"><span aria-hidden="true">&#8599;</span></button>
                </div>
            </section>

            <div class="listing-detail-grid">
                <div class="listing-left-column">
                    <section class="listing-gallery" data-listing-gallery aria-label="Listing photo gallery">
                        <?php if ($photos): ?>
                            <div class="listing-gallery-main">
                                <img id="listing-main-photo" src="<?= htmlspecialchars($photos[0]['photo_url']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>">
                                <?php if (count($photos) > 1): ?>
                                    <button class="listing-gallery-arrow listing-gallery-arrow--previous" type="button" data-gallery-direction="-1" aria-label="Previous photo">&#8249;</button>
                                    <button class="listing-gallery-arrow listing-gallery-arrow--next" type="button" data-gallery-direction="1" aria-label="Next photo">&#8250;</button>
                                <?php endif; ?>
                                <span class="listing-photo-caption"><?= htmlspecialchars(listingLabel($listing['room_type'])) ?> view</span>
                            </div>
                            <?php if (count($photos) > 1): ?>
                                <div class="listing-thumbnails">
                                    <?php foreach ($photos as $index => $photo): ?>
                                        <button class="listing-thumbnail<?= $index === 0 ? ' is-active' : '' ?>" type="button" data-photo-index="<?= $index ?>" data-photo-src="<?= htmlspecialchars($photo['photo_url']) ?>" data-photo-alt="<?= htmlspecialchars($listing['title'] . ' photo ' . ($index + 1)) ?>" aria-label="Show photo <?= $index + 1 ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                                            <img src="<?= htmlspecialchars($photo['photo_url']) ?>" alt=""><span>Photo <?= $index + 1 ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="listing-gallery-main listing-photo-fallback">Photo unavailable</div>
                        <?php endif; ?>
                    </section>

                    <section class="listing-panel listing-safety-panel">
                        <div class="listing-panel-heading">
                            <div class="listing-heading-with-icon"><span class="listing-section-icon" aria-hidden="true">&#10003;</span><h2>Verified Safety &amp; Security</h2></div>
                            <span class="listing-badge listing-badge--verified">Inspection passed</span>
                        </div>
                        <ul class="listing-safety-list">
                            <li><span aria-hidden="true">&#10003;</span><p>The property address and room details were reviewed before publication.</p></li>
                            <li><span aria-hidden="true">&#10003;</span><p>The landlord account is linked to a verified BoardNest profile.</p></li>
                            <li><span aria-hidden="true">&#10003;</span><p>Safety concerns can be reported to BoardNest for field-agent follow-up.</p></li>
                            <li><span aria-hidden="true">&#10003;</span><p>Only approved listings are shown to students in browse results.</p></li>
                        </ul>
                    </section>

                    <section class="listing-panel listing-description-panel">
                        <h2>About this boarding place</h2><p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
                    </section>
                </div>

                <aside class="listing-right-column">
                    <section class="listing-panel listing-booking-panel">
                        <div class="listing-price-header">
                            <div><span class="listing-eyebrow">Monthly rental</span><p class="listing-price">LKR <?= number_format((float) $listing['monthly_rent'], 0) ?><span>/ month</span></p></div>
                            <span class="listing-availability-pill"><span aria-hidden="true"></span><?= (int) $listing['available_slots'] ?> <?= (int) $listing['available_slots'] === 1 ? 'place' : 'places' ?> left</span>
                        </div>
                        <div class="listing-cost-grid">
                            <div><span class="listing-eyebrow">Rent includes</span><strong><?= (int) $listing['wifi'] === 1 ? 'WiFi included' : 'Room rental' ?></strong><small>Utility terms are confirmed with the landlord.</small></div>
                            <div><span class="listing-eyebrow">Security deposit</span><strong><?= $listing['deposit'] !== null ? 'LKR ' . number_format((float) $listing['deposit'], 0) : 'Confirm with landlord' ?></strong><small>Refund terms apply at checkout.</small></div>
                        </div>
                        <?php if ($listing['room_type'] === 'shared' && (int) $listing['partial_occupancy'] === 1): ?>
                            <?php $partialPrice = (float) $listing['monthly_rent'] * 0.75; ?>
                            <div class="listing-pricing-note"><strong>Partial occupancy pricing</strong><span>Your estimated share is LKR <?= number_format($partialPrice, 0) ?> until the room is fully occupied.</span></div>
                        <?php endif; ?>
                        <div class="listing-booking-actions">
                            <?php if ($canAccessStudentActions): ?>
                                <a class="listing-button listing-button--primary" href="../../modules/booking/views/student/booking_create.php?listing_id=<?= $listingId ?>">Request to book</a>
                                <a class="listing-button listing-button--outline" href="tel:<?= htmlspecialchars($listing['landlord_mobile']) ?>">Contact landlord</a>
                            <?php else: ?>
                                <a class="listing-button listing-button--primary" href="../../login.php">Log in to request</a>
                                <a class="listing-button listing-button--outline" href="../../login.php">Log in to contact</a>
                            <?php endif; ?>
                        </div>
                        <?php if (!$canAccessStudentActions): ?><p class="listing-login-note">Student actions and contact details become available after login.</p><?php endif; ?>
                        <div class="listing-landlord-row">
                            <span class="listing-landlord-avatar" aria-hidden="true"><?= htmlspecialchars(listingInitials($listing['landlord_name'])) ?></span>
                            <div><div class="listing-landlord-name"><strong><?= htmlspecialchars($listing['landlord_name']) ?></strong><span>Verified landlord</span></div><p>BoardNest property host</p></div>
                            <?php if ($canAccessStudentActions): ?><a class="listing-call-button" href="tel:<?= htmlspecialchars($listing['landlord_mobile']) ?>" aria-label="Call landlord" title="Call landlord">&#9742;</a><?php endif; ?>
                        </div>
                    </section>

                    <section class="listing-panel listing-specifications-panel">
                        <div class="listing-panel-heading"><div class="listing-heading-with-icon"><span class="listing-section-icon" aria-hidden="true">i</span><h2>Detailed Specifications</h2></div></div>
                        <div class="listing-specification-list">
                            <div class="listing-specification"><span class="listing-spec-icon" aria-hidden="true">R</span><div><strong>Room type &amp; occupancy</strong><p><?= htmlspecialchars(listingLabel($listing['room_type'])) ?> for <?= htmlspecialchars(listingLabel($listing['gender_pref'])) ?>; <?= (int) $listing['available_slots'] ?> of <?= (int) $listing['slot_cap'] ?> places available.</p></div></div>
                            <div class="listing-specification"><span class="listing-spec-icon" aria-hidden="true">F</span><div><strong>Furnishing &amp; study setup</strong><p><?= htmlspecialchars(listingLabel($listing['furnishing'])) ?> room<?= $listing['sq_footage'] ? ' with approximately ' . (int) $listing['sq_footage'] . ' sq ft' : '' ?>.</p></div></div>
                            <div class="listing-specification"><span class="listing-spec-icon" aria-hidden="true">B</span><div><strong>Bathroom details</strong><p><?= htmlspecialchars(listingLabel($listing['bathroom_type'])) ?> bathroom arrangement.</p></div></div>
                            <div class="listing-specification"><span class="listing-spec-icon" aria-hidden="true">L</span><div><strong>Location &amp; commute</strong><p><?= htmlspecialchars($listing['address']) ?>, with student services and public transport available around <?= htmlspecialchars($listing['city']) ?>.</p></div></div>
                            <div class="listing-specification"><span class="listing-spec-icon" aria-hidden="true">A</span><div><strong>House amenities &amp; shared facilities</strong>
                                <?php if ($facilities): ?><ul class="listing-amenity-list"><?php foreach ($facilities as $facility): ?><li><span aria-hidden="true">&#10003;</span><?= htmlspecialchars($facility) ?></li><?php endforeach; ?></ul><?php else: ?><p>No shared facilities have been listed.</p><?php endif; ?>
                            </div></div>
                            <div class="listing-specification listing-specification--rules"><span class="listing-spec-icon" aria-hidden="true">!</span><div><strong>House rules</strong><div class="listing-rule-list">
                                <?php if ($houseRules): ?><?php foreach ($houseRules as $rule): ?><span><?= htmlspecialchars($rule) ?></span><?php endforeach; ?><?php else: ?><span>Confirm house rules before booking</span><?php endif; ?>
                            </div></div></div>
                        </div>
                        <?php if (!empty($listing['maps_url'])): ?><a class="listing-text-link" href="<?= htmlspecialchars($listing['maps_url']) ?>" target="_blank" rel="noopener noreferrer">View location on map <span aria-hidden="true">&#8599;</span></a><?php endif; ?>
                    </section>
                </aside>
            </div>

            <?php if ($listing['room_type'] === 'shared' && (int) $listing['partial_occupancy'] === 1 && $occupant): ?>
                <section class="listing-panel listing-roommate-panel">
                    <div><span class="listing-eyebrow">Shared room</span><h2>Meet your potential roommate</h2><p>One student already occupies this room. Review the profile summary before making a request.</p></div>
                    <div class="listing-roommate-profile"><span class="listing-landlord-avatar" aria-hidden="true"><?= htmlspecialchars(listingInitials($occupant['full_name'])) ?></span><div><strong><?= htmlspecialchars($occupant['full_name']) ?></strong><p><?= htmlspecialchars($occupant['university'] ?: 'University not provided') ?> &middot; <?= htmlspecialchars($occupant['academic_year'] ?: 'Academic year not provided') ?></p></div></div>
                </section>
            <?php endif; ?>

            <section class="listing-reviews-section">
                <div class="listing-reviews-heading">
                    <div><span class="listing-eyebrow">Resident feedback</span><h2>Student Reviews</h2></div>
                    <?php if ($detail['avg_rating'] !== null): ?><div class="listing-rating-summary"><span><?= listingStars((int) round($detail['avg_rating'])) ?></span><strong><?= number_format($detail['avg_rating'], 1) ?></strong><small><?= $detail['review_count'] ?> reviews</small></div><?php endif; ?>
                </div>
                <?php if (!$reviews): ?>
                    <div class="listing-panel listing-empty-reviews"><h3>No reviews yet</h3><p>Students who complete a stay can share their experience here.</p></div>
                <?php else: ?>
                    <div class="listing-review-grid">
                        <?php foreach ($reviews as $review): ?>
                            <article class="listing-panel listing-review-card">
                                <div class="listing-review-meta"><span class="listing-landlord-avatar" aria-hidden="true"><?= htmlspecialchars(listingInitials($review['reviewer_name'])) ?></span><div><h3><?= htmlspecialchars($review['reviewer_name']) ?></h3><time datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($review['created_at']))) ?>"><?= htmlspecialchars(date('M j, Y', strtotime($review['created_at']))) ?></time></div><span class="listing-review-stars"><?= listingStars((int) $review['rating']) ?></span></div>
                                <p><?= nl2br(htmlspecialchars($review['review_text'] ?: 'No written comment.')) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="listing-footer"><div class="listing-shell"><strong>BoardNest</strong><span>Verified student boarding, made easier.</span></div></footer>

    <script>
        (() => {
            const mainPhoto = document.getElementById('listing-main-photo');
            const thumbnails = Array.from(document.querySelectorAll('[data-photo-index]'));
            let activeIndex = 0;

            const showPhoto = (index) => {
                if (!mainPhoto || thumbnails.length === 0) return;
                activeIndex = (index + thumbnails.length) % thumbnails.length;
                const thumbnail = thumbnails[activeIndex];
                mainPhoto.src = thumbnail.dataset.photoSrc;
                mainPhoto.alt = thumbnail.dataset.photoAlt;
                thumbnails.forEach((button, buttonIndex) => {
                    const active = buttonIndex === activeIndex;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            };

            thumbnails.forEach((button, index) => button.addEventListener('click', () => showPhoto(index)));
            document.querySelectorAll('[data-gallery-direction]').forEach((button) => button.addEventListener('click', () => showPhoto(activeIndex + Number(button.dataset.galleryDirection))));

            if (mainPhoto) {
                mainPhoto.addEventListener('error', () => {
                    const container = mainPhoto.parentElement;
                    mainPhoto.remove();
                    container.classList.add('listing-photo-fallback');
                    container.append('Photo unavailable');
                }, { once: true });
            }

            const shareButton = document.querySelector('[data-share-listing]');
            if (shareButton) {
                shareButton.addEventListener('click', async () => {
                    try {
                        if (navigator.share) await navigator.share({ title: document.title, url: window.location.href });
                        else {
                            await navigator.clipboard.writeText(window.location.href);
                            shareButton.setAttribute('title', 'Link copied');
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') shareButton.setAttribute('title', 'Unable to share');
                    }
                });
            }
        })();
    </script>
</body>
</html>
