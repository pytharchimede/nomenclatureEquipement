<?php
require_once __DIR__ . '/../model/Database.php';

class DroitUtilisateur
{
    public $id, $groupe_id, $ressource, $droit;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM droits_groupe");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByGroupe($groupe_id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM droits_groupe WHERE groupe_id = ?");
        $stmt->execute([$groupe_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function hasDroit($groupe_id, $ressource, $droit)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM droits_groupe WHERE groupe_id = ? AND ressource = ? AND droit = ?");
        $stmt->execute([$groupe_id, $ressource, $droit]);
        return (bool)$stmt->fetchColumn();
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO droits_groupe (groupe_id, ressource, droit) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['groupe_id'],
            $data['ressource'],
            $data['droit']
        ]);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM droits_groupe WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
