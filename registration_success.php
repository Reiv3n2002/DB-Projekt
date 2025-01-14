<?php
session_start();

// Überprüfe ob ein Recovery Code existiert
if (!isset($_SESSION['recovery_code'])) {
    header("Location: auth.php");
    exit();
}

$recoveryCode = $_SESSION['recovery_code'];
// Lösche den Code aus der Session nachdem er angezeigt wurde
unset($_SESSION['recovery_code']);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung erfolgreich - Spaßkasse Essen</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .recovery-code {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 24px;
            letter-spacing: 2px;
        }
        .copy-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        .copy-button:hover {
            background: #0056b3;
        }
        .login-link {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="success-container">
        <h1>Willkommen bei der Spaßkasse Essen!</h1>
        <p>Vielen Dank für Ihre Registrierung. Ihr Konto wurde erfolgreich erstellt.</p>
        
        <div>
            <h2>Ihr Recovery Code</h2>
            <p>Bitte speichern Sie diesen Code sicher ab. Er wird benötigt, falls Sie Ihr Passwort zurücksetzen müssen.</p>
            <div class="recovery-code" id="recoveryCode"><?php echo htmlspecialchars($recoveryCode); ?></div>
            <button class="copy-button" onclick="copyRecoveryCode()">
                Code kopieren <i class="fas fa-copy"></i>
            </button>
        </div>

        <div class="login-link">
            <p>Sie können sich jetzt <a href="auth.php">hier anmelden</a>.</p>
        </div>
    </div>

    <script>
        function copyRecoveryCode() {
            const code = document.getElementById('recoveryCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert('Recovery Code wurde in die Zwischenablage kopiert!');
            });
        }
    </script>
</body>
</html> 