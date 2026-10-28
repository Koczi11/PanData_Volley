<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; min-height: 100vh;">
        <h1>Strona główna</h1>
        <p>Witaj w PanData Volley!</p>
        <div style="display: flex; gap: 1rem;">
            <a href="/projects">Zobacz projekty</a>
            <a href="/login">Logowanie</a>
        </div>
    </main>
</body>
</html>
