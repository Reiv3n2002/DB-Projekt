<?php
session_start();
require_once 'assets/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $pin = $_POST['pin'];

    // Datenbankverbindung herstellen
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    try {
        // Benutzer in der Datenbank suchen
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Prüfe zuerst, ob der Account gesperrt ist
            if ($user['is_locked'] == 1) {
                $sperrzeit = new DateTime($user['locked_at']);
                $_SESSION['error'] =
                    'Ihr Account wurde am ' .
                    $sperrzeit->format('d.m.Y H:i') .
                    ' Uhr gesperrt.' .
                    'Grund: ' .
                    htmlspecialchars($user['locked_reason']) .
                    ' Bitte kontaktieren Sie uns.';
                header('Location: auth.php?error=locked');
                exit();
            }

            // Benutzer gefunden, überprüfe das Passwort
            if (password_verify($pin, $user['password_hash'])) {
                // Login erfolgreich

                // Aktualisiere last_login Zeitstempel
                $updateStmt = $pdo->prepare(
                    'UPDATE users SET last_login = NOW() WHERE user_id = ?'
                );
                $updateStmt->execute([$user['user_id']]);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // Reset der Fehlversuche und Reset-Link
                unset($_SESSION['show_reset_link']);
                unset($_SESSION['error']);

                // Direkte Weiterleitung zum Dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                // Falsches Passwort
                $_SESSION['show_reset_link'] = true;
                $_SESSION['last_username'] = $username;
                $_SESSION['error'] = 'Falsches Passwort';
                header('Location: auth.php?error=password');
                exit();
            }
        } else {
            // Benutzer nicht gefunden
            $_SESSION['error'] = 'Benutzer nicht gefunden';
            header('Location: auth.php?error=user');
            exit();
        }
    } catch (PDOException $e) {
        // Datenbankfehler
        $_SESSION['error'] = 'Ein Fehler ist aufgetreten';
        header('Location: auth.php?error=database');
        exit();
    }
} else {
    // Wenn der Benutzer bereits eingeloggt ist, zum Dashboard weiterleiten
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit();
    }

    // Ansonsten zur Auth-Seite
    header('Location: auth.php');
    exit();
}