<?php
require_once '../../../includes/session.php';
requireRole('admin');

require_once '../../../config/db.php';

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);

/* Check whether an announcement is being edited */

$editAnnouncement = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {

    $editId = (int) $_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT
            announcements_id,
            audience,
            subject,
            message,
            priority
        FROM announcements
        WHERE announcements_id = ?
    ");

    $stmt->execute([$editId]);

    $editAnnouncement = $stmt->fetch();

    if (!$editAnnouncement) {
        $_SESSION['error'] = 'Announcement not found.';

        header('Location: announcements.php');
        exit();
    }
}

/*Recent announcements*/

$stmt = $pdo->prepare("
    SELECT
        a.announcements_id,
        a.subject,
        a.audience,
        a.message,
        a.priority,
        a.sent_at,
        u.full_name AS sender_name
    FROM announcements a
    INNER JOIN users u
        ON a.sent_by = u.user_id
    ORDER BY a.sent_at DESC
    LIMIT 10
");

$stmt->execute();
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Announcements</title>

    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
</head>

<body class="admin-body">

    <div class="admin-layout">

        <!-- Sidebar -->
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

                <a href="../../approvals/views/registration_approvals.php" class="admin-nav-item">
                    <span class="admin-nav-icon">✓</span>
                    <span>Registration Approvals</span>
                </a>

                <a href="../../approvals/views/listings_decisions.php" class="admin-nav-item">
                    <span class="admin-nav-icon">▤</span>
                    <span>Listings</span>
                </a>

                <a href="../../complaints/views/complaints.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⚖</span>
                    <span>Complaint Moderation</span>
                </a>

                <a href="../../approvals/views/field_agent_manage.php" class="admin-nav-item">
                    <span class="admin-nav-icon">♙</span>
                    <span>Field Agents</span>
                </a>

                <a href="../../admin/views/area_profiles.php" class="admin-nav-item">
                    <span class="admin-nav-icon">⌖</span>
                    <span>Area Profiles</span>
                </a>

                <a href="announcements.php" class="admin-nav-item admin-nav-item-active">
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


        <!-- Main content -->
        <main class="admin-main">

            <header class="admin-topbar">
                <!-- TOP BAR -->
                <div class="admin-page-heading">
                    <h1>Send Announcement</h1>
                    <p>Send an announcement to BoardNest users.</p>
                </div>

                <!-- <div>
                <strong>
                   <? //= htmlspecialchars($_SESSION['full_name']) 
                    ?>
                </strong>
            </div> -->

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


            <!-- Flash messages -->
            <div class="admin-announcement-messages">
                <?php if ($success): ?>

                    <div class="alert alert--success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


                <?php if ($error): ?>

                    <div class="alert alert--error">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>
            </div>

            <!-- Announcement form -->

            <section class="card admin-announcement-card">

                <div class="section-header">
                    <div>
                        <h2><?= $editAnnouncement
                                ? 'Edit Announcement'
                                : 'Create Announcement' ?>
                        </h2>

                        <p>
                            <?= $editAnnouncement
                                ? 'Update the announcement details below.'
                                : 'Choose the audience and enter the announcement details.' ?>
                        </p>
                    </div>
                </div>


                <form
                    method="POST"
                    action="<?= $editAnnouncement
                                ? '../controller/edit_announcements.php'
                                : '../controller/send_announcements.php' ?>">

                    <?php if ($editAnnouncement): ?>

                        <input
                            type="hidden"
                            name="announcements_id"
                            value="<?= htmlspecialchars($editAnnouncement['announcements_id']) ?>">

                    <?php endif; ?>

                    <!-- Audience -->

                    <div class="admin-form-group">

                        <label for="admin-audience">
                            Audience
                        </label>

                        <select
                            id="admin-audience"
                            name="audience"
                            required>

                            <option value="">
                                Select audience
                            </option>

                            <option value="all"
                                <?= ($editAnnouncement && $editAnnouncement['audience'] === 'all') ? 'selected' : '' ?>>
                                All Users
                            </option>

                            <option value="student"
                                <?= ($editAnnouncement && $editAnnouncement['audience'] === 'student') ? 'selected' : '' ?>>
                                Students
                            </option>

                            <option value="landlord"
                                <?= ($editAnnouncement && $editAnnouncement['audience'] === 'landlord') ? 'selected' : '' ?>>
                                Landlords
                            </option>

                            <option value="field_agent"
                                <?= ($editAnnouncement && $editAnnouncement['audience'] === 'field_agent') ? 'selected' : '' ?>>
                                Field Agents
                            </option>

                        </select>

                    </div>


                    <!-- Subject -->

                    <div class="admin-form-group">

                        <label for="admin-subject">
                            Subject
                        </label>

                        <input
                            type="text"
                            id="admin-subject"
                            name="subject"
                            maxlength="255"
                            value="<?= $editAnnouncement
                                        ? htmlspecialchars($editAnnouncement['subject'])
                                        : '' ?>"
                            required>

                    </div>


                    <!-- Priority -->

                    <div class="admin-form-group">

                        <label for="admin-priority">
                            Priority
                        </label>

                        <select
                            id="admin-priority"
                            name="priority"
                            required>

                            <option value="normal"
                                <?= ($editAnnouncement && $editAnnouncement['priority'] === 'normal') ? 'selected' : '' ?>>
                                Normal
                            </option>

                            <option value="urgent"
                                <?= ($editAnnouncement && $editAnnouncement['priority'] === 'urgent') ? 'selected' : '' ?>>
                                Urgent
                            </option>

                        </select>

                    </div>


                    <!-- Message -->

                    <div class="admin-form-group">

                        <label for="admin-message">
                            Message
                        </label>

                        <textarea
                            id="admin-message"
                            name="message"
                            rows="8"
                            required>
                            <?= $editAnnouncement
                                ? htmlspecialchars($editAnnouncement['message'])
                                : '' ?>
                        </textarea>

                    </div>


                    <!-- Submit -->

                    <div class="admin-form-actions">

                        <button
                            type="submit"
                            class="btn btn--primary">

                            <?= $editAnnouncement
                                ? 'Save Changes'
                                : 'Send Announcement' ?>

                        </button>

                        <?php if ($editAnnouncement): ?>
                            <a
                                href="announcements.php"
                                class="btn">
                                Cancel
                            </a>

                        <?php endif; ?>

                    </div>

                </form>

            </section>


            <!-- Previous announcements -->

            <section class="card admin-announcement-history">

                <div class="section-header">
                    <div>
                        <h2>Recent Announcements</h2>
                    </div>
                </div>


                <div class="table-wrapper">

                    <table class="admin-table">

                        <thead>

                            <tr>
                                <th>Subject</th>
                                <th>Audience</th>
                                <th>Priority</th>
                                <th>Sent By</th>
                                <th>Sent At</th>
                                <th>Actions</th>
                            </tr>

                        </thead>


                        <tbody>

                            <?php if (empty($announcements)): ?>

                                <tr>

                                    <td colspan="6">
                                        No announcements have been sent yet.
                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($announcements as $announcement): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars(
                                                $announcement['subject']
                                            ) ?>
                                        </td>


                                        <td>
                                            <?= htmlspecialchars(
                                                ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $announcement['audience']
                                                    )
                                                )
                                            ) ?>
                                        </td>


                                        <td>

                                            <?php if (
                                                $announcement['priority'] === 'urgent'
                                            ): ?>

                                                <span class="badge badge--error">
                                                    Urgent
                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge--muted">
                                                    Normal
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td>
                                            <?= htmlspecialchars(
                                                $announcement['sender_name']
                                            ) ?>
                                        </td>


                                        <td>
                                            <?= htmlspecialchars(
                                                date(
                                                    'M d, Y h:i A',
                                                    strtotime(
                                                        $announcement['sent_at']
                                                    )
                                                )
                                            ) ?>
                                        </td>

                                        <td>

                                            <div class="admin-announcement-actions">

                                                <a
                                                    href="announcements.php?edit=<?= (int) $announcement['announcements_id'] ?>"
                                                    class="btn btn--sm btn--outline">
                                                    Edit
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="../controller/delete_announcements.php"
                                                    class="admin-delete-form">

                                                    <input
                                                        type="hidden"
                                                        name="announcements_id"
                                                        value="<?= (int) $announcement['announcements_id'] ?>">

                                                    <button
                                                        type="submit"
                                                        class="btn btn--sm btn--danger"
                                                        onclick="return confirm('Are you sure you want to delete this announcement?');">
                                                        Delete
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>
        </main>

    </div>

</body>

</html>