<!-- ===== FICHIER: pages/infrastructure.php ===== -->
<div class="body-content">
    <div class="page-header">
        <h1>Gestion des Infrastructures</h1>
        <div class="actions-header">
            <button id="btnAjouterCSS" class="btn btn-info">
                <i class="fas fa-building"></i> Nouveau CSS
            </button>
            <button id="btnAjouterEcole" class="btn btn-success">
                <i class="fas fa-school"></i> Nouvelle École
            </button>
            <button id="btnAjouterGymnase" class="btn btn-warning">
                <i class="fas fa-dumbbell"></i> Nouveau Gymnase
            </button>
            <button id="btnAjouterTerrain" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Terrain
            </button>
        </div>
    </div>

    <!-- Section des filtres -->
    <div class="filters-section">
        <div class="filter-group">
            <label for="filterCSS">CSS</label>
            <select id="filterCSS" class="form-select">
                <option value="">Tous les CSS</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterEcole">École</label>
            <select id="filterEcole" class="form-select">
                <option value="">Toutes les écoles</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterGymnase">Gymnase</label>
            <select id="filterGymnase" class="form-select">
                <option value="">Tous les gymnases</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterSport">Sport</label>
            <select id="filterSport" class="form-select">
                <option value="">Tous les sports</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="searchInfrastructure">Recherche</label>
            <input type="text" id="searchInfrastructure" class="form-control" placeholder="Rechercher...">
        </div>
    </div>

    <!-- Container du tableau -->
    <div class="table-container">
        <div id="loadingSpinner" class="loading-spinner">
            <div class="spinner"></div>
            <p>Chargement des infrastructures...</p>
        </div>
        
        <table id="infrastructureTable" class="data-table">
            <thead>
                <tr>
                    <th>CSS</th>
                    <th>École</th>
                    <th>Gymnase</th>
                    <th>Terrain</th>
                    <th>Sport</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="infrastructureTableBody">
                <!-- Les données seront chargées via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Modal CSS -->
    <div id="cssModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="cssModalTitle">Nouveau CSS</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="cssForm">
                    <input type="hidden" id="cssId">
                    
                    <div class="form-group">
                        <label for="cssNomComplet" class="required">Nom complet</label>
                        <input type="text" id="cssNomComplet" name="nom_complet" class="form-control" required placeholder="Ex: Centre de services scolaire des Découvreurs">
                    </div>

                    <div class="form-group">
                        <label for="cssInitiales" class="required">Initiales</label>
                        <input type="text" id="cssInitiales" name="initiales" class="form-control" required placeholder="Ex: CSSD" maxlength="10">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnAnnulerCSS">Annuler</button>
                <button type="submit" class="btn btn-info" form="cssForm">Sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal École -->
    <div id="ecolesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="ecolesModalTitle">Nouvelle École</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="ecolesForm">
                    <input type="hidden" id="ecoleId">
                    
                    <div class="form-group">
                        <label for="ecoleNom" class="required">Nom de l'école</label>
                        <input type="text" id="ecoleNom" name="nom" class="form-control" required placeholder="Ex: École secondaire de la Capitale">
                    </div>

                    <div class="form-group">
                        <label for="ecoleCSS" class="required">CSS</label>
                        <select id="ecoleCSS" name="id_css" class="form-select" required>
                            <option value="">Sélectionner un CSS</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnAnnulerEcole">Annuler</button>
                <button type="submit" class="btn btn-success" form="ecolesForm">Sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal Gymnase -->
    <div id="gymnasesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="gymnasesModalTitle">Nouveau Gymnase</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="gymnasesForm">
                    <input type="hidden" id="gymnaseId">
                    
                    <div class="form-group">
                        <label for="gymnaseNom" class="required">Nom du gymnase</label>
                        <input type="text" id="gymnaseNom" name="nom" class="form-control" required placeholder="Ex: Gymnase principal">
                    </div>

                    <div class="form-group">
                        <label for="gymnaseEcole" class="required">École</label>
                        <select id="gymnaseEcole" name="id_ecole" class="form-select" required>
                            <option value="">Sélectionner une école</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnAnnulerGymnase">Annuler</button>
                <button type="submit" class="btn btn-warning" form="gymnasesForm">Sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal Terrain -->
    <div id="terrainsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="terrainsModalTitle">Nouveau Terrain</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="terrainsForm">
                    <input type="hidden" id="terrainId">
                    
                    <div class="form-group">
                        <label for="terrainNom" class="required">Nom du terrain</label>
                        <input type="text" id="terrainNom" name="nom" class="form-control" required placeholder="Ex: Terrain A">
                    </div>

                    <div class="form-group">
                        <label for="terrainGymnase" class="required">Gymnase</label>
                        <select id="terrainGymnase" name="id_gymnase" class="form-select" required>
                            <option value="">Sélectionner un gymnase</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="terrainTypeSport" class="required">Type de sport</label>
                        <input type="text" id="terrainTypeSport" name="type_sport" class="form-control" required placeholder="Ex: basketball, volleyball, handball">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnAnnulerTerrain">Annuler</button>
                <button type="submit" class="btn btn-primary" form="terrainsForm">Sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="confirmModal" class="modal">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>⚠️ Suppression critique</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="warning-message">
                    <p><strong>ATTENTION :</strong> Cette action est irréversible !</p>
                    <p>La suppression de ce <span id="deleteEntityType"></span> peut entraîner la suppression de tous les éléments qui en dépendent.</p>
                </div>
                
                <div class="entity-info">
                    <p><strong><span id="deleteEntityType2"></span> à supprimer :</strong></p>
                    <p class="entity-name" id="deleteEntityName"></p>
                </div>
                
                <div class="confirmation-input">
                    <label for="confirmationText">
                        Pour confirmer la suppression, veuillez taper <strong>SUPPRIMER</strong> ci-dessous :
                    </label>
                    <input type="text" id="confirmationText" class="form-control" placeholder="Tapez SUPPRIMER ici..." autocomplete="off">
                    <div id="confirmationError" class="error-text" style="display: none;">
                        Vous devez taper exactement "SUPPRIMER" pour continuer.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnAnnulerSupp">Annuler</button>
                <button type="button" class="btn btn-danger" id="btnConfirmerSupp" disabled>Supprimer définitivement</button>
            </div>
        </div>
    </div>
</div>