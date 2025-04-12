<?php
// Initialiser la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Récupérer l'ID du voyage depuis l'URL
$voyage_id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Charger les données des voyages
$json_file = '../json/voyages.json';
if (!file_exists($json_file)) {
    header('Location: destination.php');
    exit;
}

$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier si le voyage existe
if ($voyage_id < 0 || $voyage_id >= count($voyages)) {
    header('Location: destination.php');
    exit;
}

// Récupérer les détails du voyage
$voyage = $voyages[$voyage_id];

// Vérifier si les clés requises existent
$voyage['max_personnes'] = isset($voyage['max_personnes']) ? $voyage['max_personnes'] : 10;
$voyage['langue'] = isset($voyage['langue']) ? $voyage['langue'] : ['Français'];
$voyage['activites'] = isset($voyage['activites']) ? $voyage['activites'] : [];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • <?php echo htmlspecialchars($voyage['titre']); ?></title>

    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/destination.css">
    <link rel="stylesheet" href="../css/voyage-detail.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <!-- En-tête du voyage avec image de fond -->
    <section class="hero-section">
        <div class="hero-backdrop" style="background-image: url('<?php echo htmlspecialchars($voyage['image']); ?>')">
        </div>
        <div class="hero-container">
            <div class="hero-content">
                <div class="breadcrumb">
                    <a href="destination.php">Destinations</a>
                    <img src="../img/svg/chevron-right.svg" alt="›">
                    <span><?php echo htmlspecialchars($voyage['titre']); ?></span>
                </div>

                <h1 class="hero-title"><?php echo htmlspecialchars($voyage['titre']); ?></h1>
                <p class="hero-subtitle"><?php echo htmlspecialchars($voyage['resume']); ?></p>

                <div class="voyage-meta">
                    <?php if (isset($voyage['rating'])): ?>
                        <div class="meta-rating">
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($voyage['rating'])): ?>
                                        <img src="../img/svg/star.svg" alt="★" class="star">
                                    <?php elseif ($i - 0.5 <= $voyage['rating']): ?>
                                        <img src="../img/svg/star-half.svg" alt="½" class="star">
                                    <?php else: ?>
                                        <img src="../img/svg/star-empty.svg" alt="☆" class="star">
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value"><?php echo $voyage['rating']; ?></span>
                            <span class="review-count"><?php echo $voyage['reviews']; ?> avis</span>
                        </div>
                    <?php endif; ?>

                    <div class="meta-categories">
                        <?php foreach ($voyage['categories'] as $categorie): ?>
                            <span class="category-tag"><?php echo htmlspecialchars($categorie); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="quick-info">
                    <div class="info-item">
                        <img src="../img/svg/calendar.svg" alt="Durée">
                        <div class="info-content">
                            <span class="info-label">Durée</span>
                            <span class="info-value"><?php echo $voyage['dates']['duree']; ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <img src="../img/svg/difficulty.svg" alt="Difficulté">
                        <div class="info-content">
                            <span class="info-label">Difficulté</span>
                            <span class="info-value"><?php echo htmlspecialchars($voyage['difficulte']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <img src="../img/svg/tag.svg" alt="Prix">
                        <div class="info-content">
                            <span class="info-label">À partir de</span>
                            <span
                                class="info-value price"><?php echo number_format($voyage['prix'], 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="main-content">
        <div class="content-wrapper">
            <!-- Colonne principale -->
            <div class="main-column">
                <!-- Section Aperçu -->
                <section id="apercu" class="content-section">
                    <div class="description-section">
                        <h2>À propos de ce voyage</h2>
                        <p class="description-text"><?php echo nl2br(htmlspecialchars($voyage['description'])); ?></p>

                        <div class="highlights-container">
                            <h3>Points forts du voyage</h3>
                            <div class="highlights-grid">
                                <?php foreach ($voyage['highlights'] as $highlight): ?>
                                    <div class="highlight-item">
                                        <img src="../img/svg/check-circle.svg" alt="✓">
                                        <span><?php echo htmlspecialchars($highlight); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($voyage['univers']) && !empty($voyage['univers']['films'])): ?>
                        <div class="universe-section">
                            <h2>L'univers Marvel</h2>
                            <div class="universe-content">
                                <?php if (isset($voyage['univers']['image'])): ?>
                                    <div class="universe-media">
                                        <img src="<?php echo htmlspecialchars($voyage['univers']['image']); ?>"
                                            alt="Univers Marvel" class="universe-image">
                                    </div>
                                <?php endif; ?>
                                <div class="universe-details">
                                    <div class="films-section">
                                        <h3>Films associés</h3>
                                        <div class="films-grid">
                                            <?php foreach ($voyage['univers']['films'] as $film): ?>
                                                <span class="film-tag"><?php echo htmlspecialchars($film); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php if (isset($voyage['univers']['personnages']) && is_array($voyage['univers']['personnages'])): ?>
                                        <div class="characters-section">
                                            <h3>Personnages principaux</h3>
                                            <div class="characters-grid">
                                                <?php
                                                // Handle both array formats
                                                foreach ($voyage['univers']['personnages'] as $key => $personnage):
                                                    if (is_array($personnage)):
                                                        ?>
                                                        <div class="character-card">
                                                            <img src="<?php echo htmlspecialchars($personnage['image']); ?>"
                                                                alt="<?php echo htmlspecialchars($personnage['nom']); ?>">
                                                            <span><?php echo htmlspecialchars($personnage['nom']); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="character-card">
                                                            <span><?php echo htmlspecialchars($personnage); ?></span>
                                                        </div>
                                                    <?php endif; endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Section Programme -->
                <section id="programme" class="content-section">
                    <h2>Programme jour par jour</h2>
                    <div class="programme-timeline">
                        <?php foreach ($voyage['etapes'] as $index => $etape): ?>
                            <div class="timeline-card">
                                <div class="timeline-header">
                                    <span class="day-badge">Jour <?php echo $index + 1; ?></span>
                                    <h3><?php echo htmlspecialchars($etape['lieu']); ?></h3>
                                </div>
                                <div class="timeline-content">
                                    <?php if (isset($etape['image'])): ?>
                                        <div class="timeline-media">
                                            <img src="<?php echo htmlspecialchars($etape['image']); ?>"
                                                alt="<?php echo htmlspecialchars($etape['lieu']); ?>">
                                        </div>
                                    <?php endif; ?>

                                    <div class="timeline-details">
                                        <div class="time-info">
                                            <img src="../img/svg/clock.svg" alt="Durée">
                                            <span><?php echo htmlspecialchars($etape['duree']); ?></span>
                                        </div>
                                        <p class="timeline-description">
                                            <?php echo nl2br(htmlspecialchars($etape['description'])); ?>
                                        </p>

                                        <?php if (!empty($etape['activites'])): ?>
                                            <div class="activities-list">
                                                <h4>Au programme</h4>
                                                <ul>
                                                    <?php foreach ($etape['activites'] as $activite): ?>
                                                        <li><?php echo htmlspecialchars($activite); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($etape['options'])): ?>
                                            <div class="options-list">
                                                <h4>Options disponibles</h4>
                                                <?php foreach ($etape['options'] as $option): ?>
                                                    <div class="option-item">
                                                        <div class="option-info">
                                                            <h5><?php echo htmlspecialchars($option['nom']); ?></h5>
                                                            <p><?php echo htmlspecialchars($option['description']); ?></p>
                                                        </div>
                                                        <div class="option-price">
                                                            +<?php echo number_format($option['prix'], 2, ',', ' '); ?>€
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Section Détails -->
                <section id="details" class="content-section">
                    <h2>Détails pratiques</h2>

                    <div class="details-container">
                        <div class="details-tabs">
                            <button class="tab-button active" data-tab="inclus">Ce qui est inclus</button>
                            <button class="tab-button" data-tab="non-inclus">Non inclus</button>
                        </div>

                        <div class="details-content">
                            <div class="tab-content active" id="inclus-content">
                                <div class="details-grid">
                                    <?php foreach (array_chunk($voyage['inclus'], ceil(count($voyage['inclus']) / 2)) as $items): ?>
                                        <div class="details-column">
                                            <ul class="details-list included">
                                                <?php foreach ($items as $item): ?>
                                                    <li>
                                                        <img src="../img/svg/check.svg" alt="✓" class="item-icon included">
                                                        <span><?php echo htmlspecialchars($item); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="tab-content" id="non-inclus-content">
                                <div class="details-grid">
                                    <?php foreach (array_chunk($voyage['non_inclus'], ceil(count($voyage['non_inclus']) / 2)) as $items): ?>
                                        <div class="details-column">
                                            <ul class="details-list not-included">
                                                <?php foreach ($items as $item): ?>
                                                    <li>
                                                        <img src="../img/svg/x.svg" alt="✕" class="item-icon not-included">
                                                        <span><?php echo htmlspecialchars($item); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Avis -->
                <?php if (!empty($voyage['temoignages'])): ?>
                    <section id="avis" class="content-section">
                        <div class="reviews-header">
                            <h2>Avis des voyageurs</h2>
                            <?php if (isset($voyage['rating'])): ?>
                                <div class="meta-rating">
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= floor($voyage['rating'])): ?>
                                                <img src="../img/svg/star.svg" alt="★" class="star">
                                            <?php elseif ($i - 0.5 <= $voyage['rating']): ?>
                                                <img src="../img/svg/star-half.svg" alt="½" class="star">
                                            <?php else: ?>
                                                <img src="../img/svg/star-empty.svg" alt="☆" class="star">
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-value"><?php echo $voyage['rating']; ?></span>
                                    <span class="review-count"><?php echo $voyage['reviews']; ?> avis</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="reviews-grid">
                            <?php foreach ($voyage['temoignages'] as $temoignage): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <img src="<?php echo htmlspecialchars($temoignage['photo']); ?>"
                                            alt="<?php echo htmlspecialchars($temoignage['auteur']); ?>"
                                            class="reviewer-avatar">
                                        <div class="reviewer-info">
                                            <h4><?php echo htmlspecialchars($temoignage['auteur']); ?></h4>
                                            <?php if (isset($temoignage['date'])): ?>
                                                <span
                                                    class="review-date"><?php echo htmlspecialchars($temoignage['date']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <img src="../img/svg/star<?php echo $i <= $temoignage['note'] ? '' : '-empty'; ?>.svg"
                                                alt="<?php echo $i <= $temoignage['note'] ? '★' : '☆'; ?>">
                                        <?php endfor; ?>
                                    </div>
                                    <p class="review-text"><?php echo htmlspecialchars($temoignage['texte']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <!-- Colonne de réservation -->
            <div class="booking-column" id="reservation">
                <a href="destination.php" class="back-button">
                    <img src="../img/svg/arrow-left.svg" alt="←">
                    Retour aux destinations
                </a>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-price">
                            <span
                                class="price-amount"><?php echo number_format($voyage['prix'], 2, ',', ' '); ?>€</span>
                            <span class="price-per">par personne</span>
                        </div>
                        <?php if (isset($voyage['disponibilite'])): ?>
                            <div
                                class="availability-badge <?php echo strtolower(str_replace(' ', '-', $voyage['disponibilite'])); ?>">
                                <?php echo htmlspecialchars($voyage['disponibilite']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="booking-dates">
                        <div class="date-row">
                            <div class="date-item">
                                <label>Départ</label>
                                <span class="date"><?php echo $voyage['dates']['debut']; ?></span>
                            </div>
                            <div class="date-item">
                                <label>Retour</label>
                                <span class="date"><?php echo $voyage['dates']['fin']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="booking-features">
                        <div class="feature-item">
                            <img src="../img/svg/users.svg" alt="Groupe">
                            <span><?php echo $voyage['max_personnes']; ?> personnes max</span>
                        </div>
                        <div class="feature-item">
                            <img src="../img/svg/calendar.svg" alt="Durée">
                            <span><?php echo $voyage['dates']['duree']; ?></span>
                        </div>
                    </div>

                    <a href="etapes/etape1.php?id=<?php echo $voyage_id; ?>" class="btn-book">
                        Réserver maintenant
                    </a>

                    <div class="booking-note">
                        <img src="../img/svg/info.svg" alt="Info">
                        <p>Réservation sans frais d'annulation jusqu'à 30 jours avant le départ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Ajoutez ce code à la section script existante
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-content').classList.add('active');
                });
            });
        });
    </script>

    <script src="../js/nav.js"></script>
</body>

</html>