RECUPERER LE NOM ET ADRESSE EMAIL DES UTILISATEURS:

Récupérer des données de notre base de données et les manipuler directement dans notre application en PHP. Pour cela, nous allons prendre un exemple simple de gestion d'utilisateur. Nous avons une base de données appelée phppdo à laquelle il va falloir se connecter. Cette base contient une liste d'utilisateurs ainsi que des groupes rattachés à chaque utilisateur. Pour chaque utilisateur, nous avons un identifiant, un nom, un mot de passe, un email et l'identifiant du groupe.

Dans un premier temps, nous allons simplement essayer de récupérer une liste d'utilisateurs et d'afficher leur nom et leur email. La première étape consiste à se connecter à notre base de données en créant un objet PDO. Dans mon cas, j'utilise une base de données MySQL. Ainsi, le DSN (Data Source Name) ressemble à ceci :
mysql:host=localhost;dbname=phppdo;charset=utf8.
Étant donné que c'est un environnement local, le login est root, et le mot de passe est une chaîne vide. Je vais gérer les erreurs avec un try/catch pour intercepter les exceptions PDO.

Ensuite, nous allons récupérer la liste des utilisateurs. Pour cela, il faut effectuer une requête SELECT. Pour envoyer cette requête, nous allons utiliser la méthode query de PDO. Par exemple, PHPStorm nous suggère directement cette méthode. La méthode query permet d'envoyer une requête SELECT et de récupérer les résultats. Je vais donc simplement récupérer les noms et emails des utilisateurs avec la requête suivante :
SELECT name, email FROM users.

La méthode query nous retourne tous les résultats de la requête ligne par ligne. Il est donc nécessaire d'utiliser une boucle foreach pour parcourir ces données. Voici le principe : la méthode query retourne les résultats ligne par ligne sous forme de tableau associatif (si nous avons spécifié ce mode via PDO::FETCH_ASSOC). Chaque ligne sera donc un tableau associatif avec deux champs : name et email.

Voici comment afficher les noms et emails :
Pour chaque utilisateur, on accède aux champs avec $user['name'] et $user['email']. Ensuite, on les concatène et ajoute un saut de ligne en HTML (<br>). Le script sera alors prêt.

Si nous exécutons ce script, nous devrions obtenir la liste des utilisateurs. Par exemple, pour trois utilisateurs nommés John, Laure, et Robert, avec leurs adresses email associées, le résultat s’affichera comme attendu. Grâce à la méthode query, nous avons pu exécuter une requête SQL SELECT et récupérer les résultats sous forme de tableau associatif.
==========================================================================
CODE
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
==========================================================================