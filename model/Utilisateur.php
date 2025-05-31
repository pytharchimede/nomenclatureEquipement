<?php
require_once __DIR__ . '/../model/Database.php';

class Utilisateur
{
    public $id, $nom, $email, $mot_de_passe, $groupe_id, $actif, $date_creation, $photo_profil, $telephone, $empreinte_numerique;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM utilisateur");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByEmail($email)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO utilisateur (nom, email, mot_de_passe, groupe_id, actif, date_creation, photo_profil, telephone, empreinte_numerique) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['nom'],
            $data['email'],
            $data['mot_de_passe'],
            $data['groupe_id'],
            $data['actif'] ?? 1,
            $data['date_creation'] ?? date('Y-m-d H:i:s'),
            $data['photo_profil'] ?? null,
            $data['telephone'] ?? null,
            $data['empreinte_numerique'] ?? null
        ]);
    }

    public static function update($id, $data)
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE utilisateur SET 
            nom = :nom, 
            email = :email, 
            mot_de_passe = :mot_de_passe, 
            groupe_id = :groupe_id, 
            actif = :actif, 
            photo_profil = :photo_profil, 
            telephone = :telephone, 
            empreinte_numerique = :empreinte_numerique
            WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $params = [
            'nom' => $data['nom'],
            'email' => $data['email'],
            'mot_de_passe' => $data['mot_de_passe'],
            'groupe_id' => $data['groupe_id'],
            'actif' => $data['actif'],
            'photo_profil' => $data['photo_profil'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'empreinte_numerique' => $data['empreinte_numerique'] ?? null,
            'id' => $id
        ];
        return $stmt->execute($params);
    }

    public static function delete($id)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function verifyPassword($email, $mot_de_passe)
    {
        $user = self::getByEmail($email);
        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }

    public static function exists($email)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public static function genererEmpreinte($nom, $email, $length = 5)
    {
        $base = strtolower(trim($nom . $email));
        $hash = substr(strtoupper(hash('crc32', $base)), 0, $length);
        return $hash;
    }

    public static function genererEmpreinteUnique($nom, $email, $length = 5)
    {
        $base = strtolower(trim($nom . $email));
        // Mélange chiffres et lettres avec base36
        $hash = strtoupper(substr(base_convert(crc32($base), 10, 36), 0, $length));
        $empreinte = $hash;
        $i = 0;
        $pdo = Database::getConnection();
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE empreinte_numerique = ?");
            $stmt->execute([$empreinte]);
            if ($stmt->fetchColumn() == 0) {
                return $empreinte;
            }
            // Ajoute un suffixe base36 pour éviter la collision
            $i++;
            $suffix = strtoupper(base_convert($i, 10, 36));
            $empreinte = strtoupper(substr($hash, 0, $length - strlen($suffix)) . $suffix);
            if (strlen($empreinte) > $length) {
                $length++;
                $empreinte = strtoupper(substr($hash, 0, $length));
                $i = 0;
            }
        }
    }
}
