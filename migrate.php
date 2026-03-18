<?php
// migrate.php - Run this ONCE to apply DB migrations
// Delete this file after running!
require_once 'includes/config.php';

$results = [];

// 1. Add max_players column to tournaments
try {
    $pdo->exec("ALTER TABLE tournaments ADD COLUMN max_players INT DEFAULT NULL");
    $results[] = "✅ Added 'max_players' column to tournaments table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        $results[] = "ℹ️ 'max_players' column already exists — skipped.";
    } else {
        $results[] = "❌ Error adding max_players: " . $e->getMessage();
    }
}

// 2. Create tournament_registrations table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tournament_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            tournament_id INT NOT NULL,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('registered','cancelled') DEFAULT 'registered',
            UNIQUE KEY unique_reg (user_id, tournament_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
        )
    ");
    $results[] = "✅ Created 'tournament_registrations' table successfully.";
} catch (PDOException $e) {
    $results[] = "❌ Error creating tournament_registrations: " . $e->getMessage();
}

include 'includes/header.php';
?>
<div style="max-width:700px; margin:4rem auto;">
    <h1 class="page-title mb-3">🛠️ Database Migration</h1>
    <?php foreach ($results as $r): ?>
        <div class="alert <?= str_starts_with($r,'✅') ? 'alert-success' : (str_starts_with($r,'❌') ? 'alert-error' : 'alert-success') ?>" style="margin-bottom:0.75rem;">
            <?= $r ?>
        </div>
    <?php endforeach; ?>
    <p class="text-muted mt-2" style="margin-top:1.5rem;">⚠️ <strong>Delete this file</strong> after migration is done for security.</p>
    <a href="/sportdeck/index.php" class="btn btn-primary" style="margin-top:1rem;">← Back to SportDeck</a>
</div>
<?php include 'includes/footer.php'; ?>
