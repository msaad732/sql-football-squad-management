<?php
require_once 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT * FROM MatchResults ORDER BY MatchDate DESC");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Results - Football Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>⚽ Football Management System</h1>
            <nav>
                <ul>
                    <li><a href="manage-players.html">Manage Players</a></li>
                    <li><a href="manage-teams.html">Manage Teams</a></li>
                    <li><a href="manage-matches.html">Manage Matches</a></li>
                    <li><a href="manage-coaches.html">Manage Coaches</a></li>
                    <li><a href="player-stats.html">View Player Stats</a></li>
                    <li><a href="team-standings.html">View Team Standings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="match-results">
                <h2>Match Results</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Match ID</th>
                            <th>Home Team</th>
                            <th>Away Team</th>
                            <th>Date</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($matches as $match): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($match['MatchID']); ?></td>
                            <td><?php echo htmlspecialchars($match['HomeTeam']); ?></td>
                            <td><?php echo htmlspecialchars($match['AwayTeam']); ?></td>
                            <td><?php echo htmlspecialchars($match['MatchDate']); ?></td>
                            <td><?php echo htmlspecialchars($match['HomeScore'] . ' - ' . $match['AwayScore']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html> 