<?php
require_once 'db_connect.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $coachName = $_POST['coachName'];
                $experience = $_POST['experience'];
                $team = $_POST['team'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Insert coach
                    $sql = "INSERT INTO Coaches (Name, Experience) 
                            VALUES (:coachName, :experience)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':coachName' => $coachName,
                        ':experience' => $experience
                    ]);
                    
                    $coachId = $pdo->lastInsertId();
                    
                    // Assign coach to team
                    if ($team) {
                        $sql = "INSERT INTO TeamCoaches (TeamID, CoachID) 
                                VALUES (:teamId, :coachId)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ':teamId' => $team,
                            ':coachId' => $coachId
                        ]);
                    }
                    
                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'delete':
                $id = $_POST['coachId'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Delete from TeamCoaches first
                    $sql = "DELETE FROM TeamCoaches WHERE CoachID = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':id' => $id]);
                    
                    // Then delete from Coaches
                    $sql = "DELETE FROM Coaches WHERE CoachID = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':id' => $id]);
                    
                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all coaches with their teams
$sql = "SELECT 
            c.CoachID,
            c.Name,
            c.Experience,
            t.TeamName
        FROM Coaches c
        LEFT JOIN TeamCoaches tc ON c.CoachID = tc.CoachID
        LEFT JOIN Teams t ON tc.TeamID = t.TeamID
        ORDER BY c.Name";
$stmt = $pdo->query($sql);
$coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all teams for dropdown
$sql = "SELECT * FROM Teams ORDER BY TeamName";
$stmt = $pdo->query($sql);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Coaches - Football Management System</title>
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
            <section class="manage-coaches">
                <h2>Manage Coaches</h2>
                <form action="manage-coaches.php" method="POST" class="coach-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="coachName">Coach Name:</label>
                        <input type="text" id="coachName" name="coachName" required>
                    </div>
                    <div class="form-group">
                        <label for="experience">Years of Experience:</label>
                        <input type="number" id="experience" name="experience" required>
                    </div>
                    <div class="form-group">
                        <label for="team">Assign to Team:</label>
                        <select id="team" name="team">
                            <option value="">Select Team (Optional)</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['TeamID']; ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit">Add Coach</button>
                        <button type="reset">Clear</button>
                    </div>
                </form>

                <h3>All Coaches</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Coach Name</th>
                            <th>Experience</th>
                            <th>Team</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coaches as $coach): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($coach['Name']); ?></td>
                                <td><?php echo htmlspecialchars($coach['Experience']); ?></td>
                                <td><?php echo htmlspecialchars($coach['TeamName'] ?? 'Not Assigned'); ?></td>
                                <td>
                                    <form action="manage-coaches.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="coachId" value="<?php echo $coach['CoachID']; ?>">
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this coach?')">Delete</button>
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