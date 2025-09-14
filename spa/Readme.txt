# 📁 Architecture Modulaire PHP - Documentation Projet

## 🎯 **Vue d'ensemble**
Site web modulaire développé en PHP avec une architecture séparée pour permettre à plusieurs équipes de travailler simultanément sur différentes pages/sections sans conflit.

**Hébergement :** WHC (PHP + MySQL disponibles)  
**Contraintes :** Pas d'installation de logiciels externes  
**Équipes :** Multiples équipes travaillant en parallèle  

---

## 🏗️ **Structure des fichiers**

```
racine/
├── index.php                 # Page principale (contrôleur)
├── components/
│   ├── nav.php              # Navigation modulaire
│   ├── header.php           # En-tête modulaire  
│   └── footer.php           # Pied de page modulaire
├── pages/
│   ├── accueil.php          # Page d'accueil (DÉVELOPPÉE)
│   └── [futur-page].php     # Pages futures à développer
├── css/
│   ├── main.css             # Styles globaux
│   ├── nav.css              # Styles navigation
│   ├── header.css           # Styles en-tête
│   ├── body.css             # Styles corps général
│   ├── footer.css           # Styles pied de page
│   ├── accueil.css          # Styles spécifiques accueil (DÉVELOPPÉ)
│   └── [futur-page].css     # CSS spécifique par page future
└── js/
    ├── main.js              # JavaScript global
    ├── accueil.js           # JS spécifique accueil (DÉVELOPPÉ)
    └── [futur-page].js      # JS spécifique par page future
```

---

## ⚙️ **Fonctionnement du système**

### **Navigation par paramètres URL**
- `?page=accueil` → Charge `pages/accueil.php`
- `?page=futur-page` → Charge `pages/futur-page.php` + `css/futur-page.css` + `js/futur-page.js`
- URL par défaut → `accueil`

### **Chargement automatique des ressources**
```php
// CSS spécifique (si existe)
<?php if (file_exists("css/{$page}.css")): ?>
    <link rel="stylesheet" href="css/<?= $page ?>.css">
<?php endif; ?>

// JS spécifique (si existe)
<?php if (file_exists("js/{$page}.js")): ?>
    <script src="js/<?= $page ?>.js"></script>
<?php endif; ?>
```

### **Sécurité**
Pages autorisées définies dans `index.php` :
```php
$allowed_pages = ['accueil']; // Ajouter les nouvelles pages ici
```

---

## 🔧 **Workflow pour ajouter une nouvelle page**

### **1. Créer les fichiers**
- `pages/futur-page.php` (contenu HTML)
- `css/futur-page.css` (styles spécifiques)  
- `js/futur-page.js` (fonctionnalités JS)

### **2. Autoriser la page**
Dans `index.php`, ajouter à `$allowed_pages` :
```php
$allowed_pages = ['accueil', 'futur-page'];
```

### **3. Ajouter au menu**
Dans `components/nav.php`, ajouter à `$nav_items` :
```php
$nav_items = [
    'accueil' => 'Accueil',
    'futur-page' => 'Nom Dans Menu'
];
```

### **4. Accès**
Page accessible via `?page=futur-page`

---

## 🎨 **Système de styles**

### **Hiérarchie CSS**
1. `main.css` → Styles globaux (chargé partout)
2. `nav.css`, `header.css`, `footer.css` → Composants (chargés partout)
3. `[page].css` → Styles spécifiques (chargés par page)

### **Palette de couleurs utilisée**
- **Navigation :** `#2c3e50` (bleu foncé)
- **Header :** `#3498db` (bleu)
- **Body :** `#ecf0f1` (gris clair)
- **Footer :** `#2c3e50` (discret)
- **Accents :** `#e74c3c` (rouge), `#2ecc71` (vert)

---

## ⚡ **Système JavaScript**

### **Architecture JS**
- `main.js` → Fonctions globales disponibles partout
- `[page].js` → Fonctionnalités spécifiques à une page

### **Fonction globale disponible**
```javascript
// Définie dans main.js
function showMessage(message, type = 'info') {
    alert(`[${type.toUpperCase()}] ${message}`);
}
```

---

## 📋 **Page actuellement développée**

### **🏠 Accueil (`pages/accueil.php`)**
- Compteurs statistiques animés (visiteurs, projets, clients)
- Section actualités dynamique avec bouton "Charger plus"
- Boutons d'actions rapides (contact, portfolio, devis)
- **CSS :** Design moderne avec cartes statistiques, actualités, actions
- **JS :** Animation compteurs, ajout actualités, gestion actions

