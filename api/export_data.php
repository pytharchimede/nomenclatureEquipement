<?php
require_once '../includes/auth.php';
require_once '../model/Database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

try {
    $db = Database::getConnection();

    $type = $_GET['type'] ?? '';
    $subtype = $_GET['subtype'] ?? '';

    // Construction de la requête selon le type et sous-type
    $query = buildQuery($type, $subtype, $_GET);
    $data = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // Création du fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configuration du fichier selon le type
    configureSheet($sheet, $type, $subtype, $data);

    // Nom du fichier
    $filename = generateFilename($type, $subtype);
    header("Content-Disposition: attachment; filename=\"{$filename}\"");

    // Écriture du fichier
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    error_log("Erreur dans export_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'export: ' . $e->getMessage()]);
}

function buildQuery($type, $subtype, $filters)
{
    $whereConditions = [];

    // Filtres de dates
    if (!empty($filters['dateDebut'])) {
        $whereConditions[] = "date_creation >= '" . $filters['dateDebut'] . "'";
    }
    if (!empty($filters['dateFin'])) {
        $whereConditions[] = "date_creation <= '" . $filters['dateFin'] . "'";
    }

    // Filtres spécifiques
    if (!empty($filters['source'])) {
        $whereConditions[] = "source = '" . $filters['source'] . "'";
    }
    if (!empty($filters['fabricant'])) {
        $whereConditions[] = "fabricant = '" . $filters['fabricant'] . "'";
    }

    $whereClause = empty($whereConditions) ? '' : ' WHERE ' . implode(' AND ', $whereConditions);

    switch ($type) {
        case 'equipements':
            return buildEquipementsQuery($subtype, $whereClause);
        case 'articles':
            return buildArticlesQuery($subtype, $whereClause);
        case 'nomenclatures':
            return buildNomenclaturesQuery($subtype, $whereClause);
        case 'doublons':
            return buildDoublonsQuery($subtype, $whereClause);
        case 'familles':
            return buildFamillesQuery($subtype, $whereClause);
        case 'quantitatif':
            return buildQuantitatifQuery($subtype, $whereClause);
        default:
            throw new Exception("Type d'export non supporté: " . $type);
    }
}

function buildEquipementsQuery($subtype, $whereClause)
{
    switch ($subtype) {
        case 'complet':
            return "SELECT * FROM equipements" . $whereClause . " ORDER BY repere_equipement";
        case 'sans_piece':
            return "SELECT e.* FROM equipements e LEFT JOIN nomenclatures n ON e.repere_equipement = n.repere_equipement WHERE n.id IS NULL" . ($whereClause ? " AND " . substr($whereClause, 7) : "") . " ORDER BY e.repere_equipement";
        case 'par_famille':
            return "SELECT famille, COUNT(*) as nombre, GROUP_CONCAT(repere_equipement) as equipements FROM equipements" . $whereClause . " GROUP BY famille ORDER BY famille";
        default:
            return "SELECT * FROM equipements" . $whereClause . " ORDER BY repere_equipement";
    }
}

function buildArticlesQuery($subtype, $whereClause)
{
    switch ($subtype) {
        case 'complet':
            return "SELECT * FROM articles" . $whereClause . " ORDER BY code_article";
        case 'non_lies':
            return "SELECT a.* FROM articles a LEFT JOIN nomenclatures n ON a.code_article = n.code_article WHERE n.id IS NULL" . ($whereClause ? " AND " . substr($whereClause, 7) : "") . " ORDER BY a.code_article";
        case 'par_fabricant':
            return "SELECT fabricant, COUNT(*) as nombre, GROUP_CONCAT(code_article) as articles FROM articles" . $whereClause . " GROUP BY fabricant ORDER BY fabricant";
        default:
            return "SELECT * FROM articles" . $whereClause . " ORDER BY code_article";
    }
}

function buildNomenclaturesQuery($subtype, $whereClause)
{
    switch ($subtype) {
        case 'complet':
            return "SELECT * FROM nomenclatures" . $whereClause . " ORDER BY repere_equipement, code_article";
        case 'par_equipement':
            return "SELECT repere_equipement, COUNT(*) as nb_articles, GROUP_CONCAT(code_article) as articles FROM nomenclatures" . $whereClause . " GROUP BY repere_equipement ORDER BY repere_equipement";
        case 'hierarchique':
            return "SELECT repere_equipement, designation_equipement, code_article, designation_article, quantite, unite FROM nomenclatures" . $whereClause . " ORDER BY repere_equipement, code_article";
        default:
            return "SELECT * FROM nomenclatures" . $whereClause . " ORDER BY repere_equipement, code_article";
    }
}

function buildDoublonsQuery($subtype, $whereClause)
{
    $baseWhere = "FROM nomenclatures_doublons_import";

    switch ($subtype) {
        case 'tous':
            return "SELECT * " . $baseWhere . $whereClause . " ORDER BY date_import DESC";
        case 'en_attente':
            $statusFilter = "statut = 'en_attente'";
            $newWhere = empty($whereClause) ? " WHERE " . $statusFilter : $whereClause . " AND " . $statusFilter;
            return "SELECT * " . $baseWhere . $newWhere . " ORDER BY date_import DESC";
        case 'resolus':
            $statusFilter = "statut IN ('valide', 'rejete')";
            $newWhere = empty($whereClause) ? " WHERE " . $statusFilter : $whereClause . " AND " . $statusFilter;
            return "SELECT * " . $baseWhere . $newWhere . " ORDER BY date_validation DESC";
        default:
            return "SELECT * " . $baseWhere . $whereClause . " ORDER BY date_import DESC";
    }
}

function buildFamillesQuery($subtype, $whereClause)
{
    switch ($subtype) {
        case 'complet':
            return "SELECT DISTINCT famille FROM equipements WHERE famille IS NOT NULL" . ($whereClause ? " AND " . substr($whereClause, 7) : "") . " ORDER BY famille";
        case 'avec_stats':
            return "SELECT famille, COUNT(*) as nb_equipements FROM equipements WHERE famille IS NOT NULL" . ($whereClause ? " AND " . substr($whereClause, 7) : "") . " GROUP BY famille ORDER BY famille";
        default:
            return "SELECT DISTINCT famille FROM equipements WHERE famille IS NOT NULL" . ($whereClause ? " AND " . substr($whereClause, 7) : "") . " ORDER BY famille";
    }
}

function buildQuantitatifQuery($subtype, $whereClause)
{
    switch ($subtype) {
        case 'global':
            return "SELECT 'Équipements' as type, COUNT(*) as total FROM equipements UNION SELECT 'Articles' as type, COUNT(*) as total FROM articles UNION SELECT 'Nomenclatures' as type, COUNT(*) as total FROM nomenclatures";
        case 'par_periode':
            return "SELECT DATE(date_creation) as date, COUNT(*) as nb_creations FROM nomenclatures" . $whereClause . " GROUP BY DATE(date_creation) ORDER BY date";
        default:
            return "SELECT 'Équipements' as type, COUNT(*) as total FROM equipements UNION SELECT 'Articles' as type, COUNT(*) as total FROM articles";
    }
}

function configureSheet($sheet, $type, $subtype, $data)
{
    if (empty($data)) {
        $sheet->setCellValue('A1', 'Aucune donnée trouvée');
        return;
    }

    // En-têtes
    $headers = array_keys($data[0]);
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', ucfirst(str_replace('_', ' ', $header)));
        $sheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4CAF50');
        $sheet->getStyle($col . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $col++;
    }

    // Données
    $row = 2;
    foreach ($data as $item) {
        $col = 'A';
        foreach ($item as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }

    // Auto-size des colonnes
    foreach (range('A', $col) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Titre du document
    $title = ucfirst($type) . ' - ' . ucfirst($subtype);
    $sheet->setTitle(substr($title, 0, 31)); // Limite Excel
}

function generateFilename($type, $subtype)
{
    $date = date('Y-m-d_H-i-s');
    return "{$type}_{$subtype}_{$date}.xlsx";
}
