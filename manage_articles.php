<?php
// Démarrage de la session
session_start();

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupération de l'ID de l'utilisateur connecté
$authorId = $_SESSION['user_id'];

// Configuration de la base de données
require_once 'db_config.php';

// Gestion CRUD pour les articles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_article'])) {
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars($_POST['category']);
        $createQuery = $pdo->prepare("INSERT INTO articles (title, content, category, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $createQuery->execute([$title, $content, $category, $authorId]);
    } elseif (isset($_POST['update_article'])) {
        $articleId = intval($_POST['article_id']);
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars($_POST['category']);
        $updateQuery = $pdo->prepare("UPDATE articles SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?");
        $updateQuery->execute([$title, $content, $category, $articleId, $authorId]);
    } elseif (isset($_POST['delete_article'])) {
        $articleId = intval($_POST['article_id']);
        $deleteQuery = $pdo->prepare("DELETE FROM articles WHERE id = ? AND user_id = ?");
        $deleteQuery->execute([$articleId, $authorId]);
    }
    header("Location: manage_articles.php");
    exit;
}

// Récupération des articles de l'utilisateur connecté
$articlesQuery = $pdo->prepare("SELECT * FROM articles WHERE user_id = ? ORDER BY created_at DESC");
$articlesQuery->execute([$authorId]);
$articles = $articlesQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des articles</title>
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

        form {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form input, form textarea, form button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        form button {
            background: #0073e6;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form button:hover {
            background: #005bb5;
        }

        hr {
            border: none;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <nav>
        <h2>BlogPress</h2>
        <ul>
            <li><a href="dasho.php">Dashboard</a></li>
            <li><a href="acceuil.php">Acceuil</a></li>
            <li><a href="manage_articles.php">Gérer les articles</a></li>
            <li><a href="my_blogs.php">Mes Blogs</a></li>
            <li><a href="logout.php">Se deconnecter</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Gérer les articles</h1>
        <!-- Formulaire de création -->
        <form method="POST">
            <input type="text" name="title" placeholder="Titre" required>
            <textarea name="content" placeholder="Contenu" rows="5" required></textarea>
            <input type="text" name="category" placeholder="Catégorie" required>
            <button type="submit" name="create_article">Créer un article</button>
        </form>
        <hr>
        <!-- Liste des articles -->
        <?php foreach ($articles as $article): ?>
            <form method="POST">
                <input type="hidden" name="article_id" value="<?= $article['id']; ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($article['title']); ?>" required>
                <textarea name="content" rows="3" required><?= htmlspecialchars($article['content']); ?></textarea>
                <input type="text" name="category" value="<?= htmlspecialchars($article['category']); ?>" required>
                <button type="submit" name="update_article">Modifier</button>
                <button type="submit" name="delete_article">Supprimer</button>
            </form>
            <hr>
        <?php endforeach; ?>
    </div>
</body>
</html>
