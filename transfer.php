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

// Hole alle aktiven Konten des Users
$stmt = $pdo->prepare("
    SELECT account_id, account_number, account_type, balance, currency
    FROM accounts 
    WHERE user_id = ? AND is_active = TRUE
");
$stmt->execute([$user_id]);
$accounts = $stmt->fetchAll();

$error_message = '';
$success_message = '';

// Verarbeite Überweisungsformular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_account = $_POST['from_account'] ?? '';
    $to_account = $_POST['to_account'] ?? '';
    $amount = str_replace(',', '.', $_POST['amount']) ?? 0;
    $description = $_POST['description'] ?? '';

    // Validierung
    if (empty($from_account) || empty($to_account) || empty($amount)) {
        $error_message = 'Bitte füllen Sie alle Pflichtfelder aus.';
    } elseif ($amount <= 0) {
        $error_message = 'Der Betrag muss größer als 0 sein.';
    } else {
        try {
            $pdo->beginTransaction();

            // Prüfe Kontostand
            $stmt = $pdo->prepare("SELECT balance FROM accounts WHERE account_id = ? FOR UPDATE");
            $stmt->execute([$from_account]);
            $current_balance = $stmt->fetchColumn();

            if ($current_balance >= $amount) {
                // Erstelle Transaktion
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (
                        from_account_id, 
                        to_account_id, 
                        amount, 
                        description, 
                        transaction_type,
                        status
                    ) VALUES (?, ?, ?, ?, 'Überweisung', 'Abgeschlossen')
                ");
                $stmt->execute([$from_account, $to_account, $amount, $description]);

                // Aktualisiere Kontostände
                $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE account_id = ?");
                $stmt->execute([$amount, $from_account]);

                $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE account_id = ?");
                $stmt->execute([$amount, $to_account]);

                $pdo->commit();
                $success_message = 'Überweisung erfolgreich durchgeführt!';
            } else {
                $error_message = 'Nicht genügend Guthaben auf dem Konto.';
                $pdo->rollBack();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Überweisung - Spaßkasse</title>
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
                <a href="transfer.php" class="active"><i class="fas fa-exchange-alt"></i> Überweisung</a>
                <a href="savings.php"><i class="fas fa-piggy-bank"></i> Sparkonten</a>
                <a href="new_account.php"><i class="fas fa-plus-circle"></i> Konto eröffnen</a>
            </nav>
        </div>

        <div class="main-content">
            <h1>Überweisung</h1>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($accounts)): ?>
                <div class="no-accounts-message">
                    <p>Sie benötigen ein Konto, um Überweisungen durchzuführen.</p>
                    <a href="new_account.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Konto eröffnen
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="transfer-form">
                    <div class="form-group">
                        <label for="from_account">Von Konto:</label>
                        <select name="from_account" id="from_account" required>
                            <option value="">Bitte wählen Sie ein Konto</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?php echo $account['account_id']; ?>">
                                    <?php echo htmlspecialchars($account['account_type'] . ' - ' . 
                                        $account['account_number'] . ' (' . 
                                        number_format($account['balance'], 2, ',', '.') . ' ' . 
                                        $account['currency'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="to_account">Empfänger-Kontonummer:</label>
                        <input type="text" name="to_account" id="to_account" required>
                    </div>

                    <div class="form-group">
                        <label for="amount">Betrag (€):</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Verwendungszweck:</label>
                        <input type="text" name="description" id="description" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Überweisung ausführen
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 