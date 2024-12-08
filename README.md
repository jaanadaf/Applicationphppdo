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
JOINTURE
Nous allons maintenant faire une requête un peu plus complexe. En plus du nom de l'utilisateur et de l'adresse email, nous allons vouloir afficher le nom du groupe auquel ils sont associés. Dans la base de données, en plus de la table users qui contient les informations des utilisateurs, il y a une table groups qui contient les identifiants et les noms des groupes. Dans la table users, il y a un champ group_id qui relie un utilisateur à un groupe.

Dans notre requête, nous allons donc devoir faire une jointure entre la table users et la table groups. Pour cela, nous allons ajouter un JOIN groups ON users.group_id = groups.id.

Cette ligne de code commence à prendre un peu de place, et pour mieux voir ce qui se passe, il faut commencer à scroller horizontalement, ce qui n’est jamais une bonne idée. Pour améliorer la lisibilité, nous allons stocker cette requête dans une variable. Ainsi, nous n'avons plus besoin de scroller horizontalement pour voir le code, ce qui le rend un peu plus lisible. Si, chez vous, la ligne tient sur une seule ligne, vous n’êtes pas obligés de faire cette étape, mais c’est tout de même conseillé pour des raisons de visibilité.

Nous devons maintenant ajouter le nom du groupe. Nous sélectionnons donc le nom des utilisateurs (users.name), leurs emails (users.email), et le nom du groupe (groups.name). Cependant, cela entraîne un problème : dans la requête, nous avons deux champs portant le même nom, name, mais désignant des informations différentes.

Si nous exécutons ce code, nous verrons que les noms des utilisateurs sont remplacés par les noms des groupes.

Admin john@example.com
Editor laura@example.com
Viewer robert@example.com

 Cela se produit parce que, dans un tableau associatif, il ne peut pas y avoir deux champs ayant le même nom mais des valeurs différentes. Lorsque cela arrive, la deuxième valeur écrase forcément la première.

Concrètement, voici ce qui se passe : PDO récupère d’abord le champ name de la table users et le place dans le tableau associatif sous la clé name. Ensuite, il ajoute le champ email sous la clé email. Mais quand PDO récupère le champ name de la table groups, ce dernier écrase le champ name déjà présent dans le tableau, car les deux ont la même clé. C’est pour cette raison qu’en affichant name et email, le code affiche le nom du groupe à la place du nom de l’utilisateur, alors que l’email reste intact, car il n’a pas été écrasé.

Pour résoudre ce problème, nous allons utiliser des alias. En réalité, les champs du tableau associatif ne sont pas basés sur les noms des colonnes de la base de données, mais sur les noms retournés par la requête SELECT. En attribuant des alias aux noms des colonnes, nous pouvons modifier les clés du tableau.

Ainsi, nous ajoutons un alias au champ name de la table groups en le renommant group_name. Désormais, notre tableau associatif contiendra trois champs distincts : name, email et group_name. Cela évite tout conflit de nom.

John john@example.com Admin
Laura laura@example.com Editor
Robert robert@example.com Viewer

Nous pouvons maintenant ajouter group_name à notre affichage. Il suffit de concaténer un espace suivi de la valeur du champ group_name. Une fois cette modification faite, nous actualisons notre page et constatons que le nom de l’utilisateur est bien affiché, suivi du nom de son groupe.

Donner des alias à vos champs peut être très pratique pour résoudre ce type de conflit et rendre votre code plus lisible et maintenable.

=====================================================================================================CODE
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
