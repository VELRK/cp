# Dream Villa Makers — Mobile API (JSON)

REST-style JSON API for the **Nobroker / Dream Villa Makers** module (`nb_*` tables: users, properties, enquiries).

**Local base URL (XAMPP):** `http://localhost:8080/property`  
Full URL example: `http://localhost:8080/property/api/nb/login`. For production, swap the host/path to match your `BASE_URL` (e.g. `https://your-domain.com/property`).

Configure the same origin in the app as in the server `BASE_URL` (see project `.env`).

---

## Conventions

| Item | Value |
|------|--------|
| **Format** | JSON request bodies use `Content-Type: application/json` |
| **Charset** | UTF-8 |
| **Dates** | ISO-style strings where applicable (e.g. token `expires_at`: `Y-m-d H:i:s`) |

### Authentication (after login/register)

Send **one** of:

- `Authorization: Bearer <token>`
- `X-Api-Token: <token>`

Tokens are returned by `POST /api/nb/register` and `POST /api/nb/login`. Default lifetime is **90 days** (`expires_at`).

On some **Apache/XAMPP (Windows)** setups, PHP may not receive the `Authorization` header, so `GET /api/nb/me` and other authenticated calls return **401** even with a valid token. Prefer **`X-Api-Token`** in those environments, or enable `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1` (or equivalent) so `Authorization` reaches PHP.

### Property detail URLs (web)

Public listing pages use **`/property/{slug}`** (SEO-friendly, unique per listing). **`slug`** is derived from the listing **title** (`nb_slugify` + uniqueness). Search and property APIs return a full **`url`** field — use that as the canonical link. Each item also includes **`slug`** so you can build `property/{slug}` under your app base if needed. **`id`** is for API/database use; **do not** assume the public path is `/property/{id}` unless `slug` is empty (legacy rows). Add the column with `004_nb_properties_slug.sql` if missing; rebuild all title-based slugs with **`php index.php cli_tool backfill_property_slugs`** (see `010_nb_slug_title_from_cli_note.sql`).

### CORS

`api/nb/*` responses include permissive CORS headers for development. For production, restrict `Access-Control-Allow-Origin` on the server if needed.

### Account status

- New registrations are **`pending`** until an admin approves them.
- **`approved`** users can use role-specific actions (e.g. owners post listings; tenants/owners send enquiries per rules below).
- **`rejected`** users cannot log in.

---

## Server setup (backend)

Run the migration so tokens work:

`application/sql/migrations/007_nb_users_api_token.sql`

If this migration is missing, register/login return **503** with a message about API tokens.

---

## Error shape

Most endpoints return:

```json
{ "success": false, "message": "Human-readable reason" }
```

HTTP status may be **401** (auth), **403** (forbidden), **404**, **405** (wrong method), **409** (conflict), **503** (misconfiguration).

---

## Sample requests & responses

Use your **`BASE_URL`** from project `.env` (example: `http://localhost:8080/property`). Replace `<TOKEN>` with the `token` string from login/register (64 hex characters).

**Demo IDs (typical seeded DB — adjust if yours differs):**

| Source | Example |
|--------|---------|
| City | `city_id=1` → Chennai (`GET /api/nb/cities` lists all) |
| Property type slug | `apartment`, `house`, `plot` (see `/api/nb/property-types`) |
| Active listing | `property_id` from search results or `SELECT id, owner_id FROM nb_properties WHERE is_active=1` |
| Demo password | `application/sql/seed_nb_demo_full.sql` sets **`Admin@123`** for seeded demo users |

Below, **curl** uses `-H "X-Api-Token: <TOKEN>"` for authenticated calls; you can use `-H "Authorization: Bearer <TOKEN>"` instead where the server passes it through.

### Register — sample request / response

**Request:**

```http
POST /api/nb/register HTTP/1.1
Host: localhost:8080
Content-Type: application/json

{
  "name": "New User",
  "email": "newuser@example.com",
  "phone": "9876543210",
  "password": "secret12",
  "password_confirm": "secret12",
  "accept_terms": true,
  "role": "customer",
  "city_id": 1
}
```

