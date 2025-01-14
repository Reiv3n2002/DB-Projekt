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

$user_id = $_SESSION['user_id'];

// Hole nur Sparkonten
$stmt = $pdo->prepare("
    SELECT 
        account_id,
        account_number,
        account_type,
        balance,
        currency,
        interest_rate,
        created_at 
    FROM accounts 
    WHERE user_id = ? 
    AND account_type = 'Sparkonto' 
    AND is_active = TRUE
");
$stmt->execute([$user_id]);
$savings_accounts = $stmt->fetchAll();

// Hole die letzten 5 Zinsgutschriften
$stmt = $pdo->prepare("
    SELECT 
        t.*,
        acc.account_number
    FROM transactions t
    JOIN accounts acc ON t.to_account_id = acc.account_id
    WHERE t.to_account_id IN (
        SELECT account_id 
        FROM accounts 
        WHERE user_id = ? AND account_type = 'Sparkonto'
    )
    AND t.transaction_type = 'Zinsgutschrift'
    ORDER BY t.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$interest_transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkonten - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <nav class="dashboard-nav">
                <a href="dashboard.php"><i class="fas fa-home"></i> Übersicht</a>
                <a href="transfer.php"><i class="fas fa-exchange-alt"></i> Überweisung</a>
                <a href="savings.php" class="active"><i class="fas fa-piggy-bank"></i> Sparkonten</a>
                <a href="new_account.php"><i class="fas fa-plus-circle"></i> Konto eröffnen</a>
            </nav>
        </div>

        <div class="main-content">
            <h1>Ihre Sparkonten</h1>
            
            <section class="savings-overview">
                <h2>Aktive Sparkonten</h2>
                <div class="accounts-grid">
                    <?php if (empty($savings_accounts)): ?>
                        <div class="no-accounts-message">
                            <p>Sie haben noch kein Sparkonto bei uns.</p>
                            <p>Eröffnen Sie jetzt Ihr erstes Sparkonto und profitieren Sie von attraktiven Zinsen!</p>
                            <a href="new_account.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Sparkonto eröffnen
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($savings_accounts as $account): ?>
                            <div class="account-card">
                                <h3>Sparkonto</h3>
                                <p class="account-number">Kontonummer: <?php echo htmlspecialchars($account['account_number']); ?></p>
                                <p class="balance">
                                    <?php echo number_format($account['balance'], 2, ',', '.'); ?> <?php echo htmlspecialchars($account['currency']); ?>
                                </p>
                                <p class="interest-rate">
                                    Zinssatz: <?php echo number_format($account['interest_rate'], 2); ?>%
                                </p>
                                <p class="created-at">
                                    Eröffnet am: <?php echo date('d.m.Y', strtotime($account['created_at'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (!empty($savings_accounts)): ?>
                <section class="interest-history">
                    <h2>Letzte Zinsgutschriften</h2>
                    <div class="transactions-list">
                        <?php if (empty($interest_transactions)): ?>
                            <p>Noch keine Zinsgutschriften vorhanden.</p>
                        <?php else: ?>
                            <?php foreach ($interest_transactions as $transaction): ?>
                                <div class="transaction-item">
                                    <div class="transaction-date">
                                        <?php echo date('d.m.Y', strtotime($transaction['created_at'])); ?>
                                    </div>
                                    <div class="transaction-details">
                                        <span class="transaction-type">Zinsgutschrift</span>
                                        <span class="transaction-description">
                                            Konto: <?php echo htmlspecialchars($transaction['account_number']); ?>
                                        </span>
                                        <span class="transaction-amount positive">
                                            +<?php echo number_format($transaction['amount'], 2, ',', '.'); ?> €
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 