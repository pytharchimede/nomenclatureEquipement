<?php
require_once __DIR__ . '/../model/Database.php';

class GroupeUtilisateur
{
    public $id, $nom, $description;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM groupe_utilisateur");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM groupe_utilisateur WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO groupe_utilisateur (nom, description) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['nom'],
            $data['description'] ?? null
        ]);
    }

    public static function update($id, $data)
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE groupe_utilisateur SET nom = :nom, description = :description WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM groupe_utilisateur WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function exists($nom)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM groupe_utilisateur WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->fetchColumn() > 0;
    }
}