**Response (200):**

```json
{
  "success": true,
  "token": "<64-char hex>",
  "expires_at": "2026-07-05 12:00:00",
  "user": {
    "id": 42,
    "name": "New User",
    "email": "newuser@example.com",
    "phone": "9876543210",
    "role": "customer",
    "db_role": "tenant",
    "user_type": "customer",
    "status": "pending",
    "city_id": 1
  }
}
```

### Login — sample request / response

**Request:**

```bash
curl -s -X POST "http://localhost:8080/property/api/nb/login" \
  -H "Content-Type: application/json" \
  -d "{\"login\":\"tenant@test.com\",\"password\":\"Admin@123\"}"
```

(You can use `"email":"tenant@test.com"` instead of `login`.)

**Response (200):**

```json
{
  "success": true,
  "token": "<64-char hex>",
  "expires_at": "2026-07-05 04:21:52",
  "user": {
    "id": 3,
    "name": "Demo Tenant",
    "email": "tenant@test.com",
    "phone": "9777777777",
    "role": "tenant",
    "status": "approved",
    "city_id": 1
  }
}
```

*(Shape captured from demo DB user `tenant@test.com`; `token` and `expires_at` differ each login.)*

### Me — sample request / response

**Request:**

```bash
curl -s "http://localhost:8080/property/api/nb/me" \
  -H "Accept: application/json" \
  -H "X-Api-Token: <TOKEN>"
```

**Response (200):**

```json
{
  "success": true,
  "user": {
    "id": 3,
    "name": "Demo Tenant",
    "email": "tenant@test.com",
    "phone": "9777777777",
    "role": "tenant",
    "status": "approved",
    "city_id": 1
  }
}
```

### Logout — sample request / response

**Request:**

```bash
curl -s -X POST "http://localhost:8080/property/api/nb/logout" \
  -H "Accept: application/json" \
  -H "X-Api-Token: <TOKEN>"
```

**Response (200):**

```json
{ "success": true, "message": "Logged out." }
```

### Wishlist (Bearer) — `nb_properties` + `wishlists`

All calls require `Authorization: Bearer <token>` or `X-Api-Token: <token>` (same token as login/register).

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/nb/wishlist` | List current user’s wishlist. Query: `limit`, `offset` |
| POST | `/api/nb/wishlist` | Add a listing. JSON body: `property_id` (or `propertyId`). Idempotent if already saved |
| POST | `/api/nb/wishlist/toggle` | Body: `property_id` — add if missing, remove if present |
| POST | `/api/nb/wishlist/remove` | Body: `property_id` — remove from wishlist |
| GET | `/api/nb/wishlist/check` | Query: `property_id` — `{ "success": true, "wishlisted": true/false }` |

**Example — add to wishlist:**

```bash
curl -s -X POST "http://localhost:8080/property/api/nb/wishlist" \
  -H "Content-Type: application/json" \
  -H "X-Api-Token: <TOKEN>" \
  -d "{\"property_id\":12}"
```

### Enquiry — save (Bearer)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/nb/enquiry` | Send enquiry on an active listing. Requires **approved** account; role **tenant** or **owner** (not admin). Cannot enquire on your own listing |

**JSON body:** `property_id` (or `propertyId`), `message` (required). Optional `phone`, `email` (default from profile).

Writes to **`nb_enquiries`** when the legacy `enquiries` table is absent (same rules as web `api/enquiry/send`). Optionally emails `nb_admin_email` if configured.

```bash
curl -s -X POST "http://localhost:8080/property/api/nb/enquiry" \
  -H "Content-Type: application/json" \
  -H "X-Api-Token: <TOKEN>" \
  -d "{\"property_id\":12,\"message\":\"I am interested in this property.\"}"
```

### Cities — sample response

**Request:** `GET /api/nb/cities`

**Response (200)** — excerpt:

