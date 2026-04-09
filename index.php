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
<html lang="pt-BR" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatec 2026 | Painel da API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        (() => {
            const stored = localStorage.getItem('theme');
            if (stored) {
                document.documentElement.setAttribute('data-theme', stored);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="sidebar-top">
                <a href="index.php" class="brand">Fatec<span>2026</span></a>
                <div class="sidebar-summary">
                    <strong>API Vendas</strong>
                </div>
            </div>
            <nav class="menu">
                <?php foreach ($entities as $entityKey => $entity): ?>
                    <a class="menu-link" href="pages/<?= e($entityKey) ?>.php">
                        <span><?= e($entity['label']) ?></span>
                        <small><?= e($entity['summary']) ?></small>
                    </a>
                <?php endforeach; ?>
            </nav>
            <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Alternar tema">
                <svg class="theme-icon-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <svg class="theme-icon-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </aside>

        <main class="content">
            <header class="hero">
                <div class="hero-main">
                    <p class="eyebrow">Bem-vindo ao painel</p>
                    <h1>API Vendas</h1>
                </div>
                <div class="hero-side">
                    <div class="hero-metrics">
                        <div class="hero-metric">
                            <span class="hero-meta-label">Recursos</span>
                            <strong><?= e($totalEntities) ?></strong>
                        </div>
                        <div class="hero-metric">
                            <span class="hero-meta-label">Registros</span>
                            <strong><?= e($totalRecords) ?></strong>
                        </div>
                    </div>
                </div>
            </header>

            <section class="dashboard-overview">
                <div class="overview-grid">
                    <article class="stat-card stat-primary">
                        <div class="stat-header">
                            <p class="stat-copy">Status da API</p>
                            <span class="stat-badge <?= $apiOnline ? 'online' : 'offline' ?>"></span>
                        </div>
                        <strong><?= $apiOnline ? 'Online' : 'Offline' ?></strong>
                    </article>
                    <article class="stat-card">
                        <p class="stat-copy">Recursos disponíveis</p>
                        <strong><?= e($totalEntities) ?></strong>
                        <span class="stat-subtext">entidades cadastradas</span>
                    </article>
                    <article class="stat-card">
                        <p class="stat-copy">Total de registros</p>
                        <strong><?= e($totalRecords) ?></strong>
                        <span class="stat-subtext">dados carregados</span>
                    </article>
                </div>
            </section>

            <section class="insights-section">
                <div class="section-header">
                    <div>
                        <p class="eyebrow">Distribuição</p>
                        <h2 class="section-title">Dados por entidade</h2>
                    </div>
                </div>
                <div class="insights-grid">
                    <?php 
                    // Ordena por quantidade de registros (decrescente)
                    $sortedStats = $stats;
                    arsort($sortedStats);
                    
                    foreach ($sortedStats as $entityKey => $count): 
                        $entity = $entities[$entityKey];
                        $percentage = $totalRecords > 0 ? ($count / $totalRecords) * 100 : 0;
                    ?>
                        <div class="insight-item">
                            <div class="insight-header">
                                <span class="insight-label"><?= e($entity['label']) ?></span>
                                <span class="insight-value"><?= e($count) ?></span>
                            </div>
                            <div class="insight-bar">
                                <div class="insight-fill" style="width: <?= e($percentage) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </main>
    </div>

    <script>
        (() => {
            const toggleBtn = document.getElementById('theme-toggle');
            const htmlRoot = document.documentElement;
            const sidebar = document.querySelector('.sidebar');
            const menu = document.querySelector('.menu');
            const menuLinks = document.querySelectorAll('.menu-link');
            const sidebarScrollKey = 'consome_api.sidebar.scroll';
            const menuScrollKey = 'consome_api.menu.scroll';

            if (toggleBtn) {
                const lightIcon = toggleBtn.querySelector('.theme-icon-light');
                const darkIcon = toggleBtn.querySelector('.theme-icon-dark');

                const updateTheme = () => {
                    const currentTheme = htmlRoot.getAttribute('data-theme') || 'light';
                    const isDark = currentTheme === 'dark';

                    toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                    lightIcon.style.display = isDark ? 'block' : 'none';
                    darkIcon.style.display = isDark ? 'none' : 'block';
                };

                toggleBtn.addEventListener('click', () => {
                    const currentTheme = htmlRoot.getAttribute('data-theme') || 'light';
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    htmlRoot.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateTheme();
                });

                updateTheme();
            }

            const persistScroll = (element, key) => {
                if (!element) {
                    return;
                }

                element.addEventListener('scroll', () => {
                    sessionStorage.setItem(key, String(element.scrollTop));
                });
            };

            const restoreScroll = (element, key) => {
                if (!element) {
                    return;
                }

                const saved = sessionStorage.getItem(key);
                if (saved !== null) {
                    element.scrollTop = Number(saved);
                }
            };

            persistScroll(sidebar, sidebarScrollKey);
            persistScroll(menu, menuScrollKey);

            menuLinks.forEach((link) => {
                link.addEventListener('click', () => {
                    if (sidebar) {
                        sessionStorage.setItem(sidebarScrollKey, String(sidebar.scrollTop));
                    }
                    if (menu) {
                        sessionStorage.setItem(menuScrollKey, String(menu.scrollTop));
                    }
                });
            });

            requestAnimationFrame(() => {
                restoreScroll(sidebar, sidebarScrollKey);
                restoreScroll(menu, menuScrollKey);
            });
        })();
    </script>
</body>
</html>
