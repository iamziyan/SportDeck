<?php
/**
 * =====================================================
 * Teams Module - Sports Tournament Management
 * =====================================================
 * Lists teams by tournament, view team details.
 * Requires user to be logged in.
 * =====================================================
 */

require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDbConnection();

// Filter by tournament
$tournamentId = isset($_GET['tournament']) ? (int) $_GET['tournament'] : null;
$tournaments = [];
$stmt = $pdo->query('SELECT id, name FROM tournaments ORDER BY start_date DESC');
while ($row = $stmt->fetch()) {
    $tournaments[$row['id']] = $row['name'];
}

// Build query
$sql = 'SELECT t.*, tr.name as tournament_name 
        FROM teams t 
        JOIN tournaments tr ON t.tournament_id = tr.id 
        WHERE 1=1';
$params = [];
if ($tournamentId) {
    $sql .= ' AND t.tournament_id = ?';
    $params[] = $tournamentId;
}
$sql .= ' ORDER BY tr.start_date DESC, t.name';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$teams = $stmt->fetchAll();

$pageTitle = 'Teams';
require_once __DIR__ . '/includes/header.php';
?>

<section class="teams-module">
    <h1>Teams</h1>
    
    <div class="filter-bar">
        <form method="GET" class="filter-form">
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

    <?php if (empty($teams)): ?>
        <div class="empty-state">
            <p>No teams found. Register a team in a tournament first.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Tournament</th>
                        <th>Captain</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($team['name']); ?></strong></td>
                            <td><a href="tournaments.php?view=<?php echo $team['tournament_id']; ?>"><?php echo htmlspecialchars($team['tournament_name']); ?></a></td>
                            <td><?php echo htmlspecialchars($team['captain_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($team['contact_email'] ?? $team['contact_phone'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