```json
{
  "success": true,
  "cities": [
    { "id": 1, "name": "Chennai", "state": "Tamil Nadu" },
    { "id": 2, "name": "Mumbai", "state": "Maharashtra" }
  ]
}
```

### Property types — sample response

**Request:** `GET /api/nb/property-types`

**Response (200)** — excerpt:

```json
{
  "success": true,
  "property_types": [
    { "slug": "apartment", "label": "Apartment / Flat" },
    { "slug": "studio", "label": "Studio" },
    { "slug": "plot", "label": "Plot / Land" }
  ]
}
```

(Full list is longer; see live endpoint.)

### Search — sample query string & response

**Request:**

```http
GET /api/nb/search?q=OMR&city_id=1&listing_type=rent&page=1&limit=2 HTTP/1.1
```

**Response (200)** — shape (one item abbreviated; real items include all columns per §8):

```json
{
  "success": true,
  "total": 17,
  "page": 1,
  "limit": 2,
  "items": [
    {
      "id": 1,
      "owner_id": 2,
      "title": "Sea View 2BHK Apartment",
      "slug": "sea-view-2bhk-apartment",
      "property_type": "apartment",
      "listing_type": "rent",
      "price": 28000,
      "city_id": 1,
      "city_name": "Chennai",
      "owner_name": "Demo Owner",
      "property_type_label": "Apartment / Flat",
      "price_formatted": "₹28,000 / month",
      "thumbnail_url": null,
      "image_urls": [],
      "url": "http://localhost:8080/property/property/sea-view-2bhk-apartment"
    }
  ]
}
```

### Search by city — sample & validation error

**Request:** `GET /api/nb/search/city?city_id=1&limit=1`

**Response:** Same `success`, `total`, `page`, `limit`, `items` as general search.

**Missing `city_id` (400):**

```json
{ "success": false, "message": "city_id is required" }
```

### Search by type — sample

**Request:** `GET /api/nb/search/type?property_type=apartment&listing_type=rent&limit=1`

**Response:** Same `items` shape as §8.

### Send enquiry — sample request / response

Use an active **property_id** that is **not** owned by the logged-in user (get from search or DB).

**Request:**

```bash
curl -s -X POST "http://localhost:8080/property/api/enquiry/send" \
  -H "Content-Type: application/json" \
  -H "X-Api-Token: <TOKEN>" \
  -d "{\"property_id\":17,\"message\":\"Interested in a visit this weekend.\",\"phone\":\"9777777777\",\"email\":\"tenant@test.com\"}"
```

**Response (200):**

```json
{
  "success": true,
  "message": "Enquiry sent. Admin will contact you."
}
```

### Save property — sample (multipart, owner token)

**Request:** `POST /api/property/save` with `multipart/form-data` and **Bearer** or **`X-Api-Token`**. Requires **approved** **`owner`** account. Example without image files:

```bash
curl -s -X POST "http://localhost:8080/property/api/property/save" \
  -H "Accept: application/json" \
  -H "X-Api-Token: <OWNER_TOKEN>" \
  -F "title=API sample listing" \
  -F "property_type=apartment" \
  -F "listing_type=rent" \
  -F "price=25000" \
  -F "address=123 Sample Street" \
  -F "locality=Adyar" \
  -F "city_id=1" \
  -F "description=Short description"
```

**Response (200)** — typical:

```json
{
  "success": true,
  "property_id": 18,
  "property_url": null
}
```

`property_url` is often **`null`** for new owner drafts until an admin sets **`is_active`**. Add **`images[]`** file fields for gallery uploads (see §11).

### Delete account

**Request:** `POST /api/nb/delete-account` with `X-Api-Token` / Bearer (same as logout). **Response (200):** `{ "success": true, "message": "Account deleted." }` — destructive; not exercised in automated samples.

---

# Endpoints

## 1. Register

**`POST /api/nb/register`**

Creates a user (`status`: `pending`) and returns a **token** (same as login).

Before register, image files from gallery/camera should be uploaded using `POST /api/nb/upload-image` and the returned `file_path` should be sent in `aadhar_file` / `profile_image`.

