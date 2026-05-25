<!-- ============================================================
  resources/views/admin.blade.php
  Vue distincte pour le sous-domaine admin.bergerpro.test
============================================================ -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BergerPro — Admin</title>
    @vite('resources/js/admin.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
