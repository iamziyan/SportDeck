<?php
/**
 * =====================================================
 * Schedule Module - Sports Tournament Management
 * =====================================================
 * Displays match schedule - all matches or filtered
 * by tournament. Calendar-style layout with dates.
 * =====================================================
 */

require_once __DIR__ . '/config.php';

$pdo = getDbConnection();

// Filter by tournament
$tournamentId = isset($_GET['tournament']) ? (int) $_GET['tournament'] : null;

$sql = 'SELECT m.id, m.match_date, m.match_time, m.venue, m.status, m.round_number,
               t1.name as team1_name, t2.name as team2_name,
               tr.name as tournament_name, m.tournament_id
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        JOIN tournaments tr ON m.tournament_id = tr.id
        WHERE 1=1';
$params = [];
if ($tournamentId) {
    $sql .= ' AND m.tournament_id = ?';
    $params[] = $tournamentId;
}
$sql .= ' ORDER BY m.match_date ASC, m.match_time ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matches = $stmt->fetchAll();

// Group matches by date
$byDate = [];
foreach ($matches as $m) {
    $d = $m['match_date'];
    if (!isset($byDate[$d])) $byDate[$d] = [];
    $byDate[$d][] = $m;
}

// Tournaments for filter
$tournaments = $pdo->query('SELECT id, name FROM tournaments ORDER BY start_date DESC')->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Schedule';
require_once __DIR__ . '/includes/header.php';
?>

<section class="schedule-module">
    <h1>Match Schedule</h1>
    
    <div class="filter-bar">
        <form method="GET">
            <label>Tournament:</label>
            <select name="tournament" onchange="this.form.submit()">
                <option value="">All Tournaments</option>
                <?php foreach ($tournaments as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo $tournamentId == $id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($matches)): ?>
        <div class="empty-state">
            <p>No matches scheduled yet.</p>
        </div>
    <?php else: ?>
        <div class="schedule-timeline">
            <?php foreach ($byDate as $date => $dayMatches): ?>
                <div class="schedule-day">
                    <h3 class="schedule-date"><?php echo date('l, F j, Y', strtotime($date)); ?></h3>
                    <div class="schedule-matches">
                        <?php foreach ($dayMatches as $m): ?>
                            <div class="schedule-match-item">
                                <span class="match-time"><?php echo $m['match_time'] ? date('g:i A', strtotime($m['match_time'])) : 'TBD'; ?></span>
                                <span class="match-fixture">
                                    <?php echo htmlspecialchars($m['team1_name']); ?> vs <?php echo htmlspecialchars($m['team2_name']); ?>
                                </span>
                                <?php if ($m['venue']): ?>
                                    <span class="match-venue">@ <?php echo htmlspecialchars($m['venue']); ?></span>
                                <?php endif; ?>
                                <?php if (!$tournamentId): ?>
                                    <span class="match-tournament">(<?php echo htmlspecialchars($m['tournament_name']); ?>)</span>
                                <?php endif; ?>
                                <span class="badge badge-<?php echo $m['status']; ?>"><?php echo $m['status']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
