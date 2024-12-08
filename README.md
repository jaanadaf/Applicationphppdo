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
==================================================================================================================
LES REQUETES PREPARES:
Donc, pour simplifier un peu, je vais enlever cette jointure et nous allons simplement ajouter une clause WHERE pour rechercher des utilisateurs particuliers. Nous allons imaginer une variable $search qui récupère une recherche d'un utilisateur, par exemple, qui souhaite chercher d'autres utilisateurs selon leur nom. Par exemple, tous les utilisateurs qui commencent par "Jo". Comme nous allons utiliser un LIKE, il suffit de mettre Jo% pour récupérer tous les utilisateurs commençant par "Jo".

<?php
$pdo->query('SELECT * FROM users WHERE name LIKE \''.$_GET['search'].'%\'', PDO::FETCH_ASSOC);

Nous allons modifier notre requête en ajoutant un WHERE name LIKE et ensuite ajouter le contenu de cette recherche. Il faut ouvrir un guillemet dans la requête, fermer le guillemet, insérer $search, refermer les guillemets, puis refermer notre chaîne de caractères. C'est une attaque un peu particulière, mais étant donné que le contenu de notre recherche est dans une variable (par exemple, récupérée depuis un formulaire), il faut l'injecter dans la requête SQL. Comme c'est une chaîne de caractères, il faut ajouter des simples cotes avant et après dans notre requête SQL. On doit également échapper les apostrophes pour éviter qu'elles ferment notre chaîne de caractères.

Je vais également modifier le code pour enlever le nom du groupe. Nous allons maintenant lancer ce code en actualisant la page. Ainsi, nous avons bien l'utilisateur "Jo" qui s'affiche, puisque c'est le seul qui commence par "Jo%".



Entre-temps, j'ai ajouté un utilisateur dans la base de données appelé "d'Artagnan". De la même manière, nous allons chercher cet utilisateur en changeant notre critère de sélection : ce n'est plus "Jo%" mais "d'" que nous allons échapper pour éviter qu'il ferme la chaîne de caractères. En actualisant la page, nous pouvons récupérer "d'Artagnan".

Cependant, il y a un problème. La requête SQL générée n'est plus valide. Le fait d'avoir ajouté un apostrophe rend cette requête SQL invalide. Pour mieux comprendre, affichons la requête générée. On observe que la requête SELECT users.name, users.email FROM users WHERE name LIKE ouvre un premier guillemet, mais à cause de l'apostrophe, la chaîne de caractères se limite à "d", suivi d'un pourcentage et d'une apostrophe qui traîne, rendant la requête invalide.

En SQL, il est possible d'échapper des caractères, comme nous l'avons fait en PHP avec un antislash. En SQL, cela peut être fait en doublant l'apostrophe. Ainsi, pour que la requête soit valide, il faut ajouter un double apostrophe. En actualisant la page, nous voyons que la requête contient le double apostrophe et qu'elle est maintenant valide. L'utilisateur "d'Artagnan" s'affiche correctement.

Le problème est qu'on ne peut pas demander aux utilisateurs de doubler eux-mêmes leurs caractères spéciaux dans un champ de recherche. C'est une opération que nous devons gérer nous-mêmes. Ce processus diffère selon le moteur de base de données utilisé et peut être lourd à mettre en place.

