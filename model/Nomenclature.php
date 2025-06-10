<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/Equipement.php'; // Ajout pour synchronisation repère/code

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

    // Ajout : recherche par repere + code_article (clé métier)
    public static function getByRepereArticle($repere, $code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM nomenclatures WHERE repere_equipement = ? AND code_article = ?");
        $stmt->execute([$repere, $code_article]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Synchronisation repere/code avant insertion ou update
    private static function syncRepereCode(&$data)
    {
        if (empty($data['code_equipement']) && !empty($data['repere_equipement'])) {
            $eq = Equipement::getByRepere($data['repere_equipement']);
            if ($eq) $data['code_equipement'] = $eq['code_equipement'];
        }
        if (empty($data['repere_equipement']) && !empty($data['code_equipement'])) {
            $eq = Equipement::getByCode($data['code_equipement']);
            if ($eq) $data['repere_equipement'] = $eq['repere_equipement'];
        }
        // Vérification de cohérence si les deux sont fournis
        if (!empty($data['code_equipement']) && !empty($data['repere_equipement'])) {
            $eq = Equipement::getByCode($data['code_equipement']);
            if (!$eq || $eq['repere_equipement'] !== $data['repere_equipement']) {
                throw new Exception("Le code équipement et le repère ne correspondent pas.");
            }
        }
    }

    public static function create($data)
    {
        self::syncRepereCode($data);
        // Contrôle de doublon métier
        if (self::existsByRepereArticle($data['repere_equipement'], $data['code_article'])) {
            throw new Exception("Une nomenclature existe déjà pour ce repère et cet article.");
        }
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
        self::syncRepereCode($data);
        // Contrôle de doublon métier (hors nomenclature courante)
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM nomenclatures WHERE repere_equipement = ? AND code_article = ? AND id != ?");
        $stmt->execute([$data['repere_equipement'], $data['code_article'], $id]);
        if ($stmt->fetch()) {
            throw new Exception("Une nomenclature existe déjà pour ce repère et cet article.");
        }
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

    // Ajout : contrôle de doublon métier (repere + code_article)
    public static function existsByRepereArticle($repere, $code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM nomenclatures WHERE repere_equipement = ? AND code_article = ?");
        $stmt->execute([$repere, $code_article]);
        return $stmt->fetchColumn() > 0;
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

    // Nombre d'articles distincts liés à au moins un équipement
    public static function countArticlesLies()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(DISTINCT code_article) FROM nomenclatures WHERE code_article IS NOT NULL AND code_article != '' AND code_equipement IS NOT NULL AND code_equipement != ''");
        return (int)$stmt->fetchColumn();
    }

    // Nombre d'équipements distincts ayant au moins une pièce de rechange (un article lié)
    public static function countEquipementsAvecPiece()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(DISTINCT code_equipement) FROM nomenclatures WHERE code_equipement IS NOT NULL AND code_equipement != '' AND code_article IS NOT NULL AND code_article != ''");
        return (int)$stmt->fetchColumn();
    }

    // Vérifie si un équipement a une pièce de rechange
    public static function equipementHasPiece($code_equipement)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM nomenclatures WHERE code_equipement = ? AND code_article IS NOT NULL AND code_article != '' LIMIT 1");
        $stmt->execute([$code_equipement]);
        return (bool)$stmt->fetchColumn();
    }

    // Vérifie si un article est lié à un équipement
    public static function articleIsLied($code_article)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM nomenclatures WHERE code_article = ? AND code_equipement IS NOT NULL AND code_equipement != '' LIMIT 1");
        $stmt->execute([$code_article]);
        return (bool)$stmt->fetchColumn();
    }

    // Nombre d'équipements existants ayant au moins une pièce de rechange
    public static function countEquipementsAvecPieceReelle()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT e.code_equipement)
            FROM equipements e
            INNER JOIN nomenclatures n ON n.code_equipement = e.code_equipement
            WHERE n.code_article IS NOT NULL AND n.code_article != ''
        ");
        return (int)$stmt->fetchColumn();
    }

    // Nombre d'articles existants liés à au moins un équipement
    public static function countArticlesLiesReels()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT a.code_article)
            FROM articles a
            INNER JOIN nomenclatures n ON n.code_article = a.code_article
            WHERE n.code_equipement IS NOT NULL AND n.code_equipement != ''
        ");
        return (int)$stmt->fetchColumn();
    }

    public static function syncFromRgmSynthese()
    {
        $pdo = Database::getConnection();

        // 1. Charger tous les repères équipements existants
        $equipements = [];
        foreach ($pdo->query("SELECT repere_equipement, code_equipement FROM equipements") as $row) {
            $equipements[strtolower(trim($row['repere_equipement'] ?? ''))] = $row['code_equipement'];
        }

        // 2. Charger tous les articles existants
        $articles = [];
        foreach ($pdo->query("SELECT code_article FROM articles") as $row) {
            $articles[strtolower(trim($row['code_article'] ?? ''))] = true;
        }

        // 3. Charger toutes les nomenclatures existantes
        $nomenclatures = [];
        foreach ($pdo->query("SELECT repere_equipement, code_article FROM nomenclatures") as $row) {
            $key = strtolower(trim($row['repere_equipement'] ?? '')) . '|' . strtolower(trim($row['code_article'] ?? ''));
            $nomenclatures[$key] = true;
        }

        // 4. Parcourir les synthèses
        $syntheses = $pdo->query("SELECT * FROM rgm_synthese")->fetchAll(PDO::FETCH_ASSOC);
        $inserted = 0;

        foreach ($syntheses as $row) {
            $repere = strtolower(trim($row['repere_equipement'] ?? ''));
            $article = strtolower(trim($row['code_article'] ?? ''));
            $key = $repere . '|' . $article;

            // 1. Vérifier ou créer l'équipement
            if (empty($equipements[$repere])) {
                $code_equipement = 'RGM-' . strtoupper(trim($row['repere_equipement'] ?? ''));
                // Vérifier unicité
                if (in_array($code_equipement, $equipements)) {
                    $code_equipement .= '-' . uniqid();
                }
                $stmt = $pdo->prepare("INSERT INTO equipements (code_equipement, repere_equipement) VALUES (?, ?)");
                $stmt->execute([$code_equipement, $row['repere_equipement']]);
                $equipements[$repere] = $code_equipement;
            } else {
                $code_equipement = $equipements[$repere];
            }

            // 2. Vérifier ou créer l'article
            if (!isset($articles[$article]) && !empty($row['code_article'])) {
                $stmt = $pdo->prepare("INSERT INTO articles (code_article, designation_article, date_creation) VALUES (?, ?, NOW())");
                $stmt->execute([$row['code_article'], $row['designation_article']]);
                $articles[$article] = true;
            }

            // 3. Vérifier si la nomenclature existe déjà
            if (isset($nomenclatures[$key])) {
                continue;
            }

            // 4. Insérer dans nomenclatures
            if ($code_equipement && !empty($row['code_article'])) {
                $stmt = $pdo->prepare("INSERT INTO nomenclatures (code_equipement, code_article, repere_equipement, designation_article, quantite, unite, date_creation, source) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'RGM')");
                $ok = $stmt->execute([
                    $code_equipement,
                    $row['code_article'],
                    $row['repere_equipement'],
                    $row['designation_article'],
                    $row['quantite'],
                    $row['unite']
                ]);
                if (!$ok) {
                    error_log("Erreur d'insertion pour {$row['repere_equipement']} / {$row['code_article']}");
                }
                $nomenclatures[$key] = true;
                $inserted++;
            }
        }
        return $inserted;
    }

    public static function getExistingRepereArticleMap()
    {
        $pdo = Database::getConnection();
        $rows = $pdo->query("SELECT repere_equipement, code_article FROM nomenclatures")->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $key = strtolower(trim($row['repere_equipement'] ?? '')) . '|' . strtolower(trim($row['code_article'] ?? ''));
            $map[$key] = true;
        }
        return $map;
    }
}
