<?php
// Récupérer l'ID de l'article
$articleId = isset($_GET['article_id']) ? $_GET['article_id'] : 0;

if ($articleId) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=blogpress;charset=utf8", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT content FROM comments WHERE article_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleId]);

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Renvoyer un tableau vide si aucun commentaire n'existe
        if ($comments) {
            echo json_encode($comments);
        } else {
            echo json_encode([]);  // Retourner un tableau vide
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
    }
}
?>
