<?php
/**
 * =====================================================
 * Admin Dashboard - Sports Tournament Management
 * =====================================================
 * Admin-only dashboard with system-wide statistics,
 * user management access, and full control panels.
 * =====================================================
 */

require_once __DIR__ . '/../config.php';
requireAdmin();

$user = getCurrentUser();
$pdo = getDbConnection();

// System-wide statistics for admin
$stats = [
    'users' => 0,
    'tournaments' => 0,
    'teams' => 0,
    'matches' => 0,
    'completed_matches' => 0,
];

$stmt = $pdo->query('SELECT COUNT(*) FROM users');
$stats['users'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM tournaments');
$stats['tournaments'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM teams');
$stats['teams'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM matches');
$stats['matches'] = (int) $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'completed'");
$stats['completed_matches'] = (int) $stmt->fetchColumn();

// Recent users
$stmt = $pdo->query('SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 8');
$recentUsers = $stmt->fetchAll();

// Recent tournaments
$stmt = $pdo->query('
    SELECT t.id, t.name, t.status, t.start_date,
           (SELECT COUNT(*) FROM teams WHERE tournament_id = t.id) as team_count
    FROM tournaments t
    ORDER BY t.created_at DESC
    LIMIT 6
');
$recentTournaments = $stmt->fetchAll();

// User role counts
$stmt = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
$roleCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<section class="admin-dashboard">
    <div class="admin-dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>System overview and management controls</p>
    </div>

    <!-- Admin Stats -->
    <div class="stats-grid admin-stats">
        <div class="stat-card admin-stat">
            <span class="stat-icon">👤</span>
            <span class="stat-value"><?php echo $stats['users']; ?></span>
            <span class="stat-label">Total Users</span>
        </div>
        <div class="stat-card admin-stat">
            <span class="stat-icon">🏆</span>
            <span class="stat-value"><?php echo $stats['tournaments']; ?></span>
            <span class="stat-label">Tournaments</span>
        </div>
        <div class="stat-card admin-stat">
            <span class="stat-icon">👥</span>
            <span class="stat-value"><?php echo $stats['teams']; ?></span>
            <span class="stat-label">Teams</span>
        </div>
        <div class="stat-card admin-stat">
            <span class="stat-icon">⚽</span>
            <span class="stat-value"><?php echo $stats['matches']; ?></span>
            <span class="stat-label">Matches</span>
        </div>
        <div class="stat-card admin-stat">
            <span class="stat-icon">✅</span>
            <span class="stat-value"><?php echo $stats['completed_matches']; ?></span>
            <span class="stat-label">Completed</span>
        </div>
    </div>

    <!-- User Role Breakdown -->
    <div class="admin-section">
        <h2>User Roles</h2>
        <div class="role-breakdown">
            <?php foreach (['admin' => 'Admin', 'organizer' => 'Organizer', 'participant' => 'Participant'] as $role => $label): ?>
                <div class="role-item">
                    <span class="role-label"><?php echo $label; ?></span>
                    <span class="role-count"><?php echo $roleCounts[$role] ?? 0; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="admin-grid">
        <!-- Recent Users -->
        <div class="admin-section">
            <h2>Recent Users</h2>
            <div class="admin-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>User</th><th>Email</th><th>Role</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="users.php" class="btn btn-outline btn-sm">View All Users</a>
        </div>

        <!-- Recent Tournaments -->
        <div class="admin-section">
            <h2>Recent Tournaments</h2>
            <div class="admin-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Tournament</th><th>Status</th><th>Teams</th><th>Start</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTournaments as $t): ?>
                            <tr>
                                <td><a href="../tournaments.php?view=<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></a></td>
                                <td><span class="badge badge-<?php echo $t['status']; ?>"><?php echo ucfirst($t['status']); ?></span></td>
                                <td><?php echo $t['team_count']; ?></td>
                                <td><?php echo date('M j', strtotime($t['start_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="../tournaments.php" class="btn btn-outline btn-sm">Manage Tournaments</a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-quick-actions">
        <h2>Quick Actions</h2>
        <div class="quick-actions-grid">
            <a href="users.php" class="quick-action-card">
                <span class="qa-icon">👤</span>
                <span>Manage Users</span>
            </a>
            <a href="../tournaments.php" class="quick-action-card">
                <span class="qa-icon">🏆</span>
                <span>Create Tournament</span>
            </a>
            <a href="../matches.php" class="quick-action-card">
                <span class="qa-icon">📅</span>
                <span>Schedule Match</span>
            </a>
            <a href="../results.php" class="quick-action-card">
                <span class="qa-icon">📊</span>
                <span>Record Results</span>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
