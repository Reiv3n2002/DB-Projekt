<?php
session_start();
require_once 'assets/database.php';

// Überprüfe ob User eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$db = Database::getInstance();
$pdo = $db->getConnection();

// Hole nur die Sparkonto-Informationen
$stmt = $pdo->query("SELECT * FROM account_prices WHERE account_type = 'Sparkonto' AND is_active = TRUE");
$savings_account = $stmt->fetch();

// Verarbeite das Formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    try {
        // Generiere Kontonummer
        $account_number = 'DE' . str_pad(mt_rand(0, 999999999999999999), 18, '0', STR_PAD_LEFT);
        
        // Hole den Zinssatz aus der account_prices Tabelle
        $interest_rate = $savings_account['credit_interest'];
        
        // Erstelle das Sparkonto
        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, account_number, account_type, balance, currency, interest_rate) 
            VALUES (?, ?, 'Sparkonto', 0.00, 'EUR', ?)
        ");
        
        $stmt->execute([$user_id, $account_number, $interest_rate]);
        
        $_SESSION['success'] = "Ihr Sparkonto wurde erfolgreich eröffnet!";
        header('Location: savings.php');
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Fehler bei der Kontoeröffnung. Bitte versuchen Sie es später erneut.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkonto eröffnen - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/new_account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="container">
        <div class="account-selection">
            <h1>Sparkonto eröffnen</h1>
            <p class="subtitle">Profitieren Sie von attraktiven Zinsen mit unserem Sparkonto</p>

            <form method="POST" action="new_savings_account.php">
                <div class="account-types">
                    <div class="account-type-card">
                        <input type="radio" name="account_type" id="Sparkonto" value="Sparkonto" checked required>
                        <label for="Sparkonto">
                            <div class="card-header">
                                <h3>Sparkonto</h3>
                            </div>
                            <div class="card-content">
                                <div class="interest-rate">
                                    <span
                                        class="amount"><?php echo number_format($savings_account['credit_interest'], 2, ',', '.'); ?>%</span>
                                    <span class="period">Zinsen p.a.</span>
                                </div>
                                <ul class="features">
                                    <li>
                                        <i class="fas fa-check"></i>
                                        Keine monatlichen Gebühren
                                    </li>
                                    <li>
                                        <i class="fas fa-check"></i>
                                        Attraktive Zinsen
                                    </li>
                                    <li>
                                        <i class="fas fa-check"></i>
                                        Flexible Einzahlungen
                                    </li>
                                    <li>
                                        <i class="fas fa-check"></i>
                                        Online Banking Zugang
                                    </li>
                                </ul>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-piggy-bank"></i> Sparkonto jetzt eröffnen
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>