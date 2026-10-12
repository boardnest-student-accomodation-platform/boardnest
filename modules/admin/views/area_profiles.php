<?php

require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';

/* Filters 

$search = trim($_GET['search'] ?? '');
$region = trim($_GET['region'] ?? '');
$status = trim($_GET['status'] ?? ''); */

/* Review Required Count

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM area_profiles
    WHERE safety_classification = 'Under Review'
");

$reviewRequiredCount = $stmt->fetchColumn();*/

/* Regions

$stmt = $pdo->query("
    SELECT DISTINCT province
    FROM cities
    WHERE province IS NOT NULL
      AND province <> ''
    ORDER BY province
");

$regions = $stmt->fetchAll();*/

/* Area Profiles

$sql = "
    SELECT
        c.id AS city_id,
        c.name AS city_name,
        c.district,
        c.province,

        ap.area_profile_id,
        ap.safety_classification,
        ap.transport_options,
        ap.amenities,
        ap.description,
        ap.updated_at

    FROM cities c

    LEFT JOIN area_profiles ap
        ON ap.city_id = c.id

    WHERE 1 = 1
";

$params = [];*/

/* Search 

if ($search !== '') {

    $sql .= "
        AND (
            c.name LIKE :search
            OR c.district LIKE :search
            OR c.province LIKE :search
        )
    ";

    $params['search'] = '%' . $search . '%';
}*/

/* Region Filter 

if ($region !== '') {

    $sql .= "
        AND c.province = :province
    ";

    $params['province'] = $region;
}*/

/* Status Filter 

if ($status !== '') {

    if ($status === 'Under Review') {

        $sql .= "
            AND (
                ap.safety_classification = 'Under Review'
                OR ap.area_profile_id IS NULL
            )
        ";

    } else {

        $sql .= "
            AND ap.safety_classification = :status
        ";

        $params['status'] = $status;
    }
}

$sql .= "
    ORDER BY c.name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$areaProfiles = $stmt->fetchAll();*/

/* Helper Functions 

function profileStatus(array $area): string
{
    if (
        empty($area['area_profile_id']) ||
        empty($area['safety_classification'])
    ) {
        return 'Under Review';
    }

    return $area['safety_classification'];
}

function statusClass(string $status): string
{
    switch ($status) {

        case 'Standard':
            return 'admin-area-status-standard';

        case 'Caution Advised':
            return 'admin-area-status-caution';

        default:
            return 'admin-area-status-review';
    }
}

function statusIcon(string $status): string
{
    switch ($status) {

        case 'Standard':
            return '✓';

        case 'Caution Advised':
            return '!';

        default:
            return '•';
    }
}

function splitTags(?string $value): array
{
    if (empty($value)) {
        return [];
    }

    $items = explode(',', $value);

    $items = array_map('trim', $items);

    return array_values(
        array_filter($items, function ($item) {
            return $item !== '';
        })
    );
}*/

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Area Profiles</title>

    <!-- Shared stylesheet -->
    <link
        rel="stylesheet"
        href="../../../public/assets/css/style.css">

    <!-- Admin-specific stylesheet -->
    <link
        rel="stylesheet"
        href="../../../public/assets/css/admin.css">

</head>