`/api/nb/register` also supports direct `multipart/form-data` uploads in the same call:
- `aadhar_file` (file)
- `profile_image` or `profile_pic` (file)
When files are sent this way, server uploads them and stores generated paths in `nb_users`.

### Upload image (for register)

**`POST /api/nb/upload-image`**

**Content-Type:** `multipart/form-data`

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `image` | file | Yes | Gallery/camera file |
| `kind` | string | No | `aadhar` or `profile` (default `profile`) |

**Success (200):**

```json
{
  "success": true,
  "kind": "profile",
  "file_path": "uploads/profiles/abc123.webp",
  "file_url": "http://localhost:8080/property/uploads/profiles/abc123.webp"
}
```

**Body (JSON):**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `name` | string | Yes | Min length enforced server-side |
| `email` | string | Yes | Valid email, must be unique |
| `phone` | string | Yes | At least 10 characters |
| `password` | string | Yes | Min 6 characters |
| `password_confirm` | string | No | Must match `password` if sent |
| `accept_terms` | boolean | Yes | Must be `true` |
| `role` | string | No | `customer` (default) or `agent` |
| `city_id` | integer | No | FK to cities list |
| `user_type` | string | No | Alternative to `role`: `customer` or `agent` |
| `aadhar_no` | string | No | Optional; if provided must be 12 digits |
| `aadhar_file` | string | No | Optional Aadhaar image/file path |
| `profile_pic` | string | No | Optional profile image path |
| `profile_image` | string | No | Alias of `profile_pic` |
| `experience_years` | integer | No | Optional; if provided must be between `0` and `60` |

**Success (200):**

`user` contains full `nb_users` columns (except `password`), plus API mapping fields like `api_role` and `api_user_type`.

```json
{
  "success": true,
  "token": "<64-char hex>",
  "expires_at": "2026-07-04 12:00:00",
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "phone": "...",
    "role": "tenant",
    "user_type": "customer",
    "status": "pending",
    "city_id": 1,
    "profile_pic": null,
    "aadhar_no": null,
    "aadhar_file": null,
    "experience_years": null,
    "api_role": "customer",
    "api_user_type": "customer"
  }
}
```

---

## 2. Login

**`POST /api/nb/login`**

**Body (JSON):**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `login` | string | Yes* | Email **or** phone |
| `email` | string | Yes* | Alternative to `login` |
| `password` | string | Yes | |

\* Provide `login` **or** `email` as identifier.

**Success:** Same shape as register (`token`, `expires_at`, `user`), where `user` includes full `nb_users` columns (except `password`).

**Errors:** **401** invalid credentials; **403** if `status` is `rejected`.

---

## 3. Logout

**`POST /api/nb/logout`**

Invalidates the token server-side.

**Headers:** Bearer or `X-Api-Token`.

**Success (200):** `{ "success": true, "message": "Logged out." }`

---

## 4. Current user

**`GET /api/nb/me`**

**Headers:** Bearer or `X-Api-Token`.

**Success (200):**

```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "...",
    "email": "...",
    "phone": "...",
    "role": "owner",
    "status": "approved",
    "city_id": 1
  }
}
```

---

## 5. Delete account

**`POST /api/nb/delete-account`**

Permanently deletes the `nb_users` row (related rows follow database foreign-key rules).

**Headers:** Bearer or `X-Api-Token`.

**Success (200):** `{ "success": true, "message": "Account deleted." }`

---

## 6. Cities (for filters & registration)

**`GET /api/nb/cities`**

Public.

**Success (200):**

```json
{
  "success": true,
  "cities": [
    { "id": 1, "name": "Chennai", "state": "Tamil Nadu" }
  ]
}
```

---

## 7. Property types (slugs)

**`GET /api/nb/property-types`**

Public. Returns allowed `property_type` values for search and listing forms.

**Success (200):**

```json
{
  "success": true,
  "property_types": [
    { "slug": "apartment", "label": "Apartment / Flat" },
    { "slug": "plot", "label": "Plot / Land" }
  ]
}
```

