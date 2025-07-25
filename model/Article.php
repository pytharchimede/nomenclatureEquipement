<?php
require_once __DIR__ . '/../model/Database.php';

class Article
{
    public $id, $code_article, $designation_article, $type_article, $temsup_niv_mdt, $ancien_num_article, $uq_base, $fabricant, $numero_piece_fabricant, $groupe_articles, $groupe_marche_externe, $document, $description, $date_creation, $cree_par;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM articles ORDER BY code_article");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les articles avec pagination et filtres
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
            $joins = "INNER JOIN nomenclatures n ON articles.code_article = n.code_article";
            $where[] = "n.source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(articles.code_article LIKE ? OR articles.designation_article LIKE ? OR articles.fabricant LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['fabricant'])) {
            $where[] = "articles.fabricant LIKE ?";
            $params[] = '%' . $filters['fabricant'] . '%';
        }

        if (!empty($filters['type_article'])) {
            $where[] = "articles.type_article LIKE ?";
            $params[] = '%' . $filters['type_article'] . '%';
        }

        if (!empty($filters['groupe_articles'])) {
            $where[] = "articles.groupe_articles = ?";
            $params[] = $filters['groupe_articles'];
        }

        if (!empty($filters['uq_base'])) {
            $where[] = "articles.uq_base = ?";
            $params[] = $filters['uq_base'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Si on filtre par source, on doit utiliser DISTINCT pour éviter les doublons
        $selectFields = !empty($filters['source']) ? 'DISTINCT articles.*' : 'articles.*';
        $countField = !empty($filters['source']) ? 'DISTINCT articles.id' : '*';

        // Requête de comptage total
        $countSql = "SELECT COUNT($countField) FROM articles $joins $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Requête des données
        $dataSql = "SELECT $selectFields FROM articles $joins $whereClause ORDER BY articles.code_article LIMIT $limit OFFSET $offset";

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
     * Récupère les valeurs distinctes pour les filtres
     * @param string $column
     * @return array
     */
    public static function getDistinctValues($column)
    {
        $allowedColumns = ['fabricant', 'type_article', 'groupe_articles', 'uq_base'];
        if (!in_array($column, $allowedColumns)) {
            return [];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT DISTINCT $column FROM articles WHERE $column IS NOT NULL AND $column != '' ORDER BY $column");
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

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO articles (code_article, designation_article, type_article, temsup_niv_mdt, ancien_num_article, uq_base, fabricant, numero_piece_fabricant, groupe_articles, groupe_marche_externe, document, description, date_creation, cree_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_article'],
            $data['designation_article'],
            $data['type_article'],
            $data['temsup_niv_mdt'],
            $data['ancien_num_article'],
            $data['uq_base'],
            $data['fabricant'],
            $data['numero_piece_fabricant'],
            $data['groupe_articles'],
            $data['groupe_marche_externe'],
            $data['document'],
            $data['description'],
            $data['date_creation'],
            $data['cree_par']
        ]);
    }

    public static function update($id, $data)
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE articles SET 
            code_article=:code_article, designation_article=:designation_article, type_article=:type_article, temsup_niv_mdt=:temsup_niv_mdt, ancien_num_article=:ancien_num_article,
            uq_base=:uq_base, fabricant=:fabricant, numero_piece_fabricant=:numero_piece_fabricant, groupe_articles=:groupe_articles, groupe_marche_externe=:groupe_marche_externe,
            document=:document, description=:description, date_creation=:date_creation, cree_par=:cree_par
            WHERE id=:id";
        $data['id'] = $id;
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteByIds($ids)
    {
        if (!is_array($ids) || empty($ids)) return false;
        $pdo = Database::getConnection();
        $in  = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id IN ($in)");
        return $stmt->execute($ids);
    }

    public static function exists($code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE code_article = ?");
        $stmt->execute([$code_article]);
        return $stmt->fetchColumn() > 0;
    }

    public static function add($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO articles (code_article) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$data['code_article']]);
    }

    public static function countAddedLast30Days()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        return (int)$stmt->fetchColumn();
    }

    public static function countByTypeArticle()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT type_article, COUNT(*) as nb FROM articles GROUP BY type_article ORDER BY type_article");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countByGroupeArticle()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT groupe_articles, COUNT(*) as nb FROM articles GROUP BY groupe_articles ORDER BY groupe_articles");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
