<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';


    echo "<h2>Informations soumises :</h2>";
    echo "<p><strong>Email :</strong> " . $email . "</p>";
    echo "<p><strong>Mot de passe :</strong> " . $password . "</p>";
} else {
    echo "Aucune donnée soumise.";
}
?>