# Mobile JSON APIs — base URL & routes

Use this **base URL** for local XAMPP (Apache on port **8080**):

**`http://localhost:8080/property`**

Append paths below **without** an extra slash (e.g. `http://localhost:8080/property/api/mobile/home`). **Wrong:** `http://localhost:8080/property//api/...` (double slash after `property/`) — can yield empty or 404 responses.

If routing returns 404, insert **`index.php`** after the base:

`http://localhost:8080/property/index.php/api/mobile/home`

**Property (core) — working samples (Nobroker data lives in `nb_properties`; use an id that exists in your DB, e.g. from the list response):**

| | URL |
|---|-----|
| List | `http://localhost:8080/property/index.php/api/mobile/properties-core?limit=10` |
| One by id | `http://localhost:8080/property/index.php/api/mobile/properties-core/2` |

Same paths without `index.php` if Apache rewrite is enabled. Optional: `?userId=8` for `isLiked` on list or detail.

| Item | Value |
|------|--------|
| **JSON bodies** | `Content-Type: application/json` for POST |
| **Responses** | `Content-Type: application/json` |

---

## Response envelope (`api/mobile/*`)

```json
{
  "success": true,
  "message": "Human-readable message",
  "data": { }
}
```

Errors: `"success": false`, optional `"errors": { }`. Nobroker `api/nb/*` often returns `{ "success", "message" }` and may omit `data`.

---

## Core entity shapes (camelCase, `api/mobile` contract)

### Live update (Property Live / Reels / Updates)

List: each item in `data.liveUpdates`. Single: `data` is one object (`GET …/live-updates/{id}`). Shape:

| Field | Type | Notes |
|-------|------|--------|
| `id` | string | |
| `image` | string | Absolute URL or empty |
| `title`, `description` | string | |
| `liveTime`, `createdAt` | string | Timestamps from DB (ISO-style strings) |
| `platform` | string | `youtube` \| `instagram` \| `app` (normalized in API) |
| `url` | string | |

### Property (core)

| Field | Type | Notes |
|-------|------|--------|
| `id` | string | |
| `listingType` | string | `buy` \| `rent` |
| `propertyCategory` | string | `residential` \| `commercial` |
| `title`, `description` | string | |
| `images` | string[] | Absolute URLs |
| `isFeatured`, `isRecommended`, `isLiked` | boolean | `isLiked` when `userId` query set |
| `addedTime` | string | |
| `details` | object | Full listing payload: `source` is `nb` or `legacy`. **Nobroker:** price, bedrooms, city, amenities, `imageUrls`, `propertyUrl`, etc. **Legacy `properties` table:** price, gallery, nearby, features, etc. Same object on `properties-core`, `properties-core/{id}`, `home` featured/latest, and related list endpoints. |

### User (profile)

| Field | Type |
|-------|------|
| `id`, `name`, `phone`, `email` | string |
| `isAgent`, `isApproved` | boolean |
| `profileImage`, `logo` | string |
| `aadharNumber`, `aadharImage`, `address` | string |
| `experienceYears` | number |
| `createdAt` | string |

**`save_profile` (`POST /api/mobile/save_profile`):** optional **`city_id`** or **`cityId`** in the JSON body. If present, it must exist in **`nb_cities`** or **`cities`** (otherwise the API returns `success: false` with a validation error). When valid, the profile’s `city` text is set from that row and **`nb_users.city_id`** is updated when that column exists and the user has an `nb_users` row.

### Wishlist (minimal), Enquiry, Housing news, Feedback

See **`Api_mobile.php`** (`_format_*` methods) for full fields returned in `data`.

---

## A. `api/mobile/*` — GET (no user id required)

Public or catalog endpoints. Base: `http://localhost:8080/property`. Replace `{id}` with a real resource id.

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/mobile/live-updates/{id}` | One live update by **live update id** |
| `http://localhost:8080/property/api/mobile/housing-news` | News list. Query: `category`, `limit`, `offset`. Demo rows + images: `application/sql/seed_housing_news.sql` |
| `http://localhost:8080/property/api/mobile/housing-news/{id}` | One article (`multiImages` are absolute URLs from stored paths) |
| `http://localhost:8080/property/api/mobile/cities` | Cities |
| `http://localhost:8080/property/api/mobile/categories` | Categories |
| `http://localhost:8080/property/api/mobile/locations` | Locations |
| `http://localhost:8080/property/api/mobile/blogs` | Blogs |
| `http://localhost:8080/property/api/mobile/banners` | Banners |
| `http://localhost:8080/property/api/mobile/offer_banners` | Offer banners |
| `http://localhost:8080/property/api/mobile/notifications` | Notifications |

---

## A2. `api/mobile/*` — GET (by user id — query or path)

Use these when the client knows the **user id** (legacy `users.id` or **`nb_users.id`** for Nobroker tenants — same numeric id in mobile payloads). Example: `userId=8` or `user_id=8`.

