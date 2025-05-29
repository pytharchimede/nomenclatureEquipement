<?php
require_once __DIR__ . '/../model/Database.php';

class Article
{
    public $id, $code_article, $designation_article, $type_article, $temsup_niv_mdt, $ancien_num_article, $uq_base, $fabricant, $numero_piece_fabricant, $groupe_articles, $groupe_marche_externe, $document, $description, $date_creation, $cree_par;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM articles");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO articles (code_article, designation_article, type_article, temsup_niv_mdt, ancien_num_article, uq_base, fabricant, numero_piece_fabricant, groupe_articles, groupe_marche_externe, document, description, date_creation, cree_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_article'],
            $data['designation_article'],
            $data['type_article'],
            $data['temsup_niv_mdt'],
            $data['ancien_num_article'],
            $data['uq_base'],
            $data['fabricant'],
            $data['numero_piece_fabricant'],
            $data['groupe_articles'],
            $data['groupe_marche_externe'],
            $data['document'],
            $data['description'],
            $data['date_creation'],
            $data['cree_par']
        ]);
    }

    public static function update($id, $data)
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE articles SET code_article=?, designation_article=?, type_article=?, temsup_niv_mdt=?, ancien_num_article=?, uq_base=?, fabricant=?, numero_piece_fabricant=?, groupe_articles=?, groupe_marche_externe=?, document=?, description=?, date_creation=?, cree_par=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['code_article'],
            $data['designation_article'],
            $data['type_article'],
            $data['temsup_niv_mdt'],
            $data['ancien_num_article'],
            $data['uq_base'],
            $data['fabricant'],
            $data['numero_piece_fabricant'],
            $data['groupe_articles'],
            $data['groupe_marche_externe'],
            $data['document'],
            $data['description'],
            $data['date_creation'],
            $data['cree_par'],
            $id
        ]);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
