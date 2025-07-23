<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Nomenclature.php';

header('Content-Type: application/json');

try {
    // Récupération des données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Données de validation manquantes');
    }

    $doublonId = (int)$input['id'];
    $action = $input['action']; // 'valide' ou 'rejete'
    $commentaire = trim($input['commentaire'] ?? '');

    if (!in_array($action, ['valide', 'rejete'])) {
        throw new Exception('Action non valide');
    }

    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // Récupération du doublon
    $stmt = $pdo->prepare("SELECT * FROM nomenclatures_doublons_import WHERE id = ? AND statut = 'en_attente'");
    $stmt->execute([$doublonId]);
    $doublon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doublon) {
        throw new Exception('Doublon introuvable ou déjà traité');
    }

    // Mise à jour du statut
    $updateQuery = "
        UPDATE nomenclatures_doublons_import 
        SET statut = ?, 
            valide_par = ?, 
            date_validation = NOW(), 
            commentaire_validation = ?
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([$action, $_SESSION['user_id'] ?? 'system', $commentaire, $doublonId]);

    $message = '';

    if ($action === 'valide') {
        // Importation de la nomenclature dans la table principale
        $nomenclatureData = [
            'code_equipement' => $doublon['code_equipement'],
            'code_article' => $doublon['code_article'],
            'repere_equipement' => $doublon['repere_equipement'],
            'designation_equipement' => $doublon['designation_equipement'],
            'fabricant' => $doublon['fabricant'],
            'type' => $doublon['type'],
            'numero_serie_fabricant' => $doublon['numero_serie_fabricant'],
            'designation_article' => $doublon['designation_article'],
            'numero_poste' => $doublon['numero_poste'],
            'quantite' => $doublon['quantite'],
            'unite' => $doublon['unite'],
            'poste_technique' => $doublon['poste_technique'],
            'metier' => $doublon['metier'],
            'source' => $doublon['source'] . '_VALIDE',
            'date_creation' => date('Y-m-d')
        ];

        // Insertion dans la table nomenclatures
        $insertQuery = "
            INSERT INTO nomenclatures (
                code_equipement, code_article, repere_equipement, designation_equipement,
                fabricant, type, numero_serie_fabricant, designation_article, numero_poste,
                quantite, unite, poste_technique, metier, source, date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            $nomenclatureData['code_equipement'],
            $nomenclatureData['code_article'],
            $nomenclatureData['repere_equipement'],
            $nomenclatureData['designation_equipement'],
            $nomenclatureData['fabricant'],
            $nomenclatureData['type'],
            $nomenclatureData['numero_serie_fabricant'],
            $nomenclatureData['designation_article'],
            $nomenclatureData['numero_poste'],
            $nomenclatureData['quantite'],
            $nomenclatureData['unite'],
            $nomenclatureData['poste_technique'],
            $nomenclatureData['metier'],
            $nomenclatureData['source'],
            $nomenclatureData['date_creation']
        ]);

        $nouveauId = $pdo->lastInsertId();

        // Mise à jour du statut vers 'importe'
        $pdo->prepare("UPDATE nomenclatures_doublons_import SET statut = 'importe' WHERE id = ?")
            ->execute([$doublonId]);

        $message = "Doublon validé et importé avec succès (ID: $nouveauId)";
    } else {
        $message = "Doublon rejeté avec succès";
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la validation: ' . $e->getMessage()
    ]);
}
