<?php
require_once 'db_connect.php';

class DatabaseExtractor {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    public function extractFullStructure() {
        echo "<h1>Structure complète de la base de données SPA</h1>\n";
        
        $this->showTables();
        $this->showTableStructures();
        $this->showForeignKeys();
        $this->showIndexes();
        $this->showSampleData();
    }
    
    private function showTables() {
        echo "<h2>1. Liste des tables</h2>\n";
        $tables = $this->db->select("SHOW TABLES");
        echo "<ul>\n";
        foreach($tables as $table) {
            echo "<li>" . array_values($table)[0] . "</li>\n";
        }
        echo "</ul>\n";
    }
    
    private function showTableStructures() {
        echo "<h2>2. Structure détaillée des tables</h2>\n";
        $tables = ['css', 'ecoles', 'gymnases', 'terrains'];
        
        foreach($tables as $tableName) {
            echo "<h3>Table: $tableName</h3>\n";
            
            // Structure de la table
            $structure = $this->db->select("DESCRIBE $tableName");
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>\n";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>\n";
            
            foreach($structure as $column) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "<td>{$column['Default']}</td>";
                echo "<td>{$column['Extra']}</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
            
            // Script CREATE TABLE
            $createTable = $this->db->selectOne("SHOW CREATE TABLE $tableName");
            echo "<h4>Script de création:</h4>\n";
            echo "<pre>" . htmlspecialchars($createTable['Create Table']) . "</pre>\n";
        }
    }
    
    private function showForeignKeys() {
        echo "<h2>3. Relations (Clés étrangères)</h2>\n";
        
        $foreignKeys = $this->db->select("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = 'spa' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY TABLE_NAME, COLUMN_NAME
        ");
        
        if (empty($foreignKeys)) {
            echo "<p>Aucune clé étrangère trouvée.</p>\n";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>\n";
            echo "<tr><th>Table</th><th>Colonne</th><th>Référence Table</th><th>Référence Colonne</th><th>Contrainte</th></tr>\n";
            
            foreach($foreignKeys as $fk) {
                echo "<tr>";
                echo "<td>{$fk['TABLE_NAME']}</td>";
                echo "<td>{$fk['COLUMN_NAME']}</td>";
                echo "<td>{$fk['REFERENCED_TABLE_NAME']}</td>";
                echo "<td>{$fk['REFERENCED_COLUMN_NAME']}</td>";
                echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
    }
    
    private function showIndexes() {
        echo "<h2>4. Index et clés</h2>\n";
        
        $tables = ['css', 'ecoles', 'gymnases', 'terrains'];
        
        foreach($tables as $tableName) {
            echo "<h3>Index pour: $tableName</h3>\n";
            
            $indexes = $this->db->select("SHOW INDEX FROM $tableName");
            
            if (empty($indexes)) {
                echo "<p>Aucun index trouvé.</p>\n";
            } else {
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>\n";
                echo "<tr><th>Nom Index</th><th>Colonne</th><th>Unique</th><th>Type</th></tr>\n";
                
                foreach($indexes as $index) {
                    echo "<tr>";
                    echo "<td>{$index['Key_name']}</td>";
                    echo "<td>{$index['Column_name']}</td>";
                    echo "<td>" . ($index['Non_unique'] == 0 ? 'Oui' : 'Non') . "</td>";
                    echo "<td>{$index['Index_type']}</td>";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            }
        }
    }
    
    private function showSampleData() {
        echo "<h2>5. Données d'exemple (5 premiers enregistrements)</h2>\n";
        
        $tables = ['css', 'ecoles', 'gymnases', 'terrains'];
        
        foreach($tables as $tableName) {
            echo "<h3>Données de: $tableName</h3>\n";
            
            try {
                $sampleData = $this->db->select("SELECT * FROM $tableName LIMIT 5");
                
                if (empty($sampleData)) {
                    echo "<p>Aucune donnée trouvée.</p>\n";
                } else {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>\n";
                    
                    // En-têtes
                    echo "<tr>";
                    foreach(array_keys($sampleData[0]) as $column) {
                        echo "<th>$column</th>";
                    }
                    echo "</tr>\n";
                    
                    // Données
                    foreach($sampleData as $row) {
                        echo "<tr>";
                        foreach($row as $value) {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>\n";
                    }
                    echo "</table>\n";
                }
            } catch (Exception $e) {
                echo "<p>Erreur lors de la récupération des données: " . $e->getMessage() . "</p>\n";
            }
        }
    }
    
    public function exportToJSON() {
        $export = [
            'database' => 'spa',
            'tables' => []
        ];
        
        $tables = ['css', 'ecoles', 'gymnases', 'terrains'];
        
        foreach($tables as $tableName) {
            $structure = $this->db->select("DESCRIBE $tableName");
            $createTable = $this->db->selectOne("SHOW CREATE TABLE $tableName");
            
            $export['tables'][$tableName] = [
                'structure' => $structure,
                'create_sql' => $createTable['Create Table']
            ];
        }
        
        // Relations
        $foreignKeys = $this->db->select("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = 'spa' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $export['foreign_keys'] = $foreignKeys;
        
        return json_encode($export, JSON_PRETTY_PRINT);
    }
}

// Utilisation
try {
    $extractor = new DatabaseExtractor();
    
    // Pour affichage HTML
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        header('Content-Type: application/json');
        echo $extractor->exportToJSON();
    } else {
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Structure DB SPA</title></head><body>";
        $extractor->extractFullStructure();
        echo "<hr><p><a href='?format=json'>Voir en format JSON</a></p>";
        echo "</body></html>";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>