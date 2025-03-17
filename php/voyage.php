<?php
// Vérifier si un ID est fourni dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h1>Erreur 🚨</h1><p>ID de voyage manquant ! <a href='destination.php'>Retour</a></p>");
}

$id = (int) $_GET['id'];
$filename = "../json/$id.json";

// Vérifier si le fichier JSON du voyage existe
if (!file_exists($filename)) {
    die("<h1>Erreur 404 🚀</h1><p>Ce voyage n'existe pas encore dans notre base.</p><p><a href='destination.php'>Retour aux destinations</a></p>");
}

// Charger et afficher les infos du voyage
$voyage = json_decode(file_get_contents($filename), true);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../css/base.css">
</head>

<body>
    <h1><?php echo htmlspecialchars($voyage['titre']); ?></h1>
    <img src="<?php echo htmlspecialchars($voyage['image']); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>"
        width="50%">
    <p><strong>Prix :</strong> <?php echo number_format($voyage['prix'], 2, ',', ' ') . "€"; ?></p>
    <p><strong>Résumé :</strong> <?php echo htmlspecialchars($voyage['resume']); ?></p>
    <h2>📍 Étapes du voyage</h2>
    <ul>
        <?php foreach ($voyage['etapes'] as $etape): ?>
            <li>
                <strong><?php echo htmlspecialchars($etape['lieu']); ?></strong> (<?php echo $etape['duree']; ?>)
                <br>
                Options : <?php echo implode(", ", $etape['options']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="destination.php">⬅ Retour aux voyages</a>
</body>

</html>