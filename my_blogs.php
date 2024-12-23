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

// ID de l'utilisateur connecté
$userId = $_SESSION['user_id'];

// Récupération des blogs de l'utilisateur
$query = $pdo->prepare("SELECT * FROM articles WHERE user_id = :user_id ORDER BY created_at DESC");
$query->execute(['user_id' => $userId]);
$blogs = $query->fetchAll(PDO::FETCH_ASSOC);

// Gestion de la suppression d'un article
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Suppression de l'article
    $deleteQuery = $pdo->prepare("DELETE FROM articles WHERE id = :id AND user_id = :user_id");
    $deleteQuery->execute(['id' => $deleteId, 'user_id' => $userId]);

    header('Location: my_blogs.php');
    exit();
}

// Gestion de la modification d'un article
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['blog_id'], $_POST['title'], $_POST['content'])) {
    $articleId = $_POST['blog_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Mise à jour de l'article
    $updateQuery = $pdo->prepare("UPDATE articles SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
    $updateQuery->execute(['title' => $title, 'content' => $content, 'id' => $articleId, 'user_id' => $userId]);

    header('Location: my_blogs.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes blogs</title>
    <style>
/* Corps de la page */
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    background: linear-gradient(to bottom right, #f4f4f9, #d9e4f5);
    color: #333;
    height: 100vh;
    overflow: hidden;
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
    position: fixed;
    left: 0;
    top: 0;
}

nav h2 {
    margin-bottom: 30px;
    font-size: 1.6em;
    color: #ffcc00;
    font-weight: bold;
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

/* Container principal */
.container {
    margin-left: 280px;
    padding: 40px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    width: 75%;
    overflow: hidden;
    height: 100vh;
    overflow-y: auto;
}

/* Titre principal */
.container h1 {
    text-align: center;
    color: #004d99;
    font-size: 3em;
    margin-bottom: 40px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Blog individuelle */
.blog {
    margin-bottom: 30px;
    padding: 25px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #f9f9f9;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}


.blog h2 {
    color: #0073e6;
    margin-bottom: 15px;
    font-size: 1.8em;
    text-transform: capitalize;
}

.blog p {
    line-height: 1.8;
    margin-bottom: 15px;
    font-size: 1.1em;
    color: #555;
}

.blog small {
    display: block;
    color: #555;
    font-size: 0.9em;
    margin-top: 10px;
}

.blog hr {
    margin: 20px 0;
    border: none;
    height: 1px;
    background: #ddd;
}

/* Boutons de suppression et modification */
button {
    background-color: #ff6f61;
    color: white;
    border: none;
    padding: 12px 25px;
    font-size: 1.2em;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    margin-right: 10px;
}

button:hover {
    background-color: #ff4c38;
    transform: translateY(-3px);
}

button:active {
    transform: translateY(2px);
}

/* Modal */
#modalEdit {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.4s ease-out;
}

.modal-content {
    background-color: #fff;
    padding: 40px;
    border-radius: 10px;
    width: 500px;
    margin: 50px auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        transform: translateY(-30px);
    }
    to {
        transform: translateY(0);
    }
}

.close {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    color: #333;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #ff6f61;
}

/* Formulaire de modification */
form input, form textarea {
    width: 100%;
    padding: 12px;
    margin: 12px 0;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 1.1em;
}

form button {
    background-color: #0073e6;
    padding: 14px 25px;
    font-size: 1.2em;
    border-radius: 5px;
    border: none;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

form button:hover {
    background-color: #005bb5;
    transform: scale(1.05);
}

form button:active {
    transform: scale(1);
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
    <h1>Mes Blogs</h1>
    <?php if (count($blogs) > 0): ?>
        <?php foreach ($blogs as $blog): ?>
            <div class="blog">
                <h2><?= htmlspecialchars($blog['title']); ?></h2>
                <p><?= nl2br(htmlspecialchars($blog['content'])); ?></p>
                <small>Catégorie : <?= htmlspecialchars($blog['category']); ?></small><br>
                <small>Publié le : <?= htmlspecialchars($blog['created_at']); ?></small>
                <hr>
                
                <!-- Boutons Supprimer et Modifier -->
                <a href="?delete_id=<?= $blog['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                    <button>Supprimer</button>
                </a>
                <button onclick="openModal(<?= $blog['id']; ?>, '<?= htmlspecialchars($blog['title']); ?>', '<?= htmlspecialchars($blog['content']); ?>')">Modifier</button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun blog trouvé.</p>
    <?php endif; ?>
</div>

<!-- Modal Modifier -->
<div id="modalEdit" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Modifier l'article</h2>
        <form method="POST">
            <input type="hidden" id="blog_id" name="blog_id">
            <label for="title">Titre :</label>
            <input type="text" id="editTitle" name="title" required><br><br>
            <label for="content">Contenu :</label><br>
            <textarea id="editContent" name="content" rows="4" required></textarea><br><br>
            <button type="submit">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<script>
    // Ouvrir la modale avec les données pré-remplies
    function openModal(id, title, content) {
        document.getElementById('blog_id').value = id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editContent').value = content;
        document.getElementById('modalEdit').style.display = 'block';
    }

    // Fermer la modale
    function closeModal() {
        document.getElementById('modalEdit').style.display = 'none';
    }
</script>

</body>
</html>
