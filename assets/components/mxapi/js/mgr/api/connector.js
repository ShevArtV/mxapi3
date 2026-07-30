import { useApi } from '@vuetools/useApi';

// Конфиг кладёт в <head> контроллер CMP (каталог) или плагин вкладки (клиенты).
const cfg = () => window.MxApiConfig || window.MxApiUserClients || {};

// Единый HTTP-слой на useApi (VueTools), а не свой fetch. useApi берёт
// baseUrl/token из window.MODx по умолчанию — у нас свой коннектор компонента и
// токен в конфиге страницы, поэтому передаём их явно. Инициализация ленивая:
// конфиг кладётся до модуля, но так безопаснее к порядку загрузки.
let _api = null;
function api() {
  if (!_api) {
    const c = cfg();
    _api = useApi({ baseUrl: c.connector_url, authToken: c.token });
  }
  return _api;
}

// action=FQCN процессора, тело — FormData. useApi БРОСАЕТ при !res.ok и при
// success===false (тело в err.data) — весь фронт работает через try/catch, а не
// проверку res.success. X-Requested-With useApi сам не шлёт.
const OPTS = { headers: { 'X-Requested-With': 'XMLHttpRequest' } };
const post = (action, params = {}) => api().post(action, params, OPTS);

const P = 'MxApi\\Processors\\Mgr\\';

/**
 * Текст ошибки из проваленного вызова useApi. Тело ответа MODX — в err.data:
 * message (общая ошибка) или errors[] (валидация полей). Иначе err.message.
 */
export function errorMessage(err, fallback = '') {
  if (!err) return fallback;
  const body = err.data ?? err;
  if (body.message) return String(body.message);
  if (Array.isArray(body.errors) && body.errors.length) {
    const parts = body.errors.map((e) => e.msg || e.message).filter(Boolean);
    if (parts.length) return parts.join('; ');
  }
  return err.message ? String(err.message) : fallback;
}

/** Список из успешного ответа: results (гриды MODX) или object (наш success). */
export function listOf(res) {
  if (!res) return [];
  if (Array.isArray(res.results)) return res.results;
  if (Array.isArray(res.object)) return res.object;
  return [];
}

// Каталог эндпоинтов и выгрузка OpenAPI — страница CMP.
export const CatalogApi = {
  getList: (params = {}) => post(P + 'Endpoints\\GetList', params),
  openapi: () => post(P + 'OpenApi\\Get'),
};

// Клиенты интеграции — вкладка на странице правки пользователя. Массив scopes
// уходит JSON-строкой: useApi раскладывает массивы в FormData поэлементно, а
// процессор всё равно принимает JSON (parseScopes).
export const ClientApi = {
  getList: (userId) => post(P + 'Client\\GetList', { user_id: userId }),
  scopes: (userId) => post(P + 'Client\\Scopes', { user_id: userId }),
  create: (userId, data) =>
    post(P + 'Client\\Create', {
      user_id: userId,
      name: data.name,
      scopes: JSON.stringify(data.scopes || []),
      token_ttl: data.token_ttl ?? 0,
    }),
  update: (userId, id, data) => {
    const params = { user_id: userId, id };
    if (data.name !== undefined) params.name = data.name;
    if (data.scopes !== undefined) params.scopes = JSON.stringify(data.scopes || []);
    if (data.token_ttl !== undefined) params.token_ttl = data.token_ttl;
    if (data.active !== undefined) params.active = data.active ? 1 : 0;
    return post(P + 'Client\\Update', params);
  },
  remove: (userId, id) => post(P + 'Client\\Remove', { user_id: userId, id }),
  regenerate: (userId, id, revokeTokens = false) =>
    post(P + 'Client\\Regenerate', { user_id: userId, id, revoke_tokens: revokeTokens ? 1 : 0 }),
};

export const config = cfg;
