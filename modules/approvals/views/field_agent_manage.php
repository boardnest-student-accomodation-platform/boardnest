<?php
require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';

/*
|--------------------------------------------------------------------------
| Active / registered field agents
| Uses the BoardNest field_agents + users tables.
| Additional values:
| - Verifications -> verification_reports
| - Rating        -> agent_performance_ratings
|
| Last Active is not displayed from the database because there is no
| confirmed last_active column in the current schema.
|--------------------------------------------------------------------------
*/

/*$sql = "
    SELECT
        fa.agent_id,
        fa.user_id,
        fa.assigned_city,
        fa.is_active,
        fa.recruit_mode,

        u.full_name,
        u.email,
        u.status AS user_status,
        u.created_at,

        COUNT(DISTINCT vr.id) AS verification_count,

        ROUND(AVG(apr.rating_score), 1) AS average_rating

    FROM field_agents fa

    INNER JOIN users u
        ON u.user_id = fa.user_id

    LEFT JOIN verification_reports vr
        ON vr.field_agent_user_id = fa.user_id

    LEFT JOIN agent_performance_ratings apr
        ON apr.field_agent_user_id = fa.user_id

    GROUP BY
        fa.agent_id,
        fa.user_id,
        fa.assigned_city,
        fa.is_active,
        fa.recruit_mode,
        u.full_name,
        u.email,
        u.status,
        u.created_at

    ORDER BY
        fa.is_active DESC,
        u.full_name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$agents = $stmt->fetchAll();*/

/*
|--------------------------------------------------------------------------
| Helper functions
|--------------------------------------------------------------------------
*/

/*function getInitials($name)
{
    $name = trim($name);

    if ($name === '') {
        return 'FA';
    }

    $parts = preg_split('/\s+/', $name);

    if (count($parts) === 1) {
        return strtoupper(substr($parts[0], 0, 2));
    }

    return strtoupper(
        substr($parts[0], 0, 1) .
        substr($parts[count($parts) - 1], 0, 1)
    );
}

function formatMode($mode)
{
    if ($mode === 'self_registered') {
        return 'Self Registered';
    }

    if ($mode === 'admin_created') {
        return 'Admin Created';
    }

    return ucfirst(str_replace('_', ' ', $mode));
}

function formatDate($date)
{
    if (empty($date)) {
        return '—';
    }

    return date('d M Y', strtotime($date));
}

function renderStars($rating)
{
    if ($rating === null) {
        return '<span class="admin-agent-no-rating">Not rated</span>';
    }

    $rating = (float) $rating;
    $html = '<div class="admin-agent-stars" aria-label="Rating ' .
        htmlspecialchars($rating) . ' out of 5">';

    for ($i = 1; $i <= 5; $i++) {

        if ($rating >= $i) {
            $icon = 'star';
            $class = 'admin-agent-star--filled';

        } elseif ($rating >= ($i - 0.5)) {
            $icon = 'star_half';
            $class = 'admin-agent-star--filled';

        } else {
            $icon = 'star';
            $class = 'admin-agent-star--empty';
        }

        $html .= '
            <span class="material-symbols-outlined admin-agent-star ' .
            $class . '">' . $icon . '</span>
        ';
    }

    $html .= '
        <span class="admin-agent-rating-number">' .
        htmlspecialchars($rating) .
        '</span>
    </div>';

    return $html;
}*/

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Field Agents - BoardNest Admin</title>

    <!-- BoardNest CSS only -->
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">

    <!-- Material Symbols are rendered as text fallback if unavailable -->
    <style>
        .material-symbols-outlined {
            font-family: Arial, sans-serif;
            font-size: 20px;
            line-height: 1;
        }
    </style>
</head>

