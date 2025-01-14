<?php
session_start();
require_once 'assets/database.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentifizierung - Spaßkasse Essen</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="auth-page-container">
        <div class="auth-form-container">
            <!-- Login Formular -->
            <form action="login.php" method="POST" class="auth-form" id="loginForm">
                <h2>Anmelden</h2>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php 
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); // Lösche die Fehlermeldung nach der Anzeige
                        ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="username">Kontonummer oder Benutzername</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="pin">PIN</label>
                    <input type="password" id="pin" name="pin" required>
                </div>
                <button type="submit" class="auth-submit-button">Anmelden</button>
                
                <div class="auth-links">
                    <p>Neukunde? <a href="#" id="showRegister">Hier registrieren</a></p>
                </div>
            </form>

            <!-- Register Formular (standardmäßig versteckt) -->
            <form action="register.php" method="POST" class="auth-form hidden" id="registerForm">
                <h2>Registrieren</h2>
                <div class="form-group">
                    <label for="reg_firstname">Vorname</label>
                    <input type="text" id="reg_firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="reg_lastname">Nachname</label>
                    <input type="text" id="reg_lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">E-Mail</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg_username">Benutzername</label>
                    <input type="text" id="reg_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="reg_pin">Passwort</label>
                    <input type="password" id="reg_pin" name="pin" required>
                </div>
                <div class="form-group">
                    <label for="reg_pin_confirm">Passwort bestätigen</label>
                    <input type="password" id="reg_pin_confirm" name="pin_confirm" required>
                </div>
                <button type="submit" class="auth-submit-button">Registrieren</button>
                
                <div class="auth-links">
                    <p>Bereits Kunde? <a href="#" id="showLogin">Zurück zum Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('showRegister').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('registerForm').classList.remove('hidden');
        });

        document.getElementById('showLogin').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('registerForm').classList.add('hidden');
            document.getElementById('loginForm').classList.remove('hidden');
        });
    </script>
</body>
</html> 