De plus, dans notre cas, nous avons juste invalidé la requête, mais des utilisateurs malveillants pourraient exploiter cette faiblesse pour injecter du code SQL. Cela leur permettrait de lancer n'importe quelle requête sur la base de données, par exemple, pour supprimer des utilisateurs ou détruire la structure des tables. Heureusement, PDO propose des outils pour échapper automatiquement les caractères et se prémunir de ce genre de problèmes.
===============================================================================================
RECHERCHE DES UTILISATEUR
METHODE AVEC RISQUE D'INJECTION SQL
Pour simplifier un peu, je vais enlever cette voiture et simplement ajouter une clause WHERE pour rechercher des utilisateurs spécifiques. Imaginons une variable $search qui contient une recherche effectuée par un utilisateur. Par exemple, si l'utilisateur veut chercher d'autres utilisateurs en fonction de leur nom, comme tous ceux qui commencent par "Jo", on peut utiliser un LIKE. Il suffit d'écrire Jo% pour récupérer tous les utilisateurs dont le nom commence par "Jo".
$search = 'jo%';
Nous allons donc modifier notre requête en ajoutant un WHERE name LIKE et y inclure le contenu de cette recherche. Pour cela, il faut ouvrir des guillemets dans la requête, insérer $search, refermer les guillemets, et fermer la chaîne de caractères. Cette approche est un peu particulière, mais comme le contenu de la recherche est contenu dans une variable (par exemple récupérée depuis un formulaire), nous devons l'injecter dans la requête SQL. Puisqu'il s'agit d'une chaîne de caractères, il faut que des apostrophes entourent cette chaîne dans la requête SQL. Cependant, il est nécessaire d'ajouter un caractère d'échappement pour éviter qu'une apostrophe utilisée dans la recherche ne ferme prématurément notre chaîne de caractères.

Ensuite, je vais également modifier le nom du groupe pour simplifier davantage. Nous allons exécuter le code et actualiser la page. Si tout fonctionne, nous verrons l'utilisateur "Jo", car c'est le seul qui commence par "Jo%"

 $sql = 'SELECT  users.name, users.email FROM users WHERE name LIKE \''.$search.\'';
 =====================================================================================
 RECHERCHE DES UTILISATEURS SANS RISQUE D'INJECTION SQL
 CODE COMPLET:

  <?php
// Paramètres de connexion
$host = 'localhost';
$dbname = 'phppdo';
$username = 'root';
$password = '';

try {
    // Création de l'objet PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recherche des utilisateurs
    $search = 'jo%'; // Critère de recherche

    // Requête SQL avec un paramètre nommé
    $sql = 'SELECT users.name, users.email FROM users WHERE users.name LIKE :search';
    $stmt = $pdo->prepare($sql); // Préparation de la requête
    $stmt->execute([':search' => $search]); // Exécution avec le paramètre

    // Affichage des résultats
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['email']) . '<br>';
    }
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}



======>Voici une explication détaillée de ce code PHP :

1. Paramètres de connexion
php
Copier le code
$host = 'localhost';
$dbname = 'phppdo';
$username = 'root';
$password = '';
Déclaration des paramètres nécessaires à la connexion à la base de données :
$host : Nom de l'hôte (généralement localhost pour les bases locales).
$dbname : Nom de la base de données (phppdo dans cet exemple).
$username : Nom d'utilisateur pour accéder à la base (ici, root).
$password : Mot de passe pour l'utilisateur (vide ici).
2. Bloc try-catch pour gérer les erreurs
php
Copier le code
try {
    // Création de l'objet PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
Création de la connexion avec PDO :

La classe PDO est utilisée pour se connecter à la base de données.
La chaîne de connexion (mysql:host=$host;dbname=$dbname;charset=utf8) spécifie :
Le type de base de données (mysql).
L’hôte ($host) et le nom de la base ($dbname).
L’encodage des caractères (charset=utf8 pour gérer les caractères spéciaux).
Définition du mode de gestion des erreurs :

PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION indique que les erreurs doivent générer des exceptions, ce qui facilite leur gestion.
Gestion des erreurs dans le catch :

Si une erreur survient (comme une base inexistante ou un mot de passe incorrect), elle est capturée par le bloc catch, et le script affiche un message d'erreur :
php
Copier le code
catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
3. Définition du critère de recherche
php
Copier le code
$search = 'jo%'; // Critère de recherche
Initialisation de la variable $search :
Contient un critère pour rechercher tous les noms qui commencent par jo (% est un joker dans SQL).
4. Requête SQL avec un paramètre nommé
php
Copier le code
$sql = 'SELECT users.name, users.email FROM users WHERE users.name LIKE :search';
$stmt = $pdo->prepare($sql); // Préparation de la requête
$stmt->execute([':search' => $search]); // Exécution avec le paramètre
Requête SQL :

Sélectionne les colonnes name et email dans la table users pour tous les utilisateurs dont le champ name correspond au critère $search.
Requête préparée :

Utilisation de prepare() pour éviter les injections SQL. Le placeholder :search sera remplacé par la valeur de $search au moment de l’exécution.
Exécution de la requête :

execute([':search' => $search]) remplace le placeholder :search par la valeur 'jo%'.
5. Affichage des résultats
php
Copier le code
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['email']) . '<br>';
}
Récupération des résultats :

