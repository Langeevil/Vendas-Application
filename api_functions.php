<?php
require_once __DIR__ . '/config.php';

global $apiMemoryCache;
$apiMemoryCache = [];

function call_api(string $method, string $path, ?array $data = null): array
{
    global $apiMemoryCache;
    global $apiBaseUrl;

    $url = str_starts_with($path, 'http') ? $path : rtrim($apiBaseUrl, '/') . '/' . ltrim($path, '/');
    $cacheKey = build_api_cache_key($method, $url, $data);

    if ($method === 'GET') {
        if (isset($apiMemoryCache[$cacheKey])) {
            return $apiMemoryCache[$cacheKey];
        }

        $cached = read_api_cache($cacheKey);
        if ($cached !== null) {
            $apiMemoryCache[$cacheKey] = $cached;
            return $cached;
        }
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 6,
    ]);

    if ($data !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    $rawBody = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    $body = null;
    if (is_string($rawBody) && $rawBody !== '') {
        $decoded = json_decode($rawBody, true);
        $body = $decoded === null && trim($rawBody) !== 'null' ? $rawBody : $decoded;
    }

    $response = [
        'ok' => $error === '' && $status >= 200 && $status < 300,
        'status' => $status,
        'error' => $error,
        'body' => $body,
        'raw' => $rawBody,
    ];

    if ($method === 'GET') {
        $apiMemoryCache[$cacheKey] = $response;
        write_api_cache($cacheKey, $response);
    } elseif ($response['ok']) {
        clear_api_cache();
    }

    return $response;
}

function call_api_many(array $requests): array
{
    global $apiMemoryCache;
    global $apiBaseUrl;

    $multiHandle = curl_multi_init();
    $handles = [];
    $responses = [];

    foreach ($requests as $requestKey => $request) {
        $method = strtoupper($request['method'] ?? 'GET');
        $data = $request['data'] ?? null;
        $path = $request['path'] ?? '/';
        $url = str_starts_with($path, 'http') ? $path : rtrim($apiBaseUrl, '/') . '/' . ltrim($path, '/');
        $cacheKey = build_api_cache_key($method, $url, $data);

        if ($method === 'GET') {
            if (isset($apiMemoryCache[$cacheKey])) {
                $responses[$requestKey] = $apiMemoryCache[$cacheKey];
                continue;
            }

            $cached = read_api_cache($cacheKey);
            if ($cached !== null) {
                $apiMemoryCache[$cacheKey] = $cached;
                $responses[$requestKey] = $cached;
                continue;
            }
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 6,
        ]);

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        curl_multi_add_handle($multiHandle, $curl);
        $handles[$requestKey] = [
            'handle' => $curl,
            'method' => $method,
            'cache_key' => $cacheKey,
        ];
    }

    do {
        $status = curl_multi_exec($multiHandle, $running);
        if ($running) {
            curl_multi_select($multiHandle, 1.0);
        }
    } while ($running && $status === CURLM_OK);

    foreach ($handles as $requestKey => $meta) {
        $curl = $meta['handle'];
        $rawBody = curl_multi_getcontent($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $body = null;

        if (is_string($rawBody) && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            $body = $decoded === null && trim($rawBody) !== 'null' ? $rawBody : $decoded;
        }

        $response = [
            'ok' => $error === '' && $status >= 200 && $status < 300,
            'status' => $status,
            'error' => $error,
            'body' => $body,
            'raw' => $rawBody,
        ];

        if ($meta['method'] === 'GET') {
            $apiMemoryCache[$meta['cache_key']] = $response;
            write_api_cache($meta['cache_key'], $response);
        }

        $responses[$requestKey] = $response;
        curl_multi_remove_handle($multiHandle, $curl);
        curl_close($curl);
    }

    curl_multi_close($multiHandle);
    ksort($responses);

    return $responses;
}

function build_api_cache_key(string $method, string $url, $data): string
{
    return sha1($method . '|' . $url . '|' . json_encode($data));
}

function api_cache_dir(): string
{
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

function read_api_cache(string $cacheKey): ?array
{
    $file = api_cache_dir() . '/' . $cacheKey . '.json';
    $ttl = 15;

    if (!is_file($file)) {
        return null;
    }

    if ((time() - filemtime($file)) > $ttl) {
        return null;
    }

    $contents = file_get_contents($file);
    if ($contents === false || $contents === '') {
        return null;
    }

    $decoded = json_decode($contents, true);
    return is_array($decoded) ? $decoded : null;
}

function write_api_cache(string $cacheKey, array $response): void
{
    $file = api_cache_dir() . '/' . $cacheKey . '.json';
    @file_put_contents($file, json_encode($response, JSON_UNESCAPED_UNICODE));
}

function clear_api_cache(): void
{
    global $apiMemoryCache;

    $apiMemoryCache = [];

    foreach (glob(api_cache_dir() . '/*.json') ?: [] as $file) {
        @unlink($file);
    }
}

function all_entity_configs(): array
{
    global $entityConfigs;
    return $entityConfigs;
}

function entity_config(string $resource): ?array
{
    $configs = all_entity_configs();
    return $configs[$resource] ?? null;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function read_path($data, string $path)
{
    if (!is_array($data)) {
        return null;
    }

    $segments = explode('.', $path);
    $current = $data;

    foreach ($segments as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return null;
        }
        $current = $current[$segment];
    }

    return $current;
}

function primary_key_source(array $config, string $primaryKey): string
{
    foreach ($config['fields'] as $field) {
        if (($field['name'] ?? null) === $primaryKey) {
            return $field['source'] ?? $primaryKey;
        }
    }

    return $primaryKey;
}

function extract_primary_key_values(array $config, array $record): array
{
    $values = [];

    foreach ($config['primary_keys'] as $primaryKey) {
        $values[$primaryKey] = stringify_primary_key_value(
            read_path($record, $primaryKey)
            ?? read_path($record, 'id.' . $primaryKey)
            ?? read_path($record, primary_key_source($config, $primaryKey))
        );
    }

    return $values;
}

function request_primary_key_values(array $config, array $source): array
{
    $values = [];
    foreach ($config['primary_keys'] as $primaryKey) {
        $values[$primaryKey] = $source[$primaryKey] ?? null;
    }
    return $values;
}

function has_complete_primary_key(array $config, array $values): bool
{
    foreach ($config['primary_keys'] as $primaryKey) {
        if (!isset($values[$primaryKey]) || $values[$primaryKey] === '') {
            return false;
        }
    }

    return true;
}

function build_resource_path(array $config, array $primaryKeyValues): string
{
    $parts = [$config['endpoint']];
    foreach ($config['primary_keys'] as $primaryKey) {
        $parts[] = rawurlencode((string) $primaryKeyValues[$primaryKey]);
    }
    return implode('/', $parts);
}

function normalize_field_value(array $field, $value)
{
    if ($value === null || $value === '') {
        return null;
    }

    $type = $field['type'] ?? 'text';

    if ($type === 'number') {
        return strpos((string) $value, '.') !== false ? (float) $value : (int) $value;
    }

    return $value;
}

function build_payload(array $config, array $source): array
{
    $payload = [];

    foreach ($config['fields'] as $field) {
        $name = $field['name'];
        $payload[$name] = normalize_field_value($field, $source[$name] ?? null);
    }

    return $payload;
}

function api_error_message(array $response, string $fallback): string
{
    if (!empty($response['error'])) {
        return $response['error'];
    }

    if (is_string($response['body']) && trim($response['body']) !== '') {
        return $response['body'];
    }

    if (is_array($response['body']) && isset($response['body']['message'])) {
        return (string) $response['body']['message'];
    }

    if (!empty($response['status'])) {
        return $fallback . ' HTTP ' . $response['status'] . '.';
    }

    return $fallback;
}

function format_value($value, ?string $format = null): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    if ($format === 'currency' && is_numeric($value)) {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    return (string) $value;
}

function stringify_primary_key_value($value): string
{
    return $value === null ? '' : (string) $value;
}
