<?php

/**
 * Resolver: создание таблиц mxApi при install/upgrade.
 *
 * Идемпотентно: createObjectContainer создаёт таблицу со всеми колонками и
 * индексами на чистой установке и молча пропускает уже существующую.
 *
 * Таблицы при uninstall НЕ дропаются: в mxapi_client лежат боевые учётки
 * интеграций, в mxapi_log — аудит. Полная зачистка — вручную.
 *
 * Charset приводится к utf8mb4: createObjectContainer мог создать таблицу в
 * дефолтной кодировке сервера (latin1), и запись кириллицы в описания падает
 * с «1366 Incorrect string value».
 *
 * @var \xPDO\Transport\xPDOTransport $transport
 * @var array $options
 */

use MODX\Revolution\modX;
use xPDO\Transport\xPDOTransport;

if (!$transport->xpdo) {
    return true;
}

/** @var modX $modx */
$modx = $transport->xpdo;
$action = $options[xPDOTransport::PACKAGE_ACTION] ?? '';

if ($action === xPDOTransport::ACTION_UNINSTALL) {
    return true;
}

$corePath = $modx->getOption('mxapi.core_path', null, $modx->getOption('core_path') . 'components/mxapi/');

$autoload = $corePath . 'vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (!isset($modx->packages['MxApi\\Model'])) {
    $modx->addPackage('MxApi\\Model', $corePath . 'src/', null, 'MxApi\\');
}

$manager = $modx->getManager();

$classes = [
    \MxApi\Model\MxApiClient::class,
    \MxApi\Model\MxApiToken::class,
    \MxApi\Model\MxApiLog::class,
];

foreach ($classes as $class) {
    $manager->createObjectContainer($class);

    try {
        $table = $modx->getTableName($class);
        if (!$table) {
            continue;
        }

        $bare = trim($table, '`');
        $stmt = $modx->query('SHOW TABLE STATUS LIKE ' . $modx->quote($bare));
        $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;
        $collation = $row['Collation'] ?? '';

        if (stripos((string)$collation, 'utf8mb4') === false) {
            $modx->exec("ALTER TABLE {$table} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $modx->log(modX::LOG_LEVEL_INFO, '[mxapi] Таблица ' . $bare . ' приведена к utf8mb4.');
        }
    } catch (\Throwable $e) {
        $modx->log(modX::LOG_LEVEL_WARN, '[mxapi] Не удалось привести таблицу к utf8mb4: ' . $e->getMessage());
    }
}

$modx->log(modX::LOG_LEVEL_INFO, '[mxapi] Таблицы mxapi_client / mxapi_token / mxapi_log проверены.');

return true;
