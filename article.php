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

// Récupérer l'ID de l'article depuis l'URL
$articleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Mettre à jour le compteur de vues
$updateViews = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
$updateViews->execute([$articleId]);

// Récupérer les informations de l'article
$getArticle = $pdo->prepare("SELECT a.*, u.name AS username 
                             FROM articles a
                             LEFT JOIN users u ON a.user_id = u.id_users
                             WHERE a.id = ?");
$getArticle->execute([$articleId]);
$article = $getArticle->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article introuvable.");
}

// Calculer le temps de lecture (environ 200 mots/minute)
$contentWordCount = str_word_count(strip_tags($article['content']));
$readingTime = ceil($contentWordCount / 200);

// Récupérer les commentaires associés à l'article
$getComments = $pdo->prepare("SELECT c.content, u.name AS username, c.created_at 
                              FROM comments c
                              JOIN users u ON c.user_id = u.id_users  -- Utilisation correcte de 'id_users'
                              WHERE c.article_id = ?
                              ORDER BY c.created_at DESC");
$getComments->execute([$articleId]);
$comments = $getComments->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = htmlspecialchars($_POST['comment']);
    $userId = 1; // Remplacez par l'ID de l'utilisateur connecté
    $addComment = $pdo->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
    $addComment->execute([$articleId, $userId, $comment]);
    header("Location: article.php?id=$articleId");
    exit;
}

// Récupérer le nombre de likes
$getLikes = $pdo->prepare("SELECT likes FROM articles WHERE id = ?");
$getLikes->execute([$articleId]);
$likes = $getLikes->fetchColumn();

// Gestion des likes
if (isset($_GET['like'])) {
    $pdo->prepare("UPDATE articles SET likes = likes + 1 WHERE id = ?")->execute([$articleId]);
    header("Location: article.php?id=$articleId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']); ?> - BlogPress</title>
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
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .comments {
            margin-top: 30px;
        }
        .comment {
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .like-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .like-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="article">
            <h1><?= htmlspecialchars($article['title']); ?></h1>
            <p>Publié le : <?= date("d/m/Y", strtotime($article['created_at'])); ?></p>
            <p><strong>Temps de lecture :</strong> ~<?= $readingTime; ?> min</p>
            <p>Par <strong><?= htmlspecialchars($article['username'] ?? 'Inconnu'); ?></strong></p> <!-- Affichage du nom de l'auteur -->
            <p><?= nl2br(htmlspecialchars($article['content'])); ?></p>
            <a href="article.php?id=<?= $articleId; ?>&like=true" class="like-btn">
                J'aime (<?= $likes; ?>)
            </a>
        </div>

        <div class="comments">
            <h2>Commentaires</h2>
            <form method="POST" action="">
                <textarea name="comment" rows="4" cols="50" placeholder="Ajoutez un commentaire..." required></textarea>
                <br>
                <button type="submit">Envoyer</button>
            </form>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p><strong><?= htmlspecialchars($comment['username']); ?></strong> - <?= date("d/m/Y H:i", strtotime($comment['created_at'])); ?></p>
                    <p><?= nl2br(htmlspecialchars($comment['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
