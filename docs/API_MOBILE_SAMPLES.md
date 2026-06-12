# Mobile API — GET / POST samples

Base URL (local XAMPP, port **8080**):

**`http://localhost:8080/property`**

If you get **404**, insert **`index.php`** after `property`:

`http://localhost:8080/property/index.php/api/mobile/...`

| Item | Value |
|------|--------|
| JSON request bodies | `Content-Type: application/json` |
| Typical response | `Content-Type: application/json` |

---

## Response envelope

**Success**

```json
{
  "success": true,
  "message": "Human-readable message",
  "data": { }
}
```

**Error**

```json
{
  "success": false,
  "message": "Reason",
  "errors": { }
}
```

---

## 1. Enquiries by customer

**GET** — list enquiries linked to a customer.

**URL**

`http://localhost:8080/property/api/mobile/enquiries/customer/8`

**Query (optional)**

| Parameter | Example | Description |
|-----------|---------|-------------|
| `status` | `new` | Filter: `new`, `read`, `replied` |

**Example response (`data`)**

```json
{
  "enquiries": [
    {
      "id": "1",
      "propertyId": "12",
      "userId": "8",
      "name": "Jane",
      "email": "jane@example.com",
      "phone": "9000000000",
      "message": "Interested in viewing",
      "status": "new",
      "createdAt": "2026-04-01 10:00:00"
    }
  ],
  "total": 1,
  "customer_id": 8
}
```

*(Field names follow `_format_enquiry_schema` in `Api_mobile.php`; exact keys depend on your DB.)*

---

## 2. Wishlist

### GET — list wishlist for a user

**URL**

`http://localhost:8080/property/api/mobile/wishlist?userId=8`

**Query (optional)**

| Parameter | Example |
|-----------|---------|
| `limit` | `10` |
| `offset` | `0` |

Alternate user key: `user_id`.

**Example response (`data`)**

```json
{
  "wishlist": [
    {
      "propertyId": "2",
      "userId": "8",
      "addedAt": "2026-04-01T12:00:00+00:00"
    }
  ],
  "total": 1,
  "limit": null,
  "offset": 0
}
```

### POST — add to wishlist

**URL**

`http://localhost:8080/property/api/mobile/wishlist`

**Body**

```json
{
  "userId": "8",
  "propertyId": "2"
}
```

**Example response (`data`)**

```json
{
  "wishlisted": true,
  "propertyId": "2"
}
```

### DELETE or POST — remove (REST-style, new)

**URL**

`http://localhost:8080/property/api/mobile/wishlist/delete/2?userId=8`

Or pass `userId` / `propertyId` in JSON body; property id may be in path as above.

**Example response (`data`)**

```json
{
  "deleted": true,
  "wishlisted": false
}
```

---

## 3. Feedback

### GET — list feedback for a user

**URL**

`http://localhost:8080/property/api/mobile/feedback?userId=8`

**Query (optional)**

| Parameter | Example |
|-----------|---------|
| `limit` | `10` |
| `offset` | `0` |

**Example response (`data`)**

```json
{
  "feedbacks": [
    {
      "id": "1",
      "userId": "8",
      "title": "Great app",
      "description": "Smooth experience",
      "createdAt": "2026-04-01T10:00:00+00:00"
    }
  ],
  "total": 1,
  "limit": null,
  "offset": 0
}
```

### POST — submit feedback

**URL**

`http://localhost:8080/property/api/mobile/feedback`

**Body**

```json
{
  "userId": "8",
  "title": "Suggestion",
  "description": "Optional longer text"
}
```

**Example response (`data`)** — one feedback object (shape from `_format_feedback`).

```json
{
  "id": "5",
  "userId": "8",
  "title": "Suggestion",
  "description": "Optional longer text",
  "createdAt": "2026-04-08T12:00:00+00:00"
}
```

---

## 4. Profile

### GET — user profile

**URL**

`http://localhost:8080/property/api/mobile/profile?userId=8`

Alternate: `user_id=8`. If the user is logged in via session, `userId` may be omitted.

**Example response (`data`)**

```json
{
  "user": {
    "id": "8",
    "name": "Demo User",
    "phone": "9000000000",
    "email": "user@example.com",
    "isAgent": false,
    "isApproved": false,
    "profileImage": "",
    "logo": "",
    "aadharNumber": "",
    "aadharImage": "",
    "address": "",
    "experienceYears": 0,
    "createdAt": "2026-01-01T00:00:00+00:00"
  }
}
```

### POST — save / update profile (optional `city_id`)

**URL**

`http://localhost:8080/property/api/mobile/save_profile`

**Body** (typical; user resolved by session or by `phone` after OTP)

```json
{
  "phone": "9000000000",
  "country_code": "+91",
  "fullname": "Demo User",
  "email": "user@example.com",
  "city": "Mumbai",
  "state": "MH",
  "pincode": "400001",
  "city_id": 1
}
```

`city_id` or `cityId` is **optional**. If sent, it must exist in **`nb_cities`** or **`cities`**, or the API returns `success: false`.

**Example success (`data`)**

```json
{
  "cityId": 1
}
```

---

## 5. Properties (core schema)

### GET — list

**URL**

`http://localhost:8080/property/api/mobile/properties-core`

**Query (optional)**

| Parameter | Example | Notes |
|-----------|---------|--------|
| `userId` | `8` | Sets `isLiked` per property when wishlist matches |
| `limit` | `20` | |
| `offset` | `0` | |
| `status` | `active` | |
| `listingType` | `buy` | |
| `propertyCategory` | `residential` | |
| `agentId` | `8` | |

