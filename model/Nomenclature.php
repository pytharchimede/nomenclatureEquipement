<?php
require_once __DIR__ . '/../model/Database.php';

class Nomenclature
{
    public $id, $code_equipement, $code_article, $repere_equipement, $designation_equipement, $fabricant, $type, $numero_serie_fabricant, $designation_article, $numero_poste, $quantite, $unite, $poste_technique, $metier, $date_creation, $source;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM nomenclatures");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM nomenclatures WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO nomenclatures (code_equipement, code_article, repere_equipement, designation_equipement, fabricant, type, numero_serie_fabricant, designation_article, numero_poste, quantite, unite, poste_technique, metier, date_creation, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_equipement'],
            $data['code_article'],
            $data['repere_equipement'],
            $data['designation_equipement'],
            $data['fabricant'],
            $data['type'],
            $data['numero_serie_fabricant'],
            $data['designation_article'],
            $data['numero_poste'],
            $data['quantite'],
            $data['unite'],
            $data['poste_technique'],
            $data['metier'],
            $data['date_creation'],
            $data['source']
        ]);
    }

    public static function update($id, $data)
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE nomenclatures SET code_equipement=?, code_article=?, repere_equipement=?, designation_equipement=?, fabricant=?, type=?, numero_serie_fabricant=?, designation_article=?, numero_poste=?, quantite=?, unite=?, poste_technique=?, metier=?, date_creation=?, source=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_equipement'],
            $data['code_article'],
            $data['repere_equipement'],
            $data['designation_equipement'],
            $data['fabricant'],
            $data['type'],
            $data['numero_serie_fabricant'],
            $data['designation_article'],
            $data['numero_poste'],
            $data['quantite'],
            $data['unite'],
            $data['poste_technique'],
            $data['metier'],
            $data['date_creation'],
            $data['source'],
            $id
        ]);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM nomenclatures WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Compte le nombre total de nomenclatures
    public static function countAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures");
        return (int)$stmt->fetchColumn();
    }

    // Compte le nombre d'articles distincts liés à au moins une nomenclature
    public static function countDistinctArticles()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(DISTINCT code_article) FROM nomenclatures WHERE code_article IS NOT NULL AND code_article != ''");
        return (int)$stmt->fetchColumn();
    }

    // Compte le nombre d'équipements distincts liés à au moins une nomenclature
    public static function countDistinctEquipements()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(DISTINCT code_equipement) FROM nomenclatures WHERE code_equipement IS NOT NULL AND code_equipement != ''");
        return (int)$stmt->fetchColumn();
    }

    // Compte le nombre de nomenclatures créées sur les 30 derniers jours
    public static function countAddedLast30Days()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM nomenclatures WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        return (int)$stmt->fetchColumn();
    }

    public static function exists($code_equipement, $code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM nomenclatures WHERE code_equipement = ? AND code_article = ?");
        $stmt->execute([$code_equipement, $code_article]);
        return $stmt->fetchColumn() > 0;
    }

    public static function add($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO nomenclatures (code_equipement, code_article, repere_equipement, designation_equipement, fabricant, type, numero_serie_fabricant, designation_article, numero_poste, quantite, unite, poste_technique, metier, date_creation, source)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_equipement'],
            $data['code_article'],
            $data['repere_equipement'],
            $data['designation_equipement'],
            $data['fabricant'],
            $data['type'],
            $data['numero_serie_fabricant'],
            $data['designation_article'],
            $data['numero_poste'],
            $data['quantite'],
            $data['unite'],
            $data['poste_technique'],
            $data['metier'],
            $data['date_creation'],
            $data['source']
        ]);
    }


    public static function countFiltered($filters = [])
    {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];
        foreach ($filters as $col => $val) {
            if ($val !== '') {
                $where[] = "$col LIKE ?";
                $params[] = "%$val%";
            }
        }
        $sql = "SELECT COUNT(*) FROM nomenclatures";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
