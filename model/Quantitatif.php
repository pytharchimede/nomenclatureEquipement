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
}
