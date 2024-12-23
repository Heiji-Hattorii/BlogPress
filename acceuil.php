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
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
            display: flex;
        }

        /* Conteneur principal */
        .container {
            width: 75%;
            margin: 10px auto;
        }

        /* Articles populaires */
        .article {
            background: #fff;
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .article:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .article h2 {
            font-size: 1.5em;
            color: #0056b3;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .article h2:hover {
            color: #007bff;
        }

        .article a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }

        .article p {
            font-size: 1em;
            color: #777;
        }

        .article p strong {
            color: #333;
        }

        /* Section graphique */
        .chart-container {
            margin-top: 40px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Navigation */
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

        /* Style pour les boutons */
        button {
            background: #0073e6;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #005bb5;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 100%;
        }

        .modal-close {
            background: #ff0000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal-close:hover {
            background: #cc0000;
        }

        .comment-list {
            list-style-type: none;
            padding: 0;
        }

        .comment-list li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .container {
            width: 75%;
            margin: 10px auto;
        }

        /* Articles populaires */
        .article {
            background: #fff;
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .article:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .article h2 {
            font-size: 1.5em;
            color: #0056b3;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .article h2:hover {
            color: #007bff;
        }

        .article a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }

        .article p {
            font-size: 1em;
            color: #777;
        }

        .article p strong {
            color: #333;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 100%;
        }

        .modal-close {
            background: #ff0000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal-close:hover {
            background: #cc0000;
        }

        .comment-list {
            list-style-type: none;
            padding: 0;
        }

        .comment-list li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
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
    <h1>Articles populaires</h1>
    <!-- Liste des articles -->
    <?php foreach ($articles as $article): ?>
        <div class="article">
            <h2><a href="article.php?id=<?= $article['id']; ?>"><?= htmlspecialchars($article['title']); ?></a></h2>
            <p>Catégorie : <?= htmlspecialchars($article['category']); ?></p>
            <p>Vues : <?= $article['views']; ?> | 
                <a href="javascript:void(0);" class="view-comments" data-article-id="<?= $article['id']; ?>">
                    Commentaires (<?= $article['comments']; ?>)
                </a>
            </p>
        </div>
    <?php endforeach; ?>

    <!-- Graphique Chart.js -->
    <div class="chart-container">
        <canvas id="popularityChart"></canvas>
    </div>
</div>

<!-- Modal pour afficher les commentaires -->
<div id="commentsModal" class="modal">
    <div class="modal-content">
        <h3>Commentaires</h3>
        <ul id="commentsList" class="comment-list">
            <!-- Les commentaires seront insérés ici via JavaScript -->
        </ul>
        <button class="modal-close" onclick="closeModal()">Fermer</button>
    </div>
</div>

<script>
// Ouvrir la modal et charger les commentaires
document.querySelectorAll('.view-comments').forEach(button => {
    button.addEventListener('click', function() {
        const articleId = this.getAttribute('data-article-id');
        fetchComments(articleId);
    });
});

function fetchComments(articleId) {
    fetch('fetch_comments.php?article_id=' + articleId)
        .then(response => response.json())
        .then(data => {
            const commentsList = document.getElementById('commentsList');
            commentsList.innerHTML = '';

            if (data.length === 0) {
                const noCommentsMessage = document.createElement('li');
                noCommentsMessage.textContent = 'Pas de commentaire pour cet article';
                commentsList.appendChild(noCommentsMessage);
            } else {
                data.forEach(comment => {
                    const li = document.createElement('li');
                    li.textContent = comment.content;
                    commentsList.appendChild(li);
                });
            }

            document.getElementById('commentsModal').style.display = 'flex';
        })
        .catch(error => console.error('Erreur de chargement des commentaires:', error));
}

// Fermer la modal
function closeModal() {
    document.getElementById('commentsModal').style.display = 'none';
}

</script>
</body>
</html>
