<?php

/**
 * Публичная точка входа mxApi (MODX 3).
 *
 * Маршрутизация возможна двумя способами:
 *   1) правилом веб-сервера на префикс (nginx):
 *        location ^~ /mxapi/ { try_files $uri /assets/components/mxapi/index.php$is_args$args; }
 *   2) без правки конфигурации веб-сервера — параметром route:
 *        /assets/components/mxapi/index.php?route=/auth/token
 *
 * Второй способ рабочий, но публичным контрактом считается первый.
 *
 * MODX поднимается в контексте mgr и обработку запроса сам не запускает
 * (handleRequest не вызывается) — маршрут разбирает ядро mxApi.
 */

require_once dirname(__DIR__, 3) . '/config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';

$modx = new \MODX\Revolution\modX();
$modx->initialize('mgr');
$modx->getRequest();
$modx->setLogLevel(\MODX\Revolution\modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$corePath = $modx->getOption('mxapi.core_path', null, MODX_CORE_PATH . 'components/mxapi/');

// Автозагрузка классов пакета: свой vendor, изолированный от других пакетов.
// Обычно его уже подключил bootstrap.php по namespace, но точка входа обязана
// работать и до того, как namespace попал в кэш расширений.
$autoload = $corePath . 'vendor/autoload.php';
if (!is_readable($autoload)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'not_installed',
            'message' => 'Зависимости mxApi не установлены: нет vendor/autoload.php.',
            'details' => new stdClass(),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
require_once $autoload;

$kernel = \MxApi\Bootstrap::createKernel($modx);
$config = $kernel->getConfig();

$request = \MxApi\Core\Http\Request::fromGlobals(
    $config->get('route_prefix'),
    $config->getList('trusted_proxies')
);

$kernel->handle($request)->send();
