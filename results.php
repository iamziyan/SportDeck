<?php
/**
 * =====================================================
 * Results Module - Sports Tournament Management
 * =====================================================
 * View match results, record/update scores for completed
 * matches. Shows scoreboards and result history.
 * =====================================================
 */

require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDbConnection();
$message = '';
$error = '';

// Record or update result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $matchId = (int) ($_POST['match_id'] ?? 0);
    $team1Score = (int) ($_POST['team1_score'] ?? 0);
    $team2Score = (int) ($_POST['team2_score'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$matchId) {
        $error = 'Invalid match.';
    } else {
        try {
            // Fetch match to get team IDs
            $stmt = $pdo->prepare('SELECT team1_id, team2_id FROM matches WHERE id = ?');
            $stmt->execute([$matchId]);
            $match = $stmt->fetch();
            if (!$match) {
                $error = 'Match not found.';
            } else {
                $winnerId = null;
                $isDraw = 0;
                if ($team1Score > $team2Score) {
                    $winnerId = $match['team1_id'];
                } elseif ($team2Score > $team1Score) {
                    $winnerId = $match['team2_id'];
                } else {
                    $isDraw = 1;
                }
                
                // Upsert result
                $stmt = $pdo->prepare('
                    INSERT INTO results (match_id, team1_score, team2_score, winner_id, is_draw, notes, recorded_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    team1_score = VALUES(team1_score), team2_score = VALUES(team2_score),
                    winner_id = VALUES(winner_id), is_draw = VALUES(is_draw), notes = VALUES(notes),
                    recorded_by = VALUES(recorded_by)
                ');
                $stmt->execute([$matchId, $team1Score, $team2Score, $winnerId, $isDraw, $notes ?: null, $_SESSION['user_id']]);
                
                // Update match status to completed
                $pdo->prepare('UPDATE matches SET status = ? WHERE id = ?')->execute(['completed', $matchId]);
                
                $message = 'Result recorded successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Failed to record result.';
            error_log($e->getMessage());
        }
    }
}

// Fetch results with match and team info
$tournamentFilter = isset($_GET['tournament']) ? (int) $_GET['tournament'] : null;
$sql = 'SELECT m.id as match_id, m.match_date, m.match_time, m.venue, m.status,
               t1.name as team1_name, t2.name as team2_name, t1.id as team1_id, t2.id as team2_id,
               tr.name as tournament_name, m.tournament_id,
               r.team1_score, r.team2_score, r.winner_id, r.is_draw, r.notes
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        JOIN tournaments tr ON m.tournament_id = tr.id
        LEFT JOIN results r ON m.id = r.match_id
        WHERE m.status = "completed" OR r.id IS NOT NULL
        ';
$params = [];
if ($tournamentFilter) {
    $sql .= ' AND m.tournament_id = ?';
    $params[] = $tournamentFilter;
}
$sql .= ' ORDER BY m.match_date DESC, m.match_time DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Get tournaments for filter
$tournaments = $pdo->query('SELECT id, name FROM tournaments ORDER BY start_date DESC')->fetchAll(PDO::FETCH_KEY_PAIR);

// Pending matches (scheduled, no result yet) - for recording
$pendingSql = 'SELECT m.id, m.match_date, m.match_time, t1.name as team1_name, t2.name as team2_name, tr.name as tournament_name
               FROM matches m
               JOIN teams t1 ON m.team1_id = t1.id
               JOIN teams t2 ON m.team2_id = t2.id
               JOIN tournaments tr ON m.tournament_id = tr.id
               WHERE m.status IN ("scheduled","in_progress")
               AND NOT EXISTS (SELECT 1 FROM results WHERE match_id = m.id)
               ORDER BY m.match_date, m.match_time LIMIT 10';
$pendingMatches = $pdo->query($pendingSql)->fetchAll();

$pageTitle = 'Results';
require_once __DIR__ . '/includes/header.php';
?>

<section class="results-module">
    <h1>Match Results</h1>
    
    <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <!-- Record Result Form (for pending matches) -->
    <?php if (!empty($pendingMatches)): ?>
        <div class="form-card">
            <h3>Record Result</h3>
            <form method="POST">
                <input type="hidden" name="action" value="record">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Match</label>
                        <select name="match_id" required>
                            <option value="">Select match</option>
                            <?php foreach ($pendingMatches as $pm): ?>
                                <option value="<?php echo $pm['id']; ?>">
                                    <?php echo htmlspecialchars($pm['team1_name']); ?> vs <?php echo htmlspecialchars($pm['team2_name']); ?>
                                    (<?php echo date('M j', strtotime($pm['match_date'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Team 1 Score</label>
                        <input type="number" name="team1_score" value="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Team 2 Score</label>
                        <input type="number" name="team2_score" value="0" min="0" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Notes</label>
                        <input type="text" name="notes" placeholder="Optional notes">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Record Result</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="filter-bar">
        <form method="GET">
            <label>Tournament:</label>
            <select name="tournament" onchange="this.form.submit()">
                <option value="">All</option>
                <?php foreach ($tournaments as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $tournamentFilter == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <h3>Result History</h3>
    <?php if (empty($results)): ?>
        <div class="empty-state">No results recorded yet.</div>
    <?php else: ?>
        <div class="results-grid">
            <?php foreach ($results as $r): ?>
                <div class="result-card">
                    <div class="result-tournament"><?php echo htmlspecialchars($r['tournament_name']); ?></div>
                    <div class="result-teams">
                        <span class="team-name <?php echo ($r['winner_id'] ?? null) == $r['team1_id'] ? 'winner' : ''; ?>"><?php echo htmlspecialchars($r['team1_name']); ?></span>
                        <span class="score"><?php echo (int)($r['team1_score'] ?? 0); ?> - <?php echo (int)($r['team2_score'] ?? 0); ?></span>
                        <span class="team-name <?php echo ($r['winner_id'] ?? null) == $r['team2_id'] ? 'winner' : ''; ?>"><?php echo htmlspecialchars($r['team2_name']); ?></span>
                    </div>
                    <div class="result-meta">
                        <?php echo date('M j, Y', strtotime($r['match_date'])); ?>
                        <?php if ($r['venue']): ?> · <?php echo htmlspecialchars($r['venue']); ?><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
