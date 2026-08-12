<?php
/**
 * English lexicon for mxApi.
 *
 * @package mxapi
 * @subpackage lexicon
 */

$_lang['mxapi'] = 'mxApi';
$_lang['mxapi_menu_desc'] = 'API endpoint catalog and integration clients.';

$_lang['mxapi_endpoints'] = 'Endpoints';
$_lang['mxapi_clients'] = 'Clients';
$_lang['mxapi_tokens'] = 'Tokens';
$_lang['mxapi_log'] = 'Log';

$_lang['mxapi_err_no_permission'] = 'Insufficient permissions for this action.';
$_lang['mxapi_err_not_found'] = 'Object not found.';
$_lang['mxapi_err_no_vendor'] = 'mxApi dependencies are missing: no vendor/autoload.php in core/components/mxapi/.';

/* System settings labels; see the Russian file for the rationale. */
$_lang['area_mxapi'] = 'mxApi';
$_lang['area_mxapi:default'] = 'mxApi';
$_lang['area_mxapi_access'] = 'mxApi: access and contexts';
$_lang['area_mxapi_limits'] = 'mxApi: limits and paging';
$_lang['area_mxapi_log'] = 'mxApi: journal and debug';

$_lang['setting_mxapi.enabled'] = 'API enabled';
$_lang['setting_mxapi.enabled_desc'] = 'Master switch: when off, the entry point answers 503 to any request, including the endpoint catalog.';
$_lang['setting_mxapi.route_prefix'] = 'Route prefix';
$_lang['setting_mxapi.route_prefix_desc'] = 'Public prefix for API routes, /mxapi/v1 by default. Project aliases go to core/config/mxapi.php.';
$_lang['setting_mxapi.context'] = 'Default context';
$_lang['setting_mxapi.context_desc'] = 'MODX context used to check permissions and run processors unless the endpoint declares its own. Management endpoints run in mgr.';
$_lang['setting_mxapi.allow_request_context'] = 'Allow context from request';
$_lang['setting_mxapi.allow_request_context_desc'] = 'Lets the caller pick a context via the X-MxApi-Context header, and only for endpoints declaring context "from request". Needed for multisite; off by default because it widens the attack surface. The context must still be allowed for the client (contexts field).';
$_lang['setting_mxapi.token_ttl'] = 'Token lifetime, sec';
$_lang['setting_mxapi.token_ttl_desc'] = 'How long an issued bearer token lives. Revocation is immediate regardless of this value.';
$_lang['setting_mxapi.default_limit'] = 'Default page size';
$_lang['setting_mxapi.default_limit_desc'] = 'Used when the client sends no limit.';
$_lang['setting_mxapi.max_limit'] = 'Maximum page size';
$_lang['setting_mxapi.max_limit_desc'] = 'Hard ceiling: larger values are clamped so a single request cannot dump the whole table.';
$_lang['setting_mxapi.rate_limit_per_minute'] = 'Requests per minute';
$_lang['setting_mxapi.rate_limit_per_minute_desc'] = 'Per-client limit; 0 disables it. A client may carry its own rate_limit, which wins.';
$_lang['setting_mxapi.cursor_secret'] = 'Cursor signing key';
$_lang['setting_mxapi.cursor_secret_desc'] = 'Generated on install. Pagination cursors are signed with it, so a foreign or tampered cursor is rejected. An empty value disables cursor pagination; changing the key invalidates issued cursors and walks start over.';
$_lang['setting_mxapi.trusted_proxies'] = 'Trusted proxies';
$_lang['setting_mxapi.trusted_proxies_desc'] = 'Comma separated IPs. X-Forwarded-For is honoured only for these, otherwise IP restrictions could be bypassed with a header.';
$_lang['setting_mxapi.log_reads'] = 'Log successful reads';
$_lang['setting_mxapi.log_reads_desc'] = 'Writes and errors are always logged. Successful reads only when enabled, otherwise the journal becomes a hit counter.';
$_lang['setting_mxapi.log_lifetime'] = 'Journal retention, sec';
$_lang['setting_mxapi.log_lifetime_desc'] = 'Old records are purged automatically, at most once an hour. 0 keeps everything.';
$_lang['setting_mxapi.cors_origins'] = 'Allowed CORS origins';
$_lang['setting_mxapi.cors_origins_desc'] = 'Comma separated; "*" allows any. Empty means no CORS headers.';
$_lang['setting_mxapi.debug'] = 'Expose internal error details';
$_lang['setting_mxapi.debug_desc'] = 'Debug only: the response carries the exception message. Keep it off in production.';

