<?php
require_once 'model/Database.php';

try {
    echo "<h1>Réinitialisation du mot de passe</h1>";

    $pdo = Database::getConnection();

    // Nouveau mot de passe temporaire
    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Mise à jour du mot de passe pour l'utilisateur admin
    $stmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE email = 'ulrich@banamur.com'");
    $result = $stmt->execute([$hashedPassword]);

    if ($result) {
        echo "<p style='color: green;'>✓ Mot de passe mis à jour avec succès !</p>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007cba;'>";
        echo "<h3>Identifiants de connexion :</h3>";
        echo "<p><strong>Email :</strong> ulrich@banamur.com</p>";
        echo "<p><strong>Mot de passe :</strong> admin123</p>";
        echo "</div>";
        echo "<p><a href='auth.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Erreur lors de la mise à jour</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
