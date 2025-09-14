<?php
// ===== FICHIER: components/nav.php  =====
$current_page = isset($_GET['page']) ? $_GET['page'] : 'accueil';
$nav_items = [
    'accueil' => 'Accueil',
    'tournoi' => 'Tournois',
    'infrastructure' => 'Infrastructure',
    'joueur' => 'Joueurs'
];

?>
<nav class="navigation">
    <div class="nav-content">
        <h3>Bienvenu</h3>
        <ul>
            <?php foreach ($nav_items as $slug => $title): ?>
                <li>
                    <a href="?page=<?= $slug ?>" 
                       class="<?= ($current_page === $slug) ? 'active' : '' ?>">
                        <?= htmlspecialchars($title) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>