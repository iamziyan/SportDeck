<?php
// player/tournaments.php - Browse & register for tournaments
require_once '../includes/config.php';
require_once '../includes/auth.php';
requirePlayer();

$user_id = $_SESSION['user_id'];
$msg = '';

// Handle registration actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $t_id = intval($_POST['tournament_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($t_id > 0 && in_array($action, ['register', 'unregister'])) {
        if ($action === 'register') {
            // Check tournament exists, is open, and not full
            $stmt = $pdo->prepare("
                SELECT t.*, 
                    (SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = t.id AND status='registered') AS reg_count
                FROM tournaments t WHERE t.id = ?
            ");
            $stmt->execute([$t_id]);
            $tournament = $stmt->fetch();

            if (!$tournament) {
                $msg = "<div class='alert alert-error'>Tournament not found.</div>";
            } elseif (!in_array($tournament['status'], ['upcoming', 'ongoing'])) {
                $msg = "<div class='alert alert-error'>This tournament is not open for registration.</div>";
            } elseif ($tournament['max_players'] !== null && $tournament['reg_count'] >= $tournament['max_players']) {
                $msg = "<div class='alert alert-error'>This tournament is full.</div>";
            } else {
                // Insert or update registration
                $ins = $pdo->prepare("
                    INSERT INTO tournament_registrations (user_id, tournament_id, status)
                    VALUES (?, ?, 'registered')
                    ON DUPLICATE KEY UPDATE status = 'registered', registered_at = CURRENT_TIMESTAMP
                ");
                if ($ins->execute([$user_id, $t_id])) {
                    $msg = "<div class='alert alert-success'>✅ You have successfully registered for the tournament!</div>";
                }
            }
        } elseif ($action === 'unregister') {
            $upd = $pdo->prepare("
                UPDATE tournament_registrations SET status = 'cancelled'
                WHERE user_id = ? AND tournament_id = ?
            ");
            if ($upd->execute([$user_id, $t_id])) {
                $msg = "<div class='alert alert-success'>You have unregistered from the tournament.</div>";
            }
        }
    }
}

// Fetch all tournaments with registration info
$stmt = $pdo->prepare("
    SELECT t.*,
        (SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = t.id AND status='registered') AS reg_count,
        (SELECT status FROM tournament_registrations WHERE tournament_id = t.id AND user_id = ? LIMIT 1) AS my_status
    FROM tournaments t
    ORDER BY t.start_date ASC
");
$stmt->execute([$user_id]);
$tournaments = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">

    <!-- Sidebar -->
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Player Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem;">My Dashboard</a></li>
            <li><a href="tournaments.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Browse Tournaments</a></li>
            <li><a href="my_registrations.php" class="text-muted" style="display:block; padding:0.5rem;">My Registrations</a></li>
            <li><a href="../fixtures.php" class="text-muted" style="display:block; padding:0.5rem;">All Fixtures</a></li>
            <li><a href="../results.php" class="text-muted" style="display:block; padding:0.5rem;">All Results</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div>
        <h1 class="page-title mb-1">Browse Tournaments</h1>
        <p class="text-muted mb-3">Find a tournament you want to join and register below.</p>

        <?php echo $msg; ?>

        <div class="grid grid-2" style="gap:1.25rem;">
            <?php if (count($tournaments) > 0): ?>
                <?php foreach ($tournaments as $t): ?>
                    <?php
                        $badge = 'badge-gray';
                        if ($t['status'] == 'ongoing') $badge = 'badge-green';
                        if ($t['status'] == 'completed') $badge = 'badge-blue';

                        $isFull = $t['max_players'] !== null && $t['reg_count'] >= $t['max_players'];
                        $isOpen = in_array($t['status'], ['upcoming', 'ongoing']);
                        $isRegistered = $t['my_status'] === 'registered';
                    ?>
                    <div class="card" style="display:flex; flex-direction:column; gap:0.75rem;">
                        <!-- Header row -->
                        <div class="flex flex-between">
                            <span class="text-muted" style="font-size:0.8rem; font-weight:600; text-transform:uppercase;"><?= htmlspecialchars($t['sport_type']) ?></span>
                            <span class="badge <?= $badge ?>"><?= ucfirst($t['status']) ?></span>
                        </div>

                        <!-- Tournament name -->
                        <h3 style="margin-bottom:0;"><?= htmlspecialchars($t['name']) ?></h3>

                        <!-- Dates -->
                        <p class="text-muted" style="font-size:0.875rem;">
                            📅 <?= displayDate($t['start_date']) ?> → <?= displayDate($t['end_date']) ?>
                        </p>

                        <!-- Player limit -->
                        <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.875rem;">
                            <span>👥 Players:</span>
                            <?php if ($t['max_players'] !== null): ?>
                                <span style="font-weight:600; color: <?= $isFull ? 'var(--danger)' : 'var(--success)' ?>">
                                    <?= $t['reg_count'] ?> / <?= $t['max_players'] ?>
                                </span>
                                <?php if ($isFull): ?>
                                    <span class="badge badge-red">Full</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-weight:600;"><?= $t['reg_count'] ?> registered <span class="text-muted">(No limit)</span></span>
                            <?php endif; ?>
                        </div>

                        <!-- Action -->
                        <div style="margin-top:auto;">
                            <?php if ($t['status'] == 'completed'): ?>
                                <span class="badge badge-blue" style="padding:0.4rem 0.75rem; font-size:0.8rem;">Tournament Ended</span>
                            <?php elseif ($isRegistered): ?>
                                <div class="flex gap-1" style="align-items:center; flex-wrap:wrap;">
                                    <span class="badge badge-green" style="padding:0.4rem 0.75rem; font-size:0.85rem;">✔ Registered</span>
                                    <form method="POST" onsubmit="return confirm('Unregister from this tournament?');" style="display:inline;">
                                        <input type="hidden" name="tournament_id" value="<?= $t['id'] ?>">
                                        <input type="hidden" name="action" value="unregister">
                                        <button type="submit" class="btn btn-outline btn-sm">Unregister</button>
                                    </form>
                                </div>
                            <?php elseif ($isFull): ?>
                                <span class="badge badge-red" style="padding:0.4rem 0.75rem; font-size:0.85rem;">🚫 Tournament Full</span>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="tournament_id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="action" value="register">
                                    <button type="submit" class="btn btn-primary btn-sm">🏆 Register Now</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted" style="grid-column: 1 / -1;">No tournaments available yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
