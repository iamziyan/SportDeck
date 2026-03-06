<?php
// admin/dashboard.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$tournamentsCount = $pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();
$teamsCount = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$playersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'player'")->fetchColumn(); // Count registered players
$matchesCount = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

include '../includes/header.php';
?>

<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    
    <!-- Sidebar -->
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Admin Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Dashboard Overview</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Tournaments</a></li>
            <li><a href="teams.php" class="text-muted" style="display:block; padding:0.5rem;">Teams</a></li>
            <li><a href="players.php" class="text-muted" style="display:block; padding:0.5rem;">Players</a></li>
            <li><a href="matches.php" class="text-muted" style="display:block; padding:0.5rem;">Match Fixtures</a></li>
            <li><a href="results.php" class="text-muted" style="display:block; padding:0.5rem;">Results</a></li>
            <li><a href="users.php" class="text-muted" style="display:block; padding:0.5rem;">User Roles</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div>
        <h1 class="page-title mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-3">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>. Here is the overview of the system.</p>

        <div class="grid grid-3 mb-3">
            <div class="card" style="border-left: 4px solid var(--primary);">
                <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; margin-bottom:0.5rem;">Total Tournaments</div>
                <div style="font-size:2rem; font-weight:700;"><?php echo $tournamentsCount; ?></div>
            </div>
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; margin-bottom:0.5rem;">Total Teams</div>
                <div style="font-size:2rem; font-weight:700;"><?php echo $teamsCount; ?></div>
            </div>
            <div class="card" style="border-left: 4px solid var(--warning);">
                <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; margin-bottom:0.5rem;">Registered Users</div>
                <div style="font-size:2rem; font-weight:700;"><?php echo $usersCount; ?></div>
            </div>
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; margin-bottom:0.5rem;">Total Matches</div>
                <div style="font-size:2rem; font-weight:700;"><?php echo $matchesCount; ?></div>
            </div>
        </div>

        <div class="card">
            <h3 class="mb-2">Quick Actions</h3>
            <div class="flex gap-2" style="flex-wrap:wrap;">
                <a href="tournaments.php" class="btn btn-outline">Manage Tournaments</a>
                <a href="users.php" class="btn btn-primary">Manage User Roles</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
