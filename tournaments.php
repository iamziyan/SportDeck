<?php
/**
 * =====================================================
 * Tournaments Module - Sports Tournament Management
 * =====================================================
 * Lists all tournaments, allows viewing details, and
 * creating new tournaments (for logged-in users).
 * Includes registration form for teams.
 * =====================================================
 */

require_once __DIR__ . '/config.php';

$pdo = getDbConnection();
$message = '';
$error = '';

// Handle tournament creation (admin and organizer only)
if (isLoggedIn() && hasRole(['admin', 'organizer']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sportType = trim($_POST['sport_type'] ?? 'General');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $maxTeams = (int) ($_POST['max_teams'] ?? 16);
    
    if (empty($name) || empty($startDate) || empty($endDate)) {
        $error = 'Name, start date, and end date are required.';
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        $error = 'End date must be after start date.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO tournaments (name, description, sport_type, start_date, end_date, venue, max_teams, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description ?: null, $sportType, $startDate, $endDate, $venue ?: null, $maxTeams, $_SESSION['user_id']]);
            $message = 'Tournament created successfully!';
            header('Location: tournaments.php?created=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to create tournament.';
            error_log($e->getMessage());
        }
    }
}

// Handle team registration for tournament
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_team') {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    $tournamentId = (int) ($_POST['tournament_id'] ?? 0);
    $teamName = trim($_POST['team_name'] ?? '');
    $captainName = trim($_POST['captain_name'] ?? '');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $contactPhone = trim($_POST['contact_phone'] ?? '');
    
    if ($tournamentId && $teamName) {
        try {
            // Create team
            $stmt = $pdo->prepare('INSERT INTO teams (tournament_id, name, captain_name, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$tournamentId, $teamName, $captainName ?: null, $contactEmail ?: null, $contactPhone ?: null]);
            $teamId = $pdo->lastInsertId();
            // Create registration
            $stmt = $pdo->prepare('INSERT INTO tournament_registrations (tournament_id, team_id, status) VALUES (?, ?, ?)');
            $stmt->execute([$tournamentId, $teamId, 'approved']);
            $message = 'Team registered successfully!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'A team with that name already exists in this tournament.';
            } else {
                $error = 'Registration failed.';
            }
            error_log($e->getMessage());
        }
    }
}

// View single tournament
$viewTournament = null;
if (isset($_GET['view'])) {
    $id = (int) $_GET['view'];
    $stmt = $pdo->prepare('SELECT * FROM tournaments WHERE id = ?');
    $stmt->execute([$id]);
    $viewTournament = $stmt->fetch();
    if ($viewTournament) {
        $stmt = $pdo->prepare('SELECT * FROM teams WHERE tournament_id = ? ORDER BY name');
        $stmt->execute([$id]);
        $viewTournament['teams'] = $stmt->fetchAll();
        $stmt = $pdo->prepare('SELECT m.*, t1.name as team1_name, t2.name as team2_name FROM matches m JOIN teams t1 ON m.team1_id=t1.id JOIN teams t2 ON m.team2_id=t2.id WHERE m.tournament_id = ? ORDER BY m.match_date, m.match_time');
        $stmt->execute([$id]);
        $viewTournament['matches'] = $stmt->fetchAll();
    }
}

