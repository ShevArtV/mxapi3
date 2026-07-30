<?php

/**
 * Конфигурация сборки transport-пакета mxApi для MODX 3.
 *
 * Сборщик (shevartv/modx-builder, CLI modxapp) ставится глобально, но его
 * конфиг обязан лежать в репозитории — иначе на другой машине пакет не собрать.
 */

return [
    'name' => 'mxApi',
    'name_lower' => 'mxapi',
    'name_short' => 'mxapi',
    'version' => '2.0.0',
    'release' => 'beta',
    'php_version' => '8.1',

    'paths' => [
        'core' => 'core/components/mxapi/',
        'assets' => 'assets/components/mxapi/',
    ],

    // ⚠️ Регистр важен: генератор моделей срезает этот префикс из package
    // схемы, чтобы получить путь. Без него (при name = mxApi) классы уезжают
    // в src/MxApi/Model/ вместо src/Model/, и PSR-4 их не находит.
    'namespace_prefix' => 'MxApi',

    'schema' => [
        'file' => 'schema/mxapi.mysql.schema.xml',
        // Классы моделей генерируются командой `modxapp schema mxapi` и
        // коммитятся: в сборке они уже готовы, а таблицы создаёт резолвер.
        'auto_generate_classes' => true,
        'update_tables' => false,
    ],

    'elements' => [
        'category' => 'mxApi',
        'settings' => 'elements/settings.php',
        'events' => 'elements/events.php',
        'plugins' => 'elements/plugins.php',
        'menus' => 'elements/menus.php',
    ],

    'static' => [
        'chunks' => false,
        'snippets' => false,
        // Плагин вкладки клиентов лежит файлом в репозитории. В линии MODX 2
        // билдер брал его из БД стенда, и он мог молча не попасть в transport.
        'plugins' => true,
    ],

    'encrypt' => false,

    'tools' => [
        'analyse' => '',
        'cs' => '',
        'csMode' => 'fix',
    ],

    'build' => [
        'download' => false,
        'install' => false,
        'update' => [
            'plugins' => true,
            // Значения настроек на сайте правит администратор — апгрейд их не трогает.
            'settings' => false,
            'events' => true,
            'menus' => true,
        ],
    ],
];
