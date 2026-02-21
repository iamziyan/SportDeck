<?php
/**
 * =====================================================
 * User Dashboard - Sports Tournament Management
 * =====================================================
 * Dashboard for participants and organizers (non-admin).
 * Shows tournaments they can join, their activity,
 * and limited stats. Admins use admin/dashboard.php.
 * =====================================================
 */

require_once __DIR__ . '/config.php';
requireLogin();

// Redirect admins to admin panel
if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$user = getCurrentUser();
$pdo = getDbConnection();

// Fetch statistics for dashboard
$stats = [
    'tournaments' => 0,
    'teams' => 0,
    'matches' => 0,
    'completed' => 0,
];

$stmt = $pdo->query('SELECT COUNT(*) FROM tournaments');
$stats['tournaments'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM teams');
$stats['teams'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM matches');
$stats['matches'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'completed'");
$stats['completed'] = (int) $stmt->fetchColumn();

// Fetch tournaments for card catalogue
$stmt = $pdo->query('
    SELECT t.id, t.name, t.description, t.sport_type, t.start_date, t.end_date, t.status,
           (SELECT COUNT(*) FROM teams WHERE tournament_id = t.id) as team_count,
           (SELECT COUNT(*) FROM matches WHERE tournament_id = t.id) as match_count
    FROM tournaments t
    ORDER BY t.start_date DESC
    LIMIT 12
');
$tournaments = $stmt->fetchAll();

// Fetch upcoming matches
$stmt = $pdo->query('
    SELECT m.id, m.match_date, m.match_time, m.venue, m.status,
           t1.name as team1_name, t2.name as team2_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    WHERE m.status IN ("scheduled", "in_progress")
    ORDER BY m.match_date, m.match_time
    LIMIT 5
');
$upcomingMatches = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<section class="dashboard">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h1>
        <p>Here's an overview of tournaments and your activity (<?php echo ucfirst($user['role']); ?> account)</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">🏆</span>
            <span class="stat-value"><?php echo $stats['tournaments']; ?></span>
            <span class="stat-label">Tournaments</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👥</span>
            <span class="stat-value"><?php echo $stats['teams']; ?></span>
            <span class="stat-label">Teams</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">⚽</span>
            <span class="stat-value"><?php echo $stats['matches']; ?></span>
            <span class="stat-label">Total Matches</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✅</span>
            <span class="stat-value"><?php echo $stats['completed']; ?></span>
            <span class="stat-label">Completed</span>
        </div>
    </div>

    <!-- Tournament Card Catalogue -->
    <section class="catalogue-section">
        <h2>Tournament Catalogue</h2>
        <div class="card-catalogue">
            <?php if (empty($tournaments)): ?>
                <div class="empty-state">
                    <p>No tournaments yet. <a href="tournaments.php?action=create">Create your first tournament</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($tournaments as $t): ?>
                    <a href="tournaments.php?view=<?php echo $t['id']; ?>" class="catalogue-card">
                        <div class="card-status badge-<?php echo $t['status']; ?>"><?php echo ucfirst($t['status']); ?></div>
                        <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                        <span class="card-sport"><?php echo htmlspecialchars($t['sport_type']); ?></span>
                        <p class="card-dates">
                            <?php echo date('M j', strtotime($t['start_date'])); ?> – 
                            <?php echo date('M j, Y', strtotime($t['end_date'])); ?>
                        </p>
                        <div class="card-meta">
                            <span>👥 <?php echo $t['team_count']; ?> teams</span>
                            <span>⚽ <?php echo $t['match_count']; ?> matches</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="catalogue-actions">
            <a href="tournaments.php" class="btn btn-outline">View All Tournaments</a>
        </div>
    </section>

    <!-- Upcoming Matches -->
    <section class="upcoming-section">
        <h2>Upcoming Matches</h2>
        <?php if (empty($upcomingMatches)): ?>
            <div class="empty-state">
                <p>No upcoming matches scheduled.</p>
            </div>
        <?php else: ?>
            <div class="matches-list">
                <?php foreach ($upcomingMatches as $m): ?>
                    <div class="match-item">
                        <div class="match-teams">
                            <span><?php echo htmlspecialchars($m['team1_name']); ?></span>
                            <span class="vs">vs</span>
                            <span><?php echo htmlspecialchars($m['team2_name']); ?></span>
                        </div>
                        <div class="match-info">
                            <?php echo date('M j, Y', strtotime($m['match_date'])); ?>
                            <?php if ($m['match_time']): ?>
                                at <?php echo date('g:i A', strtotime($m['match_time'])); ?>
                            <?php endif; ?>
                            <?php if ($m['venue']): ?>
                                · <?php echo htmlspecialchars($m['venue']); ?>
                            <?php endif; ?>
                        </div>
                        <a href="matches.php?view=<?php echo $m['id']; ?>" class="btn btn-sm">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
