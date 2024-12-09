<?php

// Paramètres de connexion
$host = 'localhost';
$dbname = 'phppdo';
$username = 'root';
$password = '';

try {
    require_once 'user.php';

    // Création de l'objet PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Déterminer la page actuelle
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;

    // Nombre d'utilisateurs par page
    $usersPerPage = 2;

    // Calculer l'offset pour la requête SQL
    $offset = ($page - 1) * $usersPerPage;

    // Requête pour obtenir les utilisateurs de la page actuelle
    $sql = 'SELECT users.name FROM users LIMIT :start, :usersPerPage';
    $pdoStatement = $pdo->prepare($sql);
    $pdoStatement->bindValue(':start', $offset, PDO::PARAM_INT);
    $pdoStatement->bindValue(':usersPerPage', $usersPerPage, PDO::PARAM_INT);

    if ($pdoStatement->execute()) {
        // Requête réussie, affichage des utilisateurs
        while ($user = $pdoStatement->fetchObject('user')) {
            echo $user->getDisplayedName() . "<br>";
        }
    } else {
        // Erreur lors de l'exécution de la requête
        echo 'Une erreur est survenue lors de la récupération des utilisateurs.';
    }

    // Déterminer le nombre total d'utilisateurs
    $sqlCount = 'SELECT COUNT(*) AS total_users FROM users';
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute();
    $result = $stmtCount->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $result['total_users'];

    // Calculer le nombre total de pages
    $totalPages = ceil($totalUsers / $usersPerPage);

    // Générer les liens de pagination
    echo "<div class='pagination'>";
    for ($i = 1; $i <= $totalPages; $i++) {
        $class = ($i == $page) ? 'active' : '';
        echo "<a class='$class' href='?page=$i'>Page $i</a> ";
    }
    echo "</div>";
} catch (PDOException $e) {
    echo 'Impossible de se connecter à la base de données : ' . $e->getMessage();
}
?>
