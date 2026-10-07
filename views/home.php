<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($siteDescription) ?>">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($siteTitle) ?></h1>
        <p><?= htmlspecialchars($siteDescription) ?></p>
        <p>The application is running successfully!</p>
    </div>
</body>
</html>
