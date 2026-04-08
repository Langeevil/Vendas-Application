<?php
require_once __DIR__ . '/api_functions.php';

$config = entity_config($resource ?? '');

if (!$config) {
    http_response_code(404);
    exit('Recurso nao encontrado.');
}

$feedback = null;
$feedbackType = 'success';
$record = null;
$records = [];
$optionCache = [];
$optionRequests = [];

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $deleteValues = request_primary_key_values($config, $_GET);
    if (has_complete_primary_key($config, $deleteValues)) {
        $deleteResponse = call_api('DELETE', build_resource_path($config, $deleteValues));
        $feedback = $deleteResponse['ok'] ? 'Registro removido com sucesso.' : api_error_message($deleteResponse, 'Falha ao remover o registro.');
        $feedbackType = $deleteResponse['ok'] ? 'success' : 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['form_mode'] ?? 'create';
    $payload = build_payload($config, $_POST);
    $keyValues = request_primary_key_values($config, $_POST);
    $method = $mode === 'edit' ? 'PUT' : 'POST';
    $path = $mode === 'edit' ? build_resource_path($config, $keyValues) : $config['endpoint'];
    $saveResponse = call_api($method, $path, $payload);
    $feedback = $saveResponse['ok'] ? ($mode === 'edit' ? 'Registro atualizado com sucesso.' : 'Registro criado com sucesso.') : api_error_message($saveResponse, 'Falha ao salvar o registro.');
    $feedbackType = $saveResponse['ok'] ? 'success' : 'error';

    if ($saveResponse['ok'] && $mode === 'create') {
        $_POST = [];
    }
}

$listResponse = call_api('GET', $config['endpoint']);
$records = is_array($listResponse['body']) ? $listResponse['body'] : [];

if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $editValues = request_primary_key_values($config, $_GET);
    if (has_complete_primary_key($config, $editValues)) {
        $singleResponse = call_api('GET', build_resource_path($config, $editValues));
        if ($singleResponse['ok'] && is_array($singleResponse['body'])) {
            $record = $singleResponse['body'];
        } else {
            $feedback = api_error_message($singleResponse, 'Nao foi possivel carregar o registro para edicao.');
            $feedbackType = 'error';
        }
    }
}

foreach ($config['fields'] as $field) {
    if (!empty($field['options_resource'])) {
        $optionsResource = $field['options_resource'];
        if (!isset($optionRequests[$optionsResource])) {
            $relatedConfig = entity_config($optionsResource);
            $optionRequests[$optionsResource] = [
                'method' => 'GET',
                'path' => $relatedConfig['endpoint'],
            ];
        }
    }
}

if ($optionRequests) {
    $optionResponses = call_api_many($optionRequests);
    foreach ($optionResponses as $optionsResource => $response) {
        $optionCache[$optionsResource] = is_array($response['body']) ? $response['body'] : [];
    }
}

