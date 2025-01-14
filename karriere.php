<?php
session_start();
require_once 'assets/database.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karriere - Spaßkasse</title>
    <link rel="stylesheet" href="assets/style/main.css">
    <link rel="stylesheet" href="assets/style/karriere.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'assets/navbar.php'; ?>

    <main class="karriere-container">
        <h1>Karriere bei der Spaßkasse</h1>
        
        <section class="job-listings">
            <?php
            try {
                $db = Database::getInstance();
                $pdo = $db->getConnection();
                
                $stmt = $pdo->query("
                    SELECT * 
                    FROM jobs 
                    WHERE is_active = 1 
                    ORDER BY 
                        CASE employment_type
                            WHEN 'Festanstellung' THEN 1
                            WHEN 'Ausbildung' THEN 2
                            WHEN 'Praktikum' THEN 3
                        END,
                        created_at DESC
                ");
                
                $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($jobs)) {
                    echo "<p class='no-jobs'>Aktuell sind keine Stellenangebote verfügbar.</p>";
                } else {
                    $currentType = '';
                    foreach ($jobs as $job) {
                        if ($currentType !== $job['employment_type']) {
                            $currentType = $job['employment_type'];
                            echo "<h2 class='employment-type-heading'>{$currentType}</h2>";
                        }
                        ?>
                        <div class="job-card">
                            <div class="job-header" onclick="toggleJobDetails(this)">
                                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                <div class="job-info">
                                    <span class="job-location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($job['location']); ?>
                                    </span>
                                    <span class="job-department">
                                        <i class="fas fa-building"></i> 
                                        <?php echo htmlspecialchars($job['department']); ?>
                                    </span>
                                    <span class="job-schedule">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo htmlspecialchars($job['schedule_type']); ?>
                                    </span>
                                    <?php if ($job['salary_range']): ?>
                                        <span class="job-salary">
                                            <i class="fas fa-euro-sign"></i> 
                                            <?php echo htmlspecialchars($job['salary_range']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                            </div>
                            
                            <div class="job-details hidden">
                                <div class="job-description">
                                    <h4>Beschreibung</h4>
                                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                </div>
                                
                                <?php if ($job['requirements']): ?>
                                    <div class="job-requirements collapsible">
                                        <h4 onclick="toggleRequirements(this)">
                                            Anforderungen
                                            <i class="fas fa-chevron-down"></i>
                                        </h4>
                                        <div class="requirements-content hidden">
                                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="job-footer">
                                    <a href="bewerbung.php?job_id=<?php echo $job['job_id']; ?>" class="apply-button">
                                        Jetzt bewerben
                                    </a>
                                    <span class="job-posted">
                                        Erstellt am: <?php echo date('d.m.Y', strtotime($job['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            } catch (PDOException $e) {
                echo "<p class='error'>Derzeit können keine Stellenangebote geladen werden.</p>";
            }
            ?>
        </section>
    </main>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script src="assets/navbar.js"></script>
    <script>
    function toggleJobDetails(header) {
        const jobCard = header.closest('.job-card');
        const details = jobCard.querySelector('.job-details');
        const toggleIcon = header.querySelector('.toggle-icon');
        
        details.classList.toggle('hidden');
        toggleIcon.classList.toggle('fa-chevron-up');
        toggleIcon.classList.toggle('fa-chevron-down');
    }

    function toggleRequirements(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('i');
        
        content.classList.toggle('hidden');
        icon.classList.toggle('fa-chevron-up');
        icon.classList.toggle('fa-chevron-down');
    }
    </script>
</body>
</html>
