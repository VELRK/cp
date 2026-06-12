# DVM API Documentation
**Base URL:** `http://localhost/dvm`
**Content-Type:** `application/json`
**All image/file URLs are returned as full absolute URLs (base_url already prepended).**

---

## Response Format

```json
// Success
{ "success": true, "message": "...", "data": { ... } }

// Error
{ "success": false, "message": "...", "errors": { ... } }
```

---

## Authentication (Mobile)

### Send OTP
`POST /api/mobile/send_otp`
```json
// Request
{ "phone": "9876543210", "country_code": "+91" }

// Response
{ "success": true, "message": "OTP sent successfully" }
```

### Verify OTP
`POST /api/mobile/verify_otp`
```json
// Request
{ "phone": "9876543210", "country_code": "+91", "otp": "123456" }

// Response
{ "success": true, "message": "OTP verified", "data": { "user": { ... }, "token": "..." } }
```

### Resend OTP
`POST /api/mobile/resend_otp`
```json
{ "phone": "9876543210", "country_code": "+91" }
```

### Save Profile (after first OTP verify)
`POST /api/mobile/save_profile`
```json
{
  "phone": "9876543210",
  "country_code": "+91",
  "fullname": "Rahul Sharma",
  "email": "rahul@example.com",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001"
}
```

### Update Profile
`POST /api/mobile/update_profile`
```json
{
  "user_id": "user_abc123",
  "fullname": "Rahul Sharma",
  "email": "rahul@example.com",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001"
}
```

### Get Profile
`GET /api/mobile/profile?user_id=user_abc123`

### Check Auth
`GET /api/mobile/check`

### Logout
`POST /api/mobile/logout`

### Check Phone Exists
`POST /api/mobile/check_phone_exists`
```json
{ "phone": "9876543210", "country_code": "+91" }
```

### Delete Account
`POST /api/mobile/delete_account`
```json
{ "user_id": "user_abc123" }
```

---

## CRUD API Endpoints

> Base path: `/api/crud/`

---

## 1. Properties

### List All
`GET /api/crud/properties`

Query params:
| Param | Type | Description |
|---|---|---|
| status | string | `active` / `inactive` |
| limit | int | Pagination limit |
| offset | int | Pagination offset |

```json
{
  "success": true,
  "data": {
    "properties": [
      {
        "id": 1,
        "name": "Luxury Villa",
        "main_image": "http://localhost/dvm/uploads/properties/villa.jpg",
        "price": 5000000,
        "city": "Mumbai",
        "status": "active"
      }
    ],
    "total": 50,
    "limit": null,
    "offset": 0
  }
}
```

### Get by ID
`GET /api/crud/properties/{id}`

### Update
`PUT /api/crud/properties/{id}/update`
```json
{ "name": "Updated Name", "price": 6000000, "status": "active" }
```

### Delete
`DELETE /api/crud/properties/{id}/delete`

---

## 2. Blogs

### List All
`GET /api/crud/blogs`

Query params: `status`, `limit`, `offset`

```json
{
  "success": true,
  "data": {
    "blogs": [
      {
        "id": 1,
        "name": "Blog Title",
        "author": "Admin",
        "date": "2026-03-01",
        "short_notes": "Summary...",
        "description": "Full content...",
        "gallery": [
          "http://localhost/dvm/uploads/blogs/img1.jpg",
          "http://localhost/dvm/uploads/blogs/img2.jpg"
        ],
        "status": "active"
      }
    ]
  }
}
```
> **Note:** `gallery` is returned as a **JSON array** of full image URLs.

### Get by ID
`GET /api/crud/blogs/{id}`

### Update
`PUT /api/crud/blogs/{id}/update`

### Delete
`DELETE /api/crud/blogs/{id}/delete`

---

## 3. Categories

### List All
`GET /api/crud/categories`

Query params: `status`

```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "id": 1,
        "category_name": "Apartment",
        "image": "http://localhost/dvm/uploads/categories/apt.jpg",
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/categories/{id}`

### Update
`PUT /api/crud/categories/{id}/update`

### Delete
`DELETE /api/crud/categories/{id}/delete`

---

## 4. Cities

### List All
`GET /api/crud/cities`

Query params: `status`

```json
{
  "success": true,
  "data": {
    "cities": [
      {
        "id": 1,
        "name": "Mumbai",
        "image": "http://localhost/dvm/uploads/cities/mumbai.jpg",
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/cities/{id}`

### Update
`PUT /api/crud/cities/{id}/update`

### Delete
`DELETE /api/crud/cities/{id}/delete`

---

## 5. Locations

### List All
`GET /api/crud/locations`

Query params: `status`, `city_id`

```json
{
  "success": true,
  "data": {
    "locations": [
      {
        "id": 1,
        "name": "Andheri",
        "city_id": 1,
        "image": "http://localhost/dvm/uploads/locations/andheri.jpg",
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/locations/{id}`

### Update
`PUT /api/crud/locations/{id}/update`

