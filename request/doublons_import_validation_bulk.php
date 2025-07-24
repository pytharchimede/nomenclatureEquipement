<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';

header('Content-Type: application/json');

// Log de débogage
error_log("=== DÉBUT doublons_import_validation_bulk.php ===");

try {
    // Vérifier la méthode POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Erreur: Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Méthode non autorisée');
    }

    error_log("Méthode POST confirmée");

    // Lire les données JSON
    $rawInput = file_get_contents('php://input');
    error_log("Données brutes reçues: " . $rawInput);

    $input = json_decode($rawInput, true);

    if (!$input) {
        error_log("Erreur: Données JSON invalides");
        throw new Exception('Données JSON invalides');
    }

    error_log("Données JSON décodées: " . print_r($input, true));

    $action = $input['action'] ?? '';
    $commentaire = $input['commentaire'] ?? 'Opération en lot';

    error_log("Action demandée: " . $action);

    if (!in_array($action, ['valider_tous', 'rejeter_tous'])) {
        error_log("Erreur: Action non valide: " . $action);
        throw new Exception('Action non valide');
    }
    $db = new Database();
    $conn = $db->getConnection();

    // Démarrer une transaction
    $conn->beginTransaction();

    try {
        // Déterminer le statut cible
        $nouveauStatut = ($action === 'valider_tous') ? 'valide' : 'rejete';

        // Compter les doublons en attente
        $countQuery = "SELECT COUNT(*) as total FROM nomenclatures_doublons_import WHERE statut = 'en_attente'";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute();
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($count == 0) {
            throw new Exception('Aucun doublon en attente à traiter');
        }

        // Mettre à jour tous les doublons en attente
        $updateQuery = "UPDATE nomenclatures_doublons_import 
                       SET statut = :statut,
                           date_validation = NOW(),
                           valide_par = :utilisateur,
                           commentaire_validation = :commentaire
                       WHERE statut = 'en_attente'";

        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':statut', $nouveauStatut);
        $updateStmt->bindParam(':utilisateur', $_SESSION['user_id']);
        $updateStmt->bindParam(':commentaire', $commentaire);

        $success = $updateStmt->execute();

        if (!$success) {
            throw new Exception('Erreur lors de la mise à jour des doublons');
        }

        $affectedRows = $updateStmt->rowCount();

        // Si on valide, on doit aussi traiter l'importation
        if ($action === 'valider_tous') {
            // Récupérer tous les doublons validés pour les importer
            $selectQuery = "SELECT * FROM nomenclatures_doublons_import WHERE statut = 'valide' AND date_validation >= NOW() - INTERVAL 1 MINUTE";
            $selectStmt = $conn->prepare($selectQuery);
            $selectStmt->execute();
            $doublonsValides = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

            $importSuccessCount = 0;
            $importErrorCount = 0;

            foreach ($doublonsValides as $doublon) {
                try {
                    // Insérer dans la table nomenclatures
                    $insertQuery = "INSERT INTO nomenclatures (
                        repere_equipement, code_article, designation_equipement, 
                        designation_article, fabricant, type, quantite, unite,
                        numero_poste, metier, source, date_creation
                    ) VALUES (
                        :repere_equipement, :code_article, :designation_equipement,
                        :designation_article, :fabricant, :type, :quantite, :unite,
                        :numero_poste, :metier, :source, NOW()
                    )";

                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bindParam(':repere_equipement', $doublon['repere_equipement']);
                    $insertStmt->bindParam(':code_article', $doublon['code_article']);
                    $insertStmt->bindParam(':designation_equipement', $doublon['designation_equipement']);
                    $insertStmt->bindParam(':designation_article', $doublon['designation_article']);
                    $insertStmt->bindParam(':fabricant', $doublon['fabricant']);
                    $insertStmt->bindParam(':type', $doublon['type']);
                    $insertStmt->bindParam(':quantite', $doublon['quantite']);
                    $insertStmt->bindParam(':unite', $doublon['unite']);
                    $insertStmt->bindParam(':numero_poste', $doublon['numero_poste']);
                    $insertStmt->bindParam(':metier', $doublon['metier']);
                    $insertStmt->bindParam(':source', $doublon['source']);
                    if ($insertStmt->execute()) {
                        $importSuccessCount++;

                        // Marquer comme importé
                        $markImportedQuery = "UPDATE nomenclatures_doublons_import SET statut = 'importe' WHERE id = :id";
                        $markImportedStmt = $conn->prepare($markImportedQuery);
                        $markImportedStmt->bindParam(':id', $doublon['id']);
                        $markImportedStmt->execute();
                    } else {
                        $importErrorCount++;
                    }
                } catch (Exception $e) {
                    $importErrorCount++;
                    error_log("Erreur import doublon {$doublon['id']}: " . $e->getMessage());
                }
            }
        }        // Valider la transaction
        $conn->commit();

        $message = $action === 'valider_tous'
            ? "Validation globale réussie ! $affectedRows doublons validés"
            : "Rejet global réussi ! $affectedRows doublons rejetés";

        if ($action === 'valider_tous' && isset($importSuccessCount)) {
            $message .= " ($importSuccessCount importés avec succès)";
            if ($importErrorCount > 0) {
                $message .= " ($importErrorCount erreurs d'importation)";
            }
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'count' => $affectedRows,
            'imported' => $importSuccessCount ?? 0,
            'import_errors' => $importErrorCount ?? 0
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
