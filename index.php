<?php
// index.php
require_once 'includes/config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$is_player = ($user_id && $_SESSION['role'] === 'player');

// Fetch tournaments with registration info
if ($is_player) {
    $stmt = $pdo->prepare("
        SELECT t.*,
            (SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = t.id AND status='registered') AS reg_count,
            (SELECT status FROM tournament_registrations WHERE tournament_id = t.id AND user_id = ? LIMIT 1) AS my_status
        FROM tournaments t
        ORDER BY t.start_date ASC
    ");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query("
        SELECT t.*,
            (SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = t.id AND status='registered') AS reg_count,
            NULL AS my_status
        FROM tournaments t
        ORDER BY t.start_date ASC
    ");
}
$tournaments = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="text-center mb-3">
    <h1 style="font-size: 2.5rem; letter-spacing:-1px;">Welcome to SportDeck</h1>
    <p class="text-muted" style="font-size: 1.1rem;">Smart Tournament Management for teams, players, and fans.</p>
</div>

<div class="flex flex-between mb-2">
    <h2>All Tournaments</h2>
    <?php if ($is_player): ?>
        <a href="<?= $base_url ?>/player/my_registrations.php" class="btn btn-outline btn-sm">My Registrations →</a>
    <?php endif; ?>
</div>

<div class="grid grid-3">
    <?php if (count($tournaments) > 0): ?>
        <?php foreach ($tournaments as $t): ?>
            <?php
                $badge = 'badge-gray';
                if($t['status'] == 'ongoing') $badge = 'badge-green';
                if($t['status'] == 'completed') $badge = 'badge-blue';

                $isOpen = in_array($t['status'], ['upcoming', 'ongoing']);
                $isFull = $t['max_players'] !== null && $t['reg_count'] >= $t['max_players'];
                $isRegistered = ($is_player && $t['my_status'] === 'registered');
            ?>
            <div class="card">
                <div class="flex flex-between mb-1">
                    <span class="text-muted" style="font-size: 0.8rem; font-weight:600; text-transform:uppercase;"><?php echo htmlspecialchars($t['sport_type']); ?></span>
                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($t['status']); ?></span>
                </div>
                <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                <p class="text-muted mb-1">
                    <?php echo displayDate($t['start_date']) . ' - ' . displayDate($t['end_date']); ?>
                </p>

                <!-- Player count -->
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                    👥
                    <?php if ($t['max_players'] !== null): ?>
                        <span style="font-weight:600; color:<?= $isFull ? 'var(--danger)' : 'inherit' ?>;"><?= $t['reg_count'] ?> / <?= $t['max_players'] ?> players</span>
                        <?php if ($isFull): ?><span class="badge badge-red" style="margin-left:0.25rem;">Full</span><?php endif; ?>
                    <?php else: ?>
                        <span style="font-weight:600;"><?= $t['reg_count'] ?> registered</span>
                    <?php endif; ?>
                </p>

                <div class="flex gap-1" style="flex-wrap:wrap;">
                    <a href="<?php echo $base_url; ?>/fixtures.php?t_id=<?php echo $t['id']; ?>" class="btn btn-outline btn-sm">Fixtures</a>
                    <a href="<?php echo $base_url; ?>/results.php?t_id=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm">Results</a>

                    <?php if ($is_player && $isOpen): ?>
                        <?php if ($isRegistered): ?>
                            <span class="badge badge-green" style="padding:0.35rem 0.7rem; font-size:0.8rem; align-self:center;">✔ Registered</span>
                        <?php elseif ($isFull): ?>
                            <span class="badge badge-red" style="padding:0.35rem 0.7rem; font-size:0.8rem; align-self:center;">Full</span>
                        <?php else: ?>
                            <a href="<?= $base_url ?>/player/tournaments.php" class="btn btn-sm" style="background:#059669; color:#fff; border-color:#059669;">Register</a>
                        <?php endif; ?>
                    <?php elseif (!$user_id && $isOpen): ?>
                        <a href="<?= $base_url ?>/login.php" class="btn btn-outline btn-sm" style="font-size:0.78rem;">Login to Register</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted text-center" style="grid-column: 1 / -1;">No tournaments available at the moment.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
