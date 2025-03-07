<?php
// Stelle sicher, dass keine Ausgaben vor diesem Punkt erfolgen
require_once 'assets/database.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datenbankverbindung herstellen
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Formulardaten sammeln
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $pin = $_POST['pin'];
    $pin_confirm = $_POST['pin_confirm'];

    try {
        // Überprüfe ob Benutzername bereits existiert
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userExists = $stmt->fetchColumn() > 0;

        if ($userExists) {
            $_SESSION['error'] = "Dieser Benutzername ist bereits vergeben";
            header("Location: auth.php?error=username_taken");
            exit();
        }

        // Überprüfe ob E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn() > 0;

        if ($emailExists) {
            $_SESSION['error'] = "Diese E-Mail-Adresse ist bereits registriert";
            header("Location: auth.php?error=email_taken");
            exit();
        }

        // Überprüfe ob PINs übereinstimmen
        if ($pin !== $pin_confirm) {
            $_SESSION['error'] = "Die PINs stimmen nicht überein";
            header("Location: auth.php?error=pin_mismatch");
            exit();
        }

        // Hash das Passwort
        $hashedPin = password_hash($pin, PASSWORD_DEFAULT);

        // Erstelle den Benutzer
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt->execute([$username, $hashedPin, $email, $firstname, $lastname])) {
            throw new PDOException("Fehler beim Erstellen des Benutzers");
        }

        // Generiere Recovery Code
        $userId = $pdo->lastInsertId();
        $recoveryCode = sprintf("%06d", mt_rand(0, 999999));
        
        // Speichere Recovery Code
        $stmt = $pdo->prepare("INSERT INTO recovery_codes (user_id, recovery_code) VALUES (?, ?)");
        if (!$stmt->execute([$userId, $recoveryCode])) {
            throw new PDOException("Fehler beim Speichern des Recovery Codes");
        }

        // Setze Success Message und leite zur Erfolgsseite weiter
        $_SESSION['success'] = "Registrierung erfolgreich! Sie können sich jetzt anmelden.";
        $_SESSION['recovery_code'] = $recoveryCode;
        header("Location: registration_success.php");
        exit();

    } catch (PDOException $e) {
        error_log($e->getMessage()); // Logge den Fehler für Debugging
        $_SESSION['error'] = "Ein Fehler ist bei der Registrierung aufgetreten: " . $e->getMessage();
        header("Location: auth.php?error=database");
        exit();
    }
}

// Falls jemand direkt auf register.php zugreift
header("Location: auth.php");
exit();
?>