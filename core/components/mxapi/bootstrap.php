<?php

/**
 * mxApi bootstrap — автозагрузка пакета, xPDO-модель и DI-сервис.
 *
 * Файл загружается ядром MODX 3 при инициализации namespace «mxapi»
 * (modX::_loadExtensionPackages), поэтому всё, что нужно и публичной точке
 * входа, и CMP, и процессорам, регистрируется в одном месте.
 *
 * Доступ к ядру API: $modx->services->get('mxapi') — готовый MxApi\Core\Kernel.
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    // Без своего vendor пакет нерабочий: точка входа отвечает not_installed,
    // а админка — понятной ошибкой. Молча продолжать нельзя.
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, '[mxapi] Нет vendor/autoload.php — зависимости пакета не установлены.');

    return;
}
require_once $autoload;

$modx->addPackage('MxApi\\Model', $namespace['path'] . 'src/', null, 'MxApi\\');

// Ядро собирается лениво: на страницах менеджера, где mxApi не нужен, за
// регистрацию сервиса не платим ни одним запросом к базе.
$modx->services->add('mxapi', function () use ($modx) {
    return \MxApi\Bootstrap::createKernel($modx);
});
