<!-- ===== FICHIER: pages/tournoi.php ===== -->
<div class="body-content">
    <div class="page-header">
        <h1>Gestion des Tournois</h1>
        <button id="btnAjouterTournoi" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau Tournoi
        </button>
    </div>

    <div class="filters-section">
        <div class="filter-group">
            <select id="filterStatut" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="planifie">Planifié</option>
                <option value="en_cours">En cours</option>
                <option value="termine">Terminé</option>
                <option value="annule">Annulé</option>
            </select>
        </div>
        <div class="filter-group">
            <select id="filterType" class="form-select">
                <option value="">Tous les types</option>
            </select>
        </div>
        <div class="filter-group">
            <input type="text" id="searchTournoi" class="form-control" placeholder="Rechercher un tournoi...">
        </div>
    </div>

    <div class="table-container">
        <div id="loadingSpinner" class="loading-spinner">
            <div class="spinner"></div>
            <p>Chargement des tournois...</p>
        </div>
        
        <table id="tournoiTable" class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Date</th>
                    <th>Lieux</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Saison</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tournoiTableBody">
                <!-- Les données seront chargées via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Modal Ajouter/Modifier Tournoi -->
    <div id="tournoiModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nouveau Tournoi</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="tournoiForm">
                    <input type="hidden" id="tournoiId">
                    
                    <div class="form-group">
                        <label for="tournoiNom">Nom du tournoi *</label>
                        <input type="text" id="tournoiNom" name="nom" class="form-control" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tournoiDate">Date *</label>
                            <input type="date" id="tournoiDate" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="tournoiLieux">Lieux</label>
                            <input type="text" id="tournoiLieux" name="lieux" class="form-control" placeholder="Ex: Centre Vidéotron, Québec">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tournoiType">Type *</label>
                            <input type="text" id="tournoiType" name="type" class="form-control" required placeholder="Ex: hockey_junior">
                        </div>
                        <div class="form-group">
                            <label for="tournoiStatut">Statut</label>
                            <select id="tournoiStatut" name="statut" class="form-select">
                                <option value="planifie">Planifié</option>
                                <option value="en_cours">En cours</option>
                                <option value="termine">Terminé</option>
                                <option value="annule">Annulé</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tournoiDescription">Description</label>
                        <textarea id="tournoiDescription" name="description" class="form-control" rows="4" placeholder="Description du tournoi..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="tournoiSaisonActif" name="saison_actif" checked>
                                Saison active
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="tournoiArchiver" name="archiver">
                                Archivé
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnAnnuler">Annuler</button>
                <button type="submit" class="btn btn-primary" id="btnSauvegarder" form="tournoiForm">Sauvegarder</button>
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
                    <p>La suppression de ce tournoi entraînera également la suppression de :</p>
                    <ul>
                        <li>Tous les matchs associés</li>
                        <li>Toutes les équipes participantes</li>
                        <li>Tous les résultats et statistiques</li>
                        <li>Toutes les données liées à ce tournoi</li>
                    </ul>
                </div>
                
                <div class="tournoi-info">
                    <p><strong>Tournoi à supprimer :</strong></p>
                    <p class="tournoi-name" id="deleteNomTournoi"></p>
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