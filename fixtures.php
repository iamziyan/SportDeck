<?php
// fixtures.php
require_once 'includes/config.php';

$where = "m.status = 'upcoming'";
$params = [];

if (isset($_GET['t_id'])) {
    $where .= " AND m.tournament_id = ?";
    $params[] = intval($_GET['t_id']);
}

$stmt = $pdo->prepare("
    SELECT m.*, t1.name as team1, t2.name as team2, tr.name as tournament_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    JOIN tournaments tr ON m.tournament_id = tr.id
    WHERE $where
    ORDER BY m.match_date ASC
");
$stmt->execute($params);
$fixtures = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="mb-3">
    <h1 class="page-title">Upcoming Fixtures</h1>
    <p class="text-muted">Don't miss the action. Check out the scheduled matches below.</p>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Tournament</th>
                <th>Matchup</th>
                <th>Venue</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($fixtures) > 0): ?>
                <?php foreach ($fixtures as $f): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?php echo displayDateTime($f['match_date']); ?></td>
                        <td><span class="badge badge-gray"><?php echo htmlspecialchars($f['tournament_name']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($f['team1']); ?></strong> <span class="text-muted text-sm mx-1">vs</span> <strong><?php echo htmlspecialchars($f['team2']); ?></strong></td>
                        <td class="text-muted"><?php echo htmlspecialchars($f['venue'] ?: 'TBA'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">No upcoming fixtures found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
