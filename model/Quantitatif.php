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
