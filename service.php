<?php
// Datenbank-Verbindung einbinden
require_once 'assets/database.php';

// Neue Zeilen:
$db = Database::getInstance();
$pdo = $db->getConnection();

// Preise aus der Datenbank laden
$stmt = $pdo->query("SELECT * FROM account_prices WHERE is_active = TRUE");
$prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preise in ein übersichtliches Array umwandeln
$accountPrices = [];
foreach ($prices as $price) {
    $accountPrices[$price['account_type']] = $price;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/service.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <main class="service-container">
        <h1>Service Center</h1>
        
        <section class="service-grid">
            <div class="service-card">
                <i class="fas fa-question-circle"></i>
                <h3>FAQ</h3>
                <p>Häufig gestellte Fragen und Antworten zu unseren Services und Leistungen</p>
                <a href="#" class="service-link" data-modal="faq-modal">Mehr erfahren</a>
            </div>
            
            <div class="service-card">
                <i class="fas fa-headset"></i>
                <h3>Kontakt</h3>
                <p>Unser Support-Team steht Ihnen bei allen Fragen zur Verfügung</p>
                <a href="#" class="service-link" data-modal="contact-modal">Kontakt aufnehmen</a>
            </div>
            
            <div class="service-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Online Banking</h3>
                <p>Professionelle Unterstützung bei allen Online-Banking Fragen</p>
                <a href="#" class="service-link" data-modal="banking-modal">Zum Support</a>
            </div>
            
        </section>
        
        <section class="contact-info">
            <h2>Direkter Kontakt</h2>
            <div class="contact-methods">
                <div class="contact-method">
                    <h3>Telefon</h3>
                    <p>0800 / 123 456 789</p>
                    <p class="subtitle">Mo-Fr 8:00-20:00 Uhr</p>
                </div>
                
                <div class="contact-method">
                    <h3>E-Mail</h3>
                    <p>service@spasskasse.de</p>
                    <p class="subtitle">24/7 erreichbar</p>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/navbar.js"></script>

    <!-- Modals -->
    <div id="faq-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Häufig gestellte Fragen</h2>
            <div class="accordion-container">
                <div class="accordion-item standard-accordion">
                    <div class="accordion-header">
                        <h3>Wie kann ich mein Online-Banking aktivieren?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Sie können Ihr Online-Banking direkt in einer unserer Filialen oder online mit Ihrer Kontonummer und Legitimation aktivieren. Folgen Sie dazu diesen Schritten:</p>
                        <ul>
                            <li>Legitimieren Sie sich mit Ihrem Personalausweis</li>
                            <li>Wählen Sie einen sicheren Benutzernamen</li>
                            <li>Erstellen Sie ein sicheres Passwort</li>
                            <li>Aktivieren Sie die 2-Faktor-Authentifizierung</li>
                        </ul>
                    </div>
                </div>
                
                <div class="accordion-item standard-accordion">
                    <div class="accordion-header">
                        <h3>Wie sicher ist das Online-Banking?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Wir verwenden modernste Sicherheitstechnologien und eine 2-Faktor-Authentifizierung für maximale Sicherheit. Unsere Sicherheitsmaßnahmen umfassen:</p>
                        <ul>
                            <li>256-Bit SSL-Verschlüsselung</li>
                            <li>Biometrische Authentifizierung</li>
                            <li>Automatische Abmeldung</li>
                            <li>Verdachtsbasierte Transaktionsüberwachung</li>
                        </ul>
                    </div>
                </div>
                
                <div class="accordion-item prices-accordion">
                    <div class="accordion-header">
                        <h3>Was kostet ein Girokonto?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Wir bieten verschiedene Kontomodelle an:</p>
                        <div class="account-prices">
                            <?php foreach ($accountPrices as $type => $price): ?>
                                <div class="price-item">
                                    <h4><?php echo htmlspecialchars($type); ?></h4>
                                    <ul>
                                        <li>
                                            <strong>Monatliche Gebühr:</strong> 
                                            <?php echo number_format($price['monthly_fee'], 2, ',', '.'); ?>€
                                        </li>
                                        <li>
                                            <strong>Auslandszahlung:</strong>
                                            <?php if ($price['foreign_payment_fee'] > 0): ?>
                                                <?php echo number_format($price['foreign_payment_fee'], 2, ',', '.'); ?>€
                                            <?php else: ?>
                                                <span class="included">✓ Inklusive</span>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <strong>Geldautomaten:</strong>
                                            <?php if ($price['atm_fee'] > 0): ?>
                                                <?php echo number_format($price['atm_fee'], 2, ',', '.'); ?>€
                                            <?php else: ?>
                                                <span class="included">✓ Inklusive</span>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <strong>Überziehungszins:</strong> 
                                            <?php echo number_format($price['overdraft_interest'], 2, ',', '.'); ?>%
                                        </li>
                                        <li>
                                            <strong>Guthabenzins:</strong> 
                                            <?php echo number_format($price['credit_interest'], 3, ',', '.'); ?>%
                                        </li>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="contact-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Kontaktmöglichkeiten</h2>
            <div class="contact-options">
                <div class="contact-option">
                    <i class="fas fa-phone"></i>
                    <h3>Telefonisch</h3>
                    <p>0800 / 123 456 789</p>
                    <p class="subtitle">Mo-Fr 8:00-20:00 Uhr</p>
                </div>
                <div class="contact-option">
                    <i class="fas fa-envelope"></i>
                    <h3>Per E-Mail</h3>
                    <p>service@spasskasse.de</p>
                    <p class="subtitle">24/7 erreichbar</p>
                </div>
                <div class="contact-option">
                    <i class="fas fa-comments"></i>
                    <h3>Live-Chat</h3>
                    <p>Direkt mit uns chatten</p>
                    <p class="subtitle">Mo-Fr 9:00-18:00 Uhr</p>
                </div>
            </div>
        </div>
    </div>

    <div id="banking-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Online-Banking Support</h2>
            <div class="support-accordion">
                <div class="accordion-item standard-accordion">
                    <div class="accordion-header">
                        <h3>Erste Schritte im Online-Banking</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Wir helfen Ihnen bei der Einrichtung und den ersten Schritten:</p>
                        <ul>
                            <li>Registrierung und Aktivierung</li>
                            <li>Navigation im Online-Banking</li>
                            <li>Überweisungen durchführen</li>
                            <li>Daueraufträge einrichten</li>
                        </ul>
                    </div>
                </div>
                
                <div class="accordion-item standard-accordion">
                    <div class="accordion-header">
                        <h3>Technische Unterstützung</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Bei technischen Problemen unterstützen wir Sie:</p>
                        <ul>
                            <li>Passwort zurücksetzen</li>
                            <li>App-Installation</li>
                            <li>Browsereinstellungen</li>
                            <li>Fehlerbehebung</li>
                        </ul>
                    </div>
                </div>
                
                <div class="accordion-item standard-accordion">
                    <div class="accordion-header">
                        <h3>Sicherheitshinweise</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p>Wichtige Sicherheitstipps für Ihr Online-Banking:</p>
                        <ul>
                            <li>Regelmäßige Passwortänderung</li>
                            <li>Sichere WLAN-Verbindung nutzen</li>
                            <li>Aktuelle Software verwenden</li>
                            <li>Phishing-Schutz beachten</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/service.js"></script>
</body>
</html>
