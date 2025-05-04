<?php
require_once 'db_connect.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $teamName = $_POST['teamName'];
                
                $sql = "INSERT INTO Teams (TeamName) VALUES (:teamName)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':teamName' => $teamName]);
                break;

            case 'delete':
                $id = $_POST['teamId'];
                
                $sql = "DELETE FROM Teams WHERE TeamID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                break;
        }
    }
}

// Get all teams
$sql = "SELECT * FROM Teams ORDER BY TeamName";
$stmt = $pdo->query($sql);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams - Football Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>⚽ Football Management System</h1>
            <nav>
                <ul>
                    <li><a href="manage-players.php">Manage Players</a></li>
                    <li><a href="manage-teams.php">Manage Teams</a></li>
                    <li><a href="manage-matches.php">Manage Matches</a></li>
                    <li><a href="manage-coaches.php">Manage Coaches</a></li>
                    <li><a href="player-stats.php">View Player Stats</a></li>
                    <li><a href="team-standings.php">View Team Standings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="manage-teams">
                <h2>Manage Teams</h2>
                <form action="manage-teams.php" method="POST" class="team-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="teamName">Team Name:</label>
                        <input type="text" id="teamName" name="teamName" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit">Add Team</button>
                        <button type="reset">Clear</button>
                    </div>
                </form>

                <h3>All Teams</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $team): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($team['TeamName']); ?></td>
                                <td>
                                    <form action="manage-teams.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="teamId" value="<?php echo $team['TeamID']; ?>">
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this team?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html> 