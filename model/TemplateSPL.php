<?php
class TemplateSPL
{
    public $id, $numero, $code_sap, $code_article, $quantite, $designation_article, $unite_base, $metier, $numero_piece_fabricant, $fabricant, $equipement, $date_import, $import_par, $source;

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO template_spl (numero, code_sap, code_article, quantite, designation_article, unite_base, metier, numero_piece_fabricant, fabricant, equipement, import_par)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['numero'],
            $data['code_sap'],
            $data['code_article'],
            $data['quantite'],
            $data['designation_article'],
            $data['unite_base'],
            $data['metier'],
            $data['numero_piece_fabricant'],
            $data['fabricant'],
            $data['equipement'],
            $data['import_par']
        ]);
    }

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM template_spl ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function exists($data)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM template_spl WHERE code_article = ? AND equipement = ?");
        $stmt->execute([$data['code_article'], $data['equipement']]);
        return $stmt->fetchColumn() > 0;
    }
}
