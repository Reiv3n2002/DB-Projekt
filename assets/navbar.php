<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style/navbar.css">
</head>
<body>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
$show_nav_pages = ['index.php', 'karriere.php', 'service.php'];
?>

<nav class="navbar">
    <div class="navbar-logo">
        <p class="logo-text">Spaßkasse Essen</p>
    </div>
    
    <?php if (in_array($current_page, $show_nav_pages)): ?>
        <div class="navbar-links">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                Ihre Spaßkasse
            </a>
            <a href="karriere.php" class="nav-link <?php echo ($current_page == 'karriere.php') ? 'active' : ''; ?>">
                Karriere
            </a>
            <a href="service.php" class="nav-link <?php echo ($current_page == 'service.php') ? 'active' : ''; ?>">
                Service-Center
            </a>
        </div>
    <?php endif; ?>
    
    <div class="navbar-auth">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <div class="user-dropdown">
                    <button class="dropbtn" onclick="toggleAdminDropdown(event)">
                        <i class="fas fa-shield-alt"></i> Admin
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="adminDropdown">
                        <a href="admin/jobs.php"><i class="fas fa-briefcase"></i> Jobs verwalten</a>
                        <a href="admin/applications.php"><i class="fas fa-file-alt"></i> Bewerbungen</a>
                        <a href="admin/prices.php"><i class="fas fa-euro-sign"></i> Kontopreise</a>
                        <a href="admin/users.php"><i class="fas fa-users-cog"></i> Benutzer verwalten</a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="user-dropdown">
                <button class="dropbtn" onclick="toggleDropdown(event)">
                    <i class="fas fa-user"></i> 
                    <?php 
                        if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                            echo htmlspecialchars($_SESSION['first_name']) . ' ' . htmlspecialchars($_SESSION['last_name']);
                        } else {
                            echo htmlspecialchars($_SESSION['username']);
                        }
                    ?>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-content" id="userDropdown">
                    <a href="settings.php"><i class="fas fa-cog"></i> Einstellungen</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Abmelden</a>
                </div>
            </div>
        <?php else: ?>
            <div class="auth-container">
                <a href="auth.php" class="auth-button">
                    <i class="fas fa-lock"></i> Authentifizierung
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
function toggleDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

function toggleAdminDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('adminDropdown');
    dropdown.classList.toggle('show');
}

// Schließt die Dropdowns wenn außerhalb geklickt wird
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const adminDropdown = document.getElementById('adminDropdown');
    
    if (!event.target.matches('.dropbtn') && userDropdown.classList.contains('show')) {
        userDropdown.classList.remove('show');
    }
    
    if (!event.target.matches('.admin-btn') && adminDropdown.classList.contains('show')) {
        adminDropdown.classList.remove('show');
    }
});

// Verhindert das Schließen wenn innerhalb der Dropdowns geklickt wird
document.getElementById('userDropdown').addEventListener('click', function(event) {
    event.stopPropagation();
});

document.getElementById('adminDropdown')?.addEventListener('click', function(event) {
    event.stopPropagation();
});
</script>
</body>
</html>