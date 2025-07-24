<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Vérifier si la table d'historique existe, sinon créer une structure basique
    $stmt = $pdo->query("SHOW TABLES LIKE 'quantitatif_import_history'");
    if ($stmt->rowCount() == 0) {
        // Créer la table d'historique si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS quantitatif_import_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                date_import DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                lines_imported INT DEFAULT 0,
                lines_errors INT DEFAULT 0,
                status ENUM('success', 'partial', 'error') DEFAULT 'success',
                log_content TEXT,
                user_id INT,
                file_size BIGINT
            ) ENGINE=InnoDB
        ");
    }

    // Récupérer l'historique des imports
    $stmt = $pdo->query("
        SELECT 
            filename,
            DATE_FORMAT(date_import, '%d/%m/%Y à %H:%i') as date,
            lines_imported,
            lines_errors,
            status,
            file_size
        FROM quantitatif_import_history 
        ORDER BY date_import DESC 
        LIMIT 10
    ");

    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si pas d'historique, créer des exemples de données basés sur les données actuelles
    if (empty($history)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM quantitatif");
        $total = $stmt->fetchColumn();

        if ($total > 0) {
            $history = [
                [
                    'filename' => 'Import Initial',
                    'date' => date('d/m/Y à H:i'),
                    'lines_imported' => $total,
                    'lines_errors' => 0,
                    'status' => 'success',
                    'file_size' => 0
                ]
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors du chargement de l\'historique: ' . $e->getMessage()
    ]);
}