---

## 8. Search (general)

**`GET /api/nb/search`**

Public. Only **active** listings (`is_active = 1`).

**Query parameters:**

| Parameter | Description |
|-----------|-------------|
| `q` | Text search on locality, address, and title |
| `city_id` | Filter by city |
| `property_type` | Slug (see `/api/nb/property-types`) |
| `listing_type` | `rent` or `sale` |
| `min_price`, `max_price` | Numeric |
| `bedrooms` | Integer |
| `sort` | `new` (default), `price_asc`, `price_desc` |
| `lat`, `lng`, `radius_km` | Map radius search (km, 1–100; default 15 if lat/lng set) |
| `page` | Page number (default 1) |
| `limit` | Page size (default 12, max 50) |

**Success (200):**

Each object in **`items`** exposes all **`nb_properties`** columns (snake_case) plus join fields and computed helpers. Summary:

| Field | Notes |
|-------|--------|
| `id`, `owner_id` | Integers |
| `title`, `slug`, `description` | Text |
| `property_type`, `listing_type` | Slugs |
| `price` | Number |
| `bedrooms`, `bathrooms`, `area_sqft` | Int or `null` |
| `address`, `locality` | Text |
| `city_id` | Integer |
| `latitude`, `longitude` | Float or `null` |
| `google_place_id` | String or `null` |
| `is_price_negotiable` | `0` or `1` |
| `rate_per_sqft` | Float or `null` |
| `available_from` | `Y-m-d` or `null` |
| `plot_length_ft`, `plot_width_ft` | Float or `null` |
| `has_boundary_wall` | `0`, `1`, or `null` |
| `amenities` | Decoded JSON array (or `null`) |
| `images` | Decoded array of **relative** paths as stored in DB |
| `video_url` | Sanitized YouTube/Vimeo `https` URL or **`null`** |
| `is_active`, `is_featured` | `0` or `1` |
| `views` | Integer |
| `created_at`, `updated_at` | Timestamp strings from DB |
| `city_name` | From `nb_cities` join |
| `owner_name` | From `nb_users` join (listing owner display name) |
| `property_type_label` | Human label for `property_type` |
| `price_formatted` | Localized display string (₹) |
| `thumbnail_url` | Absolute URL of cover image or `null` |
| `image_urls` | Array of **absolute** URLs for every gallery image (same order as `images`) |
| `url` | Canonical listing page URL |

```json
{
  "success": true,
  "total": 42,
  "page": 1,
  "limit": 12,
  "items": [
    {
      "id": 1,
      "owner_id": 2,
      "title": "2 BHK in Anna Nagar",
      "slug": "2-bhk-in-anna-nagar",
      "description": "...",
      "property_type": "apartment",
      "listing_type": "rent",
      "price": 28000,
      "bedrooms": 2,
      "bathrooms": 2,
      "area_sqft": 1200,
      "address": "...",
      "locality": "Anna Nagar",
      "city_id": 1,
      "latitude": 13.08,
      "longitude": 80.27,
      "google_place_id": null,
      "is_price_negotiable": 0,
      "rate_per_sqft": null,
      "available_from": null,
      "plot_length_ft": null,
      "plot_width_ft": null,
      "has_boundary_wall": null,
      "amenities": ["Parking", "Power backup"],
      "images": ["assets/uploads/nb_properties/a.jpg", "assets/uploads/nb_properties/b.jpg"],
      "video_url": "https://www.youtube.com/watch?v=xxxxxxxxxxx",
      "is_active": 1,
      "is_featured": 0,
      "views": 42,
      "created_at": "2026-01-15 10:00:00",
      "updated_at": "2026-02-01 12:00:00",
      "city_name": "Chennai",
      "owner_name": "Jane Owner",
      "property_type_label": "Apartment / Flat",
      "price_formatted": "₹28,000 / month",
      "thumbnail_url": "https://.../assets/uploads/nb_properties/a.jpg",
      "image_urls": [
        "https://.../assets/uploads/nb_properties/a.jpg",
        "https://.../assets/uploads/nb_properties/b.jpg"
      ],
      "url": "https://.../property/2-bhk-in-anna-nagar"
    }
  ]
}
```

