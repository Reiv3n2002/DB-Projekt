<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spaßkasse - Ihre digitale Bank</title>
    <link rel="stylesheet" href="assets/style/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <div class="container">
        <div class="info-section">
            <div class="about-header">
                <h2>Über uns</h2>
                <div class="underline"></div>
            </div>
            <div class="about-content">
                <p class="intro-text">Die Spaßkasse ist Ihre vertrauenswürdige digitale Bank. Wir bieten:</p>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-wallet"></i>
                        <h3>Kostenloses Girokonto</h3>
                        <p>Verwalten Sie Ihr Geld ohne versteckte Gebühren</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock"></i>
                        <h3>24/7 Online-Banking</h3>
                        <p>Zugriff auf Ihr Konto rund um die Uhr</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt"></i>
                        <h3>Moderne Banking-App</h3>
                        <p>Banking leicht gemacht mit unserer intuitiven App</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <h3>Persönliche Beratung</h3>
                        <p>Individuelle Unterstützung durch unsere Experten</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="contact-widget collapsed" id="contactWidget">
        <div class="contact-header">
            <h4>Wir sind für Sie da:</h4>
            <button class="toggle-btn" onclick="toggleWidget()">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="contact-content">
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <div>
                    <span>Persönlicher Kontakt:</span>
                    <a href="tel:0201112-2024">0201 112-2024</a>
                    <small>(Mo. bis Fr. 09:00-20:00 Uhr)</small>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-desktop"></i>
                <div>
                    <span>Online-Banking-Hotline:</span>
                    <a href="tel:0201103-3050">0201 103-3050</a>
                </div>
            </div>
            <div class="contact-item">
                <i class="fab fa-whatsapp"></i>
                <div>
                    <span>WhatsApp:</span>
                    <a href="tel:0201103-5000">0201 103-5000</a>
                    <small>(09:00-17:00 Uhr)</small>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <span>24h Sperr-Notruf:</span>
                    <a href="tel:116116">116 116</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleWidget() {
        const widget = document.getElementById('contactWidget');
        widget.classList.toggle('collapsed');
    }
    </script>
</body>
</html>