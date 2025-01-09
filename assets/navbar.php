<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style/navbar.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-logo">
        <p class="logo-text">Spaßkasse Essen</p>
    </div>
    
    <div class="navbar-links">
        <a href="index.php">Ihre Spaßkasse</a>
        <a href="karriere.php">Karriere</a>
        <a href="service.php">Service-Center</a>
    </div>
    
    <div class="navbar-auth">
        <div class="dropdown">
            <button class="auth-button">Anmelden</button>
            <div class="dropdown-content">
                <form action="login.php" method="POST" class="login-dropdown">
                    <div class="form-group">
                        <label for="username">Kontonummer oder Benutzername</label>
                        <input type="text" id="username" name="username" placeholder="Kontonummer/Benutzername" required>
                    </div>
                    <div class="form-group">
                        <label for="pin">PIN</label>
                        <input type="password" id="pin" name="pin" placeholder="Ihre PIN" required>
                    </div>
                    <button type="submit" class="login-button">Anmelden</button>
                    <div class="login-links">
                        <a href="#">PIN vergessen?</a>
                        <a href="#">Erstanmeldung</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</nav>
    
</body>
</html>