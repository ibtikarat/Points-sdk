# Decisions

This file records the backend-derived decisions used while building the SDK.

## Confirmed From `points-backend`

- Authentication uses a single header: `x-api-key`.
- The private key is matched against `keys.private_key`.
- A missing or invalid API key returns HTTP `400`, not `401`.
- Checkout is the only public order endpoint and uses `{publicKey}` in the URL path.
- The merchant API route prefix is `/api/v1`.
- Webhook verification uses `X-Webhook-Secret` with direct shared-secret comparison.
- Webhook events are also identified by `X-Webhook-Event`.
- Phone numbers are normalized to Saudi local format `5XXXXXXXX`.
- Payment methods are numeric string codes, not human-readable slugs.

## Differences From The Original Prompt

- The backend does not use dual API keys for authentication.
- The backend does not expose a fixed production base URL in code, so the SDK requires `base_url` explicitly.
- The live merchant API currently exposes orders and webhooks. Product merchant endpoints exist in comments only and were not implemented in the SDK.
- The original prompt said "15 endpoints"; the active merchant API currently exposes 14 route definitions:
  - 8 order routes
  - 6 webhook routes
- Refund webhooks exist in backend jobs, but the event shape was inferred from backend event names rather than an explicit published fixture.

## Assumptions

- Production users will pass `https://business.papp.sa` as `base_url`.
- The SDK keeps raw response payloads on DTOs where forward compatibility is helpful.
- Laravel support is optional and isolated under `src/Laravel`.