**Query parameter user id**

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/mobile/home?userId=8` | Home; `isLiked` on properties uses wishlist for that user |
| `http://localhost:8080/property/api/mobile/properties?userId=8` | Property list; `isLiked` when `userId` set |
| `http://localhost:8080/property/api/mobile/properties/12?userId=8` | Single property + wishlist flag |
| `http://localhost:8080/property/api/mobile/properties-core?userId=8` | Core list with `isLiked` |
| `http://localhost:8080/property/api/mobile/properties-core/12?userId=8` | Single core property |
| `http://localhost:8080/property/api/mobile/properties/featured?userId=8` | Featured + `isLiked` |
| `http://localhost:8080/property/api/mobile/properties/latest?userId=8` | Latest + `isLiked` |
| `http://localhost:8080/property/api/mobile/live-updates?userId=8` | Live rows for publisher **user** `8` (requires `live_updates.userId`; migration `017_live_updates_user_scope.sql`) |
| `http://localhost:8080/property/api/mobile/live-updates?user_id=8` | Same as `userId` (alternate query name) |
| `http://localhost:8080/property/api/mobile/feedback?userId=8` | **Required:** `userId` or `user_id` — only that user’s feedback |
| `http://localhost:8080/property/api/mobile/wishlist?userId=8` | Wishlist rows for that user |
| `http://localhost:8080/property/api/mobile/wishlist/check?userId=8&propertyId=12` | Or `user_id` / `property_id` |
| `http://localhost:8080/property/api/mobile/wishlist/list?userId=8` | Minimal wishlist list |
| `http://localhost:8080/property/api/mobile/profile?userId=8` | Profile for that id (`user_id` works too) |

**Path segment user id**

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/mobile/live-updates/user/8` | Live updates published by user `8` (same as `?userId=8`) |
| `http://localhost:8080/property/api/mobile/enquiries/user/8` | Enquiries **sent by** tenant/user `8`. Query: `status`, `limit`, `offset` |
| `http://localhost:8080/property/api/mobile/enquiries/customer/8` | Enquiries linked to customer `8` (legacy path; Nobroker uses `nb_enquiries` + same id) |