---

## 9. Search by city

**`GET /api/nb/search/city`**

Same as search (same **`items`** shape — all columns per §8), but **`city_id` is required** (otherwise **400**).

Example: `/api/nb/search/city?city_id=1&page=1`

---

## 10. Search by property type

**`GET /api/nb/search/type`**

Same as search (same **`items`** shape), but **`property_type` is required** (otherwise **400**).

Example: `/api/nb/search/type?property_type=apartment&listing_type=rent`

---

## 11. Create / update property (owner)

**Primary endpoint:** `POST /api/property/save`  
**Mobile endpoint:** `POST /api/mobile/properties/create`

- **Auth:** Session or **Bearer / `X-Api-Token`** (same token as `/api/nb/login`).
- **Role:** **`owner`** and **`approved`**. Admins use the panel flows; mobile typically uses owner accounts.
- **Content-Type:** `multipart/form-data` (same as the web form — supports file uploads).

**JSON response:** Returned when any of these is true:

- `X-Requested-With: XMLHttpRequest`, or
- `Accept` header includes `application/json`, or
- Request includes a Bearer / `X-Api-Token` token.

Otherwise the server may redirect (browser form).

**Required fields (form fields):**

| Field | Notes |
|-------|--------|
| `title` | Max 300 chars |
| `property_type` | Slug from `/api/nb/property-types` |
| `listing_type` | `rent` or `sale` |
| `price` | Number |
| `address` | |
| `locality` | Max 200 chars |
| `city_id` | Integer |

**Optional (common):**

`description`, `bedrooms`, `bathrooms`, `area_sqft`, `latitude`, `longitude`, `google_place_id`, `is_price_negotiable`, `rate_per_sqft`, `available_from`, `plot_length_ft`, `plot_width_ft`, `has_boundary_wall` (`0`/`1`), `video_url`, `amenities[]` (array of amenity **names** allowed by server; use master list if `nb_amenities` exists).

**Images:** `images[]` — multiple files, jpg/jpeg/png/webp, max 5 MB per file, up to 10 images.

**Edit existing listing:** `property_id` = existing id (must belong to the same owner unless admin).

**New listings** from owners are typically saved with **`is_active` = 0** until an admin publishes them.

### Input field index (for mobile sharing)

| Field | Type | Required | Notes |
|---|---|---|---|
| `title` | string | Yes | Max 300 chars |
| `property_type` | string | Yes | Slug from `/api/nb/property-types` |
| `listing_type` | string | Yes | `rent` or `sale` |
| `price` | number | Yes | |
| `address` | string | Yes | |
| `locality` | string | Yes | Max 200 chars |
| `city_id` | integer | Yes | |
| `description` | string | No | |
| `bedrooms` | integer | No | |
| `bathrooms` | integer | No | |
| `area_sqft` | integer | No | |
| `latitude` | number | No | |
| `longitude` | number | No | |
| `google_place_id` | string | No | |
| `is_price_negotiable` | 0/1 | No | |
| `rate_per_sqft` | number | No | |
| `available_from` | string/date | No | |
| `plot_length_ft` | number | No | |
| `plot_width_ft` | number | No | |
| `has_boundary_wall` | 0/1 | No | |
| `amenities[]` | array[string] | No | |
| `nearby_category[]` | array[string] | No | |
| `nearby_title[]` | array[string] | No | |
| `nearby_distance[]` | array[string] | No | |
| `images[]` | file[] | No | jpg/jpeg/png/webp |
| `video_url` | string | No | |
| `property_id` | integer | No | Use for update |
| `existing_paths[]` | array[string] | No | Keep old images on update |
| `remove_existing[]` | array[string] | No | Remove old images on update |
| `cover_index` | integer | No | Cover image index |

### JSON body example (field reference)

Use this JSON for integration reference.  
When files are included (`images[]`), send `multipart/form-data`.

