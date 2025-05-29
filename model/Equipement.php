<?php
require_once __DIR__ . '/../model/Database.php';

class Equipement
{
    public $id, $code_equipement, $designation_equipement, $repere_equipement, $fabricant, $type_objet, $designation_type, $numero_serie_fabricant, $numero_piece_fabricant, $poste_technique, $designation_poste_technique, $poste_travail_principal, $categorie_equipement, $centre_de_couts, $date_creation;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM equipements");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM equipements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByCode($code)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM equipements WHERE code_equipement = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO equipements (code_equipement, designation_equipement, repere_equipement, fabricant, type_objet, designation_type, numero_serie_fabricant, numero_piece_fabricant, poste_technique, designation_poste_technique, poste_travail_principal, categorie_equipement, centre_de_couts, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_equipement'],
            $data['designation_equipement'],
            $data['repere_equipement'],
            $data['fabricant'],
            $data['type_objet'],
            $data['designation_type'],
            $data['numero_serie_fabricant'],
            $data['numero_piece_fabricant'],
            $data['poste_technique'],
            $data['designation_poste_technique'],
            $data['poste_travail_principal'],
            $data['categorie_equipement'],
            $data['centre_de_couts'],
            $data['date_creation']
        ]);
    }

    public static function update($id, $data)
    {
        $pdo = Database::getConnection();
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
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM equipements WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteByIds($ids)
    {
        if (empty($ids) || !is_array($ids)) return false;
        $pdo = Database::getConnection();
        // Création des placeholders (?, ?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM equipements WHERE id IN ($placeholders)");
        return $stmt->execute($ids);
    }

    public static function countAddedLast30Days()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM equipements WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return $stmt->fetchColumn();
    }

    public static function exists($code_equipement)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipements WHERE code_equipement = ?");
        $stmt->execute([$code_equipement]);
        return $stmt->fetchColumn() > 0;
    }

    public static function add($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO equipements (code_equipement) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$data['code_equipement']]);
    }
}