**Status :** ✅ **COMPLÈTEMENT DÉVELOPPÉE**

---

## 🔍 **Débogage et maintenance**

### **Fichier de debug**
Créer `debug.php` à la racine pour vérifier l'existence des fichiers :
```php
// Vérification des fichiers
echo file_exists('components/nav.php') ? '✅ nav.php' : '❌ nav.php manquant';
echo file_exists('pages/accueil.php') ? '✅ accueil.php' : '❌ accueil.php manquant';
```

### **Activation des erreurs PHP**
Ajouter en haut de `index.php` :
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### **Vérifications communes**
- Structure des dossiers respectée
- Fichiers uploadés correctement
- Permissions serveur OK
- Syntaxe PHP valide

---

## 👥 **Répartition possible des équipes futures**

| Équipe | Fichiers responsables |
|--------|----------------------|
| **Navigation** | `components/nav.php` + `css/nav.css` |
| **Header** | `components/header.php` + `css/header.css` |
| **Footer** | `components/footer.php` + `css/footer.css` |
| **Accueil** | `pages/accueil.php` + `css/accueil.css` + `js/accueil.js` ✅ |
| **Équipe A** | `pages/futur-page-a.php` + `css/futur-page-a.css` + `js/futur-page-a.js` |
| **Équipe B** | `pages/futur-page-b.php` + `css/futur-page-b.css` + `js/futur-page-b.js` |
| **Équipe C** | `pages/futur-page-c.php` + `css/futur-page-c.css` + `js/futur-page-c.js` |

---

## 🚀 **Fonctionnalités avancées développées**

### **Modal System**
- Modal JavaScript réutilisable
- Exemple fonctionnel : bouton `.shishka` avec modal "Allo Shishka!"
- Fermeture multiple : X, OK, Escape, clic extérieur
- **Status :** ✅ **FONCTIONNEL**

### **Navigation Active**
- Lien actuel automatiquement surligné
- Classe `.active` gérée par PHP
- **Status :** ✅ **FONCTIONNEL**

### **Responsive Design**
- Mobile-first approach
- Breakpoint principal : `768px`
- **Status :** ✅ **IMPLÉMENTÉ**

---

## 🛡️ **Sécurité implémentée**

- ✅ Whitelist des pages autorisées
- ✅ Vérification existence fichiers
- ✅ Échappement HTML avec `htmlspecialchars()`
- ✅ Validation paramètres URL

---

## 📋 **État actuel du projet**

### **✅ Terminé et fonctionnel**
- Architecture modulaire complète
- Page d'accueil avec toutes fonctionnalités
- Système de navigation
- Header et footer professionnels
- Modal system
- Responsive design

### **🔄 Prêt pour développement**
- Structure prête pour nouvelles pages
- Workflow établi pour équipes
- CSS/JS modulaire en place

---

## 🔄 **Prochaines étapes possibles**

- [ ] Développement nouvelles pages (selon besoins projet)
- [ ] Intégration base de données MySQL
- [ ] Système d'authentification
- [ ] Upload de fichiers
- [ ] API REST
- [ ] Système de cache
- [ ] Optimisation SEO

---

## 📝 **Notes pour développement futur**

### **Contraintes techniques**
- Pas de Node.js/NPM disponible
- Pas de framework PHP (Laravel, Symfony)
- Pas de préprocesseur CSS (SASS, LESS)
- Solutions natives PHP/JS/CSS uniquement

### **Bonnes pratiques établies**
- Un fichier par fonctionnalité
- CSS organisé par composant
- JavaScript modulaire par page
- Validation côté serveur ET client

### **Template pour nouvelle page**
```php
// pages/futur-page.php
<div class="body-content">
    <h2>Titre de la page</h2>
    <p>Contenu de la page...</p>
    <!-- Votre contenu ici -->
</div>
```

```css
/* css/futur-page.css */
/* Styles spécifiques à cette page */
.ma-classe-specifique {
    /* Vos styles ici */
}
```

```javascript
// js/futur-page.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript futur-page chargé');
    // Votre code JS ici
});
```

---

**Version du README :** 1.0  
**Dernière mise à jour :** Septembre 2024  
**Statut projet :** Architecture fonctionnelle, page d'accueil terminée, prêt pour développement nouvelles pages