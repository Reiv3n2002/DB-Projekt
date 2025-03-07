<?php
session_start();
require_once 'assets/database.php';

$job_id = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;
$success_message = '';
$error_message = '';

// Überprüfen, ob die Stelle existiert
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare(
        'SELECT title FROM jobs WHERE job_id = ? AND is_active = 1'
    );
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        header('Location: karriere.php');
        exit();
    }

    // Bewerbungsformular verarbeiten
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $applicant_name = trim($_POST['applicant_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $resume = trim($_POST['resume'] ?? '');
        $cover_letter = trim($_POST['cover_letter'] ?? '');

        $errors = [];

        // Validierung
        if (empty($applicant_name)) {
            $errors[] = 'Bitte geben Sie Ihren Namen ein.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        }
        if (empty($phone)) {
            $errors[] = 'Bitte geben Sie Ihre Telefonnummer ein.';
        }
        if (empty($resume)) {
            $errors[] = 'Bitte fügen Sie Ihren Lebenslauf ein.';
        }

        if (empty($errors)) {
            // In Datenbank speichern
            $stmt = $pdo->prepare("
                INSERT INTO applications (job_id, applicant_name, email, phone, resume, cover_letter, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Eingegangen')
            ");

            if (
                $stmt->execute([
                    $job_id,
                    $applicant_name,
                    $email,
                    $phone,
                    $resume,
                    $cover_letter,
                ])
            ) {
                $success_message =
                    'Ihre Bewerbung wurde erfolgreich eingereicht. Wir werden uns in Kürze bei Ihnen melden.';
            } else {
                $error_message =
                    'Es gab einen Fehler beim Speichern Ihrer Bewerbung. Bitte versuchen Sie es später erneut.';
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
} catch (PDOException $e) {
    $error_message =
        'Es ist ein Datenbankfehler aufgetreten. Bitte versuchen Sie es später erneut.';
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bewerbung - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/bewerbung.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'assets/navbar.php'; ?>

    <main class="bewerbung-container">
        <h1>Bewerbung für: <?php echo htmlspecialchars($job['title']); ?></h1>

        <?php if ($success_message): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
            <p>
                <a href="karriere.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>Zurück zur Karriereseite
                </a>
            </p>
        </div>
        <?php else: ?>
        <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bewerbung-form">
            <div class="form-group">
                <label for="applicant_name">Name *</label>
                <input type="text" id="applicant_name" name="applicant_name" required value="<?php echo htmlspecialchars(
                    $_POST['applicant_name'] ?? ''
                ); ?>">
            </div>

            <div class="form-group">
                <label for="email">E-Mail *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars(
                    $_POST['email'] ?? ''
                ); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Telefon *</label>
                <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars(
                    $_POST['phone'] ?? ''
                ); ?>">
            </div>

            <div class="form-group">
                <label for="resume">Lebenslauf *</label>
                <textarea id="resume" name="resume" required class="form-textarea" rows="10"
                    placeholder="Bitte fügen Sie hier Ihren Lebenslauf ein..."><?php echo htmlspecialchars(
                        $_POST['resume'] ?? ''
                    ); ?></textarea>
            </div>

            <div class="form-group">
                <label for="cover_letter">Anschreiben (optional)</label>
                <textarea id="cover_letter" name="cover_letter" class="form-textarea" rows="10"
                    placeholder="Bitte fügen Sie hier Ihr Anschreiben ein..."><?php echo htmlspecialchars(
                        $_POST['cover_letter'] ?? ''
                    ); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="submit-button">Bewerbung absenden</button>
            </div>
        </form>
        <?php endif; ?>
    </main>

    <script src="assets/navbar.js"></script>
    <script>
    document.getElementById('resume').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Lebenslauf (PDF) *';
        document.getElementById('resume-name').textContent = fileName;
    });

    document.getElementById('cover_letter').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Anschreiben (PDF, optional)';
        document.getElementById('cover-letter-name').textContent = fileName;
    });
    </script>
</body>

</html>