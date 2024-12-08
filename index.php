<?php
// Paramètres de connexion
$host = 'localhost';
$dbname = 'phppdo';
$username = 'root';
$password = '';

try {
    // Création de l'objet PDO
    $pdo = new PDO('mysql:host=localhost;dbname=phppdo', 'root', '');

      // Requête SQL avec jointure
    $sql = 'SELECT users.name, users.email , groups.name AS groupName FROM users JOIN groups ON users.group_id = groups.id';
     

    // Initialisation de l'objet PDO, construction de la requête...
    foreach ($pdo->query( $sql,  PDO::FETCH_ASSOC) as $user) {
        echo $user['name'].' '.$user['email'].' '.$user['groupName'].'<br>';
    }
    // Ici, la variable $row est un tableau associatif


} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

