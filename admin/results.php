<?php
// admin/results.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['record'])) {
        $match_id = intval($_POST['match_id']);
        $winner_team_id = !empty($_POST['winner_team_id']) ? intval($_POST['winner_team_id']) : null;
        $score = sanitize($_POST['score']);

        try {
            $pdo->beginTransaction();
            // Check if result exists
            $stmt = $pdo->prepare("SELECT id FROM results WHERE match_id = ?");
            $stmt->execute([$match_id]);
            if ($stmt->rowCount() > 0) {
                // Update
                $stmt2 = $pdo->prepare("UPDATE results SET winner_team_id = ?, score = ? WHERE match_id = ?");
                $stmt2->execute([$winner_team_id, $score, $match_id]);
            } else {
                // Insert
                $stmt2 = $pdo->prepare("INSERT INTO results (match_id, winner_team_id, score) VALUES (?, ?, ?)");
                $stmt2->execute([$match_id, $winner_team_id, $score]);
            }

            // Mark match as completed
            $stmt3 = $pdo->prepare("UPDATE matches SET status = 'completed' WHERE id = ?");
            $stmt3->execute([$match_id]);
            
            $pdo->commit();
            $msg = "<div class='alert alert-success'>Result recorded & match marked as completed.</div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "<div class='alert alert-error'>Failed to record result.</div>";
        }
    }
}

$upcoming_matches = $pdo->query("
    SELECT m.id, m.match_date, t1.id as t1_id, t1.name as team1, t2.id as t2_id, t2.name as team2, tr.name as tr_name 
    FROM matches m 
    JOIN teams t1 ON m.team1_id = t1.id 
    JOIN teams t2 ON m.team2_id = t2.id 
    JOIN tournaments tr ON m.tournament_id = tr.id
    WHERE m.status = 'upcoming'
    ORDER BY m.match_date ASC
")->fetchAll();

$results = $pdo->query("
    SELECT r.*, m.match_date, t1.name as team1, t2.name as team2, w.name as winner 
    FROM results r 
    JOIN matches m ON r.match_id = m.id 
    JOIN teams t1 ON m.team1_id = t1.id 
    JOIN teams t2 ON m.team2_id = t2.id 
    LEFT JOIN teams w ON r.winner_team_id = w.id
    ORDER BY r.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>
<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Admin Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Dashboard Overview</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Tournaments</a></li>
            <li><a href="teams.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Teams</a></li>
            <li><a href="players.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Players</a></li>
            <li><a href="matches.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Match Fixtures</a></li>
            <li><a href="results.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Results</a></li>
            <li><a href="users.php" class="text-muted" style="display:block; padding:0.5rem;">User Roles</a></li>
        </ul>
    </div>
    
    <div>
        <h1 class="page-title mb-2">Record Results</h1>
        <?php echo $msg; ?>

        <?php if(count($upcoming_matches) > 0): ?>
            <?php foreach($upcoming_matches as $um): ?>
            <div class="card mb-2" style="padding:1rem;">
                <form method="POST" class="flex flex-between gap-2" style="flex-wrap:wrap; align-items:flex-end;">
                    <input type="hidden" name="match_id" value="<?php echo $um['id']; ?>">
                    
                    <div style="flex:2">
                        <div class="text-sm text-muted mb-1"><?php echo displayDateTime($um['match_date']) . ' | ' . htmlspecialchars($um['tr_name']); ?></div>
                        <div style="font-weight:600; font-size:1.1rem;"><?php echo htmlspecialchars($um['team1']) . ' vs ' . htmlspecialchars($um['team2']); ?></div>
                    </div>
                    
                    <div class="form-group mb-0" style="flex:1">
                        <label class="form-label" style="font-size:0.8rem;">Score Line</label>
                        <input type="text" name="score" class="form-control" placeholder="e.g. 2-1" required>
                    </div>

                    <div class="form-group mb-0" style="flex:1.5">
                        <label class="form-label" style="font-size:0.8rem;">Winner</label>
                        <select name="winner_team_id" class="form-control">
                            <option value="">Draw / No Winner</option>
                            <option value="<?php echo $um['t1_id']; ?>"><?php echo htmlspecialchars($um['team1']); ?></option>
                            <option value="<?php echo $um['t2_id']; ?>"><?php echo htmlspecialchars($um['team2']); ?></option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" name="record" class="btn btn-primary h-full">Save Result</button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-success mt-2 mb-3">No pending match results to record.</div>
        <?php endif; ?>

        <h3 class="mt-2 mb-2">Recent Results</h3>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Date</th><th>Matchup</th><th>Score</th><th>Winner</th></tr></thead>
                <tbody>
                    <?php foreach($results as $r): ?>
                    <tr>
                        <td><?php echo displayDate($r['match_date']); ?></td>
                        <td><strong><?php echo htmlspecialchars($r['team1']); ?></strong> vs <strong><?php echo htmlspecialchars($r['team2']); ?></strong></td>
                        <td><span style="font-family:monospace; padding:0.2rem 0.6rem; background:var(--bg-color); border-radius:4px; font-weight:600;"><?php echo htmlspecialchars($r['score']); ?></span></td>
                        <td><?php echo $r['winner'] ? htmlspecialchars($r['winner']) : '<span class="text-muted">Draw</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
