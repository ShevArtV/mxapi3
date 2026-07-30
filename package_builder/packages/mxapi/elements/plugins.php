<?php

/**
 * Плагины пакета. Статические (static => plugins в config.php): код лежит
 * файлом в репозитории, а не в базе стенда — в линии MODX 2 билдер тянул
 * плагин из БД, и он мог молча не попасть в transport.
 */

return [
    'mxApiUserClients' => [
        'description' => 'Вкладка «mxApi» на странице правки пользователя: клиенты интеграции.',
        'content' => 'file:elements/plugins/plugin.mxapiuserclients.php',
        'events' => [
            'OnManagerPageBeforeRender',
        ],
    ],
];
