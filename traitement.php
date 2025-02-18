<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Affichage des informations soumises
    echo "<h2>Informations soumises :</h2>";
    echo "<p><strong>Nom :</strong> " . $nom . "</p>";
    echo "<p><strong>Email :</strong> " . $email . "</p>";
    echo "<p><strong>Message :</strong> " . $message . "</p>";
} else {
    echo "Aucune donnée soumise.";
}
?>