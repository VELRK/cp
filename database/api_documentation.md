# Coimbatore Properties — Web Frontend API

**Source of truth (TypeScript):** `lib/frontendApi.ts`  
**HTTP client:** `lib/api.ts` (axios + auth token header)  
**Next.js dev base:** `http://localhost:3000` (proxies PHP routes via `next.config.js`)

All frontend code should import from `lib/frontendApi.ts` — do not call `api.get('/api/...')` directly in pages/components.

---

## Architecture

| Backend | Routes | Used for |
|---------|--------|----------|
| **PHP** (CodeIgniter, proxied) | `/api/nb/*`, `/api/blogs`, `/api/property/*`, `/api/mobile/live-updates/create` | Auth, search, wishlist, blogs, property save, live updates |
| **Next.js App Router** (local) | `/api/owner/*`, `/api/tenant/*`, `/api/properties/[id]`, `/api/feedback` | Owner/tenant dashboards, property detail, feedback |

Auth: send `X-Api-Token` header (from `localStorage.nb_token`).

---

## Auth (PHP — `API_PATHS`)

| Function | Method | Path |
|----------|--------|------|
| `getMe()` | GET | `/api/nb/me` |
| `login(login, password)` | POST | `/api/nb/login` |
| `register(formData)` | POST | `/api/nb/register` |
| `logout()` | POST | `/api/nb/logout` |

---

## Cities, search & home banners (PHP)

| Function | Method | Path | Query params |
|----------|--------|------|--------------|
| `getCities()` | GET | `/api/nb/cities` | — |
| `searchProperties(params)` | GET | `/api/nb/search` | `city_id`, `q`, `property_type`, `listing_type`, `min_price`, `max_price`, `bedrooms`, `sort`, `page`, `limit`, flags |
| `getHomeBanners(params)` | GET | `/api/nb/home-banners` | `city_id`, `limit`, `page` |

---

## Wishlist & enquiry (PHP)

| Function | Method | Path |
|----------|--------|------|
| `getWishlist(userId)` | GET | `/api/nb/wishlist?userId=` |
| `checkWishlist(propertyId, userId)` | GET | `/api/nb/wishlist/check` |
| `toggleWishlist({ property_id, userId })` | POST | `/api/nb/wishlist/toggle` |
| `submitEnquiry(payload)` | POST | `/api/nb/enquiry` |
| `getNotifications()` | GET | `/api/nb/notifications` |

---

## Blogs & property (PHP + Next.js)

| Function | Method | Path | Backend |
|----------|--------|------|---------|
| `getBlogs()` | GET | `/api/blogs` | PHP |
| `getBlogById(id)` | GET | `/api/blogs?id=` | PHP |
| `saveProperty(formData)` | POST | `/api/property/save` | PHP |
| `getProperty(idOrSlug)` | GET | `/api/properties/{idOrSlug}` | Next.js |

---

## Owner / tenant dashboards (Next.js)

| Function | Method | Path |
|----------|--------|------|
| `getOwnerDashboard()` | GET | `/api/owner/dashboard` |
| `getOwnerListings()` | GET | `/api/owner/listings` |
| `getOwnerEnquiries()` | GET | `/api/owner/enquiries` |
| `getTenantEnquiries()` | GET | `/api/tenant/enquiries` |

---

## Feedback & live updates

| Function | Method | Path | Backend |
|----------|--------|------|---------|
| `getFeedbacks()` | GET | `/api/feedback` | Next.js |
| `submitFeedback(formData)` | POST | `/api/feedback` | Next.js |
| `createLiveUpdate(formData)` | POST | `/api/mobile/live-updates/create` | PHP |

---

## Other routes (not in frontendApi)

- **`GET /logout`** — PHP session + cookie logout (proxied in `next.config.js`)
- **`/panel/*`** — Admin panel (PHP forms, not JSON)
- **`/api/mobile/*`** — Native mobile app only (OTP, properties CRUD, etc.)

---

## Removed / unused (do not use)

These were removed from routes and backend; they are **not** available:

- `/api/crud/*` — legacy CRUD API
- `/api/admin/*` — unused JSON admin API
- `/api/auth/*` — duplicate of `/api/nb/*`
- `/api/locations` — unused
- `/api/nb/property/update`, `/upload-image`, `/search/city`, `/search/type`, `/property-types`, `/wishlist/remove` — unused by web app

---

## PHP route config

See `application/config/routes.php` (sections `api/nb/*`, `api/property/save`, `api/blogs`, `api/cities`, `api/mobile/*`).

## Next.js proxy config

See `next.config.js` rewrites for `/api/nb/*`, `/api/blogs`, `/api/property/*`, `/api/mobile/*`, `/logout`, `/panel/*`.