<body>

    <div class="admin-layout">

        <!-- SIDEBAR -->

        <aside class="admin-sidebar">

            <div class="admin-sidebar-brand">
                <div class="admin-brand-icon">B</div>

                <div>
                    <h2 class="admin-brand-title">BoardNest</h2>
                    <span class="admin-brand-subtitle">Admin Portal</span>
                </div>
            </div>

            <nav class="admin-navigation">

                <a href="../../admin/views/dashboard.php"
                    class="admin-nav-item">
                    <span class="admin-nav-icon">⌂</span>
                    <span>Dashboard</span>
                </a>

                <a href="registration_approvals.php" class="admin-nav-item">
                    <span class="admin-nav-icon">✓</span>
                    <span>Registration Approvals</span>
                </a>

                <a href="listings_decisions.php" class="admin-nav-item">
                    <span class="admin-nav-icon">▤</span>
                    <span>Listings</span>
                </a>

                <a href="../../complaints/views/complaints.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚖</span>
                    <span>Complaint Moderation</span>
                </a>

                <a href="field_agent_manage.php" class="admin-nav-item admin-nav-item-active">
                    <span class="admin-nav-icon">♙</span>
                    <span>Field Agents</span>
                </a>

                <a href="../../admin/views/area_profiles.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⌖</span>
                    <span>Area Profiles</span>
                </a>

                <a href="../../announcements/views/announcements.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚑</span>
                    <span>Send Announcement</span>
                </a>

            </nav>

            <div class="admin-sidebar-bottom">

                <a href="../../../logout.php" class="admin-nav-item admin-signout">
                    <span class="admin-nav-icon">↪</span>
                    <span>Sign Out</span>
                </a>

            </div>

        </aside>


        <!-- MAIN AREA -->

        <div class="admin-main">

            <!-- Top navigation -->

            <header class="admin-topbar">

                <div class="admin-page-heading">
                    <h1>Field Agents</h1>
                    <p>Manage and review the on ground verification team.</p>
                </div>

                <div class="admin-topbar-actions">

                    <div class="admin-profile">

                        <div class="admin-profile-avatar">
                            A
                        </div>

                        <div class="admin-profile-details">
                            <span class="admin-profile-name">Admin</span>
                            <span class="admin-profile-role">Administrator</span>
                        </div>

                    </div>

                </div>

            </header>


            <!-- CONTENT -->

            <main class="admin-content">

                <div class="admin-content__container">

                    <!-- ACTIVE AGENTS -->

                    <section class="admin-agent-section">

                        <div class="admin-agent-section__header">

                            <div>
                                <h3 class="admin-agent-section__title">
                                    Active Agents
                                </h3>
                            </div>

                            <div class="admin-agent-actions">

                                <button
                                    type="button"
                                    class="btn btn--secondary"
                                    id="fieldAgentFilterBtn">

                                    Filter

                                </button>

                                <button
                                    type="button"
                                    class="btn btn--secondary"
                                    id="fieldAgentExportBtn">

                                    Export

                                </button>

                            </div>

                        </div>


                        <!-- Filter area -->

                        <div
                            id="fieldAgentFilterPanel"
                            class="admin-agent-filter"
                            hidden>

                            <div class="admin-agent-filter__group">

                                <label for="agentStatusFilter">
                                    Status
                                </label>

                                <select id="agentStatusFilter">

                                    <option value="all">
                                        All
                                    </option>

                                    <option value="active">
                                        Active
                                    </option>

                                    <option value="inactive">
                                        Inactive
                                    </option>

                                </select>

                            </div>

                            <div class="admin-agent-filter__group">

                                <label for="agentCityFilter">
                                    City
                                </label>

                                <input
                                    type="text"
                                    id="agentCityFilter"
                                    placeholder="Search city">

                            </div>

                        </div>


                        <!-- =================================================
                         TABLE
                         ================================================= -->

                        <div class="table-wrapper admin-agent-table-wrapper">

                            <table
                                class="admin-agent-table"
                                id="fieldAgentsTable">

                                <thead>

                                    <tr>

                                        <th>
                                            Agent Name
                                        </th>

                                        <th>
                                            Assigned City
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th class="admin-agent-number-column">
                                            Verifications
                                        </th>

                                        <th class="admin-agent-number-column">
                                            Open Tasks
                                        </th>

                                        <th>
                                            Rating
                                        </th>

                                        <th>
                                            Last Active
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if (empty($agents)): ?>

                                        <tr>

                                            <td
                                                colspan="8"
                                                class="admin-agent-empty">

                                                No field agents found.

                                            </td>

                                        </tr>

                                    <?php else: ?>

                                        <?php foreach ($agents as $agent): ?>

                                            <?php
                                            $isActive =
                                                (int) $agent['is_active'] === 1;

                                            $statusClass = $isActive
                                                ? 'admin-agent-status--active'
                                                : 'admin-agent-status--inactive';

                                            $statusText = $isActive
                                                ? 'Active'
                                                : 'Inactive';

                                            $mode = $agent['recruit_mode'];
                                            ?>

                                            <tr
                                                class="admin-agent-row <?php
                                                                        echo $isActive
                                                                            ? ''
                                                                            : 'admin-agent-row--inactive';
                                                                        ?>"
                                                data-status="<?php
                                                                echo $isActive
                                                                    ? 'active'
                                                                    : 'inactive';
                                                                ?>"
                                                data-mode="<?php
                                                            echo htmlspecialchars($mode);
                                                            ?>"
                                                data-city="<?php
                                                            echo htmlspecialchars(
                                                                strtolower(
                                                                    $agent['assigned_city'] ?? ''
                                                                )
                                                            );
                                                            ?>">

                                                <!-- Agent -->

                                                <td>

                                                    <div class="admin-agent-name">

                                                        <div class="admin-agent-avatar">

                                                            <?php
                                                            echo htmlspecialchars(
                                                                getInitials(
                                                                    $agent['full_name']
                                                                )
                                                            );
                                                            ?>

                                                        </div>

                                                        <div>

                                                            <div
                                                                class="admin-agent-name__text">

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $agent['full_name']
                                                                );
                                                                ?>

                                                            </div>

                                                            <div
                                                                class="admin-agent-name__email">

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $agent['email']
                                                                );
                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </td>


                                                <!-- City -->

                                                <td>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $agent['assigned_city']
                                                            ?: 'Not assigned'
                                                    );
                                                    ?>

                                                </td>


                                                <!-- Status -->

                                                <td>

                                                    <span
                                                        class="admin-agent-status <?php
                                                                                    echo $statusClass;
                                                                                    ?>">

                                                        <span
                                                            class="material-symbols-outlined">

                                                            <?php
                                                            echo $isActive
                                                                ? 'check_circle'
                                                                : 'pause_circle';
                                                            ?>

                                                        </span>

                                                        <?php echo $statusText; ?>

                                                    </span>

                                                </td>


                                                <!-- Mode -->

                                                <td class="admin-agent-muted">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        formatMode($mode)
                                                    );
                                                    ?>

                                                </td>


                                                <!-- Verifications -->

                                                <td
                                                    class="admin-agent-number-column">

                                                    <?php
                                                    echo (int)
                                                    $agent['verification_count'];
                                                    ?>

                                                </td>


                                                <!-- Open Tasks -->

                                                <td
                                                    class="admin-agent-number-column">

                                                    <span
                                                        class="admin-agent-not-available">

                                                        —

                                                    </span>

                                                </td>


                                                <!-- Rating -->

                                                <td>

                                                    <?php
                                                    echo renderStars(
                                                        $agent['average_rating']
                                                    );
                                                    ?>

                                                </td>


                                                <!-- Last Active -->

                                                <td class="admin-agent-muted">

                                                    —

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>


                        <!-- TABLE FOOTER 

                    <div class="admin-agent-table-footer">

                        <span id="fieldAgentResultCount">

                            Showing
                            <?php echo count($agents); ?>
                            agents

                        </span>

                        <div class="pagination">

                            <button
                                type="button"
                                class="pagination__btn"
                                disabled>

                                <span class="material-symbols-outlined">
                                    chevron_left
                                </span>

                            </button>

                            <button
                                type="button"
                                class="pagination__btn pagination__btn--active">

                                1

                            </button>

                            <button
                                type="button"
                                class="pagination__btn"
                                disabled>

                                <span class="material-symbols-outlined">
                                    chevron_right
                                </span>

                            </button>

                        </div>

                    </div> -->

                    </section>

                </div>

            </main>

        </div>

    </div>


    <script src="../../../public/assets/js/admin.js"></script>

</body>

</html>
