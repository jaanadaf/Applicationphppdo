<?php
// Paramètres de connexion
$host = 'localhost';
$dbname = 'phppdo';
$username = 'root';
$password = '';

try {
    // Création de l'objet PDO
    $pdo = new PDO("mysql:host=localhost;dbname=phppdo;charset=utf8", "root", "");


    // Initialisation de l'objet PDO, construction de la requête...
    foreach ($pdo->query('SELECT name, email FROM users', PDO::FETCH_ASSOC) as $user) {
        echo $user['name'].' '.$user['email'].'<br>';
    }
    // Ici, la variable $row est un tableau associatif


} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

