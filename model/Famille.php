<?php
require_once __DIR__ . '/Database.php';

class Famille
{
    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM familles ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insert($nom)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT IGNORE INTO familles (nom) VALUES (?)");
        return $stmt->execute([$nom]);
    }
}