$_lang['setting_mxapi.catalog_filter'] = 'Catalog visibility';
$_lang['setting_mxapi.catalog_filter_desc'] = 'all — the whole public contract (the catalog works as documentation, so integrators can see which scopes exist); scope — only endpoints callable with the presented token; permission — only endpoints the user holds a MODX permission for. Tighten it when several independent integrations share the site: with all, one client sees the other one\'s endpoints along with permission names.';

/* mxApi tab on the user edit page: integration clients. */
$_lang['mxapi_client_intro'] = 'Integration clients act on behalf of this user and are limited by their permissions. The secret is shown once — on creation and on regeneration.';
$_lang['mxapi_client_create'] = 'Add';
$_lang['mxapi_client_edit'] = 'Edit';
$_lang['mxapi_client_remove'] = 'Delete';
$_lang['mxapi_client_regenerate'] = 'Regenerate secret';
$_lang['mxapi_client_activate'] = 'Enable';
$_lang['mxapi_client_deactivate'] = 'Disable';

$_lang['mxapi_client_name'] = 'Name';
$_lang['mxapi_client_key'] = 'client_id';
$_lang['mxapi_client_scopes'] = 'Access';
$_lang['mxapi_client_tokens'] = 'Live tokens';
$_lang['mxapi_client_createdon'] = 'Created';

$_lang['mxapi_client_ttl'] = 'Token lifetime';
$_lang['mxapi_client_ttl_site'] = 'Site default';
$_lang['mxapi_client_ttl_custom'] = 'Custom value';
$_lang['mxapi_client_ttl_never'] = 'Never expires';
$_lang['mxapi_client_ttl_seconds'] = 'Seconds';
$_lang['mxapi_client_ttl_sec_short'] = 'sec.';
$_lang['mxapi_client_ttl_never_warning'] = 'A never-expiring token does not lapse on its own: it can only be revoked manually — by disabling the client or regenerating the secret with token revocation. Use it for integrations that cannot be taught to refresh tokens.';

$_lang['mxapi_client_window_create'] = 'New integration client';
$_lang['mxapi_client_window_update'] = 'Integration client';
$_lang['mxapi_client_scopes_caption'] = 'What the client is allowed to do. Only scopes the user has MODX permissions for are listed.';
$_lang['mxapi_client_scopes_all'] = 'All endpoints';
$_lang['mxapi_client_scopes_none_allowed'] = 'The user has no access to the mxapi namespace — nothing to grant.';
$_lang['mxapi_client_scopes_none_exist'] = 'This site has no endpoints with a scope.';

$_lang['mxapi_client_secret_title'] = 'Client credentials';
$_lang['mxapi_client_secret_warning'] = 'The secret is shown once. Copy it now: only a hash is stored, the value cannot be recovered — you would have to regenerate it.';
$_lang['mxapi_client_secret_copy'] = 'Copy secret';
$_lang['mxapi_client_secret_copied'] = 'Secret copied';
$_lang['mxapi_client_remove_confirm'] = 'Delete this client together with its tokens? Any integration using them stops working immediately.';
$_lang['mxapi_client_regenerate_confirm'] = 'A new secret will be issued and the old one stops working. Tokens already issued remain valid until they expire — revoke them if the secret is compromised.';
$_lang['mxapi_client_revoke_tokens'] = 'Revoke issued tokens';

$_lang['mxapi_client_err_user_ns'] = 'No user specified.';
$_lang['mxapi_client_err_user_nf'] = 'User not found.';
$_lang['mxapi_client_err_nf'] = 'Client not found.';
$_lang['mxapi_client_err_name_ns'] = 'Enter a client name.';
$_lang['mxapi_client_err_scopes_ns'] = 'Select at least one scope.';
$_lang['mxapi_client_err_scope_na'] = 'Scope is not available to this user: [[+scope]]';
$_lang['mxapi_client_err_ttl'] = 'Token lifetime: 0 — site default, -1 — never expires, otherwise at least 60 seconds.';
$_lang['mxapi_client_err_save'] = 'Could not save the client.';
$_lang['mxapi_client_err_remove'] = 'Could not delete the client.';

