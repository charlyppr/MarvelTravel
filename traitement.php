<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification de l'existence des clés dans $_POST
    $nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

    // Affichage des informations soumises
    echo "<h2>Informations soumises :</h2>";
    echo "<p><strong>Nom :</strong> " . $nom . "</p>";
    echo "<p><strong>Email :</strong> " . $email . "</p>";
    echo "<p><strong>Message :</strong> " . $message . "</p>";
} else {
    echo "Aucune donnée soumise.";
}
?>