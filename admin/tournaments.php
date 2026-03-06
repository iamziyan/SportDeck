<?php
// admin/tournaments.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $sport_type = sanitize($_POST['sport_type']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("INSERT INTO tournaments (name, sport_type, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $sport_type, $start_date, $end_date, $status])) {
            $msg = "<div class='alert alert-success'>Tournament added successfully.</div>";
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "<div class='alert alert-success'>Tournament deleted successfully.</div>";
        }
    }
}

$tournaments = $pdo->query("SELECT * FROM tournaments ORDER BY id DESC")->fetchAll();

include '../includes/header.php';
?>
<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Admin Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Dashboard Overview</a></li>
            <li><a href="tournaments.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Tournaments</a></li>
            <li><a href="teams.php" class="text-muted" style="display:block; padding:0.5rem;">Teams</a></li>
            <li><a href="players.php" class="text-muted" style="display:block; padding:0.5rem;">Players</a></li>
            <li><a href="matches.php" class="text-muted" style="display:block; padding:0.5rem;">Match Fixtures</a></li>
            <li><a href="results.php" class="text-muted" style="display:block; padding:0.5rem;">Results</a></li>
            <li><a href="users.php" class="text-muted" style="display:block; padding:0.5rem;">User Roles</a></li>
        </ul>
    </div>
    
    <div>
        <div class="flex flex-between mb-2">
            <h1 class="page-title mb-0">Tournaments</h1>
        </div>
        <?php echo $msg; ?>

        <!-- Add Form -->
        <div class="card mb-3">
            <h3 class="mb-2">Add New Tournament</h3>
            <form method="POST" class="grid grid-3" style="align-items: end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Sport Type</label>
                    <input type="text" name="sport_type" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <button type="submit" name="add" class="btn btn-primary w-full">Add Tournament</button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Name</th><th>Sport</th><th>Dates</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($tournaments as $t): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($t['sport_type']); ?></td>
                        <td><?php echo displayDate($t['start_date']) . ' - ' . displayDate($t['end_date']); ?></td>
                        <td>
                            <?php 
                                $badge = 'badge-gray';
                                if($t['status'] == 'ongoing') $badge = 'badge-green';
                                if($t['status'] == 'completed') $badge = 'badge-blue';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($t['status']); ?></span>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this tournament? All related teams and matches will be deleted.');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $t['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
