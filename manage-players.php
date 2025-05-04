<?php
require_once 'db_connect.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['playerName'];
                $age = $_POST['age'];
                $position = $_POST['position'];
                $team = $_POST['team'];
                
                $sql = "INSERT INTO Players (Name, Age, PositionID, TeamID) 
                        VALUES (:name, :age, :position, :team)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':age' => $age,
                    ':position' => $position,
                    ':team' => $team
                ]);
                break;

            case 'delete':
                $id = $_POST['playerId'];
                
                $sql = "DELETE FROM Players WHERE PlayerID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                break;
        }
    }
}

// Get all players
$sql = "SELECT 
            p.PlayerID, 
            p.Name, 
            p.Age, 
            pos.PositionName, 
            t.TeamName
        FROM Players p 
        JOIN Positions pos ON p.PositionID = pos.PositionID 
        JOIN Teams t ON p.TeamID = t.TeamID
        ORDER BY p.Name";
$stmt = $pdo->query($sql);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all positions
$sql = "SELECT * FROM Positions";
$stmt = $pdo->query($sql);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all teams
$sql = "SELECT * FROM Teams";
$stmt = $pdo->query($sql);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players - Football Management System</title>
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
            <section class="manage-players">
                <h2>Manage Players</h2>
                <form action="manage-players.php" method="POST" class="player-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="playerName">Player Name:</label>
                        <input type="text" id="playerName" name="playerName" required>
                    </div>
                    <div class="form-group">
                        <label for="age">Age:</label>
                        <input type="number" id="age" name="age" required>
                    </div>
                    <div class="form-group">
                        <label for="team">Team:</label>
                        <select id="team" name="team" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['TeamID']; ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <select id="position" name="position" required>
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo $position['PositionID']; ?>"><?php echo htmlspecialchars($position['PositionName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit">Add Player</button>
                        <button type="reset">Clear</button>
                    </div>
                </form>

                <h3>All Players</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Player Name</th>
                            <th>Age</th>
                            <th>Team</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($players as $player): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($player['Name']); ?></td>
                                <td><?php echo htmlspecialchars($player['Age']); ?></td>
                                <td><?php echo htmlspecialchars($player['TeamName']); ?></td>
                                <td><?php echo htmlspecialchars($player['PositionName']); ?></td>
                                <td>
                                    <form action="manage-players.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="playerId" value="<?php echo $player['PlayerID']; ?>">
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this player?')">Delete</button>
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