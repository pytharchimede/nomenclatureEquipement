<?php
require_once __DIR__ . '/../model/Database.php';

class Equipement
{
    public $id, $code_equipement, $designation_equipement, $repere_equipement, $fabricant, $type_objet, $designation_type, $numero_serie_fabricant, $numero_piece_fabricant, $poste_technique, $designation_poste_technique, $poste_travail_principal, $categorie_equipement, $centre_de_couts, $date_creation;

    /**
     * Récupère tous les équipements
     * @return array
     */
    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM equipements ORDER BY repere_equipement");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les équipements avec pagination et filtres
     * @param int $page Page actuelle
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Filtres à appliquer
     * @return array ['data' => [...], 'total' => int, 'hasMore' => bool]
     */
    public static function getPaginated($page = 1, $limit = 50, $filters = [])
    {
        $pdo = Database::getConnection();
        $offset = ($page - 1) * $limit;

        // Construction de la requête avec filtres
        $where = [];
        $params = [];
        $joins = "";

        // Si on filtre par source, on doit joindre avec nomenclatures
        if (!empty($filters['source'])) {
            $joins = "INNER JOIN nomenclatures n ON equipements.code_equipement = n.code_equipement";
            $where[] = "n.source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(equipements.repere_equipement LIKE ? OR equipements.designation_equipement LIKE ? OR equipements.fabricant LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['fabricant'])) {
            $where[] = "equipements.fabricant LIKE ?";
            $params[] = '%' . $filters['fabricant'] . '%';
        }

        if (!empty($filters['type_objet'])) {
            $where[] = "equipements.type_objet LIKE ?";
            $params[] = '%' . $filters['type_objet'] . '%';
        }

        if (!empty($filters['categorie_equipement'])) {
            $where[] = "equipements.categorie_equipement = ?";
            $params[] = $filters['categorie_equipement'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Si on filtre par source, on doit utiliser DISTINCT pour éviter les doublons
        $selectFields = !empty($filters['source']) ? 'DISTINCT equipements.*' : 'equipements.*';

        // Requête de comptage total
        if (!empty($filters['source'])) {
            $countSql = "SELECT COUNT(DISTINCT equipements.id) FROM equipements $joins $whereClause";
        } else {
            $countSql = "SELECT COUNT(*) FROM equipements $whereClause";
        }
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Requête des données
        $dataSql = "SELECT $selectFields FROM equipements $joins $whereClause ORDER BY equipements.repere_equipement LIMIT $limit OFFSET $offset";

        $dataStmt = $pdo->prepare($dataSql);
        $dataStmt->execute($params);
        $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'hasMore' => ($offset + count($data)) < $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Recherche un équipement par son repère (clé primaire métier)
     * @param string $repere
     * @return array|false
     */
    public static function getByRepere($repere)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM equipements WHERE repere_equipement = ?");
        $stmt->execute([$repere]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche un équipement par son code (pour compatibilité)
     * @param string $code
     * @return array|false
     */
    public static function getByCode($code)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM equipements WHERE code_equipement = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel équipement
     * @param array $data
     * @return bool
     */
    public static function create($data)
    {
        $pdo = Database::getConnection();

        // Validation du repère obligatoire
        if (empty($data['repere_equipement'])) {
            throw new Exception("Le repère équipement est obligatoire");
        }

        // Auto-génération du code si absent
        if (empty($data['code_equipement'])) {
            $data['code_equipement'] = 'EQP-' . strtoupper(preg_replace('/\W+/', '', $data['repere_equipement']));
        }

        $sql = "INSERT INTO equipements (code_equipement, designation_equipement, repere_equipement, fabricant, type_objet, designation_type, numero_serie_fabricant, numero_piece_fabricant, poste_technique, designation_poste_technique, poste_travail_principal, categorie_equipement, centre_de_couts, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_equipement'],
            $data['designation_equipement'] ?? null,
            $data['repere_equipement'],
            $data['fabricant'] ?? null,
            $data['type_objet'] ?? null,
            $data['designation_type'] ?? null,
            $data['numero_serie_fabricant'] ?? null,
            $data['numero_piece_fabricant'] ?? null,
            $data['poste_technique'] ?? null,
            $data['designation_poste_technique'] ?? null,
            $data['poste_travail_principal'] ?? null,
            $data['categorie_equipement'] ?? null,
            $data['centre_de_couts'] ?? null,
            $data['date_creation'] ?? date('Y-m-d')
        ]);
    }

    /**
     * Met à jour un équipement par son repère
     * @param string $repere
     * @param array $data
     * @return bool
     */
    public static function update($repere, $data)
    {
        $pdo = Database::getConnection();

        // Validation des données
        if (empty($data['repere_equipement']) || empty($data['code_equipement'])) {
            throw new Exception("Le repère et le code équipement sont obligatoires");
        }

        $sql = "UPDATE equipements SET
            code_equipement = :code_equipement,
            designation_equipement = :designation_equipement,
            repere_equipement = :repere_equipement,
            fabricant = :fabricant,
            type_objet = :type_objet,
            designation_type = :designation_type,
            numero_serie_fabricant = :numero_serie_fabricant,
            numero_piece_fabricant = :numero_piece_fabricant,
            poste_technique = :poste_technique,
            designation_poste_technique = :designation_poste_technique,
            poste_travail_principal = :poste_travail_principal,
            categorie_equipement = :categorie_equipement,
            centre_de_couts = :centre_de_couts,
            date_creation = :date_creation
            WHERE repere_equipement = :original_repere";

        $params = $data;
        $params['original_repere'] = $repere;

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime un équipement par son repère
     * @param string $repere
     * @return bool
     */
    public static function delete($repere)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM equipements WHERE repere_equipement = ?");
        return $stmt->execute([$repere]);
    }

    /**
     * Supprime plusieurs équipements par leurs repères
     * @param array $reperes
     * @return bool
     */
    public static function deleteByReperes($reperes)
    {
        if (empty($reperes) || !is_array($reperes)) return false;
        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($reperes), '?'));
        $stmt = $pdo->prepare("DELETE FROM equipements WHERE repere_equipement IN ($placeholders)");
        return $stmt->execute($reperes);
    }

