<?php
require_once __DIR__ . '/Database.php';

class Quantitatif
{
    public static function insert($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO quantitatif (famille, unite, quantite, repere, autres_colonnes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['famille'],
            $data['unite'],
            $data['quantite'],
            $data['repere'],
            json_encode($data['autres_colonnes'])
        ]);
    }

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM quantitatif ORDER BY famille, repere");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPaginated($page = 1, $limit = 50, $filters = [])
    {
        $pdo = Database::getConnection();
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($filters['famille'])) {
            $where[] = "famille LIKE ?";
            $params[] = '%' . $filters['famille'] . '%';
        }

        if (!empty($filters['repere'])) {
            $where[] = "repere LIKE ?";
            $params[] = '%' . $filters['repere'] . '%';
        }

        if (!empty($filters['unite'])) {
            $where[] = "unite LIKE ?";
            $params[] = '%' . $filters['unite'] . '%';
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // En MySQL, on ne peut pas utiliser de placeholders pour LIMIT et OFFSET
        // On s'assure que les valeurs sont des entiers pour éviter les injections SQL
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT * FROM quantitatif $whereClause ORDER BY famille, repere LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalCount($filters = [])
    {
        $pdo = Database::getConnection();

        $where = [];
        $params = [];

        if (!empty($filters['famille'])) {
            $where[] = "famille LIKE ?";
            $params[] = '%' . $filters['famille'] . '%';
        }

        if (!empty($filters['repere'])) {
            $where[] = "repere LIKE ?";
            $params[] = '%' . $filters['repere'] . '%';
        }

        if (!empty($filters['unite'])) {
            $where[] = "unite LIKE ?";
            $params[] = '%' . $filters['unite'] . '%';
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total FROM quantitatif $whereClause";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public static function getByFamille($famille)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM quantitatif WHERE famille = ? ORDER BY repere");
        $stmt->execute([$famille]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countEquipementsByFamille()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT famille, COUNT(DISTINCT repere) as nb FROM quantitatif GROUP BY famille ORDER BY famille");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFamilleByRepere($repere)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT famille FROM quantitatif WHERE repere = ? LIMIT 1");
        $stmt->execute([$repere]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['famille'] : 'Non défini';
    }
}