<body class="admin-body">

    <div class="admin-layout">

        <!-- ADMIN SIDEBAR -->

        <aside class="admin-sidebar">

            <div class="admin-sidebar-brand">

                <div class="admin-brand-icon">
                    B
                </div>

                <div>

                    <h2 class="admin-brand-title">
                        BoardNest
                    </h2>

                    <span class="admin-brand-subtitle">
                        Admin Portal
                    </span>

                </div>

            </div>


            <nav class="admin-navigation">

                <a
                    href="dashboard.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>


                <a
                    href="../../approvals/views/registration_approvals.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">✓</span>
                    <span>Registration Approvals</span>
                </a>


                <a
                    href="../../approvals/views/listings_decisions.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">▤</span>
                    <span>Listings</span>
                </a>


                <a
                    href="../../complaints/views/Complaints.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">⚖</span>
                    <span>Complaint Moderation</span>
                </a>


                <a
                    href="../../approvals/views/field_agent_manage.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">♙</span>
                    <span>Field Agents</span>
                </a>


                <a
                    href="area_profiles.php"
                    class="admin-nav-item admin-nav-item-active">
                    <span class="admin-nav-icon">⌖</span>
                    <span>Area Profiles</span>
                </a>


                <a
                    href="../../announcements/views/announcements.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">⚑</span>
                    <span>Send Announcement</span>
                </a>

            </nav>


            <div class="admin-sidebar-bottom">

                <a
                    href="../../../logout.php"
                    class="admin-nav-item admin-signout">
                    <span class="admin-nav-icon">↪</span>
                    <span>Sign Out</span>
                </a>

            </div>

        </aside>


        <!-- MAIN CONTENT -->

        <main class="admin-main">


            <!-- TOP BAR -->

            <header class="admin-topbar">

                <div class="admin-page-heading">

                    <h1>
                        Area Profiles
                    </h1>

                    <p>
                        Manage area information and student-focused area guides.
                    </p>

                </div>


                <div class="admin-topbar-actions">

                    <div class="admin-profile">

                        <div class="admin-profile-avatar">
                            A
                        </div>

                        <div class="admin-profile-details">

                            <span class="admin-profile-name">
                                Admin
                            </span>

                            <span class="admin-profile-role">
                                Administrator
                            </span>

                        </div>

                    </div>

                </div>

            </header>


            <!-- PAGE CONTENT -->

            <section class="admin-area-content">


                <!-- REVIEW BANNER 

            <?php if ($reviewRequiredCount > 0): ?>

                <div class="admin-area-review-banner">

                    <div class="admin-area-review-icon">
                        !
                    </div>

                    <div class="admin-area-review-text">

                        <strong>
                            Review Required
                        </strong>

                        <p>
                            <?= htmlspecialchars($reviewRequiredCount) ?>
                            area profile(s) are currently under review.
                        </p>

                    </div>

                    <a
                        href="?status=Under+Review"
                        class="admin-area-review-link"
                    >
                        View Reports
                    </a>

                </div>

            <?php endif; ?> -->


                <!-- FILTERS -->

                <form
                    method="GET"
                    class="admin-area-filters">

                    <div class="admin-area-filter-group">


                        <!-- Region -->

                        <select
                            name="region"
                            class="admin-area-filter-select">

                            <option value="">
                                All Regions
                            </option>

                            <?php foreach ($regions as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item['province']) ?>"
                                    <?= $region === $item['province'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['province']) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>


                        <!-- Status -->

                        <select
                            name="status"
                            class="admin-area-filter-select">

                            <option value="">
                                Any Status
                            </option>

                            <option
                                value="Standard"
                                <? //= $status === 'Standard' ? 'selected' : '' 
                                ?>>
                                Standard
                            </option>

                            <option
                                value="Caution Advised"
                                <? //= $status === 'Caution Advised' ? 'selected' : '' 
                                ?>>
                                Caution Advised
                            </option>

                            <option
                                value="Under Review"
                                <? //= $status === 'Under Review' ? 'selected' : '' 
                                ?>>
                                Under Review
                            </option>

                        </select>


                        <button
                            type="submit"
                            class="admin-area-filter-button">
                            Filter
                        </button>

                    </div>


                    <!--<div class="admin-area-search">

                    <span class="admin-area-search-icon">
                        🔍
                    </span>

                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search cities..."
                    >

                </div>-->

                </form>


                <!-- AREA CARD GRID -->

                <div class="admin-area-grid">


                    <?php if (empty($areaProfiles)): ?>

                        <div class="admin-area-empty">

                            <strong>
                                No areas found
                            </strong>

                            <p>
                                No cities match the selected filters.
                            </p>

                        </div>

                    <?php endif; ?>

                    <!-- Area Profile View -->



                    <!-- ADD NEW AREA -->

                    <button
                        type="button"
                        class="admin-area-add-card"
                        data-area-add>

                        <span class="admin-area-add-icon">
                            +
                        </span>

                        <span>
                            Add New Area
                        </span>

                    </button>

                </div>

            </section>


            <!-- EDIT PANEL -->

            <aside
                class="admin-area-panel"
                id="editPanel">


                <!-- Panel Header -->

                <div class="admin-area-panel-header">

                    <div>

                        <h2 id="panelTitle">
                            Edit Profile
                        </h2>

                        <p>
                            Update area information
                        </p>

                    </div>


                    <button
                        type="button"
                        class="admin-area-panel-close"
                        data-area-close>
                        ×
                    </button>

                </div>


                <!-- Panel Form -->

                <form
                    method="POST"
                    action="actions/save_area_profile.php"
                    id="areaProfileForm"
                    class="admin-area-panel-form">

                    <input
                        type="hidden"
                        name="city_id"
                        id="areaCityId">


                    <!-- City -->

                    <div class="admin-area-form-group">

                        <label for="areaCity">
                            Area / City
                        </label>

                        <select
                            name="city_id"
                            id="areaCity"
                            required>

                            <option value="">
                                Select city
                            </option>

                            <?php

                            $stmt = $pdo->query("
                            SELECT id, name
                            FROM cities
                            ORDER BY name ASC
                        ");

                            $allCities = $stmt->fetchAll();

                            foreach ($allCities as $city):

                            ?>

                                <option
                                    value="<?= htmlspecialchars($city['id']) ?>">
                                    <?= htmlspecialchars($city['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Safety Classification -->

                    <div class="admin-area-form-group">

                        <label for="safetySelect">
                            Safety Classification
                        </label>

                        <select
                            name="safety_classification"
                            id="safetySelect"
                            required>

                            <option value="Standard">
                                Standard
                            </option>

                            <option value="Caution Advised">
                                Caution Advised
                            </option>

                            <option value="Under Review">
                                Under Review
                            </option>

                        </select>

                    </div>


                    <!-- Transport -->

                    <div class="admin-area-form-group">

                        <label>
                            Transport Options
                        </label>


                        <div class="admin-area-checkbox-grid">

                            <label>
                                <input
                                    type="checkbox"
                                    name="transport_options[]"
                                    value="Bus Route"
                                    data-transport-option>

                                <span>
                                    Bus Route
                                </span>
                            </label>


                            <label>
                                <input
                                    type="checkbox"
                                    name="transport_options[]"
                                    value="Train Station"
                                    data-transport-option>

                                <span>
                                    Train Station
                                </span>
                            </label>


                            <label>
                                <input
                                    type="checkbox"
                                    name="transport_options[]"
                                    value="Three-wheelers"
                                    data-transport-option>

                                <span>
                                    Three-wheelers
                                </span>
                            </label>

                        </div>

                    </div>


                    <!-- Amenities -->

                    <div class="admin-area-form-group">

                        <label>
                            Key Amenities
                        </label>


                        <div class="admin-area-amenity-grid">

                            <label>
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="Grocery"
                                    data-amenity-option>

                                <span>
                                    Grocery
                                </span>
                            </label>


                            <label>
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="Pharmacy"
                                    data-amenity-option>

                                <span>
                                    Pharmacy
                                </span>
                            </label>


                            <label>
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="Restaurant"
                                    data-amenity-option>

                                <span>
                                    Restaurant
                                </span>
                            </label>


                            <label>
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="ATM"
                                    data-amenity-option>

                                <span>
                                    ATM
                                </span>
                            </label>

                        </div>

                    </div>


                    <!-- Description -->

                    <div class="admin-area-form-group">

                        <label for="areaDescription">
                            Area Description
                        </label>

                        <textarea
                            name="description"
                            id="areaDescription"
                            rows="5"
                            placeholder="Enter a brief overview of the area covering general vibe and convenience for students..."></textarea>

                    </div>


                    <!-- Footer -->

                    <div class="admin-area-panel-footer">

                        <button
                            type="submit"
                            class="admin-area-publish-button">

                            <span>
                                ✓
                            </span>

                            Publish Updates

                        </button>

                    </div>

                </form>

            </aside>

        </main>

    </div>


    <script src="../../../public/assets/js/admin.js"></script>

</body>

</html>