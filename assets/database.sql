-- Benutzer Tabelle
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NULL,
    address TEXT NULL,
    phone_number VARCHAR(20),
    is_admin BOOLEAN DEFAULT FALSE,
    is_locked TINYINT(1) DEFAULT 0,
    locked_reason VARCHAR(255) DEFAULT NULL,
    locked_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
); 

-- Füge den Admin-User hinzu

INSERT INTO users (username, password_hash, email, first_name, last_name) 

VALUES (

    'Admin', 

    '$2y$10$8FOhV6KqMRlZXqH7D3SWVeqPRhkGgKxuaBPZQjPRKQkK9QVpGpxTC', -- password_hash für 'admin'

    'admin@example.com',

    'Admin',

    'User'

);

-- Konten Tabelle
CREATE TABLE accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    account_type VARCHAR(50) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'EUR',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    interest_rate DECIMAL(4,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Transaktionen Tabelle
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    from_account_id INT,
    to_account_id INT,
    amount DECIMAL(15,2) NOT NULL,
    transaction_type ENUM('Überweisung', 'Einzahlung', 'Abhebung', 'Dauerauftrag') NOT NULL,
    description TEXT,
    status ENUM('Ausstehend', 'Abgeschlossen', 'Fehlgeschlagen') DEFAULT 'Ausstehend',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES accounts(account_id),
    FOREIGN KEY (to_account_id) REFERENCES accounts(account_id)
);

-- Vereinfachte Security-Logs Tabelle
CREATE TABLE security_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type ENUM('Login', 'Logout', 'Passwort ändern') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Wiederherstellungscodes
CREATE TABLE recovery_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    recovery_code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Optional: Erstelle einen Recovery-Code für den Admin

INSERT INTO recovery_codes (user_id, recovery_code)

VALUES (

    LAST_INSERT_ID(),

    '123456'

);

CREATE TABLE account_prices (
    price_id INT PRIMARY KEY AUTO_INCREMENT,
    account_type VARCHAR(50) NOT NULL,
    monthly_fee DECIMAL(10,2) DEFAULT 0.00,
    card_fee DECIMAL(10,2) DEFAULT 0.00,
    foreign_payment_fee DECIMAL(10,2) DEFAULT 0.00,
    overdraft_interest DECIMAL(5,2) DEFAULT 0.00,
    credit_interest DECIMAL(5,2) DEFAULT CASE 
        WHEN account_type = 'Basis-Konto' THEN 0.01
        WHEN account_type = 'Komfort-Konto' THEN 0.05
        WHEN account_type = 'Premium-Konto' THEN 0.10
        WHEN account_type = 'Student & Azubi' THEN 0.01
        WHEN account_type = 'Sparkonto' THEN 2.50
        WHEN account_type = 'Festgeldkonto' THEN 3.00
        ELSE 0.00
    END,
    atm_fee DECIMAL(10,2) DEFAULT 0.00,
    welcome_bonus DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT valid_account_type CHECK (
        account_type IN (
            'Basis-Konto', 
            'Komfort-Konto', 
            'Premium-Konto', 
            'Student & Azubi',
            'Sparkonto',
            'Festgeldkonto'
        )
    )
);

-- Füge die Standardwerte ein
INSERT INTO account_prices (
    account_type, 
    monthly_fee, 
    card_fee, 
    foreign_payment_fee, 
    overdraft_interest, 
    credit_interest,
    atm_fee,
    welcome_bonus
) VALUES 
('Basis-Konto', 3.90, 0.00, 1.50, 11.90, 0.01, 2.50, 250.00),
('Komfort-Konto', 7.90, 0.00, 0.00, 10.90, 0.05, 0.00, 500.00),
('Premium-Konto', 12.90, 0.00, 0.00, 9.90, 0.10, 0.00, 1000.00),
('Student & Azubi', 0.00, 0.00, 0.00, 8.90, 0.01, 0.00, 100.00),
('Sparkonto', 0.00, 0.00, 0.00, 0.00, 2.50, 0.00, 0.00),
('Festgeldkonto', 0.00, 0.00, 0.00, 0.00, 3.00, 0.00, 0.00);

-- Karriere/Jobs Tabelle
CREATE TABLE jobs (
    job_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    employment_type ENUM('Festanstellung', 'Ausbildung', 'Praktikum') NOT NULL,
    schedule_type ENUM('Vollzeit', 'Teilzeit', 'Vollzeit & Teilzeit') NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location VARCHAR(100),
    department VARCHAR(50),
    salary_range VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Neue Beispiel-Jobs für verschiedene Kategorien
INSERT INTO jobs (
    title,
    employment_type,
    schedule_type,
    description,
    requirements,
    location,
    department,
    salary_range,
    expires_at,
    created_by
) VALUES 
(
    'Ausbildung zum Bankkaufmann (m/w/d)',
    'Ausbildung',
    'Vollzeit',
    'Start deiner Karriere mit einer fundierten Ausbildung im Bankwesen. Lerne alle Bereiche einer modernen Bank kennen.',
    '- Guter Schulabschluss (Abitur oder Fachabitur)\n- Interesse an Finanzen und Wirtschaft\n- Teamfähigkeit\n- Kommunikationsstärke',
    'Frankfurt am Main',
    'Ausbildung',
    '1000€ - 1200€ p.a.',
    '2024-12-31',
    1
),
(
    'Praktikum Digital Banking',
    'Praktikum',
    'Vollzeit',
    'Sammle wertvolle Erfahrungen im Bereich Digital Banking und lerne die neuesten FinTech-Trends kennen.',
    '- Studium der Wirtschaftsinformatik oder BWL\n- Erste Programmierkenntnisse\n- Analytisches Denken\n- Eigeninitiative',
    'Berlin',
    'Digital Banking',
    '1000€ pro Monat',
    '2024-10-31',
    1
),
(
    'Kundenberater im Privatkundengeschäft',
    'Festanstellung',
    'Teilzeit',
    'Beratung und Betreuung unserer Privatkunden in allen Finanzangelegenheiten.',
    '- Abgeschlossene Bankausbildung\n- Beratungserfahrung\n- Vertriebsaffinität\n- Flexibilität',
    'Hamburg',
    'Privatkundengeschäft',
    '45.000€ - 55.000€ p.a.',
    '2024-11-30',
    1
);

-- Bewerbungen Tabelle
CREATE TABLE job_applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    applicant_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    resume_url TEXT,
    cover_letter TEXT,
    status ENUM('Neu', 'In Bearbeitung', 'Angenommen', 'Abgelehnt') DEFAULT 'Neu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id)
);

ALTER TABLE accounts MODIFY COLUMN account_type VARCHAR(50) NOT NULL;

-- Neue Prozedur zum Sperren eines Benutzers
DELIMITER //
CREATE PROCEDURE lock_user(
    IN p_user_id INT,
    IN p_locked_reason TEXT
)
BEGIN
    UPDATE users 
    SET is_locked = TRUE,
        locked_reason = p_locked_reason,
        locked_at = CURRENT_TIMESTAMP
    WHERE user_id = p_user_id;
END //
DELIMITER ;

-- Neue Prozedur zum Entsperren eines Benutzers
DELIMITER //
CREATE PROCEDURE unlock_user(
    IN p_user_id INT
)
BEGIN
    UPDATE users 
    SET is_locked = FALSE,
        locked_reason = NULL,
        locked_at = NULL
    WHERE user_id = p_user_id;
END //
DELIMITER ;
