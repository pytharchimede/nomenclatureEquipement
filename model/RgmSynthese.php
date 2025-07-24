<?php
require_once __DIR__ . '/Database.php';

class RgmSynthese
{
    public $id, $repere_equipement, $code_article, $designation_article, $quantite, $unite, $date_import, $source;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM rgm_synthese ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPaginated($page = 1, $limit = 50, $filters = [])
    {
        $pdo = Database::getConnection();
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($filters['repere_equipement'])) {
            $where[] = "repere_equipement LIKE ?";
            $params[] = '%' . $filters['repere_equipement'] . '%';
        }

        if (!empty($filters['code_article'])) {
            $where[] = "code_article LIKE ?";
            $params[] = '%' . $filters['code_article'] . '%';
        }

        if (!empty($filters['designation_article'])) {
            $where[] = "designation_article LIKE ?";
            $params[] = '%' . $filters['designation_article'] . '%';
        }

        if (!empty($filters['unite'])) {
            $where[] = "unite LIKE ?";
            $params[] = '%' . $filters['unite'] . '%';
        }

        if (!empty($filters['source'])) {
            $where[] = "source LIKE ?";
            $params[] = '%' . $filters['source'] . '%';
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // En MySQL, on ne peut pas utiliser de placeholders pour LIMIT et OFFSET
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT * FROM rgm_synthese $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalCount($filters = [])
    {
        $pdo = Database::getConnection();

        $where = [];
        $params = [];

        if (!empty($filters['repere_equipement'])) {
            $where[] = "repere_equipement LIKE ?";
            $params[] = '%' . $filters['repere_equipement'] . '%';
        }

        if (!empty($filters['code_article'])) {
            $where[] = "code_article LIKE ?";
            $params[] = '%' . $filters['code_article'] . '%';
        }

        if (!empty($filters['designation_article'])) {
            $where[] = "designation_article LIKE ?";
            $params[] = '%' . $filters['designation_article'] . '%';
        }

        if (!empty($filters['unite'])) {
            $where[] = "unite LIKE ?";
            $params[] = '%' . $filters['unite'] . '%';
        }

        if (!empty($filters['source'])) {
            $where[] = "source LIKE ?";
            $params[] = '%' . $filters['source'] . '%';
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total FROM rgm_synthese $whereClause";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO rgm_synthese (repere_equipement, code_article, designation_article, quantite, unite, source)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['repere_equipement'],
            $data['code_article'],
            $data['designation_article'],
            $data['quantite'],
            $data['unite'],
            $data['source'] ?? 'RGM'
        ]);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM rgm_synthese WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function exists($repere_equipement, $code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rgm_synthese WHERE repere_equipement = ? AND code_article = ?");
        $stmt->execute([$repere_equipement, $code_article]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Insère ou met à jour un élément RGM
     */
    public static function insertOrUpdate($data)
    {
        $pdo = Database::getConnection();

        try {
            // Vérification si l'élément existe déjà
            $checkSql = "SELECT id FROM rgm_synthese WHERE repere_equipement = ? AND code_article = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$data['repere_equipement'], $data['code_article']]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Mise à jour
                $sql = "UPDATE rgm_synthese SET 
                        designation_article = ?, 
                        quantite = ?, 
                        unite = ?, 
                        source = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                return $stmt->execute([
                    $data['designation_article'],
                    $data['quantite'],
                    $data['unite'],
                    $data['source'] ?? 'Import',
                    $existing['id']
                ]);
            } else {
                // Insertion
                $sql = "INSERT INTO rgm_synthese (repere_equipement, code_article, designation_article, quantite, unite, source) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                return $stmt->execute([
                    $data['repere_equipement'],
                    $data['code_article'],
                    $data['designation_article'],
                    $data['quantite'],
                    $data['unite'],
                    $data['source'] ?? 'Import'
                ]);
            }
        } catch (PDOException $e) {
            error_log("Erreur insertOrUpdate RGM: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime tous les éléments RGM
     */
    public static function truncate()
    {
        $pdo = Database::getConnection();

        try {
            $sql = "TRUNCATE TABLE rgm_synthese";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur truncate RGM: " . $e->getMessage());
            return false;
        }
    }
}
