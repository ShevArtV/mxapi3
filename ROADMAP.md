# mxapi3 Roadmap

> Last updated: 2026-07-29

`mxapi3` is the MODX 3 line of mxapi. Its job is to port the mxapi concept to
MODX 3, make mxapi the primary public API layer, and connect miniShop3 through an
adapter/provider instead of exposing miniShop3 as a competing integration surface.

## Product Direction

`mxapi3` owns the external integration contract:

- public routes under mxapi, for example `/mxapi/v1/...`;
- bearer-token authentication and endpoint scopes;
- MODX 3 namespace/policy checks;
- endpoint registry and metadata;
- CMP endpoint catalog similar to a small Swagger UI;
- OpenAPI export from registry metadata;
- MODX 3 core endpoints;
- miniShop3 provider built on existing miniShop3 services/router where practical.

miniShop3 keeps its own internal/web API. External integrations should target
mxapi. If miniShop3 internals change, mxapi adapters preserve the public contract.

## Phase 1: MODX 3 Platform Adapter

1. Add a MODX 3 bootstrap.
   Location: `assets/components/mxapi/api.php` or equivalent public front
   controller.
   Reason: MODX 3 uses namespaced classes and modern service loading, so the
   MODX 2 bootstrap should not be copied blindly.

2. Introduce a platform adapter interface.
   Location: mxapi core `src/Platform/`.
   Reason: shared mxapi code should ask the adapter to load services, run
   processors, check permissions and resolve class names.

3. Implement `Modx3PlatformAdapter`.
   Location: mxapi3 source tree.
   Reason: isolate MODX 3 differences such as `MODX\Revolution\modX`,
   namespaced processors and service container usage.

4. Keep endpoint metadata format compatible with mxapi2.
   Location: shared endpoint contracts.
   Reason: documentation, CMP and OpenAPI generation should work similarly on
   MODX 2 and MODX 3.

## Phase 2: Routing And Public Namespace

1. Use mxapi-owned routes as the external API.
   Location: web server route and mxapi public entrypoint.
   Reason: avoid collisions with miniShop3 routes such as `/api/v1/order/...`.

2. Reserve `/mxapi/v1/...` for public integration endpoints.
   Location: routing docs and package defaults.
   Reason: mxapi should have a stable namespace independent of site and package
   internals. Owner decision 2026-07-30: the prefix is `/mxapi/v1/...`, not the
   `/api/mx/v1/...` of earlier drafts — mxapi2 already ships this default, and
   both lines must expose the same public namespace.

3. Add compatibility aliases only per project.
   Location: project config, not package defaults.
   Reason: legacy routes like `/api/v1/orders/export` are Sleep & Glow
   compatibility, not a generic mxapi convention.

## Phase 3: CMP Mini Swagger For MODX 3

1. Build a read-only CMP endpoint catalog first.
   Location: MODX 3 manager controller and frontend assets.
   Reason: endpoint discovery is needed before route editing or test console.

2. Show provider boundaries.
   Location: CMP list/detail UI.
   Reason: users must see whether an endpoint is implemented by mxapi core,
   MODX core provider, miniShop3 provider or project custom code.

3. Add OpenAPI export from live registry.
   Location: `/mxapi/v1/meta/openapi` and CMP download action.
   Reason: OpenAPI should be generated from the same metadata that drives the
   CMP.

4. Add examples per endpoint.
   Location: endpoint metadata.
   Reason: the CMP should be useful as an integration handoff document, not only
   a route list.

## Phase 4: MODX 3 Core Provider

1. Add resource endpoints.
   Location: `Provider/ModxCore/Resources`.
   Reason: resources are the first useful management API surface.

2. Add resource group endpoints.
   Location: `Provider/ModxCore/ResourceGroups`.
   Reason: resource access automation needs group assignment support.

3. Add user group endpoints.
   Location: `Provider/ModxCore/UserGroups`.
   Reason: API clients often need group membership, but it must be permission
   gated.

4. Add user endpoints last.
   Location: `Provider/ModxCore/Users`.
   Reason: user management is high-risk and needs allow-lists, sudo protection,
   blocked-state handling and audit logs.

5. Prefer MODX processors or services over direct xPDO writes.
   Location: platform adapter and endpoint handlers.
   Reason: direct writes can bypass permissions, events, validation and cache
   invalidation.

## Phase 5: miniShop3 Provider

1. Discover miniShop3 availability.
   Location: `Provider/MiniShop3`.
   Reason: mxapi3 should work on MODX 3 sites with or without miniShop3.

2. Wrap miniShop3 through an adapter.
   Location: `Provider/MiniShop3/MiniShop3Adapter`.
   Reason: mxapi should not duplicate miniShop3 business logic.

3. Reuse miniShop3 router/services where practical.
   Location: miniShop3 provider handlers.
   Reason: miniShop3 already has FastRoute routing, controllers, order services
   and JSON responses.

4. Normalize public responses through mxapi.
   Location: miniShop3 provider response adapter.
   Reason: external clients should receive a consistent mxapi contract even when
   miniShop3 internals return their own response shape.

5. Classify miniShop3 endpoints by context.
   Location: miniShop3 provider metadata.
   Reason: some miniShop3 routes are web-session/cart/order-draft endpoints and
   should not automatically become bearer-token public API.

Initial candidate groups:

- `ms3.cart.*`
- `ms3.order.*`
- `ms3.products.*`
- `ms3.references.*`

## Phase 6: Public Contract And Migration

1. Define mxapi contracts for e-commerce actions.
   Location: OpenAPI metadata and provider handlers.
   Reason: public clients should not depend on miniShop3 route names or response
   internals.

2. Add compatibility mapping from miniShop3 routes where useful.
   Location: miniShop3 provider.
   Reason: existing miniShop3 frontend/API knowledge can be reused without
   exposing it as the canonical contract.

3. Add migration notes from mxapi2.
   Location: docs.
   Reason: Sleep & Glow and future projects need a clear MODX 2 -> MODX 3 path.

4. Add install smoke checks.
   Location: scripts/docs.
   Reason: a MODX 3 install must prove token issue, endpoint catalog, OpenAPI
   export and one read endpoint.

## Safety Rules

- mxapi is the external API path; miniShop3 is an implementation provider.
- Do not expose all miniShop3 routes automatically.
- Do not let web-session miniShop3 endpoints become bearer-token endpoints
  without explicit metadata and permissions.
- Do not use direct writes for MODX users unless processor/service coverage is
  insufficient and the handler has strict validation and audit logs.
- Keep `/api/v1` out of package defaults to avoid miniShop3 and legacy route
  collisions.
