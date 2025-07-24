<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

$columns = [];

switch ($type) {
    case 'equipements':
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'default' => false],
            ['key' => 'code_equipement', 'label' => 'Code Équipement', 'default' => true],
            ['key' => 'designation_equipement', 'label' => 'Désignation', 'default' => true],
            ['key' => 'repere_equipement', 'label' => 'Repère', 'default' => true],
            ['key' => 'fabricant', 'label' => 'Fabricant', 'default' => true],
            ['key' => 'type_objet', 'label' => 'Type d\'Objet', 'default' => false],
            ['key' => 'designation_type', 'label' => 'Désignation Type', 'default' => false],
            ['key' => 'numero_serie_fabricant', 'label' => 'N° Série Fabricant', 'default' => false],
            ['key' => 'famille', 'label' => 'Famille', 'default' => true],
            ['key' => 'numero_piece_fabricant', 'label' => 'N° Pièce Fabricant', 'default' => false],
            ['key' => 'poste_technique', 'label' => 'Poste Technique', 'default' => false],
            ['key' => 'designation_poste_technique', 'label' => 'Désignation Poste', 'default' => false],
            ['key' => 'poste_travail_principal', 'label' => 'Poste Travail Principal', 'default' => false],
            ['key' => 'categorie_equipement', 'label' => 'Catégorie', 'default' => true],
            ['key' => 'centre_de_couts', 'label' => 'Centre de Coûts', 'default' => false],
            ['key' => 'date_creation', 'label' => 'Date Création', 'default' => false]
        ];
        break;

    case 'articles':
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'default' => false],
            ['key' => 'code_article', 'label' => 'Code Article', 'default' => true],
            ['key' => 'designation_article', 'label' => 'Désignation', 'default' => true],
            ['key' => 'type_article', 'label' => 'Type Article', 'default' => true],
            ['key' => 'temsup_niv_mdt', 'label' => 'Temps Sup Niveau MDT', 'default' => false],
            ['key' => 'ancien_num_article', 'label' => 'Ancien N° Article', 'default' => false],
            ['key' => 'uq_base', 'label' => 'UQ Base', 'default' => false],
            ['key' => 'fabricant', 'label' => 'Fabricant', 'default' => true],
            ['key' => 'numero_piece_fabricant', 'label' => 'N° Pièce Fabricant', 'default' => true],
            ['key' => 'groupe_articles', 'label' => 'Groupe Articles', 'default' => false],
            ['key' => 'groupe_marche_externe', 'label' => 'Groupe Marché Externe', 'default' => false],
            ['key' => 'document', 'label' => 'Document', 'default' => false],
            ['key' => 'description', 'label' => 'Description', 'default' => false],
            ['key' => 'date_creation', 'label' => 'Date Création', 'default' => false],
            ['key' => 'cree_par', 'label' => 'Créé Par', 'default' => false]
        ];
        break;

    case 'nomenclatures':
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'default' => false],
            ['key' => 'code_equipement', 'label' => 'Code Équipement', 'default' => true],
            ['key' => 'code_article', 'label' => 'Code Article', 'default' => true],
            ['key' => 'repere_equipement', 'label' => 'Repère Équipement', 'default' => true],
            ['key' => 'designation_equipement', 'label' => 'Désignation Équipement', 'default' => true],
            ['key' => 'fabricant', 'label' => 'Fabricant', 'default' => false],
            ['key' => 'type', 'label' => 'Type', 'default' => false],
            ['key' => 'numero_serie_fabricant', 'label' => 'N° Série Fabricant', 'default' => false],
            ['key' => 'designation_article', 'label' => 'Désignation Article', 'default' => true],
            ['key' => 'numero_poste', 'label' => 'N° Poste', 'default' => false],
            ['key' => 'quantite', 'label' => 'Quantité', 'default' => true],
            ['key' => 'unite', 'label' => 'Unité', 'default' => true],
            ['key' => 'poste_technique', 'label' => 'Poste Technique', 'default' => false],
            ['key' => 'metier', 'label' => 'Métier', 'default' => false],
            ['key' => 'date_creation', 'label' => 'Date Création', 'default' => false],
            ['key' => 'source', 'label' => 'Source', 'default' => true]
        ];
        break;

    case 'doublons':
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'default' => false],
            ['key' => 'repere_equipement', 'label' => 'Repère Équipement', 'default' => true],
            ['key' => 'code_article', 'label' => 'Code Article', 'default' => true],
            ['key' => 'designation_equipement', 'label' => 'Désignation Équipement', 'default' => true],
            ['key' => 'designation_article', 'label' => 'Désignation Article', 'default' => true],
            ['key' => 'fabricant', 'label' => 'Fabricant', 'default' => false],
            ['key' => 'type', 'label' => 'Type', 'default' => false],
            ['key' => 'quantite', 'label' => 'Quantité', 'default' => true],
            ['key' => 'unite', 'label' => 'Unité', 'default' => true],
            ['key' => 'fichier_import', 'label' => 'Fichier Import', 'default' => true],
            ['key' => 'ligne_import', 'label' => 'Ligne Import', 'default' => true],
            ['key' => 'raison_rejet', 'label' => 'Raison Rejet', 'default' => true],
            ['key' => 'statut', 'label' => 'Statut', 'default' => true],
            ['key' => 'date_import', 'label' => 'Date Import', 'default' => true],
            ['key' => 'date_validation', 'label' => 'Date Validation', 'default' => false],
            ['key' => 'valide_par', 'label' => 'Validé Par', 'default' => false],
            ['key' => 'commentaire_validation', 'label' => 'Commentaire', 'default' => false]
        ];
        break;

    case 'familles':
        $columns = [
            ['key' => 'famille', 'label' => 'Nom Famille', 'default' => true],
            ['key' => 'nb_equipements', 'label' => 'Nombre d\'Équipements', 'default' => true],
            ['key' => 'description', 'label' => 'Description', 'default' => false]
        ];
        break;

    case 'quantitatif':
        $columns = [
            ['key' => 'type', 'label' => 'Type de Données', 'default' => true],
            ['key' => 'total', 'label' => 'Total', 'default' => true],
            ['key' => 'date', 'label' => 'Date', 'default' => true],
            ['key' => 'pourcentage', 'label' => 'Pourcentage', 'default' => false]
        ];
        break;

    default:
        $columns = [
            ['key' => 'id', 'label' => 'ID', 'default' => true],
            ['key' => 'nom', 'label' => 'Nom', 'default' => true],
            ['key' => 'description', 'label' => 'Description', 'default' => false]
        ];
}

echo json_encode([
    'success' => true,
    'columns' => $columns
]);