function get_field_options(array $field, array $optionCache): array
{
    if (empty($field['options_resource'])) {
        return [];
    }

    return $optionCache[$field['options_resource']] ?? [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($config['label']) ?> | Fatec 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a href="../index.php" class="brand">Fatec<span>2026</span></a>
            <p class="sidebar-copy">Navegacao por recurso para testar a API de vendas.</p>
            <nav class="menu">
                <?php foreach (all_entity_configs() as $entityKey => $entity): ?>
                    <a class="menu-link <?= $entityKey === $resource ? 'active' : '' ?>" href="<?= e($entityKey) ?>.php">
                        <span><?= e($entity['label']) ?></span>
                        <small><?= e($entity['singular']) ?></small>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="content">
            <header class="hero compact">
                <div>
                    <p class="eyebrow">Recurso selecionado</p>
                    <h1><?= e($config['label']) ?></h1>
                    <p class="hero-copy"><?= e($config['summary']) ?></p>
                </div>
                <div class="hero-actions">
                    <a class="ghost-button" href="../index.php">Voltar ao painel</a>
                    <a class="primary-button" href="<?= e($resource) ?>.php">Novo registro</a>
                </div>
            </header>

            <?php if ($feedback): ?>
                <section class="flash <?= $feedbackType === 'error' ? 'error' : 'success' ?>">
                    <?= e($feedback) ?>
                </section>
            <?php endif; ?>

            <section class="workspace">
                <div class="panel form-panel">
                    <div class="panel-header">
                        <div>
                            <p class="panel-kicker"><?= $record ? 'Edicao' : 'Cadastro' ?></p>
                            <h2><?= $record ? 'Atualizar ' . e($config['singular']) : 'Novo ' . e($config['singular']) ?></h2>
                        </div>
                    </div>

                    <form method="post" class="grid-form">
                        <input type="hidden" name="form_mode" value="<?= $record ? 'edit' : 'create' ?>">

                        <?php foreach ($config['primary_keys'] as $primaryKey): ?>
                            <?php if ($record): ?>
                                <input type="hidden" name="<?= e($primaryKey) ?>" value="<?= e(stringify_primary_key_value(read_path($record, $primaryKey) ?? read_path($record, 'id.' . $primaryKey) ?? read_path($record, primary_key_source($config, $primaryKey)))) ?>">
                            <?php elseif (isset($_GET[$primaryKey])): ?>
                                <input type="hidden" name="<?= e($primaryKey) ?>" value="<?= e($_GET[$primaryKey]) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php foreach ($config['fields'] as $field): ?>
                            <?php
                            $fieldName = $field['name'];
                            $fieldSource = $field['source'] ?? $fieldName;
                            $fieldValue = $_POST[$fieldName] ?? ($record ? read_path($record, $fieldSource) : '');
                            $fieldType = $field['type'] ?? 'text';
                            $options = $fieldType === 'select' ? get_field_options($field, $optionCache) : [];
                            ?>
                            <label class="field">
                                <span><?= e($field['label']) ?></span>
                                <?php if ($fieldType === 'select'): ?>
                                    <select name="<?= e($fieldName) ?>" <?= !empty($field['required']) ? 'required' : '' ?>>
                                        <option value="">Selecione</option>
                                        <?php foreach ($options as $option): ?>
                                            <?php
                                            $optionValue = read_path($option, $field['option_value']);
                                            $optionLabel = read_path($option, $field['option_label']);
                                            $selected = (string) $fieldValue === (string) $optionValue;
                                            ?>
                                            <option value="<?= e($optionValue) ?>" <?= $selected ? 'selected' : '' ?>><?= e($optionLabel) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= e($fieldType) ?>" name="<?= e($fieldName) ?>" value="<?= e($fieldValue) ?>" <?= !empty($field['required']) ? 'required' : '' ?> <?= isset($field['step']) ? 'step="' . e($field['step']) . '"' : '' ?>>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>

                        <div class="form-actions">
                            <button type="submit" class="primary-button"><?= $record ? 'Salvar alteracoes' : 'Cadastrar' ?></button>
                            <?php if ($record): ?>
                                <a class="ghost-button" href="<?= e($resource) ?>.php">Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="panel table-panel">
                    <div class="panel-header">
                        <div>
                            <p class="panel-kicker">Listagem</p>
                            <h2><?= count($records) ?> registros</h2>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach ($config['columns'] as $column): ?>
                                        <th><?= e($column['label']) ?></th>
                                    <?php endforeach; ?>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$records): ?>
                                    <tr>
                                        <td colspan="<?= count($config['columns']) + 1 ?>" class="empty-state">Nenhum registro encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($records as $item): ?>
                                    <tr>
                                        <?php foreach ($config['columns'] as $column): ?>
                                            <td><?= e(format_value(read_path($item, $column['path']), $column['format'] ?? null)) ?></td>
                                        <?php endforeach; ?>
                                        <td class="actions">
                                            <a class="table-link" href="<?= e($resource) ?>.php?<?= e(http_build_query(array_merge(['action' => 'edit'], extract_primary_key_values($config, $item)))) ?>">Editar</a>
                                            <a class="table-link danger" href="<?= e($resource) ?>.php?<?= e(http_build_query(array_merge(['action' => 'delete'], extract_primary_key_values($config, $item)))) ?>" onclick="return confirm('Deseja remover este registro?')">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
