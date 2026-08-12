<?php
/**
 * Русский лексикон mxApi.
 *
 * @package mxapi
 * @subpackage lexicon
 */

$_lang['mxapi'] = 'mxApi';
$_lang['mxapi_menu_desc'] = 'Каталог эндпоинтов API и клиенты интеграций.';

$_lang['mxapi_endpoints'] = 'Эндпоинты';
$_lang['mxapi_clients'] = 'Клиенты';
$_lang['mxapi_tokens'] = 'Токены';
$_lang['mxapi_log'] = 'Журнал';

$_lang['mxapi_err_no_permission'] = 'Недостаточно прав для этого действия.';
$_lang['mxapi_err_not_found'] = 'Объект не найден.';
$_lang['mxapi_err_no_vendor'] = 'Зависимости mxApi не установлены: нет vendor/autoload.php в core/components/mxapi/.';

/* Системные настройки. Ключи вида setting_<key> / setting_<key>_desc читает грид
   настроек MODX (processors/system/settings/getlist.class.php:104-131); без них в
   списке видно только сырой ключ. Области подписываются ключами area_<area>:
   'mxapi:default' оставлен для установок, созданных до раскладки по областям. */
$_lang['area_mxapi'] = 'mxApi';
$_lang['area_mxapi:default'] = 'mxApi';
$_lang['area_mxapi_access'] = 'mxApi: доступ и контексты';
$_lang['area_mxapi_limits'] = 'mxApi: лимиты и пагинация';
$_lang['area_mxapi_log'] = 'mxApi: журнал и отладка';

$_lang['setting_mxapi.enabled'] = 'API включён';
$_lang['setting_mxapi.enabled_desc'] = 'Рубильник: при «Нет» точка входа отвечает 503 на любой запрос, включая каталог эндпоинтов.';
$_lang['setting_mxapi.route_prefix'] = 'Префикс маршрутов';
$_lang['setting_mxapi.route_prefix_desc'] = 'Публичный префикс публичных маршрутов, по умолчанию /mxapi/v1. Проектные алиасы задаются в core/config/mxapi.php.';
$_lang['setting_mxapi.context'] = 'Контекст по умолчанию';
$_lang['setting_mxapi.context_desc'] = 'Контекст MODX, в котором проверяются права и выполняются процессоры, если эндпоинт не объявил свой. Управляющие эндпоинты работают в mgr.';
$_lang['setting_mxapi.allow_request_context'] = 'Разрешить выбор контекста запросом';
$_lang['setting_mxapi.allow_request_context_desc'] = 'Позволяет вызывающей системе указать контекст заголовком X-MxApi-Context — только для эндпоинтов, объявивших контекст «из запроса». Нужно на мультисайте; по умолчанию выключено, так как расширяет поверхность атаки. Контекст всё равно обязан быть разрешён клиенту (поле contexts).';
$_lang['setting_mxapi.token_ttl'] = 'Время жизни токена, сек.';
$_lang['setting_mxapi.token_ttl_desc'] = 'Сколько живёт выданный bearer-токен. Отзыв работает мгновенно независимо от этого значения.';
$_lang['setting_mxapi.default_limit'] = 'Размер страницы по умолчанию';
$_lang['setting_mxapi.default_limit_desc'] = 'Применяется, когда клиент не передал limit.';
$_lang['setting_mxapi.max_limit'] = 'Максимальный размер страницы';
$_lang['setting_mxapi.max_limit_desc'] = 'Жёсткий потолок: значения выше приводятся к нему, чтобы один запрос не выгружал всю таблицу.';
$_lang['setting_mxapi.rate_limit_per_minute'] = 'Лимит запросов в минуту';
$_lang['setting_mxapi.rate_limit_per_minute_desc'] = 'Ограничение на клиента; 0 — выключено. У клиента может быть свой лимит в поле rate_limit — он важнее.';
$_lang['setting_mxapi.cursor_secret'] = 'Ключ подписи курсоров';
$_lang['setting_mxapi.cursor_secret_desc'] = 'Генерируется при установке. Курсор постраничного обхода подписывается этим ключом, поэтому чужой или подправленный курсор отбрасывается. Пустое значение выключает курсорную пагинацию; смена ключа обесценивает выданные курсоры — обходы начнутся заново.';
$_lang['setting_mxapi.trusted_proxies'] = 'Доверенные прокси';
$_lang['setting_mxapi.trusted_proxies_desc'] = 'IP через запятую. Только для них учитывается X-Forwarded-For при определении адреса клиента — иначе IP-ограничения можно обойти заголовком.';
$_lang['setting_mxapi.log_reads'] = 'Писать в журнал успешные чтения';
$_lang['setting_mxapi.log_reads_desc'] = 'Записи и ошибки пишутся всегда. Успешные чтения — только при «Да»: иначе журнал превращается в счётчик обращений.';
$_lang['setting_mxapi.log_lifetime'] = 'Срок хранения журнала, сек.';
$_lang['setting_mxapi.log_lifetime_desc'] = 'Старые записи удаляются автоматически, не чаще раза в час. 0 — не чистить.';
$_lang['setting_mxapi.cors_origins'] = 'Разрешённые Origin (CORS)';
$_lang['setting_mxapi.cors_origins_desc'] = 'Через запятую; «*» разрешает любой. Пусто — заголовки CORS не отдаются.';
$_lang['setting_mxapi.debug'] = 'Отдавать детали внутренних ошибок';
$_lang['setting_mxapi.debug_desc'] = 'Только для отладки: в ответе появляется текст исключения. На боевом сайте держать выключенным.';

