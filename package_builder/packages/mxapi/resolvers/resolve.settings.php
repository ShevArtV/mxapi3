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
 * Настройку резолвер при необходимости создаёт сам: vehicle категории, к которому
 * он прикреплён, едет в пакете раньше vehicle'ов настроек, поэтому на установке,
 * где ключ появляется впервые, строки в базе ещё нет (проверено на стенде MODX
 * 3.2.1: без этого 2.1.0 ставилась с пустым ключом). Своё значение настройка
 * переживёт — vehicle'ы настроек едут с UPDATE_OBJECT = false.
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
    $secret = $modx->newObject(modSystemSetting::class);
    $secret->fromArray([
        'key' => 'mxapi.cursor_secret',
        'xtype' => 'textfield',
        'namespace' => 'mxapi',
        'area' => 'mxapi_limits',
        'editedon' => null,
    ], '', true, true);
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
