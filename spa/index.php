<?php
// ===== FICHIER: index.php  =====
$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';

$allowed_pages = ['accueil', 'tournoi', 'infrastructure', 'joueur'];
if (!in_array($page, $allowed_pages)) {
    $page = 'accueil';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion</title>
    
    <!-- CSS Globaux -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/body.css">
    <link rel="stylesheet" href="css/footer.css">
    
    <!-- CSS spécifique à la page -->
    <?php if (file_exists("css/{$page}.css")): ?>
        <link rel="stylesheet" href="css/<?= $page ?>.css">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <?php include 'components/nav.php'; ?>
    <?php include 'components/header.php'; ?>
    
    <main class="page-body">
        <?php 
        $page_file = "pages/{$page}.php";
        if (file_exists($page_file)) {
            include $page_file;
        } else {
            echo "<div class='body-content'><h2>Page non trouvée</h2></div>";
        }
        ?>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <!-- JavaScript Global (optionnel) -->
    <script src="js/main.js"></script>
    
    <!-- JavaScript spécifique à la page -->
    <?php if (file_exists("js/{$page}.js")): ?>
        <script src="js/<?= $page ?>.js"></script>
    <?php endif; ?>
</body>
</html>