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

// Hole die verfügbaren Kontotypen und deren Preise (ohne Sparkonten)
$stmt = $pdo->query(
    "SELECT * FROM account_prices WHERE account_type NOT IN ('Sparkonto', 'Festgeldkonto') AND is_active = TRUE"
);
$account_types = $stmt->fetchAll();

// Verarbeite das Formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = $_POST['account_type'];
    $user_id = $_SESSION['user_id'];

    try {
        // Hole das Willkommensgeschenk und validiere den account_type
        $stmt = $pdo->prepare("
            SELECT welcome_bonus, account_type, LENGTH(account_type) as type_length 
            FROM account_prices 
            WHERE account_type = ? 
            AND is_active = TRUE 
            LIMIT 1
        ");
        $stmt->execute([$account_type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new PDOException('Ungültiger Kontotyp ausgewählt.');
        }
        
        // Debug-Information
        error_log("Account Type: " . $result['account_type'] . ", Length: " . $result['type_length']);
        
        $welcome_bonus = $result['welcome_bonus'];
        $validated_account_type = $result['account_type'];

        // Generiere eindeutige Kontonummer
        do {
            $account_number =
                'DE' .
                str_pad(mt_rand(0, 999999999999999999), 18, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM accounts WHERE account_number = ?'
            );
            $stmt->execute([$account_number]);
        } while ($stmt->fetchColumn() > 0);

        // Starte eine Transaktion
        $pdo->beginTransaction();

        // Erstelle das Konto mit dem Willkommensbonus als Startguthaben
        $stmt = $pdo->prepare("
            INSERT INTO accounts (user_id, account_number, account_type, balance, currency) 
            VALUES (?, ?, ?, ?, 'EUR')
        ");

        $stmt->execute([
            $user_id,
            $account_number,
            $validated_account_type, // Verwende den validierten Wert
            $welcome_bonus,
        ]);

        // Commit die Transaktion
        $pdo->commit();

        $_SESSION['success'] =
            'Ihr Konto wurde erfolgreich eröffnet! Ihr Willkommensgeschenk von ' .
            number_format($welcome_bonus, 2, ',', '.') .
            '€ wurde gutgeschrieben.';
        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        // Rollback bei Fehler
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] =
            'Fehler bei der Kontoeröffnung: ' . $e->getMessage();
        // Füge Logging hinzu
        error_log('Fehler bei Kontoeröffnung: ' . $e->getMessage());
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
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
        </div>
        <?php endif; ?>

        <div class="account-selection">
            <h1>Konto eröffnen</h1>
            <p class="subtitle">Wählen Sie das passende Konto für Ihre Bedürfnisse</p>

            <form method="POST" action="new_account.php">
                <div class="account-types">
                    <?php foreach ($account_types as $type): ?>
                    <div class="account-type-card">
                        <input type="radio" name="account_type" id="<?php echo htmlspecialchars(
                            $type['account_type']
                        ); ?>" value="<?php echo htmlspecialchars(
    $type['account_type']
); ?>" required>
                        <label for="<?php echo htmlspecialchars(
                            $type['account_type']
                        ); ?>">
                            <div class="card-header">
                                <h3><?php echo htmlspecialchars(
                                    $type['account_type']
                                ); ?></h3>
                            </div>
                            <div class="card-content">
                                <div class="price">
                                    <span class="amount"><?php echo number_format(
                                        $type['monthly_fee'],
                                        2,
                                        ',',
                                        '.'
                                    ); ?>€</span>
                                    <span class="period">pro Monat</span>
                                </div>
                                <?php if ($type['welcome_bonus'] > 0): ?>
                                <div class="welcome-bonus">
                                    <i class="fas fa-gift"></i>
                                    <span class="bonus-amount"><?php echo number_format(
                                        $type['welcome_bonus'],
                                        2,
                                        ',',
                                        '.'
                                    ); ?>€</span>
                                    <span class="bonus-text">Willkommensgeschenk</span>
                                </div>
                                <?php endif; ?>
                                <ul class="features">
                                    <li>
                                        <i class="fas fa-check"></i>
                                        <?php echo $type['card_fee'] == 0
                                            ? 'Kostenlose Bankkarte'
                                            : 'Bankkarte für ' .
                                                number_format(
                                                    $type['card_fee'],
                                                    2,
                                                    ',',
                                                    '.'
                                                ) .
                                                '€/Jahr'; ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-check"></i>
                                        <?php echo $type['atm_fee'] == 0
                                            ? 'Kostenlose Bargeldabhebungen'
                                            : 'Abhebungsgebühr: ' .
                                                number_format(
                                                    $type['atm_fee'],
                                                    2,
                                                    ',',
                                                    '.'
                                                ) .
                                                '€'; ?>
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