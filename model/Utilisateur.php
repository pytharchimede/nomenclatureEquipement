<?php
require_once __DIR__ . '/../model/Database.php';

class Utilisateur
{
    public $id, $nom_utilisateur, $email, $mot_de_passe, $groupe_id, $actif, $date_creation, $photo_profil, $telephone, $empreinte_numerique;

    public static function getAll()
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM utilisateur ORDER BY nom_utilisateur");
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

    public static function getByUsername($username)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE nom_utilisateur = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authentification moderne avec vérification de mot de passe
     */
    public static function authenticate($email, $password)
    {
        $user = self::getByEmail($email);
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }

    /**
     * Vérification de l'existence d'un email
     */
    public static function emailExists($email)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérification de l'existence d'un nom d'utilisateur
     */
    public static function usernameExists($username)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE nom_utilisateur = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Mise à jour de la dernière connexion
     */
    public static function updateLastLogin($userId, $ip = null)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE utilisateur 
            SET last_login_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$userId]);
    }

    /**
     * Création d'un nouvel utilisateur avec support des nouveaux champs
     */
    public static function create($data)
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO utilisateur (
            nom, nom_utilisateur, email, mot_de_passe, telephone, actif, 
            groupe_id, date_creation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['nom_utilisateur'], // Utiliser nom_utilisateur pour le champ nom aussi
            $data['nom_utilisateur'],
            $data['email'],
            $data['mot_de_passe'],
            $data['telephone'] ?? null,
            $data['actif'] ?? 1,
            $data['groupe_id'] ?? 1
        ]);

        if ($result) {
            return $pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Mise à jour d'un utilisateur
     */
    public static function update($id, $data)
    {
        $pdo = Database::getConnection();

        // Construction dynamique de la requête selon les champs fournis
        $fields = [];
        $params = [];

        $allowedFields = [
            'nom',
            'nom_utilisateur',
            'email',
            'telephone',
            'actif',
            'photo_profil',
            'empreinte_numerique',
            'groupe_id'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE utilisateur SET " . implode(', ', $fields) . " WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Mise à jour du mot de passe
     */
    public static function updatePassword($id, $newPassword)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
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
