<?php
$voyages = [
    [
        "id" => 1,
        "titre" => "New York - La ville qui ne dort jamais",
        "image" => "../img/destinations/newyork.jpg",
        "prix" => 399.95,
        "resume" => "Plongez au cœur de Manhattan, visitez la Statue de la Liberté et explorez Central Park.",
        "dates" => [
            "debut" => "2025-06-15",
            "fin" => "2025-06-22",
            "duree" => "7 jours"
        ],
        "etapes" => [
            ["lieu" => "Times Square", "duree" => "1 jour", "options" => ["Visite guidée", "Shopping"]],
            ["lieu" => "Statue de la Liberté", "duree" => "1 jour", "options" => ["Traversée en ferry"]],
            ["lieu" => "Central Park", "duree" => "2 jours", "options" => ["Balade à vélo", "Pique-nique"]]
        ]
    ],
    [
        "id" => 2,
        "titre" => "Wakanda - Le royaume technologique",
        "image" => "../img/destinations/wakanda.jpg",
        "prix" => 5099.95,
        "resume" => "Explorez le pays le plus avancé technologiquement du monde et rencontrez la royauté Wakandaise.",
        "dates" => [
            "debut" => "2025-07-10",
            "fin" => "2025-07-17",
            "duree" => "7 jours"
        ],
        "etapes" => [
            ["lieu" => "Golden City", "duree" => "2 jours", "options" => ["Musée Wakandais", "Marché tribal"]],
            ["lieu" => "Mont Bashenga", "duree" => "2 jours", "options" => ["Randonnée", "Visite du temple"]],
            ["lieu" => "Lac de Vibranium", "duree" => "1 jour", "options" => ["Plongée sous-marine"]]
        ]
    ],
    [
        "id" => 3,
        "titre" => "Hala - Voyage intergalactique",
        "image" => "../img/destinations/hala.jpg",
        "prix" => 1199.95,
        "resume" => "Embarquez pour une expédition sur la planète des Krees et découvrez leur culture.",
        "dates" => [
            "debut" => "2025-08-05",
            "fin" => "2025-08-12",
            "duree" => "7 jours"
        ],
        "etapes" => [
            ["lieu" => "Capitale Kree", "duree" => "2 jours", "options" => ["Visite du palais impérial"]],
            ["lieu" => "Arène de combat", "duree" => "1 jour", "options" => ["Spectacle de gladiateurs"]],
            ["lieu" => "Observatoire stellaire", "duree" => "2 jours", "options" => ["Nuit sous les étoiles"]]
        ]
    ],
    [
        "id" => 4,
        "titre" => "Wandar - Le mystère de l'ancien monde",
        "image" => "../img/destinations/wandar.png",
        "prix" => 999.95,
        "resume" => "Découvrez la mystérieuse cité de Wandar et ses secrets anciens.",
        "dates" => [
            "debut" => "2025-09-01",
            "fin" => "2025-09-07",
            "duree" => "6 jours"
        ],
        "etapes" => [
            ["lieu" => "Temple perdu", "duree" => "2 jours", "options" => ["Exploration guidée"]],
            ["lieu" => "Bibliothèque antique", "duree" => "1 jour", "options" => ["Lecture des manuscrits anciens"]],
            ["lieu" => "Gorges du Vent", "duree" => "2 jours", "options" => ["Randonnée extrême"]]
        ]
    ]
];
?>