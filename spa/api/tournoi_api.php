<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $db = getDatabase();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Récupérer un tournoi spécifique
                $tournoi = $db->selectOne("SELECT * FROM tournoi WHERE id = ?", [$_GET['id']]);
                if ($tournoi) {
                    echo json_encode(['success' => true, 'data' => $tournoi]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Tournoi non trouvé']);
                }
            } else {
                // Récupérer tous les tournois
                $tournois = $db->select("SELECT * FROM tournoi ORDER BY date DESC");
                echo json_encode(['success' => true, 'data' => $tournois]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validation des champs obligatoires
            if (empty($input['nom']) || empty($input['date']) || empty($input['type'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nom, date et type sont obligatoires']);
                break;
            }
            
            if (isset($input['id']) && !empty($input['id'])) {
                // Modification d'un tournoi existant
                $query = "UPDATE tournoi SET nom = ?, date = ?, description = ?, lieux = ?, statut = ?, archiver = ?, saison_actif = ?, type = ? WHERE id = ?";
                $params = [
                    $input['nom'],
                    $input['date'],
                    $input['description'] ?? null,
                    $input['lieux'] ?? null,
                    $input['statut'] ?? 'planifie',
                    $input['archiver'] ?? 0,
                    $input['saison_actif'] ?? 1,
                    $input['type'],
                    $input['id']
                ];
                
                $result = $db->execute($query, $params);
                
                if ($result > 0) {
                    echo json_encode(['success' => true, 'message' => 'Tournoi modifié avec succès']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée']);
                }
            } else {
                // Création d'un nouveau tournoi
                $query = "INSERT INTO tournoi (nom, date, description, lieux, statut, archiver, saison_actif, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $input['nom'],
                    $input['date'],
                    $input['description'] ?? null,
                    $input['lieux'] ?? null,
                    $input['statut'] ?? 'planifie',
                    $input['archiver'] ?? 0,
                    $input['saison_actif'] ?? 1,
                    $input['type']
                ];
                
                $result = $db->execute($query, $params);
                
                if ($result > 0) {
                    $newId = $db->getLastInsertId();
                    echo json_encode(['success' => true, 'message' => 'Tournoi créé avec succès', 'id' => $newId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
                }
            }
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID du tournoi requis']);
                break;
            }
            
            $result = $db->execute("DELETE FROM tournoi WHERE id = ?", [$_GET['id']]);
            
            if ($result > 0) {
                echo json_encode(['success' => true, 'message' => 'Tournoi supprimé avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tournoi non trouvé ou erreur lors de la suppression']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>