fetch(PDO::FETCH_ASSOC) retourne chaque ligne sous forme de tableau associatif (['name' => ..., 'email' => ...]).
La boucle while parcourt chaque utilisateur trouvé.
Affichage sécurisé :

htmlspecialchars() empêche les failles XSS en convertissant les caractères spéciaux (<, >, &, etc.) en entités HTML.
Format d’affichage :

Chaque utilisateur est affiché avec son nom et son email séparés par un espace, suivi d’un saut de ligne (<br>).
Résumé du fonctionnement
Le script établit une connexion sécurisée à la base de données avec PDO.
Il définit un critère pour rechercher tous les noms commençant par "jo".
Il utilise une requête préparée pour éviter les failles de sécurité.
Les résultats sont affichés de manière sécurisée dans le navigateur.
Exemple de sortie
Si la table users contient :

name	email
John	john@example.com
==============================================================================================
ECHAPPER AUX CARACTERE SPECIAUX

J'ai ajouté un utilisateur dans la base de données qui s'appelle D'Artagnan. De la même manière, on va chercher cet utilisateur, mais en réalité, on va chercher tous les utilisateurs dont le nom commence par une apostrophe. Par exemple, si nous recherchons "D'Artagnan", nous devons adapter notre critère de sélection. Ce ne sera plus 'Jo%' mais plutôt 'd'%', et il faudra échapper l'apostrophe pour éviter qu'elle ne ferme la chaîne de caractères.

Le code PHP pour préparer cette requête pourrait ressembler à ceci :

php
Copier le code
$search = "d'%" ; // Critère de recherche
$sql = 'SELECT users.name, users.email FROM users WHERE users.name LIKE :search';
$stmt = $pdo->prepare($sql); // Préparation de la requête
$stmt->execute([':search' => $search]); // Exécution avec le paramètre
Nous devons actualiser notre page pour récupérer D'Artagnan, mais là, un problème survient. 


-->Parse error: syntax error, unexpected token "\" in C:\xampp\htdocs\phppdo\index.php on line 15

Pourquoi ? Parce que notre requête SQL devient invalide à cause de l'apostrophe dans la chaîne de caractères. Pour comprendre cela, affichons la requête générée.



La requête générée serait quelque chose comme :


Voici ce qui se passe : la requête est mal formée comme suit :

sql
Copier le code
SELECT users.name, users.email FROM users WHERE users.name LIKE 'd%' 
Lorsque nous avons une apostrophe dans la recherche, cela ferme la chaîne de caractères avant le caractère '%, ce qui provoque une erreur. Donc, l'ajout de l'apostrophe dans la recherche rend la requête SQL invalide.

Pour résoudre ce problème, il existe une méthode pour échapper l'apostrophe dans la chaîne. En PHP, nous avons échappé les caractères avec un anti-slash. Mais en SQL, pour échapper une apostrophe, on double l'apostrophe comme suit :

 $search = 'D\'\'%';

php
Copier le code
$escapedSearch = str_replace("'", "''", $search); // On double l'apostrophe
$sql = 'SELECT users.name, users.email FROM users WHERE users.name LIKE :search';
$stmt = $pdo->prepare($sql);
$stmt->execute([':search' => $escapedSearch]);
Ici, nous avons doublé l'apostrophe pour qu'elle soit correctement échappée, et la requête devient valide. Après avoir ajouté cet échappement, la requête est bien formatée et nous obtenons le bon résultat :

sql
Copier le code
SELECT users.name, users.email FROM users WHERE users.name LIKE 'd''%' 
Cela permet à la requête d'être valide et d'inclure l'utilisateur D'Artagnan dans les résultats.

