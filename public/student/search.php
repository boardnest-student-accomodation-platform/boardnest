<?php
require_once '../../includes/session.php';
require_once '../../config/db.php';
require_once '../../modules/listings/services/search.php';

startSession();

$filters = studentSearchFilters($_GET);
$browseError = '';
$results = ['items' => [], 'total' => 0, 'page' => 1, 'page_count' => 1];
$universities = [];
$savedListingIds = [];

try {
    $results = studentBrowseListings($pdo, $filters);
    $universities = studentBrowseUniversities($pdo);
    $savedListingIds = studentSavedListingIds($pdo, isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
} catch (PDOException $exception) {
    $browseError = 'The boarding-place catalogue is not available yet. Import the latest schema.sql file and refresh this page.';
}

$universities = array_values(array_unique(array_merge(
    [
        'University of Moratuwa',
        'University of Colombo',
        'SLIIT Malabe',
        'NSBM Green University',
    ],
    $universities
)));

$flashSuccess = $_SESSION['success'] ?? '';
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$queryWithoutPage = $_GET;
unset($queryWithoutPage['page']);
$queryString = (string) ($_SERVER['QUERY_STRING'] ?? '');
$currentReturnUrl = '../search.php' . ($queryString !== '' ? '?' . $queryString : '');
$activeFilterCount = count($filters['universities'])
    + count($filters['room_types'])
    + ($filters['group_size'] > 1 ? 1 : 0)
    + ($filters['q'] !== '' ? 1 : 0)
    + (($filters['min_price'] !== 10000 || $filters['max_price'] !== 45000) ? 1 : 0);
$searchContext = $filters['universities'][0] ?? ($filters['q'] !== '' ? $filters['q'] : 'Sri Lankan campuses');

function searchPageUrl(array $changes = []): string
{
    $query = array_merge($_GET, $changes);
    foreach ($query as $key => $value) {
        if ($value === '' || $value === []) {
            unset($query[$key]);
        }
    }
    return 'search.php' . ($query ? '?' . http_build_query($query) : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Student Boarding Places | BoardNest</title>
    <meta name="description" content="Search verified student boarding places near Sri Lankan universities.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body class="student-search-page">
    <header class="student-header">
        <a class="student-brand" href="../../index.html" aria-label="BoardNest home">
            <span class="student-brand-mark" aria-hidden="true">B</span>
            <span>BoardNest</span>
        </a>

        <nav class="student-primary-nav" aria-label="Primary navigation">
            <a class="is-active" href="search.php">Browse</a>
            <a href="../../index.html#verification-heading">How It Works</a>
        </nav>

        <div class="student-header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="student-login-link" href="saved.php">Saved</a>
                <a class="student-login-link" href="dashboard.php">Dashboard</a>
            <?php else: ?>
                <a class="student-login-link" href="../../login.php">Login</a>
                <a class="student-signup-button" href="register.php">Sign Up</a>
            <?php endif; ?>
            <a class="student-account-icon" href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : '../../login.php' ?>" aria-label="Account" title="Account">U</a>
            <button class="student-icon-button student-menu-button" type="button" aria-label="Open navigation menu" title="Menu" data-mobile-menu-button>
                <span class="material-symbols-rounded" aria-hidden="true">&#9776;</span>
            </button>
        </div>
    </header>

    <nav class="student-mobile-nav" aria-label="Mobile navigation" data-mobile-menu hidden>
        <a href="search.php">Browse</a>
        <a href="../../index.html#verification-heading">How It Works</a>
        <a href="../../login.php">Login</a>
    </nav>

    <section class="student-search-band" aria-label="Search boarding places">
        <form class="student-main-search" method="get" action="search.php">
            <img class="student-search-icon" src="../assets/uploads/search_icon.svg" alt="" aria-hidden="true">
            <label class="sr-only" for="boardingSearch">Search by university, city, or property</label>
            <input id="boardingSearch" name="q" type="search" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Search by university, city, or area...">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($filters['mode']) ?>">
            <button class="student-search-button" type="submit">Search <span aria-hidden="true">&#8594;</span></button>
            <button class="student-filter-button" type="button" data-filter-open>
                <span class="material-symbols-rounded" aria-hidden="true">&#9881;</span>
                Filters
            </button>
        </form>

        <div class="student-search-chips" aria-label="Search filters">
            <button class="student-filter-summary" type="button" data-filter-open>All Filters (<?= $activeFilterCount ?>)</button>
            <?php foreach ($filters['universities'] as $index => $university): ?>
                <?php $remainingUniversities = $filters['universities']; unset($remainingUniversities[$index]); ?>
                <a class="student-filter-chip" href="<?= htmlspecialchars(searchPageUrl(['universities' => array_values($remainingUniversities), 'page' => 1])) ?>"><?= htmlspecialchars($university) ?> <span aria-hidden="true">&times;</span></a>
            <?php endforeach; ?>
            <?php foreach ($filters['room_types'] as $index => $roomType): ?>
                <?php $remainingRoomTypes = $filters['room_types']; unset($remainingRoomTypes[$index]); ?>
                <a class="student-filter-chip" href="<?= htmlspecialchars(searchPageUrl(['room_types' => array_values($remainingRoomTypes), 'page' => 1])) ?>"><?= htmlspecialchars(studentRoomTypeLabel($roomType)) ?> <span aria-hidden="true">&times;</span></a>
            <?php endforeach; ?>
            <?php if ($filters['min_price'] !== 10000 || $filters['max_price'] !== 45000): ?>
                <a class="student-filter-chip" href="<?= htmlspecialchars(searchPageUrl(['min_price' => 10000, 'max_price' => 45000, 'page' => 1])) ?>">LKR <?= number_format($filters['min_price']) ?> - <?= number_format($filters['max_price']) ?> <span aria-hidden="true">&times;</span></a>
            <?php endif; ?>
            <a class="student-quick-chip" href="#studentGroupSize">Beds</a>
            <a class="student-quick-chip" href="#roomTypeFilters">Room Type</a>
            <a class="student-quick-chip" href="#priceFilters">Price</a>
        </div>
    </section>

    <main class="student-browse-shell">
        <aside class="student-filters" id="studentFilters" aria-label="Search filters" data-filter-panel>
            <div class="student-filter-mobile-header">
                <h2>Filters</h2>
                <button class="student-icon-button" type="button" aria-label="Close filters" title="Close filters" data-filter-close>
                    <span class="material-symbols-rounded" aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="get" action="search.php" class="student-filter-form">
                <input type="hidden" name="q" value="<?= htmlspecialchars($filters['q']) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($filters['sort']) ?>">

                <fieldset class="student-filter-section">
                    <legend>Search Mode</legend>
                    <div class="student-segmented-control">
                        <label>
                            <input type="radio" name="mode" value="university" <?= $filters['mode'] === 'university' ? 'checked' : '' ?>>
                            <span>By University</span>
                        </label>
                        <label>
                            <input type="radio" name="mode" value="city" <?= $filters['mode'] === 'city' ? 'checked' : '' ?>>
                            <span>By City/Area</span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="student-filter-section">
                    <legend>University</legend>
                    <label class="student-university-search">
                        <img class="student-inline-search-icon" src="../assets/uploads/search_icon.svg" alt="" aria-hidden="true">
                        <span class="sr-only">Find university</span>
                        <input type="search" placeholder="Find university..." data-university-search>
                    </label>
                    <div class="student-checkbox-list">
                        <?php foreach ($universities as $university): ?>
                            <label class="student-check-row" data-university-option>
                                <input type="checkbox" name="universities[]" value="<?= htmlspecialchars($university) ?>" <?= in_array($university, $filters['universities'], true) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($university) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="student-filter-section" id="priceFilters">
                    <legend>Price Range / Month</legend>
                    <div class="student-price-range-labels"><span>LKR <?= number_format($filters['min_price']) ?></span><span>LKR <?= number_format($filters['max_price']) ?><?= $filters['max_price'] >= 100000 ? '+' : '' ?></span></div>
                    <div class="student-range-track" aria-hidden="true"><span></span><i></i></div>
                    <div class="student-price-inputs">
                        <label>
                            <span>Min</span>
                            <input type="number" name="min_price" min="0" step="1000" value="<?= (int) $filters['min_price'] ?>">
                        </label>
                        <span class="student-price-divider">to</span>
                        <label>
                            <span>Max</span>
                            <input type="number" name="max_price" min="0" step="1000" value="<?= (int) $filters['max_price'] ?>">
                        </label>
                    </div>
                </fieldset>

                <fieldset class="student-filter-section" id="roomTypeFilters">
                    <legend>Room Type</legend>
                    <div class="student-checkbox-list">
                        <?php foreach (['single_room' => 'Single Room', 'shared_room' => 'Shared Room', 'entire_boarding' => 'Entire Boarding', 'annex' => 'Annex'] as $value => $label): ?>
                            <label class="student-check-row">
                                <input type="checkbox" name="room_types[]" value="<?= $value ?>" <?= in_array($value, $filters['room_types'], true) ? 'checked' : '' ?>>
                                <span><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="student-filter-section" id="studentGroupSize">
                    <legend>Group Size</legend>
                    <label class="student-number-control">
                        <span>Students</span>
                        <input type="number" name="group_size" min="1" max="20" value="<?= (int) $filters['group_size'] ?>">
                    </label>
                </fieldset>

                <button class="student-apply-filters" type="submit">Show Results</button>
                <a class="student-reset-filters" href="search.php">Reset all filters</a>
            </form>
        </aside>
        <div class="student-filter-backdrop" data-filter-close hidden></div>

        <section class="student-results" aria-labelledby="resultsHeading">
            <?php if ($flashSuccess || $flashError || $browseError): ?>
                <div class="student-alert <?= ($flashError || $browseError) ? 'student-alert-error' : 'student-alert-success' ?>" role="status">
                    <?= htmlspecialchars($flashError ?: ($browseError ?: $flashSuccess)) ?>
                </div>
            <?php endif; ?>

            <div class="student-results-toolbar">
                <div>
                    <h1 id="resultsHeading">Showing <?= number_format($results['total']) ?> verified <?= $results['total'] === 1 ? 'listing' : 'listings' ?></h1>
                    <p><span aria-hidden="true">&#10148;</span> near <?= htmlspecialchars($searchContext) ?></p>
                </div>
                <form class="student-sort-form" method="get" action="search.php">
                    <?php foreach ($queryWithoutPage as $key => $value): ?>
                        <?php if ($key !== 'sort'): ?>
                            <?php if (is_array($value)): ?>
                                <?php foreach ($value as $arrayValue): ?>
                                    <input type="hidden" name="<?= htmlspecialchars($key) ?>[]" value="<?= htmlspecialchars((string) $arrayValue) ?>">
                                <?php endforeach; ?>
                            <?php else: ?>
                                <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars((string) $value) ?>">
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <label for="listingSort">Sort by</label>
                    <select id="listingSort" name="sort" data-auto-submit>
                        <option value="recommended" <?= $filters['sort'] === 'recommended' ? 'selected' : '' ?>>Recommended</option>
                        <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="distance" <?= $filters['sort'] === 'distance' ? 'selected' : '' ?>>Nearest First</option>
                        <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
                    </select>
                </form>
            </div>

            <?php if (!$browseError && !$results['items']): ?>
                <div class="student-empty-state">
                    <img class="student-empty-search-icon" src="../assets/uploads/search_icon.svg" alt="" aria-hidden="true">
                    <h2>No boarding places match these filters</h2>
                    <p>Try increasing the price range or removing one of the selected filters.</p>
                    <a href="search.php">Clear filters</a>
                </div>
            <?php endif; ?>

            <div class="student-listing-grid">
                <?php foreach ($results['items'] as $listing): ?>
                    <?php $isSaved = in_array((int) $listing['listing_id'], $savedListingIds, true); ?>
                    <article class="student-listing-card">
                        <a class="student-listing-image-link" href="listing.php?id=<?= (int) $listing['listing_id'] ?>" aria-label="View <?= htmlspecialchars($listing['title']) ?>">
                            <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" loading="lazy">
                            <?php if ($listing['room_type'] !== 'shared_room' && (int) $listing['is_field_verified'] === 1): ?>
                                <span class="student-verification-badge"><span aria-hidden="true">&#10003;</span> Field Verified</span>
                            <?php elseif ((int) $listing['beds_available'] === 1): ?>
                                <span class="student-availability-badge">1 Bed Available</span>
                            <?php endif; ?>
                        </a>

                        <form class="student-save-form" action="actions/save_listing.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="listing_id" value="<?= (int) $listing['listing_id'] ?>">
                            <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentReturnUrl) ?>">
                            <button class="student-save-button <?= $isSaved ? 'is-saved' : '' ?>" type="submit" aria-label="<?= $isSaved ? 'Saved listing' : 'Save listing' ?>" title="<?= $isSaved ? 'Saved listing' : 'Save listing' ?>">
                                <span class="material-symbols-rounded" aria-hidden="true">&#9829;</span>
                            </button>
                        </form>

                        <div class="student-listing-content">
                            <div class="student-listing-heading">
                                <div>
                                    <h2><a href="listing.php?id=<?= (int) $listing['listing_id'] ?>"><?= htmlspecialchars($listing['title']) ?></a></h2>
                                    <p><?= htmlspecialchars($listing['city']) ?></p>
                                </div>
                                <?php if ((float) $listing['rating'] > 0): ?>
                                    <span class="student-rating"><span class="material-symbols-rounded" aria-hidden="true">&#9733;</span><?= number_format((float) $listing['rating'], 1) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="student-tag-row">
                                <span><?= htmlspecialchars(studentRoomTypeLabel($listing['room_type'])) ?></span>
                                <span><?= htmlspecialchars(studentGenderLabel($listing['gender_preference'])) ?></span>
                            </div>

                            <div class="student-price">
                                <strong>LKR <?= number_format((int) $listing['monthly_rent']) ?></strong>
                                <span>/mo</span>
                            </div>

                            <div class="student-listing-facts">
                                <span><span aria-hidden="true">&#128694;</span><?= (int) $listing['distance_m'] > 0 ? htmlspecialchars(studentDistanceLabel((int) $listing['distance_m'])) : 'Near campus' ?></span>
                                <span><span aria-hidden="true">&#9788;</span><?= htmlspecialchars($listing['wifi_label']) ?></span>
                                <span><span aria-hidden="true">&#9638;</span><?= htmlspecialchars($listing['bathroom_label']) ?></span>
                            </div>

                            <div class="student-listing-footer">
                                <form class="student-card-save-form" action="actions/save_listing.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="listing_id" value="<?= (int) $listing['listing_id'] ?>">
                                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentReturnUrl) ?>">
                                    <button type="submit"><?= $isSaved ? 'Saved' : 'Save' ?></button>
                                </form>
                                <a class="student-details-button" href="listing.php?id=<?= (int) $listing['listing_id'] ?>">View Details</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($results['page_count'] > 1): ?>
                <nav class="student-pagination" aria-label="Search result pages">
                    <?php if ($results['page'] > 1): ?>
                        <a href="<?= htmlspecialchars(searchPageUrl(['page' => $results['page'] - 1])) ?>" aria-label="Previous page"><span class="material-symbols-rounded" aria-hidden="true">&lsaquo;</span></a>
                    <?php endif; ?>
                    <?php for ($page = 1; $page <= $results['page_count']; $page++): ?>
                        <a class="<?= $page === $results['page'] ? 'is-current' : '' ?>" href="<?= htmlspecialchars(searchPageUrl(['page' => $page])) ?>" <?= $page === $results['page'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                    <?php endfor; ?>
                    <?php if ($results['page'] < $results['page_count']): ?>
                        <a href="<?= htmlspecialchars(searchPageUrl(['page' => $results['page'] + 1])) ?>" aria-label="Next page"><span class="material-symbols-rounded" aria-hidden="true">&rsaquo;</span></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>
    </main>

    <footer class="student-footer">
        <a class="student-brand" href="../../index.html"><span class="student-brand-mark" aria-hidden="true">B</span><span>BoardNest</span></a>
        <p>Verified student housing, made easier.</p>
        <nav aria-label="Legal links"><a href="#">Privacy</a><a href="#">Terms</a><a href="../../index.html#contact">Support</a></nav>
        <span>&copy; <?= date('Y') ?> BoardNest</span>
    </footer>

    <script src="../assets/js/student-search.js"></script>
</body>
</html>
