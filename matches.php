<?php
/**
 * =====================================================
 * Matches Module - Sports Tournament Management
 * =====================================================
 * Lists matches, create new matches, view match details.
 * Allows scheduling fixtures between teams.
 * =====================================================
 */

require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDbConnection();
$message = '';
$error = '';

// Create new match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $tournamentId = (int) ($_POST['tournament_id'] ?? 0);
    $team1Id = (int) ($_POST['team1_id'] ?? 0);
    $team2Id = (int) ($_POST['team2_id'] ?? 0);
    $matchDate = $_POST['match_date'] ?? '';
    $matchTime = $_POST['match_time'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $roundNumber = (int) ($_POST['round_number'] ?? 1);
    
    if (!$tournamentId || !$team1Id || !$team2Id || !$matchDate) {
        $error = 'Tournament, both teams, and date are required.';
    } elseif ($team1Id === $team2Id) {
        $error = 'Teams must be different.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO matches (tournament_id, team1_id, team2_id, round_number, match_date, match_time, venue) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$tournamentId, $team1Id, $team2Id, $roundNumber, $matchDate, $matchTime ?: null, $venue ?: null]);
            $message = 'Match scheduled successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to create match.';
            error_log($e->getMessage());
        }
    }
}

// Fetch tournaments for dropdown
$stmt = $pdo->query('SELECT id, name FROM tournaments WHERE status IN ("upcoming","ongoing") ORDER BY start_date');
$tournaments = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch matches (filter by tournament if provided)
$tournamentFilter = isset($_GET['tournament']) ? (int) $_GET['tournament'] : null;
$sql = 'SELECT m.*, t1.name as team1_name, t2.name as team2_name, tr.name as tournament_name
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        JOIN tournaments tr ON m.tournament_id = tr.id
        WHERE 1=1';
$params = [];
if ($tournamentFilter) {
    $sql .= ' AND m.tournament_id = ?';
    $params[] = $tournamentFilter;
}
$sql .= ' ORDER BY m.match_date DESC, m.match_time DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matches = $stmt->fetchAll();

$pageTitle = 'Matches';
require_once __DIR__ . '/includes/header.php';
?>

<section class="matches-module">
    <h1>Matches</h1>
    
    <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <!-- Create Match Form (collapsible) -->
    <div class="form-card">
        <h3>Schedule New Match</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tournament *</label>
                    <select name="tournament_id" required id="match-tournament">
                        <option value="">Select Tournament</option>
                        <?php foreach ($tournaments as $id => $name): ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Team 1 *</label>
                    <select name="team1_id" required id="team1-select">
                        <option value="">Select team</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Team 2 *</label>
                    <select name="team2_id" required id="team2-select">
                        <option value="">Select team</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="match_date" required>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="match_time">
                </div>
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" placeholder="Venue name">
                </div>
                <div class="form-group">
                    <label>Round</label>
                    <input type="number" name="round_number" value="1" min="1">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Schedule Match</button>
        </form>
    </div>

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

    <?php if (empty($matches)): ?>
        <div class="empty-state">No matches found.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Match</th>
                        <th>Tournament</th>
                        <th>Venue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $m): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($m['match_date'])); ?></td>
                            <td><?php echo $m['match_time'] ? date('g:i A', strtotime($m['match_time'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($m['team1_name']); ?> vs <?php echo htmlspecialchars($m['team2_name']); ?></td>
                            <td><?php echo htmlspecialchars($m['tournament_name']); ?></td>
                            <td><?php echo htmlspecialchars($m['venue'] ?? '-'); ?></td>
                            <td><span class="badge badge-<?php echo $m['status']; ?>"><?php echo $m['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script>
// Dynamic team loading based on tournament
document.getElementById('match-tournament')?.addEventListener('change', function() {
    const tid = this.value;
    const team1 = document.getElementById('team1-select');
    const team2 = document.getElementById('team2-select');
    team1.innerHTML = team2.innerHTML = '<option value="">Select team</option>';
    if (!tid) return;
    fetch('api/teams.php?tournament_id=' + tid)
        .then(r => r.json())
        .then(teams => {
            teams.forEach(t => {
                team1.innerHTML += '<option value="' + t.id + '">' + t.name + '</option>';
                team2.innerHTML += '<option value="' + t.id + '">' + t.name + '</option>';
            });
        })
        .catch(() => {});
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
