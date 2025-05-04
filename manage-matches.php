<?php
require_once 'db_connect.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $matchDate = $_POST['matchDate'];
                $homeTeam = $_POST['homeTeam'];
                $awayTeam = $_POST['awayTeam'];
                $homeScore = $_POST['homeScore'];
                $awayScore = $_POST['awayScore'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Insert match
                    $sql = "INSERT INTO Matches (MatchDate, HomeTeamID, AwayTeamID, HomeScore, AwayScore) 
                            VALUES (:matchDate, :homeTeam, :awayTeam, :homeScore, :awayScore)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':matchDate' => $matchDate,
                        ':homeTeam' => $homeTeam,
                        ':awayTeam' => $awayTeam,
                        ':homeScore' => $homeScore,
                        ':awayScore' => $awayScore
                    ]);
                    
                    $matchId = $pdo->lastInsertId();
                    
                    // Insert player stats
                    if (isset($_POST['playerStats'])) {
                        foreach ($_POST['playerStats'] as $playerId => $stats) {
                            if ($stats['goals'] > 0 || $stats['assists'] > 0) {
                                $sql = "INSERT INTO PlayerMatchStats (PlayerID, MatchID, Goals, Assists) 
                                        VALUES (:playerId, :matchId, :goals, :assists)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([
                                    ':playerId' => $playerId,
                                    ':matchId' => $matchId,
                                    ':goals' => $stats['goals'],
                                    ':assists' => $stats['assists']
                                ]);
                            }
                        }
                    }
                    
                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                }
                break;

            case 'delete':
                $id = $_POST['matchId'];
                
                try {
                    $pdo->beginTransaction();
                    
                    // Delete player stats first
                    $sql = "DELETE FROM PlayerMatchStats WHERE MatchID = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':id' => $id]);
                    
                    // Then delete match
                    $sql = "DELETE FROM Matches WHERE MatchID = :id";
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

// Get all matches
$sql = "SELECT 
            m.MatchID,
            m.MatchDate,
            t1.TeamName AS HomeTeam,
            t2.TeamName AS AwayTeam,
            m.HomeScore,
            m.AwayScore
        FROM Matches m
        JOIN Teams t1 ON m.HomeTeamID = t1.TeamID
        JOIN Teams t2 ON m.AwayTeamID = t2.TeamID
        ORDER BY m.MatchDate DESC";
$stmt = $pdo->query($sql);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all teams for dropdowns
$sql = "SELECT * FROM Teams ORDER BY TeamName";
$stmt = $pdo->query($sql);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all players for stats input
$sql = "SELECT 
            p.PlayerID,
            p.Name,
            t.TeamID,
            t.TeamName
        FROM Players p
        JOIN Teams t ON p.TeamID = t.TeamID
        ORDER BY t.TeamName, p.Name";
$stmt = $pdo->query($sql);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Matches - Football Management System</title>
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
            <section class="manage-matches">
                <h2>Manage Matches</h2>
                <form action="manage-matches.php" method="POST" class="match-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="matchDate">Match Date:</label>
                        <input type="date" id="matchDate" name="matchDate" required>
                    </div>
                    <div class="form-group">
                        <label for="homeTeam">Home Team:</label>
                        <select id="homeTeam" name="homeTeam" required onchange="updatePlayerList()">
                            <option value="">Select Home Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['TeamID']; ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="awayTeam">Away Team:</label>
                        <select id="awayTeam" name="awayTeam" required onchange="updatePlayerList()">
                            <option value="">Select Away Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team['TeamID']; ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="homeScore">Home Score:</label>
                        <input type="number" id="homeScore" name="homeScore" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="awayScore">Away Score:</label>
                        <input type="number" id="awayScore" name="awayScore" min="0" value="0" required>
                    </div>

                    <h3>Player Statistics</h3>
                    <div class="player-stats-input">
                        <?php foreach ($players as $player): ?>
                            <div class="player-stat-row" data-team="<?php echo $player['TeamID']; ?>" style="display: none;">
                                <label>
                                    <?php echo htmlspecialchars($player['Name']); ?> (<?php echo htmlspecialchars($player['TeamName']); ?>):
                                    <input type="number" name="playerStats[<?php echo $player['PlayerID']; ?>][goals]" min="0" value="0" placeholder="Goals">
                                    <input type="number" name="playerStats[<?php echo $player['PlayerID']; ?>][assists]" min="0" value="0" placeholder="Assists">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit">Add Match</button>
                        <button type="reset">Clear</button>
                    </div>
                </form>

                <h3>All Matches</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Home Team</th>
                            <th>Away Team</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($match['MatchDate']); ?></td>
                                <td><?php echo htmlspecialchars($match['HomeTeam']); ?></td>
                                <td><?php echo htmlspecialchars($match['AwayTeam']); ?></td>
                                <td><?php echo htmlspecialchars($match['HomeScore'] . ' - ' . $match['AwayScore']); ?></td>
                                <td>
                                    <form action="manage-matches.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="matchId" value="<?php echo $match['MatchID']; ?>">
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this match?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <script>
        function updatePlayerList() {
            const homeTeam = document.getElementById('homeTeam').value;
            const awayTeam = document.getElementById('awayTeam').value;
            const playerRows = document.querySelectorAll('.player-stat-row');
            
            playerRows.forEach(row => {
                const teamId = row.getAttribute('data-team');
                if (teamId === homeTeam || teamId === awayTeam) {
                    row.style.display = 'block';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html> 