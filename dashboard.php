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

// ID de l'auteur (à remplacer par l'authentification)
$authorId = 1;

// Gestion CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars($_POST['category']);
        $pdo->prepare("INSERT INTO articles (title, content, category, user_id, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$title, $content, $category, $authorId]);
    } elseif (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars($_POST['category']);
        $pdo->prepare("UPDATE articles SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?")
            ->execute([$title, $content, $category, $id, $authorId]);
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        $pdo->prepare("DELETE FROM articles WHERE id = ? AND user_id = ?")
            ->execute([$id, $authorId]);
    }
    header("Location: dashboard.php");
    exit;
}

// Récupérer les articles de l'auteur
$articles = $pdo->prepare("SELECT * FROM articles WHERE user_id = ?");
$articles->execute([$authorId]);
$articles = $articles->fetchAll(PDO::FETCH_ASSOC);

// Préparer les statistiques
$stats = $pdo->prepare("
    SELECT 
        a.title,
        a.views,
        COUNT(c.id) AS comments,
        a.likes
    FROM articles a
    LEFT JOIN comments c ON a.id = c.article_id
    WHERE a.user_id = ?
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$stats->execute([$authorId]);
$articleStats = $stats->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour Chart.js
$viewData = $pdo->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS date, SUM(views) AS total_views
    FROM articles
    WHERE user_id = ?
    GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
    ORDER BY date ASC
");
$viewData->execute([$authorId]);
$viewChartData = $viewData->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = array_column($viewChartData, 'date');
$chartValues = array_column($viewChartData, 'total_views');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Auteur - BlogPress</title>
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
        .stats, .crud, .chart-container {
            margin-bottom: 20px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .article {
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
        }
        form textarea, form input {
            width: 100%;
            margin: 10px 0;
        }
        .chart-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard Auteur</h1>
        
        <!-- Section statistiques -->
        <div class="stats">
            <h2>Statistiques des articles</h2>
            <table border="1" width="100%" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Vues</th>
                        <th>Commentaires</th>
                        <th>Likes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articleStats as $stat): ?>
                        <tr>
                            <td><?= htmlspecialchars($stat['title']); ?></td>
                            <td><?= $stat['views']; ?></td>
                            <td><?= $stat['comments']; ?></td>
                            <td><?= $stat['likes']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Section CRUD -->
        <div class="crud">
            <h2>Gestion des articles</h2>
            <!-- Création d'un article -->
            <form method="POST">
                <input type="text" name="title" placeholder="Titre" required>
                <textarea name="content" rows="5" placeholder="Contenu" required></textarea>
                <input type="text" name="category" placeholder="Catégorie" required>
                <button type="submit" name="create">Créer un article</button>
            </form>
            <!-- Liste des articles pour modification/suppression -->
            <?php foreach ($articles as $article): ?>
    <div class="article">
        <h3><?= htmlspecialchars($article['title'] ?? 'Titre manquant'); ?></h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $article['id']; ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($article['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <textarea name="content" rows="3" required><?= htmlspecialchars($article['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            <button type="submit" name="update">Mettre à jour</button>
            <button type="submit" name="delete">Supprimer</button>
        </form>
    </div>
<?php endforeach; ?>

        </div>

        <!-- Section graphique -->
        <div class="chart-container">
            <h2>Évolution des vues</h2>
            <canvas id="viewsChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('viewsChart').getContext('2d');
        const chartLabels = <?= json_encode($chartLabels); ?>;
        const chartValues = <?= json_encode($chartValues); ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Nombre de vues',
                    data: chartValues,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
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
