// ===== FICHIER: pages/infrastructure.js =====

class InfrastructureManager {
    constructor() {
        this.infrastructures = [];
        this.filteredInfrastructures = [];
        this.options = {
            css: [],
            ecoles: [],
            gymnases: [],
            sports: []
        };
        this.currentEntityType = null;
        this.currentEntityId = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadOptions();
        this.loadInfrastructures();
    }

    bindEvents() {
        // Boutons d'ajout
        document.getElementById('btnAjouterCSS').addEventListener('click', () => {
            this.openModal('css');
        });

        document.getElementById('btnAjouterEcole').addEventListener('click', () => {
            this.openModal('ecoles');
        });

        document.getElementById('btnAjouterGymnase').addEventListener('click', () => {
            this.openModal('gymnases');
        });

        document.getElementById('btnAjouterTerrain').addEventListener('click', () => {
            this.openModal('terrains');
        });

        // Filtres en cascade
        document.getElementById('filterCSS').addEventListener('change', (e) => {
            this.onCSSFilterChange(e.target.value);
        });

        document.getElementById('filterEcole').addEventListener('change', (e) => {
            this.onEcoleFilterChange(e.target.value);
        });

        document.getElementById('filterGymnase').addEventListener('change', (e) => {
            this.applyFilters();
        });

        document.getElementById('filterSport').addEventListener('change', () => {
            this.applyFilters();
        });

        document.getElementById('searchInfrastructure').addEventListener('input', (e) => {
            this.searchInfrastructures(e.target.value);
        });

        // Modal événements
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                this.closeModals();
            });
        });

        // Boutons d'annulation
        ['btnAnnulerCSS', 'btnAnnulerEcole', 'btnAnnulerGymnase', 'btnAnnulerTerrain', 'btnAnnulerSupp'].forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.addEventListener('click', () => this.closeModals());
            }
        });

        // Formulaires
        ['cssForm', 'ecolesForm', 'gymnasesForm', 'terrainsForm'].forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    let entityType = formId.replace('Form', '');
                    if (entityType === 'css') entityType = 'css';
                    else if (entityType === 'ecoles') entityType = 'ecoles';
                    else if (entityType === 'gymnases') entityType = 'gymnases';
                    else if (entityType === 'terrains') entityType = 'terrains';
                    this.saveEntity(entityType);
                });
            }
        });

        // Validation du texte de confirmation
        document.getElementById('confirmationText')?.addEventListener('input', (e) => {
            this.validateConfirmationText(e.target.value);
        });

        // Confirmation suppression
        document.getElementById('btnConfirmerSupp')?.addEventListener('click', () => {
            this.deleteEntity();
        });

        // Fermer modal en cliquant à l'extérieur
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModals();
            }
        });
    }

    async loadOptions() {
        try {
            const response = await fetch('api/infrastructure_api.php?entity=options');
            const data = await response.json();
            
            if (data.success) {
                this.options = data.data;
                this.populateFilterOptions();
                this.populateFormOptions();
            }
        } catch (error) {
            console.error('Erreur lors du chargement des options:', error);
        }
    }

    populateFilterOptions() {
        // CSS Filter
        const cssFilter = document.getElementById('filterCSS');
        cssFilter.innerHTML = '<option value="">Tous les CSS</option>' + 
            this.options.css.map(css => 
                `<option value="${css.id}">${css.initiales}</option>`
            ).join('');

        // Sport Filter
        const sportFilter = document.getElementById('filterSport');
        sportFilter.innerHTML = '<option value="">Tous les sports</option>' + 
            this.options.sports.map(sport => 
                `<option value="${sport.type_sport}">${sport.type_sport}</option>`
            ).join('');
    }

    populateFormOptions() {
        // Options CSS pour formulaire école
        const ecoleCSS = document.getElementById('ecoleCSS');
        if (ecoleCSS) {
            ecoleCSS.innerHTML = '<option value="">Sélectionner un CSS</option>' + 
                this.options.css.map(css => 
                    `<option value="${css.id}">${css.initiales}</option>`
                ).join('');
        }

        // Options écoles pour formulaire gymnase
        const gymnaseEcole = document.getElementById('gymnaseEcole');
        if (gymnaseEcole) {
            gymnaseEcole.innerHTML = '<option value="">Sélectionner une école</option>' + 
                this.options.ecoles.map(ecole => {
                    const css = this.options.css.find(c => c.id == ecole.id_css);
                    const cssInitiales = css ? css.initiales : 'N/A';
                    return `<option value="${ecole.id}">${ecole.nom} (${cssInitiales})</option>`;
                }).join('');
        }

        // Options gymnases pour formulaire terrain
        const terrainGymnase = document.getElementById('terrainGymnase');
        if (terrainGymnase) {
            terrainGymnase.innerHTML = '<option value="">Sélectionner un gymnase</option>' + 
                this.options.gymnases.map(gymnase => 
                    `<option value="${gymnase.id}">${gymnase.nom} (${gymnase.ecole_nom})</option>`
                ).join('');
        }
    }

    onCSSFilterChange(cssId) {
        const ecoleFilter = document.getElementById('filterEcole');
        const gymnaseFilter = document.getElementById('filterGymnase');
        
        // Reset des filtres enfants
        ecoleFilter.innerHTML = '<option value="">Toutes les écoles</option>';
        gymnaseFilter.innerHTML = '<option value="">Tous les gymnases</option>';
        
        if (cssId) {
            // Filtrer les écoles pour ce CSS
            const ecolesFiltered = this.options.ecoles.filter(e => e.id_css == cssId);
            ecoleFilter.innerHTML += ecolesFiltered.map(ecole => 
                `<option value="${ecole.id}">${ecole.nom}</option>`
            ).join('');
        } else {
            // Toutes les écoles
            ecoleFilter.innerHTML += this.options.ecoles.map(ecole => 
                `<option value="${ecole.id}">${ecole.nom} (${ecole.css_nom})</option>`
            ).join('');
        }
        
        this.applyFilters();
    }

    onEcoleFilterChange(ecoleId) {
        const gymnaseFilter = document.getElementById('filterGymnase');
        
        // Reset du filtre gymnase
        gymnaseFilter.innerHTML = '<option value="">Tous les gymnases</option>';
        
        if (ecoleId) {
            // Filtrer les gymnases pour cette école
            const gymnasesFiltered = this.options.gymnases.filter(g => g.id_ecole == ecoleId);
            gymnaseFilter.innerHTML += gymnasesFiltered.map(gymnase => 
                `<option value="${gymnase.id}">${gymnase.nom}</option>`
            ).join('');
        } else {
            // Tous les gymnases (mais filtrés par CSS si applicable)
            const cssId = document.getElementById('filterCSS').value;
            let gymnasesToShow = this.options.gymnases;
            
            if (cssId) {
                const ecolesInCSS = this.options.ecoles.filter(e => e.id_css == cssId);
                const ecoleIds = ecolesInCSS.map(e => e.id);
                gymnasesToShow = this.options.gymnases.filter(g => ecoleIds.includes(parseInt(g.id_ecole)));
            }
            
            gymnaseFilter.innerHTML += gymnasesToShow.map(gymnase => 
                `<option value="${gymnase.id}">${gymnase.nom} (${gymnase.ecole_nom})</option>`
            ).join('');
        }
        
        this.applyFilters();
    }

    async loadInfrastructures() {
        try {
            this.showLoading(true);
            console.log('Chargement des infrastructures...');
            
            const response = await fetch('api/infrastructure_api.php?entity=all');
            const data = await response.json();
            
            console.log('Données reçues:', data);
            
            if (data.success) {
                this.infrastructures = data.data || [];
                this.filteredInfrastructures = [...this.infrastructures];
                this.renderTable();
                console.log('Infrastructures chargées:', this.infrastructures.length);
            } else {
                console.error('Erreur API:', data.message);
                this.showError('Erreur lors du chargement des infrastructures: ' + (data.message || 'Erreur inconnue'));
            }
        } catch (error) {
            console.error('Erreur de chargement:', error);
            this.showError('Erreur de connexion: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    renderTable() {
        const tbody = document.getElementById('infrastructureTableBody');
        if (!tbody) {
            console.error('Élément tbody non trouvé');
            return;
        }
        
        if (this.filteredInfrastructures.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">Aucune infrastructure trouvée</td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.filteredInfrastructures.map(infra => `
            <tr>
                <td class="hierarchy-cell css-cell">
                    ${infra.css_initiales || 'N/A'}
                    <!-- <div style="font-size: 0.8em; color: #6c757d;">${infra.css_nom || 'N/A'}</div> -->
                </td>
                <td class="hierarchy-cell ecole-cell">${infra.ecole_nom || 'N/A'}</td>
                <td class="hierarchy-cell gymnase-cell">${infra.gymnase_nom || 'N/A'}</td>
                <td class="hierarchy-cell terrain-cell">${infra.terrain_nom || 'N/A'}</td>
                <td><span class="sport-badge">${infra.type_sport || 'N/A'}</span></td>
                <td>
                    <div class="actions">
                        ${infra.css_id ? `<button class="btn btn-sm btn-info btn-icon" onclick="infrastructureManager.editEntity('css', ${infra.css_id})" title="Modifier CSS">
                            <i class="fas fa-building"></i>
                        </button>` : ''}
                        ${infra.ecole_id ? `<button class="btn btn-sm btn-success btn-icon" onclick="infrastructureManager.editEntity('ecoles', ${infra.ecole_id})" title="Modifier École">
                            <i class="fas fa-school"></i>
                        </button>` : ''}
                        ${infra.gymnase_id ? `<button class="btn btn-sm btn-warning btn-icon" onclick="infrastructureManager.editEntity('gymnases', ${infra.gymnase_id})" title="Modifier Gymnase">
                            <i class="fas fa-dumbbell"></i>
                        </button>` : ''}
                        ${infra.terrain_id ? `<button class="btn btn-sm btn-primary btn-icon" onclick="infrastructureManager.editEntity('terrains', ${infra.terrain_id})" title="Modifier Terrain">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                        ${infra.terrain_id ? `<button class="btn btn-sm btn-danger btn-icon" onclick="infrastructureManager.confirmDelete('terrains', ${infra.terrain_id}, '${(infra.terrain_nom || '').replace(/'/g, "&#39;")}')" title="Supprimer Terrain">
                            <i class="fas fa-trash"></i>
                        </button>` : ''}
                        ${infra.gymnase_id && !infra.terrain_id ? `<button class="btn btn-sm btn-danger btn-icon" onclick="infrastructureManager.confirmDelete('gymnases', ${infra.gymnase_id}, '${(infra.gymnase_nom || '').replace(/'/g, "&#39;")}')" title="Supprimer Gymnase">
                            <i class="fas fa-trash"></i>
                        </button>` : ''}
                        ${infra.ecole_id && !infra.gymnase_id ? `<button class="btn btn-sm btn-danger btn-icon" onclick="infrastructureManager.confirmDelete('ecoles', ${infra.ecole_id}, '${(infra.ecole_nom || '').replace(/'/g, "&#39;")}')" title="Supprimer École">
                            <i class="fas fa-trash"></i>
                        </button>` : ''}
                        ${infra.css_id && !infra.ecole_id ? `<button class="btn btn-sm btn-danger btn-icon" onclick="infrastructureManager.confirmDelete('css', ${infra.css_id}, '${(infra.css_nom || '').replace(/'/g, "&#39;")}')" title="Supprimer CSS">
                            <i class="fas fa-trash"></i>
                        </button>` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    applyFilters() {
        const cssFilter = document.getElementById('filterCSS').value;
        const ecoleFilter = document.getElementById('filterEcole').value;
        const gymnaseFilter = document.getElementById('filterGymnase').value;
        const sportFilter = document.getElementById('filterSport').value;
        
        this.filteredInfrastructures = this.infrastructures.filter(infra => {
            const matchCSS = !cssFilter || infra.css_id == cssFilter;
            const matchEcole = !ecoleFilter || infra.ecole_id == ecoleFilter;
            const matchGymnase = !gymnaseFilter || infra.gymnase_id == gymnaseFilter;
            const matchSport = !sportFilter || infra.type_sport === sportFilter;
            
            return matchCSS && matchEcole && matchGymnase && matchSport;
        });
        
        this.renderTable();
    }

    searchInfrastructures(searchTerm) {
        if (!searchTerm.trim()) {
            this.applyFilters();
            return;
        }

        const term = searchTerm.toLowerCase();
        this.filteredInfrastructures = this.infrastructures.filter(infra => {
            return (infra.css_nom && infra.css_nom.toLowerCase().includes(term)) ||
                   (infra.css_initiales && infra.css_initiales.toLowerCase().includes(term)) ||
                   (infra.ecole_nom && infra.ecole_nom.toLowerCase().includes(term)) ||
                   (infra.gymnase_nom && infra.gymnase_nom.toLowerCase().includes(term)) ||
                   (infra.terrain_nom && infra.terrain_nom.toLowerCase().includes(term)) ||
                   (infra.type_sport && infra.type_sport.toLowerCase().includes(term));
        });
        
        this.renderTable();
    }

    openModal(entityType, entity = null) {
        const modalId = entityType + 'Modal';
        const modal = document.getElementById(modalId);
        const form = document.getElementById(entityType + 'Form');
        const title = document.getElementById(entityType + 'ModalTitle');
        
        if (entity) {
            title.textContent = `Modifier ${this.getEntityLabel(entityType)}`;
            this.populateForm(entityType, entity);
            this.currentEntityId = entity.id;
        } else {
            title.textContent = `Nouveau ${this.getEntityLabel(entityType)}`;
            form.reset();
            this.currentEntityId = null;
        }
        
        this.currentEntityType = entityType;
        modal.style.display = 'block';
    }

    getEntityLabel(entityType) {
        const labels = {
            css: 'CSS',
            ecoles: 'École',
            gymnases: 'Gymnase',
            terrains: 'Terrain'
        };
        return labels[entityType] || entityType;
    }

    populateForm(entityType, entity) {
        switch(entityType) {
            case 'css':
                document.getElementById('cssId').value = entity.id;
                document.getElementById('cssNomComplet').value = entity.nom_complet;
                document.getElementById('cssInitiales').value = entity.initiales;
                break;
            case 'ecoles':
                document.getElementById('ecoleId').value = entity.id;
                document.getElementById('ecoleNom').value = entity.nom;
                document.getElementById('ecoleCSS').value = entity.id_css;
                break;
            case 'gymnases':
                document.getElementById('gymnaseId').value = entity.id;
                document.getElementById('gymnaseNom').value = entity.nom;
                document.getElementById('gymnaseEcole').value = entity.id_ecole;
                break;
            case 'terrains':
                document.getElementById('terrainId').value = entity.id;
                document.getElementById('terrainNom').value = entity.nom;
                document.getElementById('terrainGymnase').value = entity.id_gymnase;
                document.getElementById('terrainTypeSport').value = entity.type_sport;
                break;
        }
    }

    async editEntity(entityType, id) {
        try {
            const response = await fetch(`api/infrastructure_api.php?entity=${entityType}&id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                this.openModal(entityType, data.data);
            } else {
                this.showError('Erreur lors du chargement de l\'entité');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        }
    }

    async saveEntity(entityType) {
        const formId = entityType + 'Form';
        const form = document.getElementById(formId);
        if (!form) {
            console.error('Formulaire non trouvé:', formId);
            return;
        }
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        if (this.currentEntityId) {
            data.id = this.currentEntityId;
        }

        try {
            const response = await fetch(`api/infrastructure_api.php?entity=${entityType}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
                this.closeModals();
                
                // Recharger dans le bon ordre
                await this.loadOptions();
                await this.loadInfrastructures();
                
                // Réappliquer les filtres après rechargement
                setTimeout(() => {
                    this.applyFilters();
                }, 100);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        }
    }

    confirmDelete(entityType, id, name) {
        this.currentEntityType = entityType;
        this.currentEntityId = id;
        document.getElementById('deleteEntityName').textContent = name;
        document.getElementById('deleteEntityType').textContent = this.getEntityLabel(entityType);
        document.getElementById('deleteEntityType2').textContent = this.getEntityLabel(entityType);
        this.resetConfirmationModal();
        document.getElementById('confirmModal').style.display = 'block';
    }

    resetConfirmationModal() {
        const confirmationText = document.getElementById('confirmationText');
        const btnConfirmer = document.getElementById('btnConfirmerSupp');
        const errorDiv = document.getElementById('confirmationError');
        
        if (confirmationText) confirmationText.value = '';
        if (btnConfirmer) btnConfirmer.disabled = true;
        if (errorDiv) errorDiv.style.display = 'none';
    }

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

    async deleteEntity() {
        const confirmationText = document.getElementById('confirmationText').value;
        if (confirmationText !== 'SUPPRIMER') {
            this.showError('Vous devez taper exactement "SUPPRIMER" pour confirmer.');
            return;
        }

        try {
            const response = await fetch(`api/infrastructure_api.php?entity=${this.currentEntityType}&id=${this.currentEntityId}`, {
                method: 'DELETE'
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
                this.closeModals();
                this.loadOptions();
                this.loadInfrastructures();
            } else {
                this.showError(result.message);
                if (result.dependencies) {
                    this.showError('Éléments dépendants: ' + result.dependencies.join(', '));
                }
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        }
    }

    closeModals() {
        const modals = ['cssModal', 'ecolesModal', 'gymnasesModal', 'terrainsModal', 'confirmModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        });
        this.resetConfirmationModal();
    }

    showLoading(show) {
        const spinner = document.getElementById('loadingSpinner');
        const table = document.getElementById('infrastructureTable');
        
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
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    window.infrastructureManager = new InfrastructureManager();
});