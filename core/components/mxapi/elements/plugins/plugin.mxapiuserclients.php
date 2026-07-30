<?php

/**
 * mxApiUserClients — вкладка «mxApi» на странице правки пользователя.
 *
 * Подключает виджет клиентов интеграции к штатной панели менеджера. Своей
 * страницы у него быть не может: клиенты принадлежат пользователю, и заводить
 * их логично там же, где правится учётка.
 *
 * Событие: OnManagerPageBeforeRender.
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $scriptProperties
 */

if ($modx->event->name !== 'OnManagerPageBeforeRender') {
    return;
}

// Только страница правки пользователя. На security/user/create вкладки нет
// намеренно: клиент привязывается к user_id, которого до сохранения не
// существует, и форма обещала бы то, чего сделать нельзя.
$action = isset($_GET['a']) ? (string)$_GET['a'] : '';
if ($action !== 'security/user/update') {
    return;
}

// Право то же, что и на саму правку учётки: кто может сменить пользователю
// пароль, тот и так может выпустить токен от его имени.
if (!$modx->hasPermission('save_user')) {
    return;
}

if (!isset($modx->controller) || !is_object($modx->controller)) {
    return;
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId < 1) {
    return;
}

$corePath = $modx->getOption('mxapi.core_path', null, MODX_CORE_PATH . 'components/mxapi/');
$autoload = $corePath . 'vendor/autoload.php';
if (!is_readable($autoload)) {
    return;
}
require_once $autoload;

$assetsUrl = $modx->getOption('mxapi.assets_url', null, MODX_ASSETS_URL . 'components/mxapi/');
$assetsPath = $modx->getOption('mxapi.assets_path', null, MODX_ASSETS_PATH . 'components/mxapi/');

$config = [
    'connector_url' => $assetsUrl . 'connector.php',
    'user_id' => $userId,
    'token' => $modx->user ? $modx->user->getUserToken($modx->context->get('key')) : '',
];

$modx->regClientStartupHTMLBlock(
    '<script>window.MxApiUserClients = '
    . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . ';</script>'
);

\MxApi\Manager\Assets::registerLexicon($modx, 'mxapi_');
\MxApi\Manager\Assets::registerVueToolsCheck($modx);
\MxApi\Manager\Assets::registerModule($modx, $assetsPath, $assetsUrl, 'clients');

return;
