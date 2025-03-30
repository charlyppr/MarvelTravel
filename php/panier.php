<?php
require_once('session.php');

// Charger le panier depuis le JSON
$panierJson = file_get_contents('../json/panier.json');
$panier = json_decode($panierJson, true);

// Supprimer un voyage du panier
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($panier['items'][$index])) {
        array_splice($panier['items'], $index, 1);
        file_put_contents('../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    }
    header('Location: panier.php');
    exit;
}

// Vider le panier
if (isset($_GET['empty'])) {
    $panier['items'] = [];
    file_put_contents('../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    header('Location: panier.php');
    exit;
}

// Charger les voyages
$voyagesJson = file_get_contents('../json/voyages.json');
$voyages = json_decode($voyagesJson, true);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier • Marvel Travel</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/panier.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>
<body>
    <div class="default"></div>
    
    <?php include 'nav.php'; ?>
    
    <div class="reservation-container">
        <div class="booking-header">
            <div class="breadcrumb">
                <a href="destination.php" class="breadcrumb-link">
                    <img src="../img/svg/arrow-left.svg" alt="Retour">
                    <span>Destinations</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Mon panier</span>
            </div>
        </div>

        <div class="cart-container card">
            <div class="card-header">
                <div class="header-content">
                    <img src="../img/svg/cart.svg" alt="Panier" class="card-icon">
                    <h2 class="destination-title">Votre panier de voyages</h2>
                </div>
                <?php if(count($panier['items']) > 0): ?>
                <a href="panier.php?empty=1" class="empty-cart-btn">
                    <img src="../img/svg/trash.svg" alt="Vider">
                    Vider le panier
                </a>
                <?php endif; ?>
            </div>
            
            <div class="card-content">
                <?php if(count($panier['items']) === 0): ?>
                    <div class="empty-cart">
                        <img src="../img/svg/empty-cart.svg" alt="Panier vide" class="empty-cart-img">
                        <h2>Votre panier est vide</h2>
                        <p>Explorez nos destinations et ajoutez des voyages à votre panier</p>
                        <a href="destination.php" class="primary-button">
                            Découvrir nos voyages
                            <img src="../img/svg/plane.svg" alt="Découvrir">
                        </a>
                    </div>
                <?php else: ?>
                    <div class="cart-items">
                        <?php 
                        $total = 0;
                        foreach($panier['items'] as $index => $item): 
                            // Trouver le voyage correspondant
                            $voyage = null;
                            if (isset($voyages[$item['voyage_id']])) {
                                $voyage = $voyages[$item['voyage_id']];
                                $total += $voyage['prix'] * $item['nb_personnes'];
                            }
                            
                            if($voyage):
                        ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo $voyage['image']; ?>" alt="<?php echo $voyage['titre']; ?>">
                            </div>
                            <div class="item-details">
                                <h3><?php echo $voyage['titre']; ?></h3>
                                <div class="item-info">
                                    <div class="info-row">
                                        <span class="info-label">Date:</span>
                                        <span class="info-value"><?php echo $item['date']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Personnes:</span>
                                        <span class="info-value"><?php echo $item['nb_personnes']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Prix unitaire:</span>
                                        <span class="info-value"><?php echo number_format($voyage['prix'], 2, ',', ' '); ?> €</span>
                                    </div>
                                    <div class="info-row total-row">
                                        <span class="info-label">Total:</span>
                                        <span class="info-value"><?php echo number_format($voyage['prix'] * $item['nb_personnes'], 2, ',', ' '); ?> €</span>
                                    </div>
                                </div>
                            </div>
                            <div class="item-actions">
                                <a href="etapes/etape1.php?id=<?php echo $voyage['id']; ?>&from_cart=1&cart_index=<?php echo $index; ?>" class="continue-btn">
                                    Continuer
                                    <img src="../img/svg/arrow-right.svg" alt="Continuer">
                                </a>
                                <a href="panier.php?remove=<?php echo $index; ?>" class="remove-btn">
                                    <img src="../img/svg/trash.svg" alt="Supprimer">
                                    Supprimer
                                </a>
                            </div>
                        </div>
                        <?php endif; endforeach; ?>
                    </div>
                    
                    <!-- Ajouter le résumé du total ici -->
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Sous-total</span>
                            <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                        </div>
                        <div class="summary-row">
                            <span>Frais de réservation</span>
                            <span>0,00 €</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="price-total"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="destination.php" class="secondary-button">
                            <img src="../img/svg/arrow-left.svg" alt="Retour">
                            Continuer mes achats
                        </a>
                        <a href="mes-voyages.php" class="primary-button">
                            Voir mes réservations
                            <img src="../img/svg/check.svg" alt="Voir">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>
</html> 