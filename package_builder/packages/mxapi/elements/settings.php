<?php

/**
 * Системные настройки mxApi. Подписи и описания — в лексиконе пакета
 * (ключи setting_<key> / setting_<key>_desc, области area_<area>).
 */

return [
    // Рубильник: 0 — точка входа отвечает 503 (service_disabled) на ЛЮБОЙ запрос,
    // включая /meta/*. Проверка стоит до роутинга: выключенный API не должен
    // раскрывать даже состав своего каталога.
    'mxapi.enabled' => ['xtype' => 'combo-boolean', 'value' => '1', 'area' => 'mxapi'],
    // Публичный префикс маршрутов. Проектные алиасы (напр. /api/v1/...) задаются
    // в core/config/mxapi.php, а не здесь.
    'mxapi.route_prefix' => ['xtype' => 'textfield', 'value' => '/mxapi/v1', 'area' => 'mxapi'],
    // Контекст MODX по умолчанию: в нём проверяются права и выполняются процессоры,
    // если эндпоинт не объявил свой. Управляющие эндпоинты работают в mgr.
    'mxapi.context' => ['xtype' => 'textfield', 'value' => 'mgr', 'area' => 'mxapi_access'],
    // Разрешить вызывающей системе выбирать контекст заголовком X-MxApi-Context
    // (только для эндпоинтов, объявивших modx_context = request). Нужно на
    // мультисайте; выключено по умолчанию — расширяет поверхность атаки.
    'mxapi.allow_request_context' => ['xtype' => 'combo-boolean', 'value' => '0', 'area' => 'mxapi_access'],
    // Время жизни выданного bearer-токена, сек.
    'mxapi.token_ttl' => ['xtype' => 'numberfield', 'value' => '86400', 'area' => 'mxapi_access'],
    // Пагинация по умолчанию и жёсткий потолок для limit.
    'mxapi.default_limit' => ['xtype' => 'numberfield', 'value' => '100', 'area' => 'mxapi_limits'],
    'mxapi.max_limit' => ['xtype' => 'numberfield', 'value' => '1000', 'area' => 'mxapi_limits'],
    // Лимит запросов в минуту на клиента; 0 — выключено. Клиент может переопределить своим rate_limit.
    'mxapi.rate_limit_per_minute' => ['xtype' => 'numberfield', 'value' => '120', 'area' => 'mxapi_limits'],
    // Ключ подписи курсоров постраничного обхода. Пустое значение заполняет
    // резолвер при установке: курсор без подписи — это произвольный ввод в
    // параметрах выборки, поэтому пустой ключ курсорную пагинацию выключает.
    // Смена ключа обесценивает выданные курсоры: обходы начнутся заново.
    'mxapi.cursor_secret' => ['xtype' => 'textfield', 'value' => '', 'area' => 'mxapi_limits'],
    // Доверенные прокси: только для них учитывается X-Forwarded-For при определении IP клиента.
    'mxapi.trusted_proxies' => ['xtype' => 'textfield', 'value' => '', 'area' => 'mxapi_access'],
    // Что показывать в каталоге эндпоинтов и OpenAPI: all — весь публичный
    // контракт, scope — только вызываемое предъявленным токеном, permission —
    // только то, на что у пользователя есть право MODX.
    'mxapi.catalog_filter' => ['xtype' => 'textfield', 'value' => 'all', 'area' => 'mxapi_access'],
    // Писать в журнал успешные read-вызовы (write пишутся всегда).
    'mxapi.log_reads' => ['xtype' => 'combo-boolean', 'value' => '0', 'area' => 'mxapi_log'],
    // Срок хранения записей журнала, сек. 0 — не чистить.
    'mxapi.log_lifetime' => ['xtype' => 'numberfield', 'value' => '2592000', 'area' => 'mxapi_log'],
    // Разрешённые Origin для CORS через запятую; пусто — заголовки CORS не отдаются.
    'mxapi.cors_origins' => ['xtype' => 'textfield', 'value' => '', 'area' => 'mxapi_access'],
    // Отдавать детали внутренних ошибок в ответе. Только для отладки.
    'mxapi.debug' => ['xtype' => 'combo-boolean', 'value' => '0', 'area' => 'mxapi_log'],
];