**Session-only (no user id in URL)**

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/mobile/check` | Uses logged-in **session** `user_id` if present |

More routes: `application/config/routes.php` (search `api/mobile`).

---

## B. `api/mobile/*` — POST (no user id in body — optional)

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/mobile/properties/search` | Search body; **`userId` optional** in query/body for `isLiked` |
| `http://localhost:8080/property/api/mobile/contact` | Contact form (see `Api_mobile.php`) |

---

## B2. `api/mobile/*` — POST (requires `userId` or `user_id` in body)

| Full URL | Body fields |
|----------|-------------|
| `http://localhost:8080/property/api/mobile/feedback` | `userId` (or `user_id`), `title`; optional `description` |
| `http://localhost:8080/property/api/mobile/wishlist` | `userId`, `propertyId` — add |
| `http://localhost:8080/property/api/mobile/wishlist/store` | `user_id`, `property_id` — toggle |
| `http://localhost:8080/property/api/mobile/wishlist/remove` | `user_id`, `property_id` |
| `http://localhost:8080/property/api/mobile/wishlist/delete/{propertyId}` | Same as remove: **`userId`** (or `user_id`) in query or JSON body; **`propertyId`** in path or body. **DELETE** or **POST** (REST-style delete). |
| `http://localhost:8080/property/api/mobile/enquiry` | `userId`, `property_id`, `name`, `email`, `phone`, `message` |

OTP / profile / logout and other POST endpoints: **`routes.php`** (`api/mobile/*`).

**Controller:** `application/controllers/Api_mobile.php`.

---

## B3. `api/mobile/*` — Live updates & properties (owner writes)

All URLs below use base `http://localhost:8080/property` (add `index.php/` after `property/` if rewrite is off). Use **`Content-Type: application/json`** for JSON bodies.

### Live updates (`live_updates` table)

| Method | Path | Body / query |
|--------|------|----------------|
| **POST** | `/api/mobile/live-updates/create` | **`title`** (required). **`userId`** required if column `live_updates.userId` exists (see migration `017_live_updates_user_scope.sql`). Optional: `image`, `description`, `liveTime` / `live_time`, `platform` (normalized to `youtube` \| `instagram` \| `app`), `url`. |
| **POST** or **PUT** | `/api/mobile/live-updates/update/{id}` | **`userId`** (must own the row when `userId` column exists). Patch any of: `title`, `image`, `description`, `liveTime` / `live_time`, `platform`, `url`. |
| **POST** or **DELETE** | `/api/mobile/live-updates/delete/{id}` | **`userId`** in query or JSON body (owner check when `userId` column exists). |

If `live_updates.userId` is missing, create may omit `userId`; update/delete treat ownership as open (documented limitation — run migration for production).

### Properties (Nobroker `nb_properties` or legacy `properties`)

| Method | Path | Body / query |
|--------|------|----------------|
| **POST** | `/api/mobile/properties/create` | **`userId`** required. Supports Nobroker-style create when `source=nb` (or nb-like fields are sent) with required `title` and `city_id`; optional fields include `property_type`, `listing_type`, `price`, `bedrooms`, `bathrooms`, `area_sqft`, `address`, `locality`, `latitude`, `longitude`, `video_url`, `amenities`, `images`. Legacy create is used otherwise (`title` required). |
| **POST** or **PUT** | `/api/mobile/properties/update/{id}` | **`userId`** must match **`nb_properties.owner_id`** (Nobroker row) or legacy **`properties.agent_id`**. Allowed Nobroker fields include: `title`, `description`, `property_type`, `listing_type`, `price`, `bedrooms`, `bathrooms`, `area_sqft`, `address`, `locality`, `city_id`, `latitude`, `longitude`, `video_url`, `amenities`, `images`, `is_price_negotiable`, `rate_per_sqft`, `available_from`. Legacy: `name`, `title`, `description`, `price`, `city`, `location`, `listing_type`, `property_category`, `category`, `type`, `main_image`, `status`. |
| **POST** or **DELETE** | `/api/mobile/properties/delete/{id}` | **`userId`** in query or body. **Nobroker:** soft-deactivate (`is_active = 0`). **Legacy:** hard delete if `agent_id` matches **`userId`** (empty `agent_id` is not deletable via API — **Forbidden**). |

**Sample curl** (replace ids and base URL):

```bash
curl -s -X POST "http://localhost:8080/property/index.php/api/mobile/live-updates/create" \
  -H "Content-Type: application/json" \
  -d "{\"userId\":\"8\",\"title\":\"Test live\",\"platform\":\"app\"}"

curl -s -X POST "http://localhost:8080/property/index.php/api/mobile/live-updates/update/1" \
  -H "Content-Type: application/json" \
  -d "{\"userId\":\"8\",\"title\":\"Updated title\"}"

curl -s -X DELETE "http://localhost:8080/property/index.php/api/mobile/live-updates/delete/1?userId=8"

curl -s -X POST "http://localhost:8080/property/index.php/api/mobile/properties/create" \
  -H "Content-Type: application/json" \
  -d "{\"userId\":\"8\",\"source\":\"nb\",\"title\":\"New listing\",\"city_id\":1,\"property_type\":\"apartment\"}"

curl -s -X POST "http://localhost:8080/property/index.php/api/mobile/properties/update/12" \
  -H "Content-Type: application/json" \
  -d "{\"userId\":\"8\",\"title\":\"New title\"}"

curl -s -X DELETE "http://localhost:8080/property/index.php/api/mobile/properties/delete/12?userId=8"

curl -s -X DELETE "http://localhost:8080/property/index.php/api/mobile/wishlist/delete/12?userId=8"
```

---

## C. `api/nb/*` — GET (public — no user id in URL)

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/nb/search` | Search listings. Query: `q`, `city_id`, `page`, `limit`, … |
| `http://localhost:8080/property/api/nb/search/city` | Requires `city_id` |
| `http://localhost:8080/property/api/nb/search/type` | Requires `property_type` |
| `http://localhost:8080/property/api/nb/cities` | Active cities |
| `http://localhost:8080/property/api/nb/property-types` | Property type slugs + labels |

---

## C2. `api/nb/*` — GET (current user only — Bearer token; no user id parameter)

The user is inferred from the token, not from a `userId` query.

Auth: `Authorization: Bearer <token>` or `X-Api-Token: <token>`.

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/nb/me` | Profile of the token’s user |
| `http://localhost:8080/property/api/nb/wishlist` | That user’s wishlist. Query: `limit`, `offset` |
| `http://localhost:8080/property/api/nb/wishlist/check?property_id=12` | Whether **that user** wishlisted the property |

**Controller:** `application/controllers/Api_nb_app.php`.

---

## D. `api/nb/*` — POST URLs

**No Bearer (returns token on success)**

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/nb/register` | Body: `name`, `email`, `phone`, `password`, `accept_terms`, … |
| `http://localhost:8080/property/api/nb/login` | Body: `login` (email or phone), `password` |

**Bearer required (acts as the logged-in user — no `userId` in body)**

| Full URL | Description |
|----------|-------------|
| `http://localhost:8080/property/api/nb/logout` | Invalidate token |
| `http://localhost:8080/property/api/nb/delete-account` | Delete account |
| `http://localhost:8080/property/api/nb/wishlist` | Body: `property_id` — add to wishlist |
| `http://localhost:8080/property/api/nb/wishlist/toggle` | Body: `property_id` |
| `http://localhost:8080/property/api/nb/wishlist/remove` | Body: `property_id` |
| `http://localhost:8080/property/api/nb/enquiry` | Body: `property_id`, `message`; optional `phone`, `email` |

Run migration **`007_nb_users_api_token.sql`** so login/register return tokens.

---

## Related

- **Samples & curl** (register/login, wishlist, enquiry): **`docs/API_MOBILE_NB.md`** — use base URL `http://localhost:8080/property` in those examples.