Cependant, un problème subsiste : il n'est pas réaliste de demander à nos utilisateurs de doubler eux-mêmes leurs caractères spéciaux lorsqu'ils saisissent une recherche dans un formulaire. Dans ce cas, l'échappement doit être fait automatiquement par le système. C'est une tâche que nous devons gérer, car chaque moteur de base de données a ses propres règles pour échapper les caractères spéciaux.

Ce processus peut devenir lourd à gérer, surtout lorsque nous devons l'appliquer à chaque fois. De plus, si cette procédure n'est pas correctement mise en place, des utilisateurs malveillants pourraient exploiter cette faiblesse pour réaliser des injections SQL, ce qui leur permettrait d'exécuter des requêtes malveillantes sur notre base de données, comme par exemple supprimer des utilisateurs ou corrompre la structure des tables.

Heureusement, PHP et PDO offrent des mécanismes pour éviter ce genre de problème, notamment en utilisant des requêtes préparées et des paramètres liés, qui échappent automatiquement les caractères spéciaux, protégeant ainsi notre base de données des injections SQL.
=============================================================================================
LES REQUETES PEREPARE:
Une requete préparé c'est juste une requete Qui contient des sortes de paramètres dans lesquels on va vouloir injecter des valeurs. Dans notre cas, par exemple, ici on va pouvoir déclarer un paramètre qui va recevoir la valeur de notre variable $search. L'avantage d'utiliser une requête préparée, c'est que toutes les valeurs qu'on va injecter à l'intérieur vont être automatiquement nettoyées par PDO. Ainsi, les caractères spéciaux vont être doublés pour les échapper, et les requêtes vont être nettoyées ensuite pour éviter les injections SQL. Toute cette opération va être réalisée de manière automatique par PDO à partir du moment où on utilise des requêtes préparées. Donc, le développeur n'a plus à se soucier de faire ce genre de choses et donc de faire des fonctions spécifiques pour doubler les apostrophes par exemple.

Pour utiliser une requête préparée, il y a deux étapes. La première, comme son nom l'indique, c'est de préparer la requête, et ensuite on va l'exécuter. C'est à ce moment-là où on va lui injecter les valeurs.

1. Préparation de la requête
Les requêtes préparées utilisent ce qu'on appelle des marqueurs pour signaler les endroits de la requête qui vont devoir recevoir des valeurs. Donc, par exemple, ici dans notre requête, on va vouloir insérer le contenu de la variable $search. On va donc placer un marqueur qui va signaler à PDO qu'on va vouloir injecter une valeur. Il existe deux types de marqueurs : les marqueurs nommés et les marqueurs interrogatifs.

Les marqueurs nommés sont symbolisés par la syntaxe :nom, suivie d'un nom. Donc, ici, par exemple, on veut récupérer la recherche d'un utilisateur. On va pouvoir appeler notre marqueur :search. On peut voir ce marqueur comme un paramètre et donc, au moment d'exécuter la requête, il va falloir renseigner le paramètre :search pour lui injecter une valeur.

2. Exemple de requête avec marqueur nommé
php
Copier le code
$search = "D'"; // Exemple de critère de recherche
$sql = 'SELECT users.name, users.email FROM users WHERE name LIKE :search';
$stmt = $pdo->prepare($sql);

Le deuxième type de marqueur qui existe est le marqueur interrogatif. Donc, à la place ici de mettre :search, on va tout simplement mettre un point d'interrogation. Et si on a plusieurs marqueurs, cette fois, ce n'est pas le nom qui va différencier les marqueurs entre eux, mais leur position. Au moment d'injecter les valeurs, on va tout simplement dire : le marqueur en position 1 va recevoir telle valeur, puis le marqueur en position 2 va recevoir telle autre valeur, etc.

3. Exemple de requête avec marqueur interrogatif
php
Copier le code
$search = "D'"; // Exemple de critère de recherche
$sql = 'SELECT users.name, users.email FROM users WHERE name LIKE ?';
$stmt = $pdo->prepare($sql);

Dans la pratique, on va surtout utiliser les marqueurs nommés, car ils sont plus pratiques à utiliser. Mais il est important de savoir aussi que les marqueurs interrogatifs existent.

