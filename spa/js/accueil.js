// JavaScript pour la page d'accueil
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript Accueil chargé');

    // Sélectionner toutes les cards cliquables
    const clickableCards = document.querySelectorAll('.clickable-card');

    // Ajouter des effets de survol et de clic
    clickableCards.forEach(card => {
        // Ajouter un curseur pointer
        card.style.cursor = 'pointer';
        
        // Effets de survol
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.3s ease';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });

        // Effet de clic
        card.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.98)';
        });

        card.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1.05)';
        });
    });
});