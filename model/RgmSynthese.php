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
}