Je tiens votre attention sur le fait qu'un marqueur remplace une valeur à part entière. Donc, dans notre cas ici, cela va remplacer la chaîne de caractères qui va être comparée grâce à l'opérateur LIKE avec le nom de notre utilisateur. Dans le cas de notre LIKE, cela signifie que le symbole % fait partie de ce qui va être injecté à l'intérieur du marqueur. On ne doit donc pas écrire ?%, mais le % va bien devoir faire partie de la recherche et donc de la valeur qui va être injectée à l'intérieur du marqueur.

4. Ajout du caractère % à la recherche
php
Copier le code
$search = "%D'"; // Ajout du pourcentage dans la recherche de l'utilisateur
5. Exécution de la requête
Maintenant qu'on a préparé notre requête en utilisant un marqueur, il va falloir renseigner la valeur de ce marqueur et l'exécuter. Pour cela, on va devoir manipuler un nouvel objet de PDOStatement :

php
Copier le code
$stmt->execute([':search' => $search]); // Injection de la valeur dans le marqueur
6. Récupération des résultats
Une fois la requête exécutée, on peut récupérer les résultats :

php
Copier le code
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['email']) . '<br>';
}
===========================================================================================================================================================
PDO STATEMENT
on a rajouté des marqueures à notre requéte préparé Il faut maintenant la donner à PDO pour qu'il puisse la gérer et injecter des valeurs. Pour cela, on va utiliser une nouvelle méthode issue de l'objet PDO, qui est la méthode prepare :


$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'password');
$sql = "SELECT * FROM users WHERE name LIKE :search";


Cette méthode prend en paramètre la requête préparée que PDO va devoir gérer. Dans notre cas, c'est $sql, et cette méthode va retourner un objet particulier du type PDOStatement.

 $pdo->prepare($sql);

 PDOStatement est une classe native de PHP, au même titre que PDO ou PDOException. L'objectif d'un PDOStatement est de représenter, de matérialiser sous forme d'objet, une requête.

 $pdoStatement=$pdo->prepare($sql);

Ainsi, après avoir préparé la requête, tout ce que l'on va pouvoir faire sur cette requête préparée sera géré par un objet PDOStatement. Par exemple, on pourra donner des valeurs à ses marqueurs, l'exécuter, récupérer des résultats, etc. En fait, on peut voir l'objet PDO comme une fabrique servant à instancier des PDOStatement. On aura donc un PDOStatement par requête SQL que l'on souhaite manipuler.

La première chose qu'on va vouloir faire avec notre PDOStatement est de renseigner une valeur à nos marqueurs. Pour cela, on a le choix entre deux méthodes : bindValue et bindParam. 



Les deux sont assez similaires. Dans un premier temps, on va utiliser bindValue, qui va nous permettre de lier un marqueur à une valeur et donc d'échapper les caractères spéciaux. Elle prend trois paramètres : le marqueur, la valeur et le type de la valeur.

 $pdoStatement->bindValue(1, $search, PDO::PARAM_STR);

Voici un exemple d'utilisation de bindValue :

$search = "%john%";
$stmt->bindValue(':search', $search, PDO::PARAM_STR);

Comme on l'a vu, il existe deux types de marqueurs : les marqueurs nommés (comme :search) et les marqueurs interrogatifs (comme ?). Dans notre cas, on utilise un marqueur nommé (:search), et donc, au moment de renseigner la valeur, on l'associe à ce marqueur en précisant son type avec PDO::PARAM_STR pour une chaîne de caractères. Il n'est donc plus nécessaire de doubler les caractères spéciaux comme l'apostrophe, car cela sera fait automatiquement par PDO.

Si on avait eu un marqueur interrogatif à la place de :search, cela aurait ressemblé à ceci :


$sql = "SELECT * FROM users WHERE name LIKE ?";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(1, $search, PDO::PARAM_STR);

On se base alors sur la position du marqueur (1 pour le premier marqueur) pour lui injecter la valeur.

Maintenant, il existe aussi une autre méthode, bindParam, qui est similaire à bindValue, mais avec une différence importante : bindParam lie une variable par référence à un marqueur. Voici un exemple d'utilisation :

