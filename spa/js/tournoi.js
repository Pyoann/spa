// ===== FICHIER: pages/tournoi.js =====

class TournoiManager {
    constructor() {
        this.tournois = [];
        this.filteredTournois = [];
        this.currentTournoiId = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadTournois();
    }

    bindEvents() {
        // Bouton nouveau tournoi
        document.getElementById('btnAjouterTournoi').addEventListener('click', () => {
            this.openModal();
        });

        // Filtres
        document.getElementById('filterStatut').addEventListener('change', () => {
            this.applyFilters();
        });
        
        document.getElementById('filterType').addEventListener('change', () => {
            this.applyFilters();
        });
        
        document.getElementById('searchTournoi').addEventListener('input', (e) => {
            this.searchTournois(e.target.value);
        });

        // Modal événements
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                this.closeModals();
            });
        });

        document.getElementById('btnAnnuler').addEventListener('click', () => {
            this.closeModals();
        });

        document.getElementById('btnAnnulerSupp').addEventListener('click', () => {
            this.closeModals();
        });

        // Formulaire
        document.getElementById('tournoiForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveTournoi();
        });

        // Validation du texte de confirmation
        document.getElementById('confirmationText').addEventListener('input', (e) => {
            this.validateConfirmationText(e.target.value);
        });

        // Confirmation suppression
        document.getElementById('btnConfirmerSupp').addEventListener('click', () => {
            this.deleteTournoi();
        });

        // Fermer modal en cliquant à l'extérieur
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModals();
            }
        });
    }

    // Méthode pour réinitialiser le modal de confirmation
    resetConfirmationModal() {
        const confirmationText = document.getElementById('confirmationText');
        const btnConfirmer = document.getElementById('btnConfirmerSupp');
        const errorDiv = document.getElementById('confirmationError');
        
        if (confirmationText) confirmationText.value = '';
        if (btnConfirmer) btnConfirmer.disabled = true;
        if (errorDiv) errorDiv.style.display = 'none';
    }

    // Méthode pour valider le texte de confirmation
    validateConfirmationText(value) {
        const button = document.getElementById('btnConfirmerSupp');
        const errorDiv = document.getElementById('confirmationError');
        
        if (value === 'SUPPRIMER') {
            button.disabled = false;
            errorDiv.style.display = 'none';
            return true;
        } else {
            button.disabled = true;
            if (value.length > 0 && value !== 'SUPPRIMER') {
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none';
            }
            return false;
        }
    }

    async loadTournois() {
        try {
            this.showLoading(true);
            
            const response = await fetch('api/tournoi_api.php');
            const data = await response.json();
            
            if (data.success) {
                this.tournois = data.data;
                this.filteredTournois = [...this.tournois];
                this.renderTable();
                this.populateTypeFilter();
            } else {
                this.showError('Erreur lors du chargement des tournois');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        } finally {
            this.showLoading(false);
        }
    }

    renderTable() {
        const tbody = document.getElementById('tournoiTableBody');
        
        if (this.filteredTournois.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">Aucun tournoi trouvé</td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.filteredTournois.map(tournoi => `
            <tr>
                <td>${tournoi.id}</td>
                <td><strong>${tournoi.nom}</strong></td>
                <td>${this.formatDate(tournoi.date)}</td>
                <td>${tournoi.lieux || '-'}</td>
                <td>${tournoi.type}</td>
                <td>${this.getStatutBadge(tournoi.statut)}</td>
                <td>${this.getSaisonBadge(tournoi.saison_actif)}</td>
                <td>
                    <div class="actions">
                        <button class="btn btn-sm btn-primary btn-icon" onclick="tournoiManager.editTournoi(${tournoi.id})" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-icon" onclick="tournoiManager.confirmDelete(${tournoi.id}, '${tournoi.nom.replace(/'/g, "&#39;")}')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    populateTypeFilter() {
        const typeFilter = document.getElementById('filterType');
        const types = [...new Set(this.tournois.map(t => t.type))];
        
        typeFilter.innerHTML = '<option value="">Tous les types</option>' + 
            types.map(type => `<option value="${type}">${type}</option>`).join('');
    }

    applyFilters() {
        const statutFilter = document.getElementById('filterStatut').value;
        const typeFilter = document.getElementById('filterType').value;
        
        this.filteredTournois = this.tournois.filter(tournoi => {
            const matchStatut = !statutFilter || tournoi.statut === statutFilter;
            const matchType = !typeFilter || tournoi.type === typeFilter;
            return matchStatut && matchType;
        });
        
        this.renderTable();
    }

    searchTournois(searchTerm) {
        if (!searchTerm.trim()) {
            this.applyFilters();
            return;
        }

        const term = searchTerm.toLowerCase();
        this.filteredTournois = this.tournois.filter(tournoi => {
            return tournoi.nom.toLowerCase().includes(term) ||
                   (tournoi.lieux && tournoi.lieux.toLowerCase().includes(term)) ||
                   tournoi.type.toLowerCase().includes(term) ||
                   (tournoi.description && tournoi.description.toLowerCase().includes(term));
        });
        
        this.renderTable();
    }

    openModal(tournoi = null) {
        const modal = document.getElementById('tournoiModal');
        const form = document.getElementById('tournoiForm');
        const title = document.getElementById('modalTitle');
        
        if (tournoi) {
            title.textContent = 'Modifier le tournoi';
            this.populateForm(tournoi);
            this.currentTournoiId = tournoi.id;
        } else {
            title.textContent = 'Nouveau tournoi';
            form.reset();
            this.currentTournoiId = null;
            document.getElementById('tournoiSaisonActif').checked = true;
        }
        
        modal.style.display = 'block';
    }

    populateForm(tournoi) {
        document.getElementById('tournoiId').value = tournoi.id;
        document.getElementById('tournoiNom').value = tournoi.nom;
        document.getElementById('tournoiDate').value = tournoi.date;
        document.getElementById('tournoiLieux').value = tournoi.lieux || '';
        document.getElementById('tournoiType').value = tournoi.type;
        document.getElementById('tournoiStatut').value = tournoi.statut;
        document.getElementById('tournoiDescription').value = tournoi.description || '';
        document.getElementById('tournoiSaisonActif').checked = tournoi.saison_actif == 1;
        document.getElementById('tournoiArchiver').checked = tournoi.archiver == 1;
    }

    async saveTournoi() {
        const formData = new FormData(document.getElementById('tournoiForm'));
        const data = Object.fromEntries(formData);
        
        // Convertir les checkboxes
        data.saison_actif = document.getElementById('tournoiSaisonActif').checked ? 1 : 0;
        data.archiver = document.getElementById('tournoiArchiver').checked ? 1 : 0;
        
        if (this.currentTournoiId) {
            data.id = this.currentTournoiId;
        }

        try {
            const response = await fetch('api/tournoi_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(this.currentTournoiId ? 'Tournoi modifié avec succès' : 'Tournoi créé avec succès');
                this.closeModals();
                this.loadTournois();
            } else {
                this.showError(result.message || 'Erreur lors de la sauvegarde');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        }
    }

    editTournoi(id) {
        const tournoi = this.tournois.find(t => t.id == id);
        if (tournoi) {
            this.openModal(tournoi);
        }
    }

    confirmDelete(id, nom) {
        this.currentTournoiId = id;
        document.getElementById('deleteNomTournoi').textContent = nom;
        this.resetConfirmationModal();
        document.getElementById('confirmModal').style.display = 'block';
    }

    async deleteTournoi() {
        // Double vérification
        const confirmationText = document.getElementById('confirmationText').value;
        if (confirmationText !== 'SUPPRIMER') {
            this.showError('Vous devez taper exactement "SUPPRIMER" pour confirmer.');
            return;
        }

        try {
            const response = await fetch(`api/tournoi_api.php?id=${this.currentTournoiId}`, {
                method: 'DELETE'
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Tournoi supprimé avec succès');
                this.closeModals();
                this.loadTournois();
            } else {
                this.showError(result.message || 'Erreur lors de la suppression');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        }
    }

    closeModals() {
        document.getElementById('tournoiModal').style.display = 'none';
        document.getElementById('confirmModal').style.display = 'none';
        this.resetConfirmationModal();
    }

    showLoading(show) {
        const spinner = document.getElementById('loadingSpinner');
        const table = document.getElementById('tournoiTable');
        
        if (show) {
            spinner.style.display = 'flex';
            table.style.display = 'none';
        } else {
            spinner.style.display = 'none';
            table.style.display = 'table';
        }
    }

    showError(message) {
        alert('Erreur: ' + message);
    }

    showSuccess(message) {
        alert('Succès: ' + message);
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-CA');
    }

    getStatutBadge(statut) {
        const badges = {
            'planifie': 'badge-planifie',
            'en_cours': 'badge-en-cours',
            'termine': 'badge-termine',
            'annule': 'badge-annule'
        };
        
        const labels = {
            'planifie': 'Planifié',
            'en_cours': 'En cours',
            'termine': 'Terminé',
            'annule': 'Annulé'
        };
        
        return `<span class="badge ${badges[statut] || ''}">${labels[statut] || statut}</span>`;
    }

    getSaisonBadge(saisonActif) {
        const isActif = saisonActif == 1;
        const badgeClass = isActif ? 'badge-actif' : 'badge-inactif';
        const label = isActif ? 'Active' : 'Inactive';
        
        return `<span class="badge ${badgeClass}">${label}</span>`;
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    window.tournoiManager = new TournoiManager();
});