$_lang['mxapi_client_actions'] = 'Actions';
$_lang['mxapi_client_regenerate_short'] = 'Regenerate';

/* Vue-based endpoint catalog UI. Component code carries keys only — the texts
   live here and are passed to MODx.lang. */
$_lang['mxapi_vuetools_required'] = 'The mxApi interface requires the VueTools package. Install it via Package Manager.';
$_lang['mxapi_catalog_loading'] = 'Loading…';
$_lang['mxapi_catalog_error'] = 'Could not load the endpoint catalog.';
$_lang['mxapi_catalog_empty'] = 'Nothing found.';
$_lang['mxapi_catalog_search_ph'] = 'Search by route, scope, permission…';
$_lang['mxapi_catalog_all_providers'] = 'All providers';
$_lang['mxapi_catalog_download_openapi'] = 'Download OpenAPI';
$_lang['mxapi_catalog_base_url'] = 'Base URL:';
$_lang['mxapi_catalog_count'] = 'endpoints: [[+shown]] of [[+total]]';
$_lang['mxapi_catalog_disabled'] = 'The API is switched off by mxapi.enabled: every request gets 503.';

$_lang['mxapi_badge_internal'] = 'internal';
$_lang['mxapi_badge_internal_hint'] = 'Never exposed in the public catalog or OpenAPI.';
$_lang['mxapi_badge_write'] = 'write';
$_lang['mxapi_badge_deprecated'] = 'deprecated';

$_lang['mxapi_detail_id'] = 'Identifier';
$_lang['mxapi_detail_url'] = 'Full URL';
$_lang['mxapi_detail_scope'] = 'Scope';
$_lang['mxapi_detail_permission'] = 'MODX permission';
$_lang['mxapi_detail_auth'] = 'Authentication';
$_lang['mxapi_detail_context'] = 'MODX context';
$_lang['mxapi_detail_provider'] = 'Provider';
$_lang['mxapi_detail_route_template'] = 'Route template';
$_lang['mxapi_detail_processor'] = 'Processor';
$_lang['mxapi_detail_not_required'] = 'not required';
$_lang['mxapi_detail_bearer'] = 'Bearer token';
$_lang['mxapi_context_request'] = 'from request (X-MxApi-Context)';
$_lang['mxapi_context_default'] = 'default (mxapi.context)';

$_lang['mxapi_param_none'] = 'No parameters.';
$_lang['mxapi_param_name'] = 'Parameter';
$_lang['mxapi_param_type'] = 'Type';
$_lang['mxapi_param_in'] = 'In';
$_lang['mxapi_param_required'] = 'Required';
$_lang['mxapi_param_description'] = 'Description';
$_lang['mxapi_param_default'] = 'default';
$_lang['mxapi_param_enum'] = 'values:';

$_lang['mxapi_yes'] = 'yes';
$_lang['mxapi_no'] = 'no';
$_lang['mxapi_save'] = 'Save';
$_lang['mxapi_cancel'] = 'Cancel';
$_lang['mxapi_close'] = 'Close';

$_lang['mxapi_client_empty'] = 'No integration clients yet.';
$_lang['mxapi_client_status'] = 'State';
$_lang['mxapi_client_active'] = 'enabled';
$_lang['mxapi_client_inactive'] = 'disabled';
$_lang['mxapi_client_err_load'] = 'Could not load the client list.';
$_lang['mxapi_client_tokens_revoked'] = 'Tokens revoked: [[+count]]';

$_lang['mxapi_catalog_empty_none'] = 'No endpoints yet.';
$_lang['mxapi_catalog_empty_none_hint'] = 'The core ships token issuing and meta endpoints only. Everything else comes from providers: a package plugin on the mxApiOnRegisterEndpoints event, or the providers key in core/config/mxapi.php.';
$_lang['mxapi_catalog_reset_filters'] = 'Reset filters';
$_lang['mxapi_curl_copy'] = 'Copy the example call';
$_lang['mxapi_curl_copied'] = 'Example copied';

$_lang['mxapi_client_empty_hint'] = 'A client is a client_id / client_secret pair an external system uses to get a token and act as this user.';
$_lang['mxapi_client_saved'] = 'Client saved';
$_lang['mxapi_client_removed'] = 'Client removed';
