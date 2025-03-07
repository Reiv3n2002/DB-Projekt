<?php
require_once '../assets/database.php';
session_start();

// Datenbankverbindung herstellen
$db = Database::getInstance();

// Überprüfe Admin-Berechtigung
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    error_log(
        'Zugriff verweigert auf manage_jobs.php - Session ID: ' .
            (isset($_SESSION['user_id'])
                ? $_SESSION['user_id']
                : 'nicht gesetzt')
    );
    header('Location: ../login.php');
    exit();
}

// Job löschen
if (isset($_POST['delete_job'])) {
    $job_id = filter_var($_POST['job_id'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $db->prepare('DELETE FROM jobs WHERE job_id = ?');
    $stmt->execute([$job_id]);
}

// Job aktivieren/deaktivieren
if (isset($_POST['toggle_active'])) {
    $job_id = filter_var($_POST['job_id'], FILTER_SANITIZE_NUMBER_INT);
    $is_active = filter_var($_POST['is_active'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = $db->prepare('UPDATE jobs SET is_active = ? WHERE job_id = ?');
    $stmt->execute([$is_active, $job_id]);
}

// Job hinzufügen
if (isset($_POST['add_job'])) {
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $employment_type = filter_var(
        $_POST['employment_type'],
        FILTER_SANITIZE_STRING
    );
    $schedule_type = filter_var(
        $_POST['schedule_type'],
        FILTER_SANITIZE_STRING
    );
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $requirements = filter_var($_POST['requirements'], FILTER_SANITIZE_STRING);
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $department = filter_var($_POST['department'], FILTER_SANITIZE_STRING);
    $salary_range = filter_var($_POST['salary_range'], FILTER_SANITIZE_STRING);
    $expires_at = filter_var($_POST['expires_at'], FILTER_SANITIZE_STRING);

    $stmt = $db->prepare(
        'INSERT INTO jobs (title, employment_type, schedule_type, description, requirements, location, department, salary_range, is_active, created_by, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)'
    );
    $stmt->execute([
        $title,
        $employment_type,
        $schedule_type,
        $description,
        $requirements,
        $location,
        $department,
        $salary_range,
        $_SESSION['user_id'],
        $expires_at,
    ]);
}

// Job bearbeiten
if (isset($_POST['edit_job'])) {
    $job_id = filter_var($_POST['job_id'], FILTER_SANITIZE_NUMBER_INT);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $employment_type = filter_var(
        $_POST['employment_type'],
        FILTER_SANITIZE_STRING
    );
    $schedule_type = filter_var(
        $_POST['schedule_type'],
        FILTER_SANITIZE_STRING
    );
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $requirements = filter_var($_POST['requirements'], FILTER_SANITIZE_STRING);
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $department = filter_var($_POST['department'], FILTER_SANITIZE_STRING);
    $salary_range = filter_var($_POST['salary_range'], FILTER_SANITIZE_STRING);
    $expires_at = filter_var($_POST['expires_at'], FILTER_SANITIZE_STRING);

    $stmt = $db->prepare('UPDATE jobs SET 
        title = ?, 
        employment_type = ?, 
        schedule_type = ?, 
        description = ?, 
        requirements = ?, 
        location = ?, 
        department = ?, 
        salary_range = ?, 
        expires_at = ? 
        WHERE job_id = ?');

    $stmt->execute([
        $title,
        $employment_type,
        $schedule_type,
        $description,
        $requirements,
        $location,
        $department,
        $salary_range,
        $expires_at,
        $job_id,
    ]);
}

// Job duplizieren
if (isset($_POST['duplicate_job'])) {
    $job_id = filter_var($_POST['job_id'], FILTER_SANITIZE_NUMBER_INT);

    // Hole die Daten des zu duplizierenden Jobs
    $stmt = $db->prepare('SELECT * FROM jobs WHERE job_id = ?');
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    // Erstelle einen neuen Job mit den gleichen Daten
    $stmt = $db->prepare('INSERT INTO jobs (
        title, 
        employment_type, 
        schedule_type, 
        description, 
        requirements, 
        location, 
        department, 
        salary_range, 
        is_active, 
        created_by, 
        expires_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)');

    $stmt->execute([
        $job['title'] . ' (Kopie)',
        $job['employment_type'],
        $job['schedule_type'],
        $job['description'],
        $job['requirements'],
        $job['location'],
        $job['department'],
        $job['salary_range'],
        $_SESSION['user_id'],
        $job['expires_at'],
    ]);

    // Hole die ID des neu erstellten Jobs
    $new_job_id = $db->getPDO()->lastInsertId();

    // Hole die Daten des neuen Jobs für das Modal
    $stmt = $db->prepare('SELECT * FROM jobs WHERE job_id = ?');
    $stmt->execute([$new_job_id]);
    $new_job = $stmt->fetch(PDO::FETCH_ASSOC);

    // Setze eine Session-Variable, um das Modal mit den neuen Daten zu öffnen
    $_SESSION['show_duplicated_job'] = json_encode($new_job);
}

// Alle Jobs abrufen
$stmt = $db->prepare('SELECT * FROM jobs ORDER BY created_at DESC');
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobverwaltung - Admin</title>
    <link rel="stylesheet" href="../assets/style/admin/admin.css">
</head>

<body>
    <div class="admin-container">
        <div class="header-section">
            <h1>Jobverwaltung</h1>
            <a href="../dashboard.php" class="back-btn">Zurück</a>
            <button onclick="showAddJobForm()" class="add-btn">Neuen Job hinzufügen</button>
        </div>

        <table class="job-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel</th>
                    <th>Abteilung</th>
                    <th>Standort</th>
                    <th>Beschäftigungsart</th>
                    <th>Gehaltsspanne</th>
                    <th>Status</th>
                    <th>Ablaufdatum</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?= htmlspecialchars($job['job_id']) ?></td>
                    <td><?= htmlspecialchars($job['title']) ?></td>
                    <td><?= htmlspecialchars($job['department']) ?></td>
                    <td><?= htmlspecialchars($job['location']) ?></td>
                    <td><?= htmlspecialchars($job['employment_type']) ?></td>
                    <td><?= htmlspecialchars($job['salary_range']) ?></td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="job_id" value="<?= $job[
                                'job_id'
                            ] ?>">
                            <select name="is_active" onchange="this.form.submit()">
                                <option value="1" <?= $job['is_active'] == 1
                                    ? 'selected'
                                    : '' ?>>Aktiv</option>
                                <option value="0" <?= $job['is_active'] == 0
                                    ? 'selected'
                                    : '' ?>>Inaktiv</option>
                            </select>
                            <input type="hidden" name="toggle_active" value="1">
                        </form>
                    </td>
                    <td><?= htmlspecialchars($job['expires_at']) ?></td>
                    <td>
                        <button onclick="showEditJobForm(<?= htmlspecialchars(
                            json_encode($job)
                        ) ?>)" class="edit-btn">Bearbeiten</button>
                        <form method="POST" class="delete-form" style="display: inline;">
                            <input type="hidden" name="job_id" value="<?= $job[
                                'job_id'
                            ] ?>">
                            <button type="submit" name="delete_job" class="delete-btn">Löschen</button>
                        </form>
                        <form method="POST" class="duplicate-form" style="display: inline;">
                            <input type="hidden" name="job_id" value="<?= $job[
                                'job_id'
                            ] ?>">
                            <button type="submit" name="duplicate_job" class="duplicate-btn">Duplizieren</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal für Job -->
    <div id="jobModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Job hinzufügen</h2>
            <form method="POST" id="jobForm">
                <input type="hidden" id="job_id" name="job_id">
                <div class="form-group">
                    <label for="title">Titel:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="department">Abteilung:</label>
                    <input type="text" id="department" name="department" required>
                </div>
                <div class="form-group">
                    <label for="location">Standort:</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div class="form-group">
                    <label for="employment_type">Beschäftigungsart:</label>
                    <select id="employment_type" name="employment_type" required>
                        <option value="Vollzeit">Vollzeit</option>
                        <option value="Teilzeit">Teilzeit</option>
                        <option value="Praktikum">Praktikum</option>
                        <option value="Werkstudent">Werkstudent</option>
                        <option value="Ausbildung">Ausbildung</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="schedule_type">Arbeitszeit:</label>
                    <select id="schedule_type" name="schedule_type" required>
                        <option value="Teilzeit">Teilzeit</option>
                        <option value="Flexibel">Flexibel</option>
                        <option value="Vollzeit">Vollzeit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="salary_range">Gehaltsspanne:</label>
                    <input type="text" id="salary_range" name="salary_range" required>
                </div>
                <div class="form-group">
                    <label for="description">Beschreibung:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="requirements">Anforderungen:</label>
                    <textarea id="requirements" name="requirements" required></textarea>
                </div>
                <div class="form-group">
                    <label for="expires_at">Ablaufdatum:</label>
                    <input type="date" id="expires_at" name="expires_at" required>
                </div>
                <button type="submit" id="submitBtn" name="add_job">Speichern</button>
            </form>
        </div>
    </div>

    <script>
    // Modal Funktionalität
    const modal = document.getElementById('jobModal');
    const span = document.getElementsByClassName('close')[0];

    function showAddJobForm() {
        modal.style.display = "block";
        document.getElementById('jobForm').reset();
        document.getElementById('modalTitle').textContent = 'Job hinzufügen';
        document.getElementById('submitBtn').name = 'add_job';
        document.getElementById('job_id').value = '';
    }

    function showEditJobForm(job) {
        modal.style.display = "block";
        document.getElementById('modalTitle').textContent = 'Job bearbeiten';
        document.getElementById('submitBtn').name = 'edit_job';

        // Formular mit den Job-Daten füllen
        document.getElementById('job_id').value = job.job_id;
        document.getElementById('title').value = job.title;
        document.getElementById('department').value = job.department;
        document.getElementById('location').value = job.location;
        document.getElementById('employment_type').value = job.employment_type;
        document.getElementById('schedule_type').value = job.schedule_type;
        document.getElementById('salary_range').value = job.salary_range;
        document.getElementById('description').value = job.description;
        document.getElementById('requirements').value = job.requirements;
        document.getElementById('expires_at').value = job.expires_at.split(' ')[0];
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Bestätigung für das Löschen
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Sind Sie sicher, dass Sie diesen Job löschen möchten?')) {
                e.preventDefault();
            }
        });
    });

    // Prüfe, ob ein duplizierter Job angezeigt werden soll
    <?php if (isset($_SESSION['show_duplicated_job'])): ?>
    const duplicatedJob = <?= $_SESSION['show_duplicated_job'] ?>;
    showEditJobForm(duplicatedJob);
    <?php unset($_SESSION['show_duplicated_job']); ?>
    <?php endif; ?>
    </script>
</body>

</html>