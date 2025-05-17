<?php
// Démarrer la session si ce n'est pas déjà fait
session_start();

// Inclure le fichier session.php pour accéder à la fonction load_user_theme
require_once 'session.php';

$page_title = "Création de destination";
$message = "";
$message_type = "";

// Vérifier si l'utilisateur est connecté et est administrateur
if ($_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas admin
    header('Location: connexion.php');
    exit;
}

// Traiter le formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = trim($_POST['titre'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $disponibilite = trim($_POST['disponibilite'] ?? '');
    $resume = trim($_POST['resume'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $difficulte = trim($_POST['difficulte'] ?? '');
    $famille = isset($_POST['famille']) ? true : false;
    $type_localisation = trim($_POST['type_localisation'] ?? '');
    
    // Gestion des tableaux
    $categories = isset($_POST['categories']) ? explode(',', $_POST['categories']) : [];
    $langues = isset($_POST['langues']) ? explode(',', $_POST['langues']) : [];
    $inclus = isset($_POST['inclus']) ? explode(',', $_POST['inclus']) : [];
    $non_inclus = isset($_POST['non_inclus']) ? explode(',', $_POST['non_inclus']) : [];
    $highlights = isset($_POST['highlights']) ? explode(',', $_POST['highlights']) : [];
    
    // Gestion de l'image
    $upload_success = false;
    $image_path = "";
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $target_file = "../img/destinations/" . basename($filename);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $upload_success = true;
                $image_path = "../img/destinations/" . $filename;
            }
        }
    }

    // Gestion des images de la galerie
    $gallery_images = [];
    if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
        $file_count = count($_FILES['gallery_images']['name']);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Limiter à 5 images
        $file_count = min($file_count, 5);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['gallery_images']['error'][$i] === 0) {
                $filename = $_FILES['gallery_images']['name'][$i];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $target_file = "../img/destinations-more/" . basename($filename);
                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $target_file)) {
                        $gallery_images[] = "../img/destinations-more/" . $filename;
                    }
                }
            }
        }
    }

    // Validation de base
    if ($titre && $prix > 0 && $disponibilite && $resume && $description && $difficulte && $type_localisation) {
        if (!$upload_success && empty($image_path)) {
            $message = "Veuillez télécharger une image valide";
            $message_type = "error";
        } else {
            // Lire le fichier JSON existant
            $json_file = "../json/voyages.json";
            $destinations = [];
            if (file_exists($json_file)) {
                $json_data = file_get_contents($json_file);
                $destinations = json_decode($json_data, true) ?? [];
            }
            
            // Déterminer le prochain ID
            $next_id = 0;
            if (!empty($destinations)) {
                $ids = array_column($destinations, 'id');
                $next_id = max($ids) + 1;
            }
            
            // Préparer la nouvelle destination
            $newDestination = [
                'id' => $next_id,
                'titre' => $titre,
                'image' => $image_path,
                'galerie' => $gallery_images,
                'prix' => $prix,
                'prix_initial' => null,
                'promo' => null,
                'disponibilite' => $disponibilite,
                'resume' => $resume,
                'description' => $description,
                'categories' => $categories,
                'rating' => 0,
                'reviews' => 0,
                'highlights' => $highlights,
                'famille' => $famille,
                'difficulte' => $difficulte,
                'langue' => $langues,
                'inclus' => $inclus,
                'non_inclus' => $non_inclus,
                'dates' => [
                    'duree' => intval($_POST['duree_jours'] ?? 7) . ' jours'
                ],
                'localisation' => [
                    'type' => $type_localisation,
                    'coordonnees' => [
                        'lat' => floatval($_POST['lat'] ?? 0),
                        'lng' => floatval($_POST['lng'] ?? 0)
                    ]
                ],
                'univers' => [
                    'films' => isset($_POST['films']) ? explode(',', $_POST['films']) : [],
                    'personnages' => isset($_POST['personnages']) ? explode(',', $_POST['personnages']) : []
                ],
                'temoignages' => [],
                'etapes' => []
            ];
            
            // Ajouter la nouvelle destination
            $destinations[] = $newDestination;
            
            // Sauvegarder dans le fichier JSON
            if (file_put_contents($json_file, json_encode($destinations, JSON_PRETTY_PRINT))) {
                $message = "La destination a été créée avec succès";
                $message_type = "success";
                
                // Réinitialiser les valeurs du formulaire
                $titre = $resume = $description = $disponibilite = $difficulte = $type_localisation = "";
                $prix = 0;
                $categories = $langues = $inclus = $non_inclus = $highlights = [];
                $famille = false;
            } else {
                $message = "Erreur lors de la création de la destination";
                $message_type = "error";
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires";
        $message_type = "error";
    }
}

// Récupérer le thème de la session
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Création de destination</title>
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="stylesheet" href="../css/form-validation.css">
    <link rel="stylesheet" href="../css/admin-new.css">
</head>



<body class="<?php echo $theme; ?>-theme">
    <?php
    include('sidebar.php');
    ?>

    <div class="destination-creation-container">
        <div class="card-content">
            <div class="card-header">
                <h2 class="titre-2">Créer une nouvelle destination</h2>
                <p class="sous-titre-3">Remplissez le formulaire pour ajouter une nouvelle destination à Marvel Travel</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form class="form" action="admin-nouvelle-destination.php" method="post" enctype="multipart/form-data">
                <div class="form-section">
                    <h3 class="form-section-title">Informations principales</h3>
                    
                    <div class="form-row">
                        <input type="text" name="titre" placeholder="Titre de la destination" required 
                            value="<?php echo isset($titre) ? htmlspecialchars($titre) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <input type="file" name="image" id="image" accept="image/*" required>
                        <label for="image" class="file-label">Image principale</label>
                    </div>

                    <div class="form-col">
                        <input type="file" name="gallery_images[]" id="gallery_images" accept="image/*" multiple>
                        <label for="gallery_images" class="file-label">Images pour le carrousel (max 5)</label>
                        <div id="gallery-preview" class="gallery-preview"></div>
                    </div>

                    <div class="form-row">
                        <input type="number" name="prix" placeholder="Prix" step="0.01" min="0" required
                            value="<?php echo isset($prix) ? htmlspecialchars($prix) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <select name="disponibilite" required>
                            <option value="" disabled <?php echo empty($disponibilite) ? 'selected' : ''; ?>>Disponibilité</option>
                            <option value="Disponible" <?php echo isset($disponibilite) && $disponibilite === 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                            <option value="Places limitées" <?php echo isset($disponibilite) && $disponibilite === 'Places limitées' ? 'selected' : ''; ?>>Places limitées</option>
                            <option value="Dernières places" <?php echo isset($disponibilite) && $disponibilite === 'Dernières places' ? 'selected' : ''; ?>>Dernières places</option>
                            <option value="Complet" <?php echo isset($disponibilite) && $disponibilite === 'Complet' ? 'selected' : ''; ?>>Complet</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Description du voyage</h3>
                    
                    <div class="form-row">
                        <textarea name="resume" placeholder="Résumé (court descriptif)" required rows="2"><?php echo isset($resume) ? htmlspecialchars($resume) : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <textarea name="description" placeholder="Description détaillée" required rows="4"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <input type="text" name="categories" placeholder="Catégories (séparées par des virgules)" 
                            value="<?php echo isset($categories) ? htmlspecialchars(implode(',', $categories)) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <input type="text" name="highlights" placeholder="Points forts (séparés par des virgules)" 
                            value="<?php echo isset($highlights) ? htmlspecialchars(implode(',', $highlights)) : ''; ?>">
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Caractéristiques</h3>
                    
                    <div class="form-row">
                        <select name="difficulte" required>
                            <option value="" disabled <?php echo empty($difficulte) ? 'selected' : ''; ?>>Difficulté</option>
                            <option value="Facile" <?php echo isset($difficulte) && $difficulte === 'Facile' ? 'selected' : ''; ?>>Facile</option>
                            <option value="Modérée" <?php echo isset($difficulte) && $difficulte === 'Modérée' ? 'selected' : ''; ?>>Modérée</option>
                            <option value="Difficile" <?php echo isset($difficulte) && $difficulte === 'Difficile' ? 'selected' : ''; ?>>Difficile</option>
                        </select>
                    </div>

                    <div class="checkbox-container">
                        <input type="checkbox" id="famille" name="famille" <?php echo isset($famille) && $famille ? 'checked' : ''; ?>>
                        <label for="famille">Adapté aux familles</label>
                    </div>

                    <div class="form-row">
                        <input type="text" name="langues" placeholder="Langues disponibles (séparées par des virgules)" 
                            value="<?php echo isset($langues) ? htmlspecialchars(implode(',', $langues)) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <input type="text" name="inclus" placeholder="Services inclus (séparés par des virgules)" 
                            value="<?php echo isset($inclus) ? htmlspecialchars(implode(',', $inclus)) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <input type="text" name="non_inclus" placeholder="Services non inclus (séparés par des virgules)" 
                            value="<?php echo isset($non_inclus) ? htmlspecialchars(implode(',', $non_inclus)) : ''; ?>">
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Localisation et durée recommandée</h3>
                    
                    <div class="form-row">
                        <select name="type_localisation" required>
                            <option value="" disabled <?php echo empty($type_localisation) ? 'selected' : ''; ?>>Type de localisation</option>
                            <option value="earth" <?php echo isset($type_localisation) && $type_localisation === 'earth' ? 'selected' : ''; ?>>Terre</option>
                            <option value="space" <?php echo isset($type_localisation) && $type_localisation === 'space' ? 'selected' : ''; ?>>Espace</option>
                            <option value="dimension" <?php echo isset($type_localisation) && $type_localisation === 'dimension' ? 'selected' : ''; ?>>Dimension</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="duree-container">
                            <label for="duree_jours">Durée recommandée du voyage (en jours)</label>
                            <input type="number" id="duree_jours" name="duree_jours" min="2" max="31" value="<?php echo isset($_POST['duree_jours']) ? intval($_POST['duree_jours']) : 7; ?>" required>
                        </div>
                    </div>

                    <div class="coords-container">
                        <div class="coord-input">
                            <label for="lat">Latitude</label>
                            <input type="number" id="lat" name="lat" placeholder="Latitude" step="any" 
                                value="<?php echo isset($_POST['lat']) ? htmlspecialchars($_POST['lat']) : '0'; ?>">
                        </div>
                        <div class="coord-input">
                            <label for="lng">Longitude</label>
                            <input type="number" id="lng" name="lng" placeholder="Longitude" step="any" 
                                value="<?php echo isset($_POST['lng']) ? htmlspecialchars($_POST['lng']) : '0'; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Univers Marvel (optionnel)</h3>
                    
                    <div class="form-row">
                        <input type="text" name="films" placeholder="Films associés (séparés par des virgules)" 
                            value="<?php echo isset($_POST['films']) ? htmlspecialchars($_POST['films']) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <input type="text" name="personnages" placeholder="Personnages associés (séparés par des virgules)" 
                            value="<?php echo isset($_POST['personnages']) ? htmlspecialchars($_POST['personnages']) : ''; ?>">
                    </div>
                </div>

                <button class="next-button" type="submit">Créer la destination<img src="../img/svg/fleche-droite.svg" alt="fleche"></button>
            </form>
        </div>
    </div>

    <script src="../js/password-toggle.js"></script>
    <script src="../js/form-validation.js"></script>
    <script src="../js/admin-destination.js"></script>
</body>
</html>