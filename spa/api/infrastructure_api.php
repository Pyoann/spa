<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

try {
    $db = getDatabase();
    $method = $_SERVER['REQUEST_METHOD'];
    $entity = $_GET['entity'] ?? 'all';
    
    switch($method) {
        case 'GET':
            if ($entity === 'all') {
                // Récupérer toute la hiérarchie
                $data = getAllInfrastructuresHierarchy($db);
                echo json_encode(['success' => true, 'data' => $data]);
            } elseif ($entity === 'options') {
                // Récupérer les options pour les sélecteurs
                $options = getOptionsForSelectors($db);
                echo json_encode(['success' => true, 'data' => $options]);
            } elseif (isset($_GET['id'])) {
                // Récupérer une entité spécifique
                $result = getEntityById($db, $entity, $_GET['id']);
                if ($result) {
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => ucfirst($entity) . ' non trouvé']);
                }
            } elseif (isset($_GET['parent_id']) && isset($_GET['parent_entity'])) {
                // Récupérer les enfants d'un parent
                $children = getChildrenEntities($db, $_GET['parent_entity'], $_GET['parent_id'], $entity);
                echo json_encode(['success' => true, 'data' => $children]);
            } else {
                // Récupérer toutes les entités d'un type
                $entities = getAllEntities($db, $entity);
                echo json_encode(['success' => true, 'data' => $entities]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            $validationResult = validateEntityInput($entity, $input);
            if (!$validationResult['valid']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $validationResult['message']]);
                break;
            }
            
            if (isset($input['id']) && !empty($input['id'])) {
                // Modification
                $result = updateEntity($db, $entity, $input);
                if ($result > 0) {
                    echo json_encode(['success' => true, 'message' => ucfirst($entity) . ' modifié avec succès']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée']);
                }
            } else {
                // Création
                $result = createEntity($db, $entity, $input);
                if ($result > 0) {
                    $newId = $db->getLastInsertId();
                    echo json_encode(['success' => true, 'message' => ucfirst($entity) . ' créé avec succès', 'id' => $newId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
                }
            }
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                break;
            }
            
            // Vérifier les dépendances avant suppression
            $dependencies = checkDependencies($db, $entity, $_GET['id']);
            if (!empty($dependencies)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Impossible de supprimer: des éléments dépendants existent',
                    'dependencies' => $dependencies
                ]);
                break;
            }
            
            $result = deleteEntity($db, $entity, $_GET['id']);
            
            if ($result > 0) {
                echo json_encode(['success' => true, 'message' => ucfirst($entity) . ' supprimé avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Élément non trouvé ou erreur lors de la suppression']);
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

// Fonctions utilitaires

function getAllInfrastructuresHierarchy($db) {
    // Requête pour récupérer tous les CSS avec leurs données complètes
    $query = "
        SELECT DISTINCT
            c.id as css_id,
            c.nom_complet as css_nom,
            c.initiales as css_initiales,
            e.id as ecole_id,
            e.nom as ecole_nom,
            g.id as gymnase_id,
            g.nom as gymnase_nom,
            t.id as terrain_id,
            t.nom as terrain_nom,
            t.type_sport
        FROM css c
        LEFT JOIN ecoles e ON c.id = e.id_css
        LEFT JOIN gymnases g ON e.id = g.id_ecole
        LEFT JOIN terrains t ON g.id = t.id_gymnase
        ORDER BY c.nom_complet, e.nom, g.nom, t.nom
    ";
    
    return $db->select($query);
}

function getOptionsForSelectors($db) {
    return [
        'css' => $db->select("SELECT id, nom_complet, initiales FROM css ORDER BY nom_complet"),
        'ecoles' => $db->select("SELECT e.id, e.nom, e.id_css, c.nom_complet as css_nom FROM ecoles e JOIN css c ON e.id_css = c.id ORDER BY c.nom_complet, e.nom"),
        'gymnases' => $db->select("SELECT g.id, g.nom, g.id_ecole, e.nom as ecole_nom FROM gymnases g JOIN ecoles e ON g.id_ecole = e.id ORDER BY e.nom, g.nom"),
        'sports' => $db->select("SELECT DISTINCT type_sport FROM terrains WHERE type_sport IS NOT NULL ORDER BY type_sport")
    ];
}

function getEntityById($db, $entity, $id) {
    switch($entity) {
        case 'css':
            return $db->selectOne("SELECT * FROM css WHERE id = ?", [$id]);
        case 'ecoles':
            return $db->selectOne("SELECT e.*, c.nom_complet as css_nom FROM ecoles e JOIN css c ON e.id_css = c.id WHERE e.id = ?", [$id]);
        case 'gymnases':
            return $db->selectOne("SELECT g.*, e.nom as ecole_nom FROM gymnases g JOIN ecoles e ON g.id_ecole = e.id WHERE g.id = ?", [$id]);
        case 'terrains':
            return $db->selectOne("SELECT t.*, g.nom as gymnase_nom FROM terrains t JOIN gymnases g ON t.id_gymnase = g.id WHERE t.id = ?", [$id]);
        default:
            return null;
    }
}

function getChildrenEntities($db, $parentEntity, $parentId, $childEntity) {
    $queries = [
        'css_ecoles' => "SELECT * FROM ecoles WHERE id_css = ? ORDER BY nom",
        'ecoles_gymnases' => "SELECT * FROM gymnases WHERE id_ecole = ? ORDER BY nom",
        'gymnases_terrains' => "SELECT * FROM terrains WHERE id_gymnase = ? ORDER BY nom"
    ];
    
    $key = $parentEntity . '_' . $childEntity;
    if (isset($queries[$key])) {
        return $db->select($queries[$key], [$parentId]);
    }
    
    return [];
}

function getAllEntities($db, $entity) {
    switch($entity) {
        case 'css':
            return $db->select("SELECT * FROM css ORDER BY nom_complet");
        case 'ecoles':
            return $db->select("SELECT e.*, c.nom_complet as css_nom FROM ecoles e JOIN css c ON e.id_css = c.id ORDER BY c.nom_complet, e.nom");
        case 'gymnases':
            return $db->select("SELECT g.*, e.nom as ecole_nom FROM gymnases g JOIN ecoles e ON g.id_ecole = e.id ORDER BY e.nom, g.nom");
        case 'terrains':
            return $db->select("SELECT t.*, g.nom as gymnase_nom FROM terrains t JOIN gymnases g ON t.id_gymnase = g.id ORDER BY g.nom, t.nom");
        default:
            return [];
    }
}

function validateEntityInput($entity, $input) {
    switch($entity) {
        case 'css':
            if (empty($input['nom_complet']) || empty($input['initiales'])) {
                return ['valid' => false, 'message' => 'Nom complet et initiales sont requis'];
            }
            break;
        case 'ecoles':
            if (empty($input['nom']) || empty($input['id_css'])) {
                return ['valid' => false, 'message' => 'Nom et CSS sont requis'];
            }
            break;
        case 'gymnases':
            if (empty($input['nom']) || empty($input['id_ecole'])) {
                return ['valid' => false, 'message' => 'Nom et école sont requis'];
            }
            break;
        case 'terrains':
            if (empty($input['nom']) || empty($input['id_gymnase']) || empty($input['type_sport'])) {
                return ['valid' => false, 'message' => 'Nom, gymnase et type de sport sont requis'];
            }
            break;
    }
    return ['valid' => true];
}

function createEntity($db, $entity, $input) {
    switch($entity) {
        case 'css':
            return $db->execute("INSERT INTO css (nom_complet, initiales) VALUES (?, ?)", 
                [$input['nom_complet'], $input['initiales']]);
        case 'ecoles':
            return $db->execute("INSERT INTO ecoles (nom, id_css) VALUES (?, ?)", 
                [$input['nom'], $input['id_css']]);
        case 'gymnases':
            return $db->execute("INSERT INTO gymnases (nom, id_ecole) VALUES (?, ?)", 
                [$input['nom'], $input['id_ecole']]);
        case 'terrains':
            return $db->execute("INSERT INTO terrains (nom, id_gymnase, type_sport) VALUES (?, ?, ?)", 
                [$input['nom'], $input['id_gymnase'], $input['type_sport']]);
    }
    return 0;
}

function updateEntity($db, $entity, $input) {
    switch($entity) {
        case 'css':
            return $db->execute("UPDATE css SET nom_complet = ?, initiales = ? WHERE id = ?", 
                [$input['nom_complet'], $input['initiales'], $input['id']]);
        case 'ecoles':
            return $db->execute("UPDATE ecoles SET nom = ?, id_css = ? WHERE id = ?", 
                [$input['nom'], $input['id_css'], $input['id']]);
        case 'gymnases':
            return $db->execute("UPDATE gymnases SET nom = ?, id_ecole = ? WHERE id = ?", 
                [$input['nom'], $input['id_ecole'], $input['id']]);
        case 'terrains':
            return $db->execute("UPDATE terrains SET nom = ?, id_gymnase = ?, type_sport = ? WHERE id = ?", 
                [$input['nom'], $input['id_gymnase'], $input['type_sport'], $input['id']]);
    }
    return 0;
}

function checkDependencies($db, $entity, $id) {
    $dependencies = [];
    
    switch($entity) {
        case 'css':
            $count = $db->selectOne("SELECT COUNT(*) as count FROM ecoles WHERE id_css = ?", [$id]);
            if ($count['count'] > 0) {
                $dependencies[] = $count['count'] . ' école(s)';
            }
            break;
        case 'ecoles':
            $count = $db->selectOne("SELECT COUNT(*) as count FROM gymnases WHERE id_ecole = ?", [$id]);
            if ($count['count'] > 0) {
                $dependencies[] = $count['count'] . ' gymnase(s)';
            }
            break;
        case 'gymnases':
            $count = $db->selectOne("SELECT COUNT(*) as count FROM terrains WHERE id_gymnase = ?", [$id]);
            if ($count['count'] > 0) {
                $dependencies[] = $count['count'] . ' terrain(s)';
            }
            break;
    }
    
    return $dependencies;
}

function deleteEntity($db, $entity, $id) {
    switch($entity) {
        case 'css':
            return $db->execute("DELETE FROM css WHERE id = ?", [$id]);
        case 'ecoles':
            return $db->execute("DELETE FROM ecoles WHERE id = ?", [$id]);
        case 'gymnases':
            return $db->execute("DELETE FROM gymnases WHERE id = ?", [$id]);
        case 'terrains':
            return $db->execute("DELETE FROM terrains WHERE id = ?", [$id]);
    }
    return 0;
}
?>