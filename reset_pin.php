<?php
require_once 'assets/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getInstance()->getConnection();
    
    $username = $_POST['username'];
    $recoveryCode = $_POST['recovery_code'];
    $newPin = password_hash($_POST['new_pin'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.user_id FROM users u
            JOIN recovery_codes r ON u.user_id = r.user_id
            WHERE u.username = ? AND r.recovery_code = ? AND r.is_used = FALSE
        ");
        $stmt->execute([$username, $recoveryCode]);
        $user = $stmt->fetch();
        
        if ($user) {
            // PIN aktualisieren
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmt->execute([$newPin, $user['user_id']]);
            
            // Recovery Code als verwendet markieren
            $stmt = $pdo->prepare("UPDATE recovery_codes SET is_used = TRUE WHERE user_id = ? AND recovery_code = ?");
            $stmt->execute([$user['user_id'], $recoveryCode]);
            
            header("Location: index.php?reset=success");
            exit();
        } else {
            header("Location: index.php?reset=failed");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: index.php?error=database");
        exit();
    }
}
?>