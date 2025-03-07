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

// Hole Kontoinformationen
$stmt = $pdo->prepare("
    SELECT 
        account_id,
        account_number,
        account_type,
        balance,
        currency,
        is_active,
        created_at 
    FROM accounts 
    WHERE user_id = ? AND is_active = TRUE
");
$stmt->execute([$user_id]);
$accounts = $stmt->fetchAll();

// Neue Hilfsfunktion zur Formatierung der Kontonummer
function formatAccountNumber($accountNumber)
{
    // Gruppiere die Zahlen in 4er-Blöcke nach den ersten 4 Zeichen (DE + 2 Ziffern)
    $formatted =
        substr($accountNumber, 0, 2) .
        ' ' . // DE
        substr($accountNumber, 2, 2) .
        ' ' . // 2 Ziffern
        chunk_split(substr($accountNumber, 4), 4, ' '); // Rest in 4er-Blöcke
    return trim($formatted); // Entferne überschüssige Leerzeichen am Ende
}

// Hole die letzten 5 Transaktionen
$stmt = $pdo->prepare("
    SELECT 
        t.*,
        from_acc.account_number as from_account_number,
        to_acc.account_number as to_account_number
    FROM transactions t
    LEFT JOIN accounts from_acc ON t.from_account_id = from_acc.account_id
    LEFT JOIN accounts to_acc ON t.to_account_id = to_acc.account_id
    WHERE (from_account_id IN (SELECT account_id FROM accounts WHERE user_id = ?)
    OR to_account_id IN (SELECT account_id FROM accounts WHERE user_id = ?))
    AND status = 'Abgeschlossen'
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id, $user_id]);
$recent_transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <nav class="dashboard-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Übersicht</a>
                <a href="transfer.php"><i class="fas fa-exchange-alt"></i> Überweisung</a>
                <a href="savings.php"><i class="fas fa-piggy-bank"></i> Sparkonten</a>
                <!-- <a href="new_account.php"><i class="fas fa-plus-circle"></i> Konto eröffnen</a> -->
            </nav>
        </div>

        <div class="main-content">
            <h1>Willkommen, <?php echo htmlspecialchars(
                $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
            ); ?>.</h1>

            <section class="accounts-overview">
                <h2>Ihre Konten</h2>
                <div class="accounts-grid">
                    <?php if (empty($accounts)): ?>
                    <div class="no-accounts-message">
                        <p>Sie haben noch kein Konto bei uns.</p>
                        <p>Eröffnen Sie jetzt Ihr erstes Konto bei der Spaßkasse!</p>
                        <a href="new_account.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Konto eröffnen
                        </a>
                    </div>
                    <?php else: ?>
                    <?php foreach ($accounts as $account): ?>
                    <div class="account-card">
                        <h3><?php echo htmlspecialchars(
                            $account['account_type']
                        ); ?></h3>
                        <p class="account-number">Kontonummer: <?php echo htmlspecialchars(
                            formatAccountNumber($account['account_number'])
                        ); ?></p>
                        <p class="balance">
                            <?php echo number_format(
                                $account['balance'],
                                2,
                                ',',
                                '.'
                            ); ?> <?php echo htmlspecialchars(
     $account['currency']
 ); ?>
                        </p>
                        <p class="created-at">
                            Eröffnet am: <?php echo date(
                                'd.m.Y',
                                strtotime($account['created_at'])
                            ); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (!empty($accounts)): ?>
            <section class="recent-transactions">
                <h2>Letzte Transaktionen</h2>
                <div class="transactions-list">
                    <?php foreach ($recent_transactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="transaction-date">
                            <?php echo date(
                                'd.m.Y',
                                strtotime($transaction['created_at'])
                            ); ?>
                        </div>
                        <div class="transaction-details">
                            <span class="transaction-type">
                                <?php echo htmlspecialchars(
                                    $transaction['transaction_type']
                                ); ?>
                            </span>
                            <span class="transaction-description">
                                <?php echo htmlspecialchars(
                                    $transaction['description']
                                ); ?>
                            </span>
                            <span class="transaction-amount <?php
                            $isOutgoing = false;
                            foreach ($accounts as $acc) {
                                if (
                                    $acc['account_id'] ==
                                    $transaction['from_account_id']
                                ) {
                                    $isOutgoing = true;
                                    break;
                                }
                            }
                            echo $isOutgoing ? 'negative' : 'positive';
                            ?>">
                                <?php
                                echo $isOutgoing ? '-' : '+';
                                echo number_format(
                                    $transaction['amount'],
                                    2,
                                    ',',
                                    '.'
                                );
                                ?> €
                            </span>
                        </div>
                        <div class="transaction-status">
                            Status: <?php echo htmlspecialchars(
                                $transaction['status']
                            ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>