    /**
     * Compte les équipements ajoutés dans les 30 derniers jours
     * @return int
     */
    public static function countAddedLast30Days()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM equipements WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Vérifie si un équipement existe par son repère
     * @param string $repere
     * @return bool
     */
    public static function exists($repere)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipements WHERE repere_equipement = ?");
        $stmt->execute([$repere]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Ajoute un équipement avec données minimales (repère obligatoire)
     * @param array $data
     * @return bool
     */
    public static function add($data)
    {
        $pdo = Database::getConnection();

        // Validation du repère obligatoire
        if (empty($data['repere_equipement'])) {
            throw new Exception("Le repère équipement est obligatoire");
        }

        // Auto-génération du code si absent
        if (empty($data['code_equipement'])) {
            $data['code_equipement'] = 'EQP-' . strtoupper(preg_replace('/\W+/', '', $data['repere_equipement']));
        }

        $sql = "INSERT INTO equipements (code_equipement, repere_equipement, date_creation) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_equipement'],
            $data['repere_equipement']
        ]);
    }

    /**
     * Récupère les valeurs distinctes pour les filtres
     * @param string $column
     * @return array
     */
    public static function getDistinctValues($column)
    {
        $allowedColumns = ['fabricant', 'type_objet', 'categorie_equipement', 'poste_technique'];
        if (!in_array($column, $allowedColumns)) {
            return [];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT DISTINCT $column FROM equipements WHERE $column IS NOT NULL AND $column != '' ORDER BY $column");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère les sources distinctes depuis les nomenclatures
     * @return array
     */
    public static function getDistinctSources()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT DISTINCT source FROM nomenclatures WHERE source IS NOT NULL ORDER BY source");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
