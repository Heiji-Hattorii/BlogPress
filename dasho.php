<?php
// Démarrer la session
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas authentifié
    header('Location: login.php');
    exit();
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blogpress';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupérer les données spécifiques à l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les articles de l'utilisateur
$query_articles = $pdo->prepare("SELECT * FROM articles WHERE user_id = :user_id");
$query_articles->execute(['user_id' => $user_id]);
$articles = $query_articles->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les commentaires de l'utilisateur
$query_comments = $pdo->prepare("SELECT * FROM comments WHERE user_id = :user_id");
$query_comments->execute(['user_id' => $user_id]);
$comments = $query_comments->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'historique des vues pour Chart.js
$query_views = $pdo->prepare("SELECT created_at, views FROM articles WHERE user_id = :user_id ORDER BY created_at DESC");
$query_views->execute(['user_id' => $user_id]);
$views_data = $query_views->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogPress - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background: linear-gradient(to bottom right, #f4f4f9, #d9e4f5);
            color: #333;
        }

        nav {
            width: 250px;
            height: 100vh;
            background: linear-gradient(to bottom, #004d99, #0073e6);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.1);
        }

        nav h2 {
            margin-bottom: 30px;
            font-size: 1.5em;
            color: #ffcc00;
        }

        nav ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        nav ul li {
            margin: 15px 0;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            font-size: 1.1em;
            padding: 10px 20px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        nav ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .container {
            flex-grow: 1;
            margin: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .container h1 {
            text-align: center;
            color: #004d99;
        }

        #chart-container {
            margin-top: 20px;
            text-align: center;
        }

        #chart-container h2 {
            color: #0073e6;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        footer {
            text-align: center;
            background: linear-gradient(to right, #004d99, #0073e6);
            color: #fff;
            padding: 10px 0;
            font-size: 0.9em;
            margin-top: 20px;
            border-radius: 0 0 10px 10px;
        }

        footer a {
            color: #ffcc00;
            text-decoration: none;
            font-weight: bold;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="dasho.php">Dashboard</a></li>
            <li><a href="acceuil.php">Acceuil</a></li>
            <li><a href="manage_articles.php">Gérer mes articles</a></li>
            <li><a href="my_blogs.php">Mes Blogs</a></li>
            <li><a href="logout.php">Se deconnecter</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Bienvenue dans votre Dashboard</h1>
        <p>Bonjour, utilisateur ID: <?php echo htmlspecialchars($user_id); ?> !</p>

        <h2>Vos articles</h2>
        <?php if (count($articles) > 0): ?>
            <ul>
                <?php foreach ($articles as $article): ?>
                    <li>
                        <?php echo htmlspecialchars($article['title']); ?> 
                        (Créé le <?php echo htmlspecialchars($article['created_at']); ?>)
                        <br>
                        Vues : <?php echo htmlspecialchars($article['views']); ?> 
                        | Likes : <?php echo htmlspecialchars($article['likes']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez aucun article pour le moment.</p>
        <?php endif; ?>

        <h2>Vos commentaires</h2>
        <?php if (count($comments) > 0): ?>
            <ul>
                <?php foreach ($comments as $comment): ?>
                    <li>Commentaire sur l'article ID <?php echo htmlspecialchars($comment['article_id']); ?> : "<?php echo htmlspecialchars($comment['contenu']); ?>"</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Vous n'avez laissé aucun commentaire pour le moment.</p>
        <?php endif; ?>

        <!-- Conteneur pour le graphique -->
        <div id="chart-container">
            <h2>Évolution des vues dans le temps</h2>
            <canvas id="viewsChart"></canvas>
        </div>
    </div>

    <!-- Script pour Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Données des vues à partir de la base de données
        const viewsData = <?php echo json_encode($views_data); ?>;
        const labels = viewsData.map(item => item.created_at);
        const data = viewsData.map(item => item.views);

        // Créer le graphique
        const ctx = document.getElementById('viewsChart').getContext('2d');
        const viewsChart = new Chart(ctx, {
            type: 'line', // Type de graphique
            data: {
                labels: labels,
                datasets: [{
                    label: 'Vues des articles',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'category',
                        labels: labels
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
