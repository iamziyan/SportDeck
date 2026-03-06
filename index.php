<?php
// index.php
require_once 'includes/config.php';

$stmt = $pdo->query("SELECT * FROM tournaments ORDER BY start_date ASC");
$tournaments = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="text-center mb-3">
    <h1 style="font-size: 2.5rem; letter-spacing:-1px;">Welcome to SportDeck</h1>
    <p class="text-muted" style="font-size: 1.1rem;">Smart Tournament Management for teams, players, and fans.</p>
</div>

<div class="flex flex-between mb-2">
    <h2>All Tournaments</h2>
</div>

<div class="grid grid-3">
    <?php if (count($tournaments) > 0): ?>
        <?php foreach ($tournaments as $t): ?>
            <?php 
                $badge = 'badge-gray';
                if($t['status'] == 'ongoing') $badge = 'badge-green';
                if($t['status'] == 'completed') $badge = 'badge-blue';
            ?>
            <div class="card">
                <div class="flex flex-between mb-1">
                    <span class="text-muted" style="font-size: 0.8rem; font-weight:600; text-transform:uppercase;"><?php echo htmlspecialchars($t['sport_type']); ?></span>
                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($t['status']); ?></span>
                </div>
                <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                <p class="text-muted mb-2">
                    <?php echo displayDate($t['start_date']) . ' - ' . displayDate($t['end_date']); ?>
                </p>
                <div class="flex gap-1">
                    <a href="<?php echo $base_url; ?>/fixtures.php?t_id=<?php echo $t['id']; ?>" class="btn btn-outline btn-sm">Fixtures</a>
                    <a href="<?php echo $base_url; ?>/results.php?t_id=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm">Results</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted text-center" style="grid-column: 1 / -1;">No tournaments available at the moment.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
