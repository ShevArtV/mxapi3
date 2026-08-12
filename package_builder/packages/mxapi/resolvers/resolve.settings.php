<?php

/**
 * Resolver: ключ подписи курсоров постраничного обхода.
 *
 * Пакет привозит настройку mxapi.cursor_secret пустой: одинаковый на всех
 * установках ключ означал бы, что курсор, выданный одним сайтом, принимается
 * другим. Значение рождается здесь и на каждой установке своё.
 *
 * Заполняем только пустую настройку — иначе обновление пакета обесценивало бы
 * курсоры, выданные работающим интеграциям, и их инкрементальные обходы
 * начинались бы заново.
 *
 * Настройки едут отдельными vehicle'ами раньше категории, к которой прикреплён
 * этот резолвер, поэтому к моменту запуска строка в базе уже есть.
 *
 * @var \xPDO\Transport\xPDOTransport $transport
 * @var array $options
 */

use MODX\Revolution\modSystemSetting;
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

/** @var modSystemSetting|null $secret */
$secret = $modx->getObject(modSystemSetting::class, ['key' => 'mxapi.cursor_secret']);

if (!$secret) {
    $modx->log(modX::LOG_LEVEL_WARN, '[mxapi] Настройка mxapi.cursor_secret не найдена:'
        . ' курсорная пагинация останется выключенной.');

    return true;
}

if (trim((string)$secret->get('value')) !== '') {
    return true;
}

$secret->set('value', bin2hex(random_bytes(32)));

if ($secret->save()) {
    $modx->log(modX::LOG_LEVEL_INFO, '[mxapi] Сгенерирован ключ подписи курсоров (mxapi.cursor_secret).');
    $modx->getCacheManager()->refresh(['system_settings' => []]);
}

return true;
