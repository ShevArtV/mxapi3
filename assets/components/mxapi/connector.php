<?php

/**
 * Коннектор менеджера mxApi: обслуживает только админку (каталог эндпоинтов,
 * выгрузка OpenAPI, клиенты интеграции). Публичный API живёт в index.php и с
 * этим файлом не связан.
 *
 * Параметр `action` — FQCN процессора (например
 * MxApi\Processors\Mgr\Endpoints\GetList); ядро резолвит его через autoload.
 */

require_once dirname(__DIR__, 3) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var \MODX\Revolution\modX $modx */
$modx->lexicon->load('mxapi:default');

$autoload = $modx->getOption('mxapi.core_path', null, MODX_CORE_PATH . 'components/mxapi/') . 'vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

$modx->request->handleRequest();
