<?php
require_once '../assets/database.php';
session_start();

// Datenbankverbindung herstellen
$db = Database::getInstance();

// Überprüfe Admin-Berechtigung
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    error_log(
        'Zugriff verweigert auf manage_applications.php - Session ID: ' .
            (isset($_SESSION['user_id'])
                ? $_SESSION['user_id']
                : 'nicht gesetzt')
    );
    header('Location: ../login.php');
    exit();
}

// Status einer Bewerbung aktualisieren
if (isset($_POST['update_status'])) {
    $application_id = filter_var(
        $_POST['application_id'],
        FILTER_SANITIZE_NUMBER_INT
    );
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

    $stmt = $db->prepare(
        'UPDATE applications SET status = ? WHERE application_id = ?'
    );
    $stmt->execute([$status, $application_id]);
}

// Bewerbung löschen
if (isset($_POST['delete_application'])) {
    $application_id = filter_var(
        $_POST['application_id'],
        FILTER_SANITIZE_NUMBER_INT
    );

    // Lösche zuerst die Dateien aus dem Filesystem
    $stmt = $db->prepare(
        'SELECT resume, cover_letter FROM applications WHERE application_id = ?'
    );
    $stmt->execute([$application_id]);
    $files = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($files['resume']) {
        @unlink('../uploads/resumes/' . $files['resume']);
    }
    if ($files['cover_letter']) {
        @unlink('../uploads/cover_letters/' . $files['cover_letter']);
    }

    // Dann lösche den Datensatz aus der Datenbank
    $stmt = $db->prepare('DELETE FROM applications WHERE application_id = ?');
    $stmt->execute([$application_id]);
}

// Alle Bewerbungen abrufen
$stmt = $db->prepare('
    SELECT a.*, j.title as job_title, 
           a.resume_text, a.cover_letter_text
    FROM applications a 
    LEFT JOIN jobs j ON a.job_id = j.job_id 
    ORDER BY a.created_at DESC
');
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bewerbungsverwaltung - Admin</title>
    <link rel="stylesheet" href="../assets/style/admin/admin.css">
    <style>
    .status-new {
        background-color: #e3f2fd;
    }

    .status-in-review {
        background-color: #fff3e0;
    }

    .status-interview {
        background-color: #e8f5e9;
    }

    .status-accepted {
        background-color: #c8e6c9;
    }

    .status-rejected {
        background-color: #ffebee;
    }

    .download-link {
        color: #1976d2;
        text-decoration: none;
    }

    .download-link:hover {
        text-decoration: underline;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .document-content {
        white-space: pre-wrap;
        padding: 20px;
        line-height: 1.5;
        font-family: Arial, sans-serif;
    }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="header-section">
            <h1>Bewerbungsverwaltung</h1>
            <a href="../dashboard.php" class="back-btn">Zurück</a>
        </div>

        <!-- Modal hinzufügen -->
        <div id="documentModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle"></h2>
                <div id="modalContent"></div>
            </div>
        </div>

        <table class="application-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Stelle</th>
                    <th>Bewerber</th>
                    <th>E-Mail</th>
                    <th>Telefon</th>
                    <th>Unterlagen</th>
                    <th>Status</th>
                    <th>Eingangsdatum</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                <tr class="status-<?= strtolower($application['status']) ?>">
                    <td><?= htmlspecialchars(
                        $application['application_id']
                    ) ?></td>
                    <td><?= htmlspecialchars($application['job_title']) ?></td>
                    <td><?= htmlspecialchars(
                        $application['applicant_name']
                    ) ?></td>
                    <td><?= htmlspecialchars($application['email']) ?></td>
                    <td><?= htmlspecialchars($application['phone']) ?></td>
                    <td>
                        <?php if ($application['resume_text']): ?>
                        <a href="#" class="download-link view-document" data-content="<?= htmlspecialchars(
                                   $application['resume_text']
                               ) ?>" data-type="Lebenslauf">Lebenslauf</a>
                        <?php endif; ?>
                        <br>
                        <?php if ($application['cover_letter_text']): ?>
                        <a href="#" class="download-link view-document" data-content="<?= htmlspecialchars(
                                   $application['cover_letter_text']
                               ) ?>" data-type="Anschreiben">Anschreiben</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="application_id" value="<?= $application[
                                'application_id'
                            ] ?>">
                            <select name="status" onchange="this.form.submit()" class="status-select">
                                <option value="Neu" <?= $application[
                                    'status'
                                ] == 'Neu'
                                    ? 'selected'
                                    : '' ?>>
                                    Neu
                                </option>
                                <option value="In Prüfung" <?= $application[
                                    'status'
                                ] == 'In Prüfung'
                                    ? 'selected'
                                    : '' ?>>
                                    In Prüfung
                                </option>
                                <option value="Zum Gespräch" <?= $application[
                                    'status'
                                ] == 'Zum Gespräch'
                                    ? 'selected'
                                    : '' ?>>
                                    Zum Gespräch
                                </option>
                                <option value="Angenommen" <?= $application[
                                    'status'
                                ] == 'Angenommen'
                                    ? 'selected'
                                    : '' ?>>
                                    Angenommen
                                </option>
                                <option value="Abgelehnt" <?= $application[
                                    'status'
                                ] == 'Abgelehnt'
                                    ? 'selected'
                                    : '' ?>>
                                    Abgelehnt
                                </option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td><?= htmlspecialchars(
                        date('d.m.Y', strtotime($application['created_at']))
                    ) ?></td>
                    <td>
                        <form method="POST" class="delete-form" style="display: inline;">
                            <input type="hidden" name="application_id" value="<?= $application[
                                'application_id'
                            ] ?>">
                            <button type="submit" name="delete_application" class="delete-btn">Löschen</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    // Bestätigung für das Löschen
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Sind Sie sicher, dass Sie diese Bewerbung löschen möchten?')) {
                e.preventDefault();
            }
        });
    });

    // Modal Funktionalität
    const modal = document.getElementById("documentModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalContent = document.getElementById("modalContent");
    const span = document.getElementsByClassName("close")[0];

    // Dokumente anzeigen
    document.querySelectorAll('.view-document').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const content = this.getAttribute('data-content');
            const docType = this.getAttribute('data-type');

            modalTitle.textContent = docType;
            modalContent.innerHTML =
                `<div class="document-content">${content.replace(/\n/g, '<br>')}</div>`;
            modal.style.display = "block";
        });
    });

    // Modal schließen wenn auf X geklickt wird
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Modal schließen wenn außerhalb geklickt wird
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>

</html>