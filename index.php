<?php

// Paramètres de connexion
$host = 'localhost';
$dbname = 'phppdo';
$username = 'root';
$password = '';

try {
  require_once 'user.php';
    // Création de l'objet PDO
    $pdo = new PDO('mysql:host=localhost;dbname=phppdo', 'root', '');

  

    //recherche des utilisateurs
    $search = '%.com';

      // Requête SQL avec jointure

      $sql = 'SELECT users.name, users.email FROM users WHERE email LIKE :search';
      $pdoStatement=$pdo->prepare($sql);
      $pdoStatement->bindValue(':search', $search, PDO::PARAM_STR);
      $pdoStatement->setFetchMode(PDO::FETCH_CLASS, 'user' );
      if($pdoStatement->execute()){

        // Requete OK 

       while($user = $pdoStatement->fetch()) {

        echo $user->getDisplayedName();
       /* echo '<pre>';
        print_r($user);
        echo '</pre>';*/

        
    }

       
      }else{
        // Erreur
        echo 'Une erreur est survenue';
      }

      /*echo $sql;
    
    // Initialisation de l'objet PDO, construction de la requête...
    foreach ($pdo->query( $sql,  PDO::FETCH_ASSOC) as $user) {
        echo $user['name'].' '.$user['email'].'<br>';
    }*/
    // Ici, la variable $row est un tableau associatif


} catch (PDOException $e) {
    echo 'Impossible de se connecter à la base de données';
}

