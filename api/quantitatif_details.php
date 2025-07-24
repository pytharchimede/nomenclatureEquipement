<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        throw new Exception('ID manquant ou invalide');
    }

    $pdo = Database::getConnection();

    // Vérifier d'abord si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'quantitatif'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Table quantitatif non trouvée');
    }

    $stmt = $pdo->prepare("SELECT * FROM quantitatif WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception('Élément non trouvé avec l\'ID: ' . $id);
    }

    // Décodage des colonnes additionnelles avec gestion d'erreurs robuste
    if ($item['autres_colonnes']) {
        if (is_string($item['autres_colonnes'])) {
            $decoded = json_decode($item['autres_colonnes'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $item['autres_colonnes'] = $decoded;
            } else {
                // Conserver la chaîne originale et ajouter l'erreur
                $item['autres_colonnes_raw'] = $item['autres_colonnes'];
                $item['autres_colonnes_error'] = 'Erreur de décodage JSON: ' . json_last_error_msg();
                $item['autres_colonnes'] = null;
            }
        }
        // Si c'est déjà un array/object, on le laisse tel quel
    }

    echo json_encode([
        'success' => true,
        'item' => $item,
        'debug' => [
            'id' => $id,
            'autres_colonnes_type' => gettype($item['autres_colonnes']),
            'has_error' => isset($item['autres_colonnes_error'])
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'id_received' => $_GET['id'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null
        ]
    ]);
}
