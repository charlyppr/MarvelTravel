<?php
require_once('session.php');

// Rediriger l'utilisateur vers la connexion s'il n'est pas connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à votre panier.";
    $_SESSION['current_url'] = current_url();
    header("Location: connexion.php");
    exit;
}

// Charger le panier depuis le JSON
$panierJson = file_get_contents('../json/panier.json');
$panier = json_decode($panierJson, true);

// Si le panier n'est pas dans le bon format, l'initialiser
if (!isset($panier) || !is_array($panier)) {
    $panier = [];
}

// Récupérer l'email de l'utilisateur connecté
$userEmail = $_SESSION['email'];

// Initialiser le panier de l'utilisateur s'il n'existe pas
if (!isset($panier[$userEmail])) {
    $panier[$userEmail] = [
        'items' => []
    ];
}

// Supprimer un voyage du panier
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($panier[$userEmail]['items'][$index])) {
        array_splice($panier[$userEmail]['items'], $index, 1);
        file_put_contents('../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    }
    header('Location: panier.php');
    exit;
}

// Vider le panier
if (isset($_GET['empty'])) {
    $panier[$userEmail]['items'] = [];
    file_put_contents('../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    header('Location: panier.php');
    exit;
}

// Utiliser seulement le panier de l'utilisateur courant pour la suite du script
$userPanier = $panier[$userEmail];

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
                <?php if(count($userPanier['items']) > 0): ?>
                <a href="panier.php?empty=1" class="empty-cart-btn">
                    <img src="../img/svg/trash.svg" alt="Vider">
                    Vider le panier
                </a>
                <?php endif; ?>
            </div>
            
            <div class="card-content">
                <?php if(count($userPanier['items']) === 0): ?>
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
                        foreach($userPanier['items'] as $index => $item): 
                            // Trouver le voyage correspondant
                            $voyage = null;
                            if (isset($voyages[$item['voyage_id']])) {
                                $voyage = $voyages[$item['voyage_id']];
                                // Calculer le prix de base + options
                                $prix_item = $voyage['prix'] * $item['nb_personnes'] + ($item['prix_options'] ?? 0);
                                
                                // Appliquer la réduction si un code promo est présent
                                if (isset($item['reduction'])) {
                                    $prix_item = $prix_item - $item['reduction'];
                                }
                                
                                $total += $prix_item;
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
                                        <span class="info-value">
                                            <?php 
                                            if (isset($item['date_debut']) && isset($item['date_fin'])) {
                                                echo date('d/m/Y', strtotime($item['date_debut'])) . ' au ' . date('d/m/Y', strtotime($item['date_fin'])); 
                                            } else {
                                                echo $item['date']; // Pour compatibilité avec ancien format
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">Voyageurs:</span>
                                        <span class="info-value">
                                            <?php 
                                            if (isset($item['voyageurs']) && !empty($item['voyageurs'])) {
                                                foreach ($item['voyageurs'] as $i => $voyageur) {
                                                    echo $voyageur['prenom'] . ' ' . $voyageur['nom'];
                                                    if ($i < count($item['voyageurs']) - 1) echo ', ';
                                                }
                                            } else {
                                                echo $item['nb_personnes'] . ' personne(s)';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">Prix base:</span>
                                        <span class="info-value"><?php echo number_format($voyage['prix'] * $item['nb_personnes'], 2, ',', ' '); ?> €</span>
                                    </div>
                                    
                                    <?php if (isset($item['prix_options']) && $item['prix_options'] > 0): ?>
                                    <div class="info-row">
                                        <span class="info-label">Options:</span>
                                        <span class="info-value"><?php echo number_format($item['prix_options'], 2, ',', ' '); ?> €</span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($item['reduction']) && $item['reduction'] > 0): ?>
                                    <div class="info-row reduction-row">
                                        <span class="info-label">Réduction:</span>
                                        <span class="info-value">-<?php echo number_format($item['reduction'], 2, ',', ' '); ?> €</span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-row total-row">
                                        <span class="info-label">Total:</span>
                                        <span class="info-value">
                                            <?php 
                                            $total_item = $voyage['prix'] * $item['nb_personnes'] + ($item['prix_options'] ?? 0);
                                            if (isset($item['reduction'])) {
                                                $total_item -= $item['reduction'];
                                            }
                                            echo number_format($total_item, 2, ',', ' '); 
                                            ?> €
                                        </span>
                                    </div>
                                    
                                    <?php if (isset($item['etape_atteinte'])): ?>
                                    <div class="info-row progress-row">
                                        <span class="info-label">Progression:</span>
                                        <span class="info-value etape-<?php echo $item['etape_atteinte']; ?>">
                                            Étape <?php echo $item['etape_atteinte']; ?> sur 4
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="item-actions">
                                <?php
                                // Déterminer la bonne URL en fonction de l'étape atteinte
                                $etape_url = 'etapes/etape1.php'; // URL par défaut
                                if (isset($item['etape_atteinte'])) {
                                    switch ($item['etape_atteinte']) {
                                        case 2:
                                            $etape_url = 'etapes/etape2.php';
                                            break;
                                        case 3:
                                            $etape_url = 'etapes/etape3.php';
                                            break;
                                        case 4:
                                            $etape_url = 'etapes/etape4.php';
                                            break;
                                        default:
                                            $etape_url = 'etapes/etape1.php';
                                    }
                                }
                                ?>
                                <a href="<?php echo $etape_url; ?>?id=<?php echo $item['voyage_id']; ?>&from_cart=1&cart_index=<?php echo $index; ?><?php echo isset($item['reduction']) ? '&promo_code=MARVEL10' : ''; ?>" class="continue-btn">
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
</body>
</html>