// List all tournaments
if (!$viewTournament) {
    $stmt = $pdo->query('
        SELECT t.*, (SELECT COUNT(*) FROM teams WHERE tournament_id = t.id) as team_count
        FROM tournaments t ORDER BY t.start_date DESC
    ');
    $tournaments = $stmt->fetchAll();
}

$pageTitle = $viewTournament ? $viewTournament['name'] : 'Tournaments';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($viewTournament): ?>
    <!-- Single Tournament View -->
    <section class="tournament-detail">
        <div class="detail-header">
            <h1><?php echo htmlspecialchars($viewTournament['name']); ?></h1>
            <span class="badge badge-<?php echo $viewTournament['status']; ?>"><?php echo ucfirst($viewTournament['status']); ?></span>
        </div>
        <?php if ($viewTournament['description']): ?>
            <p class="detail-description"><?php echo nl2br(htmlspecialchars($viewTournament['description'])); ?></p>
        <?php endif; ?>
        <div class="detail-meta">
            <span>Sport: <?php echo htmlspecialchars($viewTournament['sport_type']); ?></span>
            <span><?php echo date('M j, Y', strtotime($viewTournament['start_date'])); ?> – <?php echo date('M j, Y', strtotime($viewTournament['end_date'])); ?></span>
            <?php if ($viewTournament['venue']): ?>
                <span>Venue: <?php echo htmlspecialchars($viewTournament['venue']); ?></span>
            <?php endif; ?>
            <span><?php echo count($viewTournament['teams']); ?> / <?php echo $viewTournament['max_teams']; ?> teams</span>
        </div>

        <!-- Registration Form -->
        <?php if (isLoggedIn() && $viewTournament['status'] === 'upcoming' && count($viewTournament['teams']) < $viewTournament['max_teams']): ?>
            <div class="registration-form-card">
                <h3>Register Your Team</h3>
                <form method="POST" class="form-inline-grid">
                    <input type="hidden" name="action" value="register_team">
                    <input type="hidden" name="tournament_id" value="<?php echo $viewTournament['id']; ?>">
                    <div class="form-group">
                        <input type="text" name="team_name" placeholder="Team Name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="captain_name" placeholder="Captain Name">
                    </div>
                    <div class="form-group">
                        <input type="email" name="contact_email" placeholder="Contact Email">
                    </div>
                    <div class="form-group">
                        <input type="tel" name="contact_phone" placeholder="Phone">
                    </div>
                    <button type="submit" class="btn btn-primary">Register Team</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Teams List -->
        <h3>Teams</h3>
        <?php if (empty($viewTournament['teams'])): ?>
            <p class="empty-state">No teams registered yet.</p>
        <?php else: ?>
            <ul class="teams-list">
                <?php foreach ($viewTournament['teams'] as $team): ?>
                    <li><?php echo htmlspecialchars($team['name']); ?><?php echo $team['captain_name'] ? ' (Capt: ' . htmlspecialchars($team['captain_name']) . ')' : ''; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h3>Matches</h3>
        <a href="schedule.php?tournament=<?php echo $viewTournament['id']; ?>">View Full Schedule</a>
        <?php if (!empty($viewTournament['matches'])): ?>
            <div class="matches-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Date</th><th>Time</th><th>Match</th><th>Venue</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($viewTournament['matches'] as $m): ?>
                            <tr>
                                <td><?php echo date('M j', strtotime($m['match_date'])); ?></td>
                                <td><?php echo $m['match_time'] ? date('g:i A', strtotime($m['match_time'])) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($m['team1_name']); ?> vs <?php echo htmlspecialchars($m['team2_name']); ?></td>
                                <td><?php echo htmlspecialchars($m['venue'] ?? '-'); ?></td>
                                <td><span class="badge badge-<?php echo $m['status']; ?>"><?php echo $m['status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a href="tournaments.php" class="btn btn-outline">← Back to Tournaments</a>
    </section>
<?php else: ?>
    <!-- Tournaments List -->
    <section class="tournaments-list">
        <h1>Tournaments</h1>
        <?php if (isLoggedIn() && hasRole(['admin', 'organizer'])): ?>
            <div class="section-actions">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('create-form').style.display='block'">Create Tournament</button>
            </div>
            <div id="create-form" class="form-card" style="display:none">
                <h3>Create New Tournament</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Sport Type</label>
                            <input type="text" name="sport_type" value="Football" placeholder="e.g. Football, Basketball">
                        </div>
                        <div class="form-group">
                            <label>Start Date *</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date *</label>
                            <input type="date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label>Venue</label>
                            <input type="text" name="venue" placeholder="Venue name">
                        </div>
                        <div class="form-group">
                            <label>Max Teams</label>
                            <input type="number" name="max_teams" value="16" min="2">
                        </div>
                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="Tournament description"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Tournament</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="card-catalogue">
            <?php foreach ($tournaments ?? [] as $t): ?>
                <a href="tournaments.php?view=<?php echo $t['id']; ?>" class="catalogue-card">
                    <div class="card-status badge-<?php echo $t['status']; ?>"><?php echo ucfirst($t['status']); ?></div>
                    <h3><?php echo htmlspecialchars($t['name']); ?></h3>
                    <span class="card-sport"><?php echo htmlspecialchars($t['sport_type']); ?></span>
                    <p class="card-dates"><?php echo date('M j', strtotime($t['start_date'])); ?> – <?php echo date('M j, Y', strtotime($t['end_date'])); ?></p>
                    <div class="card-meta"><span>👥 <?php echo $t['team_count']; ?> teams</span></div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