$_lang['setting_mxapi.catalog_filter'] = 'Что показывать в каталоге';
$_lang['setting_mxapi.catalog_filter_desc'] = 'all — весь публичный контракт сайта (каталог работает как документация: видно, какие scope существуют); scope — только эндпоинты, которые можно вызвать предъявленным токеном; permission — только те, на которые у пользователя есть право MODX. Ужесточать стоит там, где на сайте несколько независимых интеграций: при all клиент одной видит состав эндпоинтов другой вместе с именами прав.';

/* Вкладка «mxApi» на странице правки пользователя: клиенты интеграции.
   Клиент — это пара client_id/client_secret, по которой внешняя система
   получает токен и работает от имени этого пользователя. */
$_lang['mxapi_client_intro'] = 'Клиенты интеграции работают от имени этого пользователя и ограничены его правами. Секрет показывается один раз — при создании и при перевыпуске.';
$_lang['mxapi_client_create'] = 'Добавить';
$_lang['mxapi_client_edit'] = 'Изменить';
$_lang['mxapi_client_remove'] = 'Удалить';
$_lang['mxapi_client_regenerate'] = 'Перевыпустить секрет';
$_lang['mxapi_client_activate'] = 'Включить';
$_lang['mxapi_client_deactivate'] = 'Отключить';

$_lang['mxapi_client_name'] = 'Название';
$_lang['mxapi_client_key'] = 'client_id';
$_lang['mxapi_client_scopes'] = 'Доступ';
$_lang['mxapi_client_tokens'] = 'Живых токенов';
$_lang['mxapi_client_createdon'] = 'Создан';

$_lang['mxapi_client_ttl'] = 'Время жизни токена';
$_lang['mxapi_client_ttl_site'] = 'Как на сайте';
$_lang['mxapi_client_ttl_custom'] = 'Своё значение';
$_lang['mxapi_client_ttl_never'] = 'Бессрочно';
$_lang['mxapi_client_ttl_seconds'] = 'Секунд';
$_lang['mxapi_client_ttl_sec_short'] = 'сек.';
$_lang['mxapi_client_ttl_never_warning'] = 'Бессрочный токен не истекает сам: отозвать его можно только вручную — отключением клиента или перевыпуском секрета с отзывом токенов. Включайте для интеграций, которые нельзя научить перевыпуску.';

$_lang['mxapi_client_window_create'] = 'Новый клиент интеграции';
$_lang['mxapi_client_window_update'] = 'Клиент интеграции';
$_lang['mxapi_client_scopes_caption'] = 'Что разрешено клиенту. Показаны только те scope, на которые у пользователя есть права MODX.';
$_lang['mxapi_client_scopes_all'] = 'Все эндпоинты';
$_lang['mxapi_client_scopes_none_allowed'] = 'Пользователю не выдан доступ к namespace mxapi — выдавать нечего.';
$_lang['mxapi_client_scopes_none_exist'] = 'На сайте нет ни одного эндпоинта со scope.';

$_lang['mxapi_client_secret_title'] = 'Учётные данные клиента';
$_lang['mxapi_client_secret_warning'] = 'Секрет показывается один раз. Скопируйте его сейчас: в базе хранится только хэш, и восстановить значение невозможно — останется лишь перевыпустить.';
$_lang['mxapi_client_secret_copy'] = 'Скопировать секрет';
$_lang['mxapi_client_secret_copied'] = 'Секрет скопирован';
$_lang['mxapi_client_remove_confirm'] = 'Удалить клиента вместе с его токенами? Интеграция, которая ими пользуется, перестанет работать сразу.';
$_lang['mxapi_client_regenerate_confirm'] = 'Будет выпущен новый секрет, старый перестанет работать. Уже выданные токены продолжают действовать до истечения — отзовите их, если секрет скомпрометирован.';
$_lang['mxapi_client_revoke_tokens'] = 'Отозвать выданные токены';

$_lang['mxapi_client_err_user_ns'] = 'Не указан пользователь.';
$_lang['mxapi_client_err_user_nf'] = 'Пользователь не найден.';
$_lang['mxapi_client_err_nf'] = 'Клиент не найден.';
$_lang['mxapi_client_err_name_ns'] = 'Укажите название клиента.';
$_lang['mxapi_client_err_scopes_ns'] = 'Выберите хотя бы один scope.';
$_lang['mxapi_client_err_scope_na'] = 'Scope недоступен этому пользователю: [[+scope]]';
$_lang['mxapi_client_err_ttl'] = 'Время жизни токена: 0 — как на сайте, -1 — бессрочно, иначе не меньше 60 секунд.';
$_lang['mxapi_client_err_save'] = 'Не удалось сохранить клиента.';
$_lang['mxapi_client_err_remove'] = 'Не удалось удалить клиента.';