```json
{
  "title": "3BHK Premium Apartment",
  "property_type": "apartment",
  "listing_type": "sale",
  "price": 8500000,
  "address": "12, MG Road",
  "locality": "Indiranagar",
  "city_id": 1,
  "description": "Semi-furnished apartment",
  "bedrooms": 3,
  "bathrooms": 2,
  "area_sqft": 1450,
  "latitude": 12.9716,
  "longitude": 77.5946,
  "google_place_id": "ChIJ...",
  "is_price_negotiable": 1,
  "rate_per_sqft": 5862,
  "available_from": "2026-04-20",
  "plot_length_ft": null,
  "plot_width_ft": null,
  "has_boundary_wall": null,
  "amenities": ["Lift", "Parking", "Security"],
  "nearby_category": ["School", "Hospital"],
  "nearby_title": ["ABC Public School", "City Hospital"],
  "nearby_distance": ["1.2 km", "800 m"],
  "video_url": "https://youtu.be/xxxx",
  "property_id": null
}
```

### Success (JSON response)

```json
{
  "success": true,
  "property_id": 123,
  "property": {
    "id": "123",
    "owner_id": "12",
    "title": "3BHK Premium Apartment",
    "property_type": "apartment",
    "listing_type": "sale",
    "price": "8500000.00",
    "address": "12, MG Road",
    "locality": "Indiranagar",
    "city_id": "1",
    "amenities": ["Lift", "Parking", "Security"],
    "images": [
      "assets/uploads/nb_properties/a1.jpg",
      "assets/uploads/nb_properties/a2.jpg"
    ],
    "image_urls": [
      "http://localhost:8080/property/assets/uploads/nb_properties/a1.jpg",
      "http://localhost:8080/property/assets/uploads/nb_properties/a2.jpg"
    ],
    "is_active": "0"
  },
  "property_url": "https://.../property/slug"
}
```

`property_url` is present only when the listing is active on the public site.

---

## 12. Send enquiry (tenant / owner)

**`POST /api/enquiry/send`**

**Auth:** Bearer or `X-Api-Token`.

**Rules:**

- User must be **`approved`**.
- Role must be **`tenant`** or **`owner`** (not admin for this action).
- Property must exist, be **active**, and **not** be the user’s own listing.

**Body:** JSON or form fields:

| Field | Required |
|-------|----------|
| `property_id` | Yes |
| `message` | Yes |
| `phone` | Yes |
| `email` | Yes |

**Success (200):**

```json
{
  "success": true,
  "message": "Enquiry sent. Admin will contact you."
}
```

**Typical errors:** **401** not logged in; **403** not approved / wrong role / own listing; **404** property missing or not published; **400** missing fields.

---

## Quick reference

| Action | Method | Path |
|--------|--------|------|
| Register | POST | `/api/nb/register` |
| Login | POST | `/api/nb/login` |
| Logout | POST | `/api/nb/logout` |
| Me | GET | `/api/nb/me` |
| Delete account | POST | `/api/nb/delete-account` |
| Cities | GET | `/api/nb/cities` |
| Property types | GET | `/api/nb/property-types` |
| Search | GET | `/api/nb/search` |
| Search by city | GET | `/api/nb/search/city` |
| Search by type | GET | `/api/nb/search/type` |
| Save property | POST | `/api/property/save` |
| Save property (mobile alias) | POST | `/api/mobile/properties/create` |
| Send enquiry | POST | `/api/enquiry/send` |

---

## Notes for app developers

1. **Store the token** securely (e.g. Keychain / Keystore) after login/register.
2. **Refresh login** when `401` / invalid token.
3. **Listing URLs** in search results are full site URLs; open in a WebView or in-app browser if you do not have a native detail screen yet.
4. **Legacy API:** This project may also expose older `api/mobile/*` routes for a different schema; for **Dream Villa Makers**, use **`api/nb/*`** and the two endpoints above.

---

*Generated for integration with the Dream Villa Makers CodeIgniter backend. Version follows the repository in use.*
