<?php
// results.php
require_once 'includes/config.php';

$where = "m.status = 'completed'";
$params = [];

if (isset($_GET['t_id'])) {
    $where .= " AND m.tournament_id = ?";
    $params[] = intval($_GET['t_id']);
}

$stmt = $pdo->prepare("
    SELECT r.*, m.match_date, t1.name as team1, t2.name as team2, w.name as winner, tr.name as tournament_name
    FROM results r
    JOIN matches m ON r.match_id = m.id
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    JOIN tournaments tr ON m.tournament_id = tr.id
    LEFT JOIN teams w ON r.winner_team_id = w.id
    WHERE $where
    ORDER BY m.match_date DESC
");
$stmt->execute($params);
$results = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="mb-3">
    <h1 class="page-title">Match Results</h1>
    <p class="text-muted">Catch up on the latest scores and match outcomes.</p>
</div>

<div class="grid grid-3">
    <?php if (count($results) > 0): ?>
        <?php foreach ($results as $r): ?>
            <div class="card text-center flex-col flex-between">
                <div>
                    <div class="mb-1 text-muted" style="font-size:0.8rem; text-transform:uppercase; font-weight:600;"><?php echo htmlspecialchars($r['tournament_name']); ?></div>
                    <div class="text-muted mb-2" style="font-size:0.85rem;"><?php echo displayDateTime($r['match_date']); ?></div>
                    
                    <div class="flex flex-between mb-3" style="align-items: center;">
                        <div style="flex:1; font-weight:600;"><?php echo htmlspecialchars($r['team1']); ?></div>
                        <div style="padding: 0 10px; font-weight:bold; color:var(--text-muted);">vs</div>
                        <div style="flex:1; font-weight:600;"><?php echo htmlspecialchars($r['team2']); ?></div>
                    </div>
                    
                    <div style="display:inline-block; padding: 0.5rem 1.5rem; background: var(--bg-color); border:1px solid var(--border); border-radius: 6px; font-size:1.25rem; font-weight:700; font-family:monospace; letter-spacing:2px; margin-bottom:1rem;">
                        <?php echo htmlspecialchars($r['score']); ?>
                    </div>
                </div>
                <div style="width:100%; padding-top:1rem; border-top:1px solid var(--border-light); font-size:0.9rem;">
                    Winner: <strong style="color:var(--text-main);"><?php echo $r['winner'] ? htmlspecialchars($r['winner']) : 'Draw'; ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted text-center" style="grid-column: 1 / -1;">No results have been posted yet.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
