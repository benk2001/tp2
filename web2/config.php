<?php
// Configuration de la base de données
$host = 'localhost'; 
$db   = 'gestion_etudiants'; 
$user = 'root'; 
$pass = '';

try {
    // Connexion avec encodage UTF-8 pour les accents
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Activation des erreurs pour faciliter le débogage
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>