**Example response (`data`)** — abbreviated; each item is a full core property object.

```json
{
  "properties": [
    {
      "id": "2",
      "listingType": "rent",
      "propertyCategory": "residential",
      "title": "2 BHK Sample",
      "description": "...",
      "images": ["http://localhost:8080/property/uploads/..."],
      "isFeatured": false,
      "isRecommended": false,
      "isLiked": false,
      "addedTime": "2026-04-01T00:00:00+00:00",
      "details": {
        "source": "nb",
        "price": 25000
      }
    }
  ],
  "total": 15,
  "limit": null,
  "offset": 0
}
```

### GET — one property by id

**URL**

`http://localhost:8080/property/api/mobile/properties-core/2`

**Query (optional)**

| Parameter | Example |
|-----------|---------|
| `userId` | `8` |

**Example response (`data`)** — single core property object (same shape as one element of `properties` above).

```json
{
  "id": "2",
  "listingType": "rent",
  "propertyCategory": "residential",
  "title": "2 BHK Sample",
  "description": "...",
  "images": [],
  "isFeatured": false,
  "isRecommended": false,
  "isLiked": true,
  "addedTime": "2026-04-01T00:00:00+00:00",
  "details": {
    "source": "nb"
  }
}
```

### POST — create property (new)

**URL**

`http://localhost:8080/property/api/mobile/properties/create`

**Body (Nobroker-style create)**

```json
{
  "userId": "8",
  "source": "nb",
  "title": "New 2 BHK",
  "city_id": 1,
  "property_type": "apartment",
  "listing_type": "rent",
  "price": 25000,
  "bedrooms": 2,
  "bathrooms": 2,
  "area_sqft": 950,
  "address": "Street name",
  "locality": "Andheri East"
}
```

`userId` is required. For `source: "nb"` (or nb-like fields), `title` and `city_id` are required.

**Example success (`data`)** — created property in core format.

### POST or PUT — update property (owner)

**URL**

`http://localhost:8080/property/api/mobile/properties/update/2`

**Body** — `userId` must match Nobroker `owner_id` or legacy `agent_id`.

```json
{
  "userId": "8",
  "title": "Updated title",
  "description": "New description"
}
```

**Example success (`data`)** — updated property in core format.

### POST or DELETE — delete / deactivate property (owner)

**URL**

`http://localhost:8080/property/api/mobile/properties/delete/2?userId=8`

Nobroker: soft-deactivate (`is_active = 0`). Legacy: hard delete when `agent_id` matches.

**Example success (`data`)**

```json
{
  "deleted": true,
  "id": "2",
  "soft": true
}
```

---

## 6. Live updates by user

**GET**

`http://localhost:8080/property/api/mobile/live-updates/user/8?limit=10`

**Query (optional)**

| Parameter | Example |
|-----------|---------|
| `limit` | `10` |
| `offset` | `0` |

**Example response (`data`)**

```json
{
  "liveUpdates": [
    {
      "id": "1",
      "image": "http://localhost:8080/property/...",
      "title": "Walkthrough",
      "description": "",
      "liveTime": "2026-04-08 10:00:00",
      "createdAt": "2026-04-08 09:00:00",
      "platform": "youtube",
      "url": "https://..."
    }
  ],
  "total": 3,
  "limit": 10,
  "offset": 0
}
```

### POST — create live update

**URL**

`http://localhost:8080/property/api/mobile/live-updates/create`

**Body**

```json
{
  "userId": "8",
  "title": "New stream",
  "platform": "app",
  "url": "https://example.com/live",
  "description": "Optional",
  "image": "",
  "liveTime": "2026-04-08 18:00:00"
}
```

`userId` is required when the `live_updates.userId` column exists.

### POST or PUT — update live update

**URL**

`http://localhost:8080/property/api/mobile/live-updates/update/1`

**Body**

```json
{
  "userId": "8",
  "title": "Updated title"
}
```

### POST or DELETE — delete live update

**URL**

`http://localhost:8080/property/api/mobile/live-updates/delete/1?userId=8`

**Example success (`data`)**

```json
{
  "deleted": true,
  "id": "1"
}
```

---

## 7. Housing news

### GET — list

**URL**

`http://localhost:8080/property/api/mobile/housing-news`

**Query (optional)**

| Parameter | Example |
|-----------|---------|
| `category` | `market` |
| `limit` | `10` |
| `offset` | `0` |

**Example response (`data`)**

```json
{
  "housingNews": [
    {
      "id": "1",
      "title": "Market update",
      "slug": "market-update",
      "summary": "Short text",
      "image": "http://localhost:8080/property/...",
      "category": "market",
      "publishedAt": "2026-04-01T00:00:00+00:00"
    }
  ],
  "total": 5,
  "limit": 10,
  "offset": 0
}
```

### GET — single article

**URL**

`http://localhost:8080/property/api/mobile/housing-news/1`

**Example response (`data`)** — one article object (may include `content`, `multiImages`, etc., per `_format_housing_news`).

---

## cURL quick reference

Replace base URL if you use `index.php`:

```bash
curl -s "http://localhost:8080/property/index.php/api/mobile/profile?userId=8"
curl -s "http://localhost:8080/property/index.php/api/mobile/wishlist?userId=8"
curl -s -X POST "http://localhost:8080/property/index.php/api/mobile/feedback" \
  -H "Content-Type: application/json" \
  -d "{\"userId\":\"8\",\"title\":\"Hello\"}"
```

---

## Related

- Broader route list: `docs/API_MOBILE.md`
- Controller: `application/controllers/Api_mobile.php`
- Routes: `application/config/routes.php` (search `api/mobile`)
