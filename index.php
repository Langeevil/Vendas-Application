<?php
require_once __DIR__ . '/api_functions.php';

$entities = all_entity_configs();
$stats = [];
$apiOnline = true;
$requests = [];

foreach ($entities as $entityKey => $config) {
    $requests[$entityKey] = [
        'method' => 'GET',
        'path' => $config['endpoint'],
    ];
}

$responses = call_api_many($requests);

foreach ($entities as $entityKey => $config) {
    $response = $responses[$entityKey] ?? ['ok' => false, 'body' => []];
    if (!$response['ok']) {
        $apiOnline = false;
    }

    $records = is_array($response['body']) ? $response['body'] : [];
    $stats[$entityKey] = count($records);
}

$totalEntities = count($entities);
$totalRecords = array_sum($stats);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatec 2026 | Painel da API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="shell">
        <aside class="sidebar">
            <a href="index.php" class="brand">Fatec<span>2026</span></a>
            <p class="sidebar-copy">Homescreen da aplicacao PHP para navegar pelos recursos da API.</p>
            <nav class="menu">
                <?php foreach ($entities as $entityKey => $entity): ?>
                    <a class="menu-link" href="pages/<?= e($entityKey) ?>.php">
                        <span><?= e($entity['label']) ?></span>
                        <small><?= e($entity['summary']) ?></small>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="content">
            <header class="hero">
                <div>
                    <p class="eyebrow">Aplicacao PHP</p>
                    <h1>Uma Aplicação para teste da API Vendas.</h1>
                    <p class="hero-copy">Esta aplicação foi feita para consumir todas as entidades criadas na API Vendas, permitindo que seja testada e validada conforme o uso.</p>
                </div>
                <div class="hero-actions">
                    <a class="primary-button" href="pages/clientes.php">Abrir clientes</a>
                    <a class="ghost-button" href="pages/produtos.php">Abrir produtos</a>
                </div>
            </header>

            <section class="stats-grid">
                <article class="stat-card">
                    <p class="stat-copy">Paginas disponiveis</p>
                    <strong><?= e($totalEntities) ?></strong>
                    <span class="stat-copy">Uma pagina para cada entidade da API.</span>
                </article>
                <article class="stat-card">
                    <p class="stat-copy">Registros carregados</p>
                    <strong><?= e($totalRecords) ?></strong>
                    <span class="stat-copy">Soma das listagens consultadas no painel.</span>
                </article>
                <article class="stat-card">
                    <p class="stat-copy">Status da API</p>
                    <strong><?= $apiOnline ? 'Online' : 'Parcial' ?></strong>
                    <span class="stat-copy"><?= $apiOnline ? 'Todos os recursos responderam.' : 'Alguns recursos nao responderam corretamente.' ?></span>
                </article>
            </section>

            <section class="dashboard-grid">
                <?php foreach ($entities as $entityKey => $entity): ?>
                    <article class="entity-card">
                        <div class="card-top">
                            <div>
                                <span class="tag"><?= e($entity['singular']) ?></span>
                                <h2><?= e($entity['label']) ?></h2>
                            </div>
                            <strong><?= e($stats[$entityKey]) ?></strong>
                        </div>
                        <p class="card-copy"><?= e($entity['summary']) ?></p>
                        <footer>
                            <a class="card-link" href="pages/<?= e($entityKey) ?>.php">Abrir pagina</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
</body>

</html>