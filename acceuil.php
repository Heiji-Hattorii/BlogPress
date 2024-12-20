<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'blogpress';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les articles triés par popularité
$sql = "SELECT a.id, a.title, a.category, a.views, 
               COUNT(c.id) AS comments 
        FROM articles a
        LEFT JOIN comments c ON a.id = c.article_id
        GROUP BY a.id
        ORDER BY a.views DESC, comments DESC
        LIMIT 10";
$stmt = $pdo->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour Chart.js
$chartData = [];
foreach ($articles as $article) {
    $chartData[] = [
        'title' => $article['title'],
        'views' => $article['views']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogPress</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 80%;
            margin: 20px auto;
        }
        .article {
            background: #fff;
            margin: 10px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .article h2 {
            margin: 0 0 10px;
        }
        .article a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .article a:hover {
            text-decoration: underline;
        }
        .chart-container {
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Articles populaires</h1>
        <!-- Liste des articles -->
        <?php foreach ($articles as $article): ?>
            <div class="article">
                <!-- Lien vers la page de l'article -->
                <h2><a href="article.php?id=<?= $article['id']; ?>"><?= htmlspecialchars($article['title']); ?></a></h2>
                <p>Catégorie : <?= htmlspecialchars($article['category']); ?></p>
                <p>Vues : <?= $article['views']; ?> | Commentaires : <?= $article['comments']; ?></p>
            </div>
        <?php endforeach; ?>

        <!-- Graphique Chart.js -->
        <div class="chart-container">
            <canvas id="popularityChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('popularityChart').getContext('2d');
        const chartData = <?= json_encode($chartData); ?>;

        const titles = chartData.map(item => item.title);
        const views = chartData.map(item => item.views);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: titles,
                datasets: [{
                    label: 'Nombre de vues',
                    data: views,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
