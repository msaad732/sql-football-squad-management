<?php
require_once 'db_connect.php';

try {
    $sql = "SELECT 
                t.TeamID,
                t.TeamName,
                COALESCE(SUM(CASE 
                    WHEN (t.TeamID = m.HomeTeamID AND m.HomeScore > m.AwayScore) 
                      OR (t.TeamID = m.AwayTeamID AND m.AwayScore > m.HomeScore)
                    THEN 1 ELSE 0 
                END), 0) AS Wins,
                COALESCE(SUM(CASE 
                    WHEN (t.TeamID = m.HomeTeamID AND m.HomeScore < m.AwayScore) 
                      OR (t.TeamID = m.AwayTeamID AND m.AwayScore < m.HomeScore)
                    THEN 1 ELSE 0 
                END), 0) AS Losses,
                COALESCE(SUM(CASE 
                    WHEN m.HomeScore = m.AwayScore THEN 1 ELSE 0 
                END), 0) AS Draws,
                (COALESCE(SUM(CASE 
                    WHEN (t.TeamID = m.HomeTeamID AND m.HomeScore > m.AwayScore) 
                      OR (t.TeamID = m.AwayTeamID AND m.AwayScore > m.HomeScore)
                    THEN 1 ELSE 0 
                END), 0) * 2) + 
                COALESCE(SUM(CASE 
                    WHEN m.HomeScore = m.AwayScore THEN 1 ELSE 0 
                END), 0) AS Points
            FROM Teams t
            LEFT JOIN Matches m ON t.TeamID = m.HomeTeamID OR t.TeamID = m.AwayTeamID
            GROUP BY t.TeamID, t.TeamName
            ORDER BY Points DESC, Wins DESC, Draws DESC";
    $stmt = $pdo->query($sql);
    $teamStandings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Standings - Football Management System</title>
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
            <section class="team-standings">
                <h2>Team Standings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>Draws</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($teamStandings as $team): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($team['TeamName']); ?></td>
                                <td><?php echo htmlspecialchars($team['Wins']); ?></td>
                                <td><?php echo htmlspecialchars($team['Losses']); ?></td>
                                <td><?php echo htmlspecialchars($team['Draws']); ?></td>
                                <td><?php echo htmlspecialchars($team['Points']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html> 