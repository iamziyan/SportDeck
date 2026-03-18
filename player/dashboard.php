<?php
// player/dashboard.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requirePlayer();

$user_id = $_SESSION['user_id'];

// Get player team assignment (if linked by admin)
$stmt = $pdo->prepare("
    SELECT p.*, t.name as team_name, tr.name as tournament_name
    FROM players p
    JOIN teams t ON p.team_id = t.id
    JOIN tournaments tr ON t.tournament_id = tr.id
    WHERE p.user_id = ?
");
$stmt->execute([$user_id]);
$player_info = $stmt->fetch();

// Count active tournament registrations
$stmt2 = $pdo->prepare("
    SELECT COUNT(*) as cnt FROM tournament_registrations
    WHERE user_id = ? AND status = 'registered'
");
$stmt2->execute([$user_id]);
$reg_count = $stmt2->fetch()['cnt'];

include '../includes/header.php';
?>

<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">

    <!-- Sidebar -->
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Player Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">My Dashboard</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem;">Browse Tournaments</a></li>
            <li><a href="my_registrations.php" class="text-muted" style="display:block; padding:0.5rem;">My Registrations</a></li>
            <li><a href="../fixtures.php" class="text-muted" style="display:block; padding:0.5rem;">All Fixtures</a></li>
            <li><a href="../results.php" class="text-muted" style="display:block; padding:0.5rem;">All Results</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div>
        <h1 class="page-title mb-1">Player Dashboard</h1>
        <p class="text-muted mb-3">Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>. Here's your overview.</p>

        <!-- Stats Row -->
        <div class="grid grid-2 mb-3" style="gap:1rem;">
            <div class="card" style="background: linear-gradient(135deg,#eff6ff,#dbeafe); border-color:#bfdbfe; display:flex; align-items:center; gap:1rem;">
                <div style="font-size:2.5rem;">🏆</div>
                <div>
                    <p class="text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; margin-bottom:0.2rem;">Active Registrations</p>
                    <p style="font-size:2rem; font-weight:700; color:var(--primary); margin:0;"><?= $reg_count ?></p>
                </div>
            </div>
            <div class="card" style="display:flex; align-items:center; gap:1rem; background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#bbf7d0;">
                <div style="font-size:2.5rem;">⚡</div>
                <div>
                    <p class="text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; margin-bottom:0.2rem;">Quick Actions</p>
                    <a href="tournaments.php" class="btn btn-primary btn-sm" style="margin-right:0.5rem;">Browse & Join</a>
                    <a href="my_registrations.php" class="btn btn-outline btn-sm">My Entries</a>
                </div>
            </div>
        </div>

        <?php if ($player_info): ?>
            <div class="card mb-3">
                <h3 class="mb-2">Your Team Assignment</h3>
                <div class="grid grid-2">
                    <div>
                        <p class="text-muted mb-1 text-sm">Tournament</p>
                        <p style="font-weight:600; font-size:1.1rem;"><?php echo htmlspecialchars($player_info['tournament_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-muted mb-1 text-sm">Team</p>
                        <p style="font-weight:600; font-size:1.1rem;"><?php echo htmlspecialchars($player_info['team_name']); ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-2">Upcoming Team Matches</h3>
                <?php
                    $team_id = $player_info['team_id'];
                    $stmt_m = $pdo->prepare("
                        SELECT m.*, t1.name as team1, t2.name as team2
                        FROM matches m
                        JOIN teams t1 ON m.team1_id = t1.id
                        JOIN teams t2 ON m.team2_id = t2.id
                        WHERE (m.team1_id = ? OR m.team2_id = ?) AND m.status = 'upcoming'
                        ORDER BY m.match_date ASC LIMIT 5
                    ");
                    $stmt_m->execute([$team_id, $team_id]);
                    $team_matches = $stmt_m->fetchAll();
                ?>
                <?php if (count($team_matches) > 0): ?>
                    <ul style="list-style:none;">
                        <?php foreach($team_matches as $tm): ?>
                        <li style="padding: 1rem; border: 1px solid var(--border-light); border-radius: 6px; margin-bottom: 0.5rem; background: var(--bg-color);">
                            <div class="flex flex-between">
                                <div>
                                    <strong><?php echo htmlspecialchars($tm['team1']); ?></strong> vs <strong><?php echo htmlspecialchars($tm['team2']); ?></strong>
                                </div>
                                <div class="text-muted text-sm"><?php echo displayDateTime($tm['match_date']); ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No upcoming matches for your team right now.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="alert alert-success">
                <p>Welcome to SportDeck! You are registered as a player. 🎉</p>
                <p class="mt-2 text-sm">You haven't been assigned to a specific team yet. In the meantime, <a href="tournaments.php"><strong>browse open tournaments</strong></a> and register for one!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
