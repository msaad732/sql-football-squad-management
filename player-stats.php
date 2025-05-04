<?php
require_once 'db_connect.php';

try {
    // Get aggregated player stats only for players who have played matches
    $sql = "SELECT 
                p.Name AS PlayerName,
                t.TeamName,
                COUNT(DISTINCT pms.MatchID) AS MatchesPlayed,
                COALESCE(SUM(pms.Goals), 0) AS TotalGoals,
                COALESCE(SUM(pms.Assists), 0) AS TotalAssists
            FROM Players p
            INNER JOIN PlayerMatchStats pms ON p.PlayerID = pms.PlayerID
            INNER JOIN Teams t ON p.TeamID = t.TeamID
            GROUP BY p.Name, t.TeamName
            HAVING COUNT(DISTINCT pms.MatchID) > 0 
            AND (COALESCE(SUM(pms.Goals), 0) > 0 OR COALESCE(SUM(pms.Assists), 0) > 0)
            ORDER BY TotalGoals DESC, TotalAssists DESC";
    $stmt = $pdo->query($sql);
    $playerStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Statistics - Football Management System</title>
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
            <section class="player-stats">
                <h2>Player Statistics</h2>
                <?php if (count($playerStats) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Player Name</th>
                                <th>Team</th>
                                <th>Matches Played</th>
                                <th>Total Goals</th>
                                <th>Total Assists</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($playerStats as $stat): ?>
                                <?php if ($stat['TotalGoals'] > 0 || $stat['TotalAssists'] > 0): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['PlayerName']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['TeamName']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['MatchesPlayed']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['TotalGoals']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['TotalAssists']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No player statistics available.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html> 