$search = "%john%";
$stmt->bindParam(1, $search, PDO::PARAM_STR);

La différence principale avec bindValue est que bindParam permet de lier directement une variable par référence. Cela signifie que si la valeur du marqueur est modifiée par la requête, la variable d'origine sera également modifiée. Ce cas est rare, mais peut se produire si on travaille avec des procédures stockées qui modifient les valeurs des paramètres. Cependant, dans la plupart des cas, bindValue est suffisant, sauf si vous avez besoin d'un lien par référence avec la variable.

Une autre différence avec bindValue est que bindParam ne permet pas de passer directement une chaîne de caractères. Par exemple, le code suivant ne fonctionnera pas :


$stmt->bindParam(1, "%john%", PDO::PARAM_STR); // Cela échouera

Il faut obligatoirement passer par une variable intermédiaire.

Maintenant que toutes les valeurs sont liées aux marqueurs, il suffit d'exécuter la requête :

$stmt->execute();
Cela permet d'exécuter la requête préparée avec les valeurs injectées.

Dans la pratique, bindValue est plus couramment utilisé, sauf dans des cas spécifiques où l'on a besoin de lier une variable par référence, ce qui est assez rare dans la plupart des applications classiques.
==========================================================================
LA METHODE EXECUT:

On va pouvoir exécuter notre requête. Pour cela, on va utiliser une autre méthode de notre PDOStatement, la méthode execute(). Il nous suffit de récupérer notre objet PDOStatement et d'utiliser cette méthode. Il faut bien faire attention à utiliser la méthode execute() seulement une fois que nous avons bien renseigné toutes les valeurs de nos marqueurs. Donc, il faut d'abord préparer la requête pour récupérer le PDOStatement, ensuite faire tous les bindValue nécessaires ou les bindParam selon votre cas, et enfin seulement utiliser la méthode execute().

Voici comment procéder :


$stmt = $pdo->prepare($sql); // Préparation de la requête
$stmt->bindValue(1, $search, PDO::PARAM_STR); // Lier la valeur au marqueur

// Exécution de la requête
if ($stmt->execute()) {
    echo "Requête OK";
} else {
    echo "Erreur dans l'exécution de la requête";
}
La méthode execute() retourne un booléen qui nous indique si la requête s'est bien déroulée ou si une erreur est survenue. Pour gérer correctement les erreurs, nous pouvons placer la méthode execute() dans un if. Si nous passons dans ce if, cela signifie que la requête s'est bien déroulée, sinon il y a eu une erreur.

Dans un premier temps, on va s'attarder sur le cas d'erreur. Si la requête ne s'est pas bien passée, on peut récupérer, toujours à partir de notre objet PDOStatement, toutes les informations concernant l'erreur. Cela se fait en appelant la méthode errorInfo() sur notre objet PDOStatement.


if (!$stmt->execute()) {
    // Récupération des informations sur l'erreur
    $errorInfo = $stmt->errorInfo();
    print_r($errorInfo); // Affiche les informations d'erreur
}
La méthode errorInfo() va nous retourner un tableau de trois éléments :

