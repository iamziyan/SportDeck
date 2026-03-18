<?php
// player/my_registrations.php - View all tournaments the player registered for
require_once '../includes/config.php';
require_once '../includes/auth.php';
requirePlayer();

$user_id = $_SESSION['user_id'];

// Fetch all registrations with tournament details
$stmt = $pdo->prepare("
    SELECT t.*, tr.registered_at, tr.status AS reg_status,
        (SELECT COUNT(*) FROM tournament_registrations WHERE tournament_id = t.id AND status='registered') AS reg_count
    FROM tournament_registrations tr
    JOIN tournaments t ON tr.tournament_id = t.id
    WHERE tr.user_id = ?
    ORDER BY tr.registered_at DESC
");
$stmt->execute([$user_id]);
$registrations = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">

    <!-- Sidebar -->
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Player Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem;">My Dashboard</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem;">Browse Tournaments</a></li>
            <li><a href="my_registrations.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">My Registrations</a></li>
            <li><a href="../fixtures.php" class="text-muted" style="display:block; padding:0.5rem;">All Fixtures</a></li>
            <li><a href="../results.php" class="text-muted" style="display:block; padding:0.5rem;">All Results</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div>
        <h1 class="page-title mb-1">My Registrations</h1>
        <p class="text-muted mb-3">All tournaments you've signed up for.</p>

        <?php if (count($registrations) > 0): ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tournament</th>
                            <th>Sport</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th>Players</th>
                            <th>Registered On</th>
                            <th>My Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $r): ?>
                            <?php
                                $tbadge = 'badge-gray';
                                if ($r['status'] == 'ongoing') $tbadge = 'badge-green';
                                if ($r['status'] == 'completed') $tbadge = 'badge-blue';

                                $regBadge = $r['reg_status'] === 'registered' ? 'badge-green' : 'badge-gray';
                                $regLabel = $r['reg_status'] === 'registered' ? '✔ Active' : '✖ Cancelled';
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                                <td><?= htmlspecialchars($r['sport_type']) ?></td>
                                <td>
                                    <span style="font-size:0.85rem;">
                                        <?= displayDate($r['start_date']) ?> →<br><?= displayDate($r['end_date']) ?>
                                    </span>
                                </td>
                                <td><span class="badge <?= $tbadge ?>"><?= ucfirst($r['status']) ?></span></td>
                                <td>
                                    <?php if ($r['max_players'] !== null): ?>
                                        <span style="font-size:0.875rem;"><?= $r['reg_count'] ?> / <?= $r['max_players'] ?></span>
                                    <?php else: ?>
                                        <span style="font-size:0.875rem;"><?= $r['reg_count'] ?> <span class="text-muted">(No limit)</span></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="text-muted" style="font-size:0.82rem;"><?= date('M j, Y g:i A', strtotime($r['registered_at'])) ?></span></td>
                                <td><span class="badge <?= $regBadge ?>"><?= $regLabel ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top:1.5rem; background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border-color: #bfdbfe;">
                <div class="flex gap-2" style="align-items:center;">
                    <div style="font-size:2rem;">🏆</div>
                    <div>
                        <p style="font-weight:600; margin-bottom:0.25rem;">
                            <?= count(array_filter($registrations, fn($r) => $r['reg_status'] === 'registered')) ?>
                            active tournament<?= count(array_filter($registrations, fn($r) => $r['reg_status'] === 'registered')) !== 1 ? 's' : '' ?>
                        </p>
                        <p class="text-muted" style="font-size:0.875rem; margin-bottom:0;">
                            Want to join more? <a href="tournaments.php">Browse all tournaments →</a>
                        </p>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="card" style="text-align:center; padding:3rem 2rem;">
                <div style="font-size:3rem; margin-bottom:1rem;">🎯</div>
                <h3 style="margin-bottom:0.5rem;">No Registrations Yet</h3>
                <p class="text-muted mb-3">You haven't registered for any tournament. Browse available tournaments and join one!</p>
                <a href="tournaments.php" class="btn btn-primary">Browse Tournaments</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