$_lang['mxapi_client_actions'] = 'Действия';
$_lang['mxapi_client_regenerate_short'] = 'Перевыпустить';

/* Интерфейс каталога эндпоинтов (Vue). Строки в компонентах не хардкодятся —
   только ключи, тексты живут здесь и прокидываются в MODx.lang. */
$_lang['mxapi_vuetools_required'] = 'Для работы интерфейса mxApi требуется пакет VueTools. Установите его через «Менеджер пакетов».';
$_lang['mxapi_catalog_loading'] = 'Загрузка…';
$_lang['mxapi_catalog_error'] = 'Не удалось загрузить каталог эндпоинтов.';
$_lang['mxapi_catalog_empty'] = 'Ничего не найдено.';
$_lang['mxapi_catalog_search_ph'] = 'Поиск по маршруту, scope, праву…';
$_lang['mxapi_catalog_all_providers'] = 'Все источники';
$_lang['mxapi_catalog_download_openapi'] = 'Скачать OpenAPI';
$_lang['mxapi_catalog_base_url'] = 'Базовый адрес:';
$_lang['mxapi_catalog_count'] = 'эндпоинтов: [[+shown]] из [[+total]]';
$_lang['mxapi_catalog_disabled'] = 'API выключен настройкой mxapi.enabled: запросы получают 503.';

$_lang['mxapi_badge_internal'] = 'служебный';
$_lang['mxapi_badge_internal_hint'] = 'Не отдаётся во внешний каталог и OpenAPI.';
$_lang['mxapi_badge_write'] = 'запись';
$_lang['mxapi_badge_deprecated'] = 'устарел';

$_lang['mxapi_detail_id'] = 'Идентификатор';
$_lang['mxapi_detail_url'] = 'Полный адрес';
$_lang['mxapi_detail_scope'] = 'Scope';
$_lang['mxapi_detail_permission'] = 'Право MODX';
$_lang['mxapi_detail_auth'] = 'Аутентификация';
$_lang['mxapi_detail_context'] = 'Контекст MODX';
$_lang['mxapi_detail_provider'] = 'Источник';
$_lang['mxapi_detail_route_template'] = 'Шаблон маршрута';
$_lang['mxapi_detail_processor'] = 'Процессор';
$_lang['mxapi_detail_not_required'] = 'не требуется';
$_lang['mxapi_detail_bearer'] = 'Bearer-токен';
$_lang['mxapi_context_request'] = 'из запроса (X-MxApi-Context)';
$_lang['mxapi_context_default'] = 'по умолчанию (mxapi.context)';

$_lang['mxapi_param_none'] = 'Параметров нет.';
$_lang['mxapi_param_name'] = 'Параметр';
$_lang['mxapi_param_type'] = 'Тип';
$_lang['mxapi_param_in'] = 'Где';
$_lang['mxapi_param_required'] = 'Обязателен';
$_lang['mxapi_param_description'] = 'Описание';
$_lang['mxapi_param_default'] = 'по умолчанию';
$_lang['mxapi_param_enum'] = 'значения:';

$_lang['mxapi_yes'] = 'да';
$_lang['mxapi_no'] = 'нет';
$_lang['mxapi_save'] = 'Сохранить';
$_lang['mxapi_cancel'] = 'Отмена';
$_lang['mxapi_close'] = 'Закрыть';

$_lang['mxapi_client_empty'] = 'Клиентов интеграции пока нет.';
$_lang['mxapi_client_status'] = 'Состояние';
$_lang['mxapi_client_active'] = 'включён';
$_lang['mxapi_client_inactive'] = 'отключён';
$_lang['mxapi_client_err_load'] = 'Не удалось загрузить список клиентов.';
$_lang['mxapi_client_tokens_revoked'] = 'Отозвано токенов: [[+count]]';

$_lang['mxapi_catalog_empty_none'] = 'Эндпоинтов пока нет.';
$_lang['mxapi_catalog_empty_none_hint'] = 'Ядро отдаёт только выпуск токенов и мета-эндпоинты. Остальное поставляют провайдеры: пакет с плагином на событии mxApiOnRegisterEndpoints или ключ providers в core/config/mxapi.php.';
$_lang['mxapi_catalog_reset_filters'] = 'Сбросить фильтры';
$_lang['mxapi_curl_copy'] = 'Скопировать пример вызова';
$_lang['mxapi_curl_copied'] = 'Пример скопирован';

$_lang['mxapi_client_empty_hint'] = 'Клиент — это пара client_id и client_secret, по которой внешняя система получает токен и работает от имени этого пользователя.';
$_lang['mxapi_client_saved'] = 'Клиент сохранён';
$_lang['mxapi_client_removed'] = 'Клиент удалён';
