<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/base.css">
    <title>paiement</title>
</head>
<body>
    <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' class="carte">
        <input type="text" name="carte" placeholder="Numero de carte">
        <input type="text" name="date" placeholder="Date d'expiration">
        <input type="text" name="crypto" placeholder="Cryptogramme">
        <input type='hidden' name='transaction' value='154632ABCD'>
        <input type='hidden' name='montant' value='18000.99'>
        <input type='hidden' name='vendeur' value='TEST'>
        <input type='hidden' name='retour' value='http://localhost/retour_paiement.php?session=s'>
        <input type='hidden' name='control' value='01c06955b2d4ad0ccdedd4aad0ab68bf'>
        <input type='submit' value="Valider et payer">
    </form>
</body>
</html>