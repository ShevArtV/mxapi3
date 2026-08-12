<?php

/**
 * Системные события mxApi — точки расширения для пакетов-провайдеров.
 *
 *   mxApiOnRegisterEndpoints  — сбор эндпоинтов от пакетов-провайдеров;
 *   mxApiOnRegisterMiddleware — сбор промежуточных обработчиков от пакетов;
 *   mxApiOnBeforeRequest      — до аутентификации и роутинга (можно отклонить запрос);
 *   mxApiOnBeforeEndpointRun  — после авторизации, до выполнения обработчика;
 *   mxApiOnAfterEndpointRun   — после обработчика, до сериализации ответа;
 *   mxApiOnResponse           — перед отдачей ответа клиенту.
 */

return [
    'mxApiOnRegisterEndpoints',
    'mxApiOnRegisterMiddleware',
    'mxApiOnBeforeRequest',
    'mxApiOnBeforeEndpointRun',
    'mxApiOnAfterEndpointRun',
    'mxApiOnResponse',
];
