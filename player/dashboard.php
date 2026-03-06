<?php
// player/dashboard.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requirePlayer();

$user_id = $_SESSION['user_id'];

// Get player data (if linked)
$stmt = $pdo->prepare("
    SELECT p.*, t.name as team_name, tr.name as tournament_name
    FROM players p
    JOIN teams t ON p.team_id = t.id
    JOIN tournaments tr ON t.tournament_id = tr.id
    WHERE p.user_id = ?
");
$stmt->execute([$user_id]);
$player_info = $stmt->fetch();

include '../includes/header.php';
?>

<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    
    <!-- Sidebar -->
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Player Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">My Dashboard</a></li>
            <li><a href="../fixtures.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">All Fixtures</a></li>
            <li><a href="../results.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">All Results</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div>
        <h1 class="page-title mb-1">Player Dashboard</h1>
        <p class="text-muted mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>. View your tournament status below.</p>

        <?php if ($player_info): ?>
            <div class="card mb-3">
                <h3 class="mb-2">Your Profile & Team</h3>
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
                <p>Welcome to SportDeck! You are registered as a player.</p>
                <p class="mt-2 text-sm">You are not currently assigned to a specific team roster by the administrator. Check out the <a href="../index.php">Tournaments page</a> to see what's happening.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
