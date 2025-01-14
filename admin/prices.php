<?php
session_start();
require_once '../assets/database.php';

// Prüfen ob User eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

// Prüfen ob User Admin-Rechte hat
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Debug-Ausgabe
    echo "Admin-Status: ";
    var_dump($_SESSION['is_admin']);
    
    // Wenn kein Admin, zeige Fehlermeldung und Link zurück
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Zugriff verweigert - Admin</title>
        <link rel="stylesheet" href="../assets/style/main.css">
        <link rel="stylesheet" href="../assets/style/navbar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <body>
        <?php include '../assets/navbar.php'; ?>
        <div class="container">
            <div class="error-message" style="text-align: center; margin-top: 50px;">
                <h1 style="color: #FF0000;">Zugriff verweigert</h1>
                <p>Sie haben keine Berechtigung, auf diese Seite zuzugreifen.</p>
                <a href="../dashboard.php" style="
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #FF0000;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                ">Zurück zum Dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Datenbankverbindung herstellen
$db = Database::getInstance();
$pdo = $db->getConnection();

// Ab hier folgt der normale Code für Admins
// Preise aktualisieren
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE account_prices 
        SET monthly_fee = ?, 
            card_fee = ?, 
            foreign_payment_fee = ?, 
            overdraft_interest = ?, 
            credit_interest = ?,
            atm_fee = ?
        WHERE price_id = ?
    ");

    foreach ($_POST['prices'] as $priceId => $price) {
        $stmt->execute([
            $price['monthly_fee'],
            $price['card_fee'],
            $price['foreign_payment_fee'],
            $price['overdraft_interest'],
            $price['credit_interest'],
            $price['atm_fee'],
            $priceId
        ]);
    }

    $updateMessage = "Preise wurden erfolgreich aktualisiert!";
}

// Aktuelle Preise laden
$prices = $pdo->query("SELECT * FROM account_prices ORDER BY price_id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontopreise verwalten - Admin</title>
    <link rel="stylesheet" href="../assets/style/main.css">
    <link rel="stylesheet" href="../assets/style/navbar.css">
    <link rel="stylesheet" href="../assets/style/admin/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include '../assets/navbar.php'; ?>
    <div class="admin-container">
        <h1>Kontopreise verwalten</h1>
        
        <?php if (isset($updateMessage)): ?>
            <div class="update-message">
                <i class="fas fa-check-circle"></i> <?php echo $updateMessage; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <table class="price-table">
                <thead>
                    <tr>
                        <th>Kontotyp</th>
                        <th>Monatliche Gebühr</th>
                        <th>Kartengebühr</th>
                        <th>Auslandszahlung</th>
                        <th>Überziehungszins</th>
                        <th>Guthabenzins</th>
                        <th>Geldautomatengebühr</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prices as $price): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($price['account_type']); ?></td>
                            <td>
                                <div class="price-input-container">
                                    <input type="number" step="0.01" class="price-input" 
                                        name="prices[<?php echo $price['price_id']; ?>][monthly_fee]" 
                                        value="<?php echo $price['monthly_fee']; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="price-input-container">
                                    <input type="number" step="0.01" class="price-input" 
                                        name="prices[<?php echo $price['price_id']; ?>][card_fee]" 
                                        value="<?php echo $price['card_fee']; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="price-input-container">
                                    <input type="number" step="0.01" class="price-input" 
                                        name="prices[<?php echo $price['price_id']; ?>][foreign_payment_fee]" 
                                        value="<?php echo $price['foreign_payment_fee']; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="price-input-container">
                                    <input type="number" step="0.01" class="price-input" 
                                        name="prices[<?php echo $price['price_id']; ?>][overdraft_interest]" 
                                        value="<?php echo $price['overdraft_interest']; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="price-input-container">
                                    <input type="number" step="0.01" class="price-input" 
                                        name="prices[<?php echo $price['price_id']; ?>][credit_interest]" 
                                        value="<?php echo $price['credit_interest']; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="price-input-container">
                                    <input type="number" step="0.01" class="price-input" 
                                        name="prices[<?php echo $price['price_id']; ?>][atm_fee]" 
                                        value="<?php echo $price['atm_fee']; ?>">
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="save-button">
                <i class="fas fa-save"></i> Änderungen speichern
            </button>
        </form>
    </div>
</body>
</html> 