Le SQLSTATE (code d'erreur SQL).
Le code d'erreur du driver, qui est spécifique au driver que l'on utilise (ici, c'est MySQL).
Le message d'erreur détaillant le problème.
Si on provoque volontairement une erreur, par exemple en modifiant le nom de la table :


$sql = "SELECT * FROM userrr WHERE name = :search"; // "userrr" n'existe pas
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', $search, PDO::PARAM_STR);

// Simuler une erreur
$stmt->execute(); 
Le retour de errorInfo() pourrait ressembler à ceci :


Array
(
    [0] => 42S02 // SQLSTATE
    [1] => 1146   // Code d'erreur du driver
    [2] => Table 'database.userrr' doesn't exist // Message d'erreur
)
Ces informations sont utiles pour le débogage et peuvent être envoyées à un administrateur par email pour qu'il puisse résoudre rapidement le problème. Cependant, soyez vigilant à ne pas afficher directement ces informations sur votre page car elles peuvent comporter des détails sensibles sur la structure de la base de données. Il est fortement recommandé de ne pas les afficher directement aux utilisateurs. Vous pouvez plutôt les utiliser à des fins de débogage, en les envoyant par email ou en les enregistrant dans un fichier de logs.

Voici un exemple pour envoyer ces informations par email ou les enregistrer dans un fichier log :

if (!$stmt->execute()) {
    $errorInfo = $stmt->errorInfo();
    // Loguer l'erreur dans un fichier
    file_put_contents('error_log.txt', implode(" - ", $errorInfo) . "\n", FILE_APPEND);
    
    // Ou envoyer l'erreur par email
    mail("admin@exemple.com", "Erreur SQL", implode(" - ", $errorInfo));
    
    // Afficher un message générique à l'utilisateur
    echo "Une erreur est survenue, nous avons été informés.";
}
Une dernière chose dont nous n'avons pas parlé : il est possible de passer un tableau à la méthode execute() pour remplacer tous les bindValue. Par exemple, au lieu de faire un bindValue pour lier le marqueur :search à la valeur de $search, on pourrait directement passer un tableau à execute(), où les clés sont les noms des marqueurs et les valeurs les valeurs correspondantes.

Voici un exemple de cette méthode :


$sql = "SELECT * FROM users WHERE name = :search";
$stmt = $pdo->prepare($sql);

// Passer les valeurs à la méthode execute()
$stmt->execute([':search' => $search]);
Cependant, cette méthode a des inconvénients. Notamment, il n'est plus possible de spécifier le type de chaque paramètre, et par défaut, tous les paramètres sont considérés comme des chaînes de caractères. Dans certains cas, cela peut provoquer des erreurs. Par conséquent, cette méthode n'est pas conseillée, même si elle peut sembler plus pratique à manipuler. Préférez utiliser bindValue() ou bindParam() pour garantir une gestion correcte des types.


==========================================================================
RECUPERER LES RESULTATS AVEC LA METHODE fetch()
Pour renseigner la valeur des marqueurs, exécuter la requête, et traiter correctement les cas d'erreurs, nous allons finalement récupérer les résultats de notre requête. Jusqu'à présent, nous utilisions la méthode query pour lancer une requête. Cependant, pour récupérer les résultats d'une requête préparée qui a déjà été exécutée, nous devons utiliser une autre méthode : la méthode fetch de notre objet PDOStatement.

La syntaxe est la suivante :

php
Copier le code
$pdoStatement->fetch(PDO::FETCH_ASSOC);
Cette méthode prend un paramètre qui détermine le mode de récupération. Nous utiliserons le mode PDO::FETCH_ASSOC, qui, comme précédemment mentionné, permet de retourner les résultats sous forme de tableau associatif.

Contrairement à query, qui retourne toutes les lignes d'un coup, fetch retourne la prochaine ligne de résultat. Cela implique que nous devons parcourir les résultats avec une boucle while. À chaque tour de boucle, la méthode fetch renvoie la prochaine ligne de résultat, qui sera stockée dans une variable (par exemple, $user) que nous pourrons utiliser à l'intérieur de la boucle.

Voici le code correspondant pour afficher le nom et l'email de chaque utilisateur :

php
Copier le code
<?php
// Exemple d'utilisation de fetch avec PDOStatement

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=phppdo', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Préparation de la requête
    $sql = 'SELECT name, email FROM users WHERE name LIKE :search';
    $pdoStatement = $pdo->prepare($sql);

    // Recherche d'un nom commençant par "D'"
    $search = "D'%";
    $pdoStatement->bindValue(':search', $search, PDO::PARAM_STR);

    // Exécution de la requête
    if ($pdoStatement->execute()) {
        // Parcours des résultats avec fetch
        while ($user = $pdoStatement->fetch(PDO::FETCH_ASSOC)) {
            echo $user['name'] . ' ' . $user['email'] . '<br>';
        }
    } else {
        echo 'Erreur dans l\'exécution de la requête.';
        var_dump($pdoStatement->errorInfo());
    }

} catch (PDOException $e) {
    echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
}