### Delete
`DELETE /api/crud/locations/{id}/delete`

---

## 6. Banners

### List All
`GET /api/crud/banners`

Query params: `status`

```json
{
  "success": true,
  "data": {
    "banners": [
      {
        "id": 1,
        "title": "Summer Sale",
        "image": "http://localhost/dvm/uploads/banners/banner1.jpg",
        "link": "https://...",
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/banners/{id}`

### Update
`PUT /api/crud/banners/{id}/update`

### Delete
`DELETE /api/crud/banners/{id}/delete`

---

## 7. Offer Banners

### List All
`GET /api/crud/offer-banners`

```json
{
  "success": true,
  "data": {
    "offer_banners": [
      {
        "id": 1,
        "title": "Diwali Offer",
        "image": "http://localhost/dvm/uploads/offer_banners/diwali.jpg",
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/offer-banners/{id}`

### Update
`PUT /api/crud/offer-banners/{id}/update`

### Delete
`DELETE /api/crud/offer-banners/{id}/delete`

---

## 8. Notifications

### List All
`GET /api/crud/notifications`

Query params: `status`

```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": 1,
        "title": "New Property Alert",
        "message": "Check out new listings",
        "image": "http://localhost/dvm/uploads/notifications/alert.jpg",
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/notifications/{id}`

### Update
`PUT /api/crud/notifications/{id}/update`

### Delete
`DELETE /api/crud/notifications/{id}/delete`

---

## 9. Reels Videos

### List All
`GET /api/crud/reels-videos`

```json
{
  "success": true,
  "data": {
    "reels_videos": [
      {
        "id": 1,
        "title": "Property Tour",
        "video_url": "http://localhost/dvm/uploads/reels/tour.mp4",
        "thumbnail": "http://localhost/dvm/uploads/reels/thumb.jpg",
        "index_no": 1,
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/reels-videos/{id}`

### Update
`PUT /api/crud/reels-videos/{id}/update`

### Delete
`DELETE /api/crud/reels-videos/{id}/delete`

---

## 10. Videos

### List All
`GET /api/crud/videos`

```json
{
  "success": true,
  "data": {
    "videos": [
      {
        "id": 1,
        "title": "About Us",
        "video_url": "http://localhost/dvm/uploads/videos/about.mp4",
        "thumbnail": "http://localhost/dvm/uploads/videos/thumb.jpg",
        "index_no": 1,
        "status": "active"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/videos/{id}`

### Update
`PUT /api/crud/videos/{id}/update`

### Delete
`DELETE /api/crud/videos/{id}/delete`

---

## 11. Users

### List All
`GET /api/crud/users`

Query params: `status`, `search`, `limit`, `offset`

```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": "user_abc123",
        "fullname": "Rahul Sharma",
        "email": "rahul@example.com",
        "countrycode": "+91",
        "phonenumber": "9876543210",
        "city": "Mumbai",
        "state": "Maharashtra",
        "pincode": "400001",
        "logintype": "manual",
        "profilepic": "http://localhost/dvm/uploads/users/rahul.jpg",
        "referralcode": "REF001A2B",
        "isactive": "active",
        "is_verified": 1,
        "created_at": "2026-03-01 10:00:00"
      }
    ],
    "total": 10,
    "limit": null,
    "offset": 0
  }
}
```

### Get by ID
`GET /api/crud/users/{id}`

### Create User
`POST /api/crud/users` *(via create_user)*
```json
{
  "fullname": "Rahul Sharma",
  "email": "rahul@example.com",
  "phonenumber": "9876543210",
  "countrycode": "+91",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "isactive": "active",
  "logintype": "manual",
  "password": "optional_password"
}
```

### Update
`PUT /api/crud/users/{id}/update`

> **Note:** Password cannot be updated via this endpoint.

### Delete
`DELETE /api/crud/users/{id}/delete`

### Bulk Delete
`POST /api/crud/users/bulk-delete`
```json
{ "ids": ["user_abc123", "user_def456"] }
```

### Bulk Status Update
`POST /api/crud/users/bulk-status`
```json
{ "ids": ["user_abc123"], "status": "inactive" }
```

---

## 12. Contacts

### List All
`GET /api/crud/contacts`

Query params: `status`, `limit`, `offset`

```json
{
  "success": true,
  "data": {
    "contacts": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "9876543210",
        "message": "I am interested...",
        "status": "new"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/contacts/{id}`

### Update
`PUT /api/crud/contacts/{id}/update`

### Delete
`DELETE /api/crud/contacts/{id}/delete`

---

## 13. Enquiries

### List All
`GET /api/crud/enquiries`

Query params: `status`, `limit`, `offset`

```json
{
  "success": true,
  "data": {
    "enquiries": [
      {
        "id": 1,
        "property_id": 5,
        "property_name": "Luxury Villa",
        "property_image": "http://localhost/dvm/uploads/properties/villa.jpg",
        "name": "Priya Patel",
        "email": "priya@example.com",
        "phone": "9876543211",
        "message": "Interested in this property",
        "status": "new"
      }
    ]
  }
}
```

