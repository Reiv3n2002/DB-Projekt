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

// Hole die verfügbaren Kontotypen und deren Preise
$stmt = $pdo->query("SELECT * FROM account_prices WHERE is_active = TRUE");
$account_types = $stmt->fetchAll();

// Verarbeite das Formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = $_POST['account_type'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Hole das Willkommensgeschenk für den gewählten Kontotyp
        $stmt = $pdo->prepare("SELECT welcome_bonus FROM account_prices WHERE account_type = ?");
        $stmt->execute([$account_type]);
        $welcome_bonus = $stmt->fetchColumn();
        
        // Generiere Kontonummer
        $account_number = 'DE' . str_pad(mt_rand(0, 999999999999999999), 18, '0', STR_PAD_LEFT);
        
        // Erstelle das Konto mit dem Willkommensbonus als Startguthaben
        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, account_number, account_type, balance, currency) 
            VALUES (?, ?, ?, ?, 'EUR')
        ");
        
        $stmt->execute([$user_id, $account_number, $account_type, $welcome_bonus]);
        
        $_SESSION['success'] = "Ihr Konto wurde erfolgreich eröffnet! Ihr Willkommensgeschenk von " . 
                              number_format($welcome_bonus, 2, ',', '.') . "€ wurde gutgeschrieben.";
        header('Location: dashboard.php');
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
    <title>Konto eröffnen - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/new_account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="container">
        <div class="account-selection">
            <h1>Konto eröffnen</h1>
            <p class="subtitle">Wählen Sie das passende Konto für Ihre Bedürfnisse</p>

            <form method="POST" action="new_account.php">
                <div class="account-types">
                    <?php foreach ($account_types as $type): ?>
                        <div class="account-type-card">
                            <input type="radio" name="account_type" 
                                   id="<?php echo htmlspecialchars($type['account_type']); ?>" 
                                   value="<?php echo htmlspecialchars($type['account_type']); ?>" required>
                            <label for="<?php echo htmlspecialchars($type['account_type']); ?>">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($type['account_type']); ?></h3>
                                </div>
                                <div class="card-content">
                                    <div class="price">
                                        <span class="amount"><?php echo number_format($type['monthly_fee'], 2, ',', '.'); ?>€</span>
                                        <span class="period">pro Monat</span>
                                    </div>
                                    <?php if ($type['welcome_bonus'] > 0): ?>
                                        <div class="welcome-bonus">
                                            <i class="fas fa-gift"></i>
                                            <span class="bonus-amount"><?php echo number_format($type['welcome_bonus'], 2, ',', '.'); ?>€</span>
                                            <span class="bonus-text">Willkommensgeschenk</span>
                                        </div>
                                    <?php endif; ?>
                                    <ul class="features">
                                        <li>
                                            <i class="fas fa-check"></i>
                                            <?php echo $type['card_fee'] == 0 ? 'Kostenlose Bankkarte' : 'Bankkarte für ' . number_format($type['card_fee'], 2, ',', '.') . '€/Jahr'; ?>
                                        </li>
                                        <li>
                                            <i class="fas fa-check"></i>
                                            <?php echo $type['atm_fee'] == 0 ? 'Kostenlose Bargeldabhebungen' : 'Abhebungsgebühr: ' . number_format($type['atm_fee'], 2, ',', '.') . '€'; ?>
                                        </li>
                                        <li>
                                            <i class="fas fa-check"></i>
                                            Überweisungen im SEPA-Raum
                                        </li>
                                        <li>
                                            <i class="fas fa-check"></i>
                                            Online & Mobile Banking
                                        </li>
                                    </ul>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Konto jetzt eröffnen
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 