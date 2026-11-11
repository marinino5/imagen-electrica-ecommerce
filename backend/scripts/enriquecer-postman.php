<?php

/**
 * Completa la colección de Postman que genera Scribe (public/docs/collection.json):
 *  - el token Bearer se toma de la variable {{token}};
 *  - "Iniciar sesión" y "Registrar cliente" guardan el token en esa variable (pestaña Tests);
 *  - agrega "Iniciar sesión (administrador)" con las variables {{adminEmail}} y {{adminPassword}};
 *  - agrega ejemplos guardados que faltan: 201, 204, 401, 404 y 422 (mensajes reales de la API);
 *  - quita la variable de ruta duplicada que genera Scribe (:id y :category).
 *
 * Se ejecuta solo con `composer docs:api`, después de scribe:generate.
 */
$path = __DIR__.'/../public/docs/collection.json';
$collection = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

$json = fn (array $data): string => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$unauthenticated = ['message' => 'No has iniciado sesión o tu token no es válido.'];
$notFound = ['message' => 'No se encontró lo que buscas.'];

// Ejemplos propios de los CRUD de administrador, por "MÉTODO ruta"
$adminExamples = [
    'POST api/v1/admin/categories' => [
        201 => ['Categoría creada', ['data' => ['id' => 1, 'parent_id' => null, 'name' => 'Motores eléctricos', 'slug' => 'motores-electricos', 'description' => 'Motores trifásicos y monofásicos.', 'image_url' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => '2026-11-11T00:21:10.000000Z', 'updated_at' => '2026-11-11T00:21:10.000000Z']]],
        422 => ['Datos inválidos', ['message' => 'El campo nombre es obligatorio.', 'errors' => ['name' => ['El campo nombre es obligatorio.']]]],
    ],
    'PUT api/v1/admin/categories/:id' => [
        422 => ['Datos inválidos', ['message' => 'El campo nombre es obligatorio.', 'errors' => ['name' => ['El campo nombre es obligatorio.']]]],
    ],
    'POST api/v1/admin/products' => [
        422 => ['Datos inválidos', ['message' => 'El campo categoría es obligatorio.', 'errors' => ['category_id' => ['El campo categoría es obligatorio.'], 'brand_id' => ['El campo marca es obligatorio.'], 'sku' => ['El campo referencia (SKU) es obligatorio.']]]],
    ],
    'PUT api/v1/admin/products/:id' => [
        422 => ['Datos inválidos', ['message' => 'El campo categoría es obligatorio.', 'errors' => ['category_id' => ['El campo categoría es obligatorio.']]]],
    ],
];

$saveToken = [
    'listen' => 'test',
    'script' => ['type' => 'text/javascript', 'exec' => [
        "// Guarda el token para que las demás peticiones lo usen como {{token}}",
        'const body = pm.response.json();',
        'if (body.token) { pm.collectionVariables.set("token", body.token); }',
    ]],
];

$hasCode = fn (array $item, int $code): bool => in_array($code, array_column($item['response'] ?? [], 'code'), true);
$addExample = function (array &$item, int $code, string $name, array $body) use ($json): void {
    $item['response'][] = ['header' => [], 'code' => $code, 'body' => $code === 204 ? '' : $json($body), 'name' => $name];
};

$process = function (array &$items) use (&$process, $saveToken, $hasCode, $addExample, $adminExamples, $unauthenticated, $notFound): void {
    foreach ($items as &$item) {
        if (isset($item['item'])) {
            $process($item['item']);

            continue;
        }

        $request = &$item['request'];
        $method = $request['method'];
        $url = $request['url'];
        $route = $method.' '.($url['path'] ?? '');
        $needsToken = ($request['auth']['type'] ?? null) !== 'noauth';

        // Scribe repite la variable de ruta (:id y :category): se deja una con la descripción útil
        if (count($url['variable'] ?? []) === 2) {
            $request['url']['variable'] = [['id' => 'id', 'key' => 'id', 'value' => $url['variable'][1]['value'] ?? 1, 'description' => $url['variable'][1]['description'] ?? '']];
        }

        if (in_array($item['name'], ['Iniciar sesión', 'Registrar cliente'], true)) {
            $item['event'] = [$saveToken];
        }

        foreach ($adminExamples[$route] ?? [] as $code => [$name, $body]) {
            if (! $hasCode($item, $code)) {
                $addExample($item, $code, $name, $body);
            }
        }

        if ($method === 'DELETE' && ! $hasCode($item, 204)) {
            $addExample($item, 204, 'Eliminado', []);
        }

        if ($needsToken && ! $hasCode($item, 401)) {
            $addExample($item, 401, 'Sin token o token inválido', $unauthenticated);
        }

        if (str_contains($url['path'] ?? '', ':') && ! $hasCode($item, 404)) {
            $addExample($item, 404, 'No existe', $notFound);
        }

        unset($request);
    }
};

$process($collection['item']);

// Token Bearer desde la variable de la colección
$collection['auth'] = ['type' => 'bearer', 'bearer' => [['key' => 'token', 'value' => '{{token}}', 'type' => 'string']]];

$variables = array_column($collection['variable'], null, 'key');
foreach (['token' => '', 'adminEmail' => 'admin@imagenelectrica.test', 'adminPassword' => ''] as $key => $value) {
    $variables[$key] ??= ['id' => $key, 'key' => $key, 'type' => 'string', 'name' => 'string', 'value' => $value];
}
$collection['variable'] = array_values($variables);

// Inicio de sesión del administrador: guarda el token igual que el del cliente
foreach ($collection['item'] as &$folder) {
    if ($folder['name'] !== 'Autenticación') {
        continue;
    }

    array_splice($folder['item'], 2, 0, [[
        'name' => 'Iniciar sesión (administrador)',
        'event' => [$saveToken],
        'request' => [
            'url' => ['host' => '{{baseUrl}}', 'path' => 'api/v1/login', 'query' => [], 'raw' => '{{baseUrl}}/api/v1/login'],
            'method' => 'POST',
            'header' => [['key' => 'Content-Type', 'value' => 'application/json'], ['key' => 'Accept', 'value' => 'application/json']],
            'body' => ['mode' => 'raw', 'raw' => '{"email":"{{adminEmail}}","password":"{{adminPassword}}"}'],
            'description' => 'Ejecútala antes de las peticiones del grupo «Productos (administrador)» y «Categorías (administrador)». '
                .'Escribe la contraseña del administrador en la variable de colección `adminPassword`. El token se guarda solo en `token`.',
            'auth' => ['type' => 'noauth'],
        ],
        'response' => [[
            'header' => [], 'code' => 200, 'name' => 'Sesión iniciada',
            'body' => $json(['data' => ['id' => 1, 'name' => 'Administrador', 'email' => 'admin@imagenelectrica.test', 'role' => 'admin', 'customer' => null], 'token' => '1|abc123...', 'token_type' => 'Bearer']),
        ]],
    ]]);
}
unset($folder);

file_put_contents($path, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
echo "Colección de Postman completada.\n";