### Get by ID
`GET /api/crud/enquiries/{id}`

### Update
`PUT /api/crud/enquiries/{id}/update`

### Delete
`DELETE /api/crud/enquiries/{id}/delete`

---

## Mobile API Endpoints

### Home
`GET /api/mobile/home`

### Properties
`GET /api/mobile/properties`
`GET /api/mobile/properties/featured`
`GET /api/mobile/properties/latest`
`GET /api/mobile/properties/search?q=villa`
`GET /api/mobile/properties/{id}`

### Blogs
`GET /api/mobile/blogs`
`GET /api/mobile/blogs/{id}`

### Categories
`GET /api/mobile/categories`
`GET /api/mobile/categories/{id}`

### Cities
`GET /api/mobile/cities`
`GET /api/mobile/cities/{id}`

### Locations
`GET /api/mobile/locations`
`GET /api/mobile/locations/{id}`
`GET /api/mobile/locations/city/{city_id}`

### Banners
`GET /api/mobile/banners`
`GET /api/mobile/offer_banners`

### Enquiry (Submit)
`POST /api/mobile/enquiry`
```json
{
  "property_id": 1,
  "name": "Rahul",
  "email": "rahul@example.com",
  "phone": "9876543210",
  "message": "I am interested"
}
```

### Wishlist
`POST /api/mobile/wishlist/store`
```json
// Request
{ "user_id": "user_abc123", "property_id": 1 }

// Response (added)
{ "success": true, "data": { "wishlisted": true, "id": 5 }, "message": "Added to wishlist" }

// Response (removed — toggles if already in wishlist)
{ "success": true, "data": { "wishlisted": false }, "message": "Removed from wishlist" }
```

`GET /api/mobile/wishlist/check?user_id=user_abc123&property_id=1`
```json
{ "success": true, "data": { "wishlisted": true }, "message": "Wishlist status checked" }
```

`GET /api/mobile/wishlist/list?user_id=user_abc123&limit=10&offset=0`
```json
{
  "success": true,
  "data": {
    "wishlist": [
      {
        "id": 5,
        "property_id": 1,
        "property_name": "Luxury Villa",
        "property_image": "http://localhost/dvm/uploads/properties/villa.jpg",
        "property_price": 5000000,
        "property_location": "Andheri",
        "created_at": "2026-03-01 10:00:00"
      }
    ],
    "total": 1
  }
}
```

`POST /api/mobile/wishlist/remove`
```json
{ "user_id": "user_abc123", "property_id": 1 }
```

---

## Referral

### Apply Referral Code
`POST /api/mobile/referral/apply`
```json
// Request
{ "user_id": "user_abc123", "referral_code": "REF001A2B" }

// Response
{
  "success": true,
  "data": { "referral_id": 3, "referrer_name": "Rahul Sharma", "status": "pending" },
  "message": "Referral code applied successfully"
}
```

### My Referrals (people I referred)
`GET /api/mobile/referral/list?user_id=user_abc123`
```json
{
  "success": true,
  "data": {
    "referrals": [
      {
        "id": 3,
        "referred_name": "Priya Patel",
        "referred_email": "priya@example.com",
        "referral_code": "REF001A2B",
        "status": "pending",
        "reward_points": 0,
        "reward_amount": 0,
        "created_at": "2026-03-01 10:00:00"
      }
    ],
    "total": 1
  }
}
```

### Referral Stats
`GET /api/mobile/referral/stats?user_id=user_abc123`
```json
{
  "success": true,
  "data": {
    "referral_code": "REF001A2B",
    "total_referrals": 5,
    "completed_referrals": 2,
    "pending_referrals": 3,
    "total_points": 200,
    "total_earned": 500.00
  }
}
```

> **Referral Status Values:** `pending` | `completed` | `expired` | `cancelled`

---

## Error Codes

| HTTP | Meaning |
|---|---|
| 200 | Success |
| 400 | Bad Request / Validation failed |
| 404 | Not Found |
| 500 | Server Error |

---

## Notes for Frontend Developers

1. **All image/video URLs** are returned as complete URLs — use them directly in `<img src>` or `<video src>`.
2. **Blog gallery** is returned as a **PHP array** (not a JSON string) after processing.
3. **User ID** is a string like `user_6abc1234` (not a number).
4. **Wishlist** — toggle via `POST /api/mobile/wishlist/store` (adds if not present, removes if already present). Use `/wishlist/check` to get current state before rendering the heart icon.
5. **Referral** — each user gets a unique `referralcode` on signup. Share it with friends. Apply a friend's code via `POST /api/mobile/referral/apply`. Get your stats via `/referral/stats`.
6. **Pagination** — pass `limit` and `offset` query params to paginate list responses.
7. **Status filters** — most list endpoints accept `?status=active` or `?status=inactive`.
