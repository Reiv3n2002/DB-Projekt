<?php
require_once '../assets/database.php';
session_start();

// Datenbankverbindung herstellen
$db = Database::getInstance();

// Überprüfe Admin-Berechtigung
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    error_log(
        'Access denied to manage_users.php - Session ID: ' .
            (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set')
    );
    error_log(
        'User Admin: ' .
            (isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'not set')
    );
    header('Location: ../login.php');
    exit();
}

// Benutzer löschen
if (isset($_POST['delete_user'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
}

// Benutzerrolle ändern
if (isset($_POST['change_role'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $is_admin = filter_var($_POST['is_admin'], FILTER_SANITIZE_NUMBER_INT);

    // Überprüfe, ob dies der letzte Admin ist
    if ($is_admin == 0) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE is_admin = 1');
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();

        $stmt = $db->prepare('SELECT is_admin FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $current_is_admin = $stmt->fetchColumn();

        if ($admin_count <= 1 && $current_is_admin == 1) {
            echo "<script>alert('Es muss mindestens ein Administrator bleiben!');</script>";
            exit();
        }
    }

    $stmt = $db->prepare('UPDATE users SET is_admin = ? WHERE user_id = ?');
    $stmt->execute([$is_admin, $user_id]);
}

// Benutzer sperren
if (isset($_POST['lock_user'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $locked_reason = filter_var(
        $_POST['locked_reason'],
        FILTER_SANITIZE_STRING
    );

    $stmt = $db->prepare(
        'UPDATE users SET is_locked = 1, locked_reason = ?, locked_at = NOW() WHERE user_id = ?'
    );
    $stmt->execute([$locked_reason, $user_id]);
}

// Benutzer entsperren
if (isset($_POST['unlock_user'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = $db->prepare(
        'UPDATE users SET is_locked = 0, locked_reason = NULL, locked_at = NULL WHERE user_id = ?'
    );
    $stmt->execute([$user_id]);
}

// Alle Benutzer abrufen
$stmt = $db->prepare('SELECT * FROM users ORDER BY created_at DESC');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung - Admin</title>
    <link rel="stylesheet" href="../assets/style/admin/admin.css">
</head>

<body>
    <div class="admin-container">
        <div class="header-section">
            <h1>Benutzerverwaltung</h1>
            <a href="../dashboard.php" class="back-btn">Zurück</a>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Benutzername</th>
                    <th>E-Mail</th>
                    <th>Rolle</th>
                    <th>Erstellt am</th>
                    <th>Letzter Login</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <form method="POST" class="role-form">
                            <input type="hidden" name="user_id" value="<?= $user[
                                'user_id'
                            ] ?>">
                            <select name="is_admin" onchange="this.form.submit()">
                                <option value="0" <?= $user['is_admin'] == 0
                                    ? 'selected'
                                    : '' ?>>Benutzer</option>
                                <option value="1" <?= $user['is_admin'] == 1
                                    ? 'selected'
                                    : '' ?>>Admin</option>
                            </select>
                            <input type="hidden" name="change_role" value="1">
                        </form>
                    </td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td><?= $user['last_login']
                        ? htmlspecialchars($user['last_login'])
                        : 'Noch nie' ?></td>
                    <td>
                        <?php if ($user['is_admin'] != 1): ?>
                        <?php if ($user['is_locked'] == 0): ?>
                        <button type="button" class="lock-btn" onclick="showLockDialog(<?= $user[
                            'user_id'
                        ] ?>)">Sperren</button>
                        <?php else: ?>
                        <form method="POST" class="unlock-form">
                            <input type="hidden" name="user_id" value="<?= $user[
                                'user_id'
                            ] ?>">
                            <button type="submit" name="unlock_user" class="unlock-btn">Entsperren</button>
                        </form>
                        <div class="lock-info">
                            <small>Gesperrt am: <?= htmlspecialchars(
                                $user['locked_at']
                            ) ?></small><br>
                            <small>Grund: <?= htmlspecialchars(
                                $user['locked_reason']
                            ) ?></small>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bestätigungsdialog für das Löschen von Benutzern
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?')) {
                    e.preventDefault();
                }
            });
        });

        // Sperrdialog anzeigen
        window.showLockDialog = function(userId) {
            const reason = prompt('Bitte geben Sie den Grund für die Sperrung ein:');
            if (reason) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="locked_reason" value="${reason}">
                    <input type="hidden" name="lock_user" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        };
    });
    </script>
</body>

</html>