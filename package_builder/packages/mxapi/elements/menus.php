<?php

/**
 * Пункт меню CMP mxApi: раздел «Компоненты» → mxApi.
 * action=index соответствует controllers/index.class.php.
 */

return [
    'mxapi' => [
        'text' => 'mxapi',
        'description' => 'mxapi_menu_desc',
        'parent' => 'components',
        'action' => 'index',
        'namespace' => 'mxapi',
        'icon' => '<i class="icon-exchange icon icon-large"></i>',
        'menuindex' => 0,
        'permissions' => '',
    ],
];
