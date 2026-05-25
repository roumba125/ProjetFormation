
<?php

return [
    'admin_email' => env('BERGERPRO_ADMIN_EMAIL', 'admin@bergerpro.com'),
    'farm_name'   => env('BERGERPRO_FARM_NAME', 'BergerPro Élevage'),
    'farm_phone'  => env('BERGERPRO_FARM_PHONE', ''),
    'farm_city'   => env('BERGERPRO_FARM_CITY', ''),

    // Frais de livraison par ville (en FCFA)
    'delivery_fees' => [
        'abidjan'      => 5000,
        'bouaké'       => 15000,
        'yamoussoukro' => 10000,
        'daloa'        => 18000,
        'korhogo'      => 20000,
    ],

    // Dépôt minimum accepté (% du total)
    'min_deposit_percent' => 30,

    // Nombre de photos max par mouton
    'max_photos_per_sheep' => 8,

    // Taille max photo en KB
    'max_photo_size_kb' => 5120,
];
