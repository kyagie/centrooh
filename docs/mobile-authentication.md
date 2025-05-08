# Mobile Authentication with One-Time Passwords

## Overview

Our mobile authentication system uses one-time passwords (OTPs) to verify phone numbers and authenticate agents. The flow works as follows:

1. Agent enters their phone number in the app
2. System sends an OTP to that phone number via SMS
3. Agent enters the OTP code in the app
4. System verifies the OTP and checks if the phone number belongs to a registered agent
5. If verified and registered, the system generates an authentication token
6. The mobile app stores this token and uses it for subsequent API requests

## Authentication Flow Diagram

```
┌─────────┐     ┌──────────────┐     ┌──────────────┐     ┌─────────────┐
│  Agent  │     │  Mobile App  │     │  API Server  │     │   SMS API   │
└────┬────┘     └──────┬───────┘     └──────┬───────┘     └──────┬──────┘
     │                 │                    │                    │
     │  Enter Phone    │                    │                    │
     │───────────────►│                    │                    │
     │                 │                    │                    │
     │                 │ Request OTP        │                    │
     │                 │───────────────────►│                    │
     │                 │                    │                    │
     │                 │                    │  Send OTP via SMS  │
     │                 │                    │───────────────────►│
     │                 │                    │                    │
     │ Receive OTP     │                    │                    │
     │◄───────────────│                    │                    │
     │                 │                    │                    │
     │ Enter OTP       │                    │                    │
     │───────────────►│                    │                    │
     │                 │ Verify OTP         │                    │
     │                 │───────────────────►│                    │
     │                 │                    │                    │
     │                 │ Return Token       │                    │
     │                 │◄───────────────────│                    │
     │                 │                    │                    │
     │ Authenticated   │                    │                    │
     │◄───────────────│                    │                    │
     │                 │                    │                    │
```

## API Endpoints

### Request OTP

```
POST /api/auth/request-otp
```

Request:
```json
{
    "phone_number": "+256712345678"
}
```

Response:
```json
{
    "message": "OTP sent successfully",
    "phone_number": "256712345678"
}
```

### Verify OTP and Authenticate

```
POST /api/auth/verify-otp
```

Request:
```json
{
    "phone_number": "+256712345678",
    "otp_code": "123456",
    "device_name": "iPhone 13"
}
```

Response (if agent exists):
```json
{
    "message": "OTP verified successfully",
    "status": "authenticated",
    "agent": {
        "id": 1,
        "username": "JohnDoe",
        "phone_number": "256712345678",
        "status": "active",
        "region": {
            "id": 1,
            "name": "Central"
        },
        "district": {
            "id": 1,
            "name": "Kampala",
            "region_id": 1
        }
    },
    "token": "1|abcdef123456...",
    "token_type": "Bearer",
    "expires_at": "2025-06-07T12:00:00.000000Z"
}
```

Response (if phone number is not registered):
```json
{
    "message": "Phone number is not associated with any agent",
    "status": "unregistered"
}
```

### Get Authenticated Agent Profile

```
GET /api/auth/me
```

Headers:
```
Authorization: Bearer 1|abcdef123456...
```

Response:
```json
{
    "agent": {
        "id": 1,
        "username": "JohnDoe",
        "phone_number": "256712345678",
        "status": "active",
        "region": {
            "id": 1,
            "name": "Central"
        },
        "district": {
            "id": 1,
            "name": "Kampala",
            "region_id": 1
        }
    },
    "user": {
        "id": 1,
        "name": "JohnDoe",
        "email": "256712345678@centrooh.com"
    }
}
```

### Logout (Current Device)

```
POST /api/auth/logout
```

Headers:
```
Authorization: Bearer 1|abcdef123456...
```

Response:
```json
{
    "message": "Logged out successfully"
}
```

### Logout (All Devices)

```
POST /api/auth/logout-all
```

Headers:
```
Authorization: Bearer 1|abcdef123456...
```

Response:
```json
{
    "message": "Logged out from all devices successfully"
}
```

## Agent API Endpoints

The following endpoints are available for authenticated agents only:

### Get Assigned Billboards

```
GET /api/agent/billboards
```

Headers:
```
Authorization: Bearer 1|abcdef123456...
```

Response:
```json
{
    "status": "success",
    "data": {
        "billboards": [
            {
                "id": 1,
                "site_name": "Kampala Road Billboard",
                "region": { ... },
                "district": { ... },
                "siteCode": { ... },
                "images": [ ... ]
            }
        ]
    }
}
```

### Upload Billboard Image

```
POST /api/agent/billboards/upload-image
```

Headers:
```
Authorization: Bearer 1|abcdef123456...
Content-Type: multipart/form-data
```

Form Data:
```
billboard_id: 1
image: [file]
latitude: 0.347596
longitude: 32.582520
notes: "Billboard in good condition"
```

Response:
```json
{
    "status": "success",
    "message": "Image uploaded successfully",
    "data": {
        "image": {
            "id": 1,
            "billboard_id": 1,
            "image_path": "billboard-images/abcdef123456.jpg",
            "uploader_id": 1,
            "uploader_type": "agent",
            "latitude": "0.347596",
            "longitude": "32.582520",
            "notes": "Billboard in good condition",
            "created_at": "2025-05-08T12:00:00.000000Z"
        }
    }
}
```

### Update Agent Profile

```
POST /api/agent/profile/update
```

Headers:
```
Authorization: Bearer 1|abcdef123456...
Content-Type: multipart/form-data
```

Form Data:
```
username: "new_username" (optional)
profile_picture: [file] (optional)
```

Response:
```json
{
    "status": "success",
    "message": "Profile updated successfully",
    "data": {
        "agent": {
            "id": 1,
            "username": "new_username",
            "profile_picture": "profile-pictures/abcdef123456.jpg",
            "phone_number": "256712345678",
            "status": "active"
        }
    }
}
```

## Implementation Notes

- Tokens are valid for 30 days by default (configurable in sanctum.php)
- Only agents with valid phone numbers can be authenticated
- Use the `Authorization: Bearer {token}` header for authenticated requests
- The middleware `auth:sanctum` protects all authenticated routes
- The middleware `ensure.agent` protects agent-specific routes

## Rate Limiting

The following rate limits are enforced to ensure system stability and security:

1. **General API Rate Limiting**: 60 requests per minute per user ID (or IP for guests)
2. **OTP Endpoints**: 5 requests per minute per phone number (or IP address)
3. **Billboard Data**: 100 requests per minute per agent
4. **Image Uploads**: 10 uploads per minute per agent

Rate limits are configured in the `AppServiceProvider` and applied using the `throttle` middleware. When a rate limit is exceeded, the API will return a 429 HTTP status code (Too Many Requests).

## Security Considerations

1. **Token Storage**: Mobile apps should store tokens securely using encrypted storage
2. **Token Expiration**: Tokens expire after 30 days, requiring re-authentication
3. **Device Identification**: Each token is associated with a specific device
4. **Logout Capabilities**: Users can revoke tokens for their current device or all devices
5. **Rate Limiting**: Protection against brute force attacks with strict rate limits on OTP endpoints

## Error Handling

All API endpoints return appropriate HTTP status codes:

- 200: Success
- 400: Bad request (invalid input)
- 401: Unauthorized (invalid or expired token)
- 403: Forbidden (unauthorized access to resource)
- 404: Resource not found
- 422: Validation error
- 429: Too many requests (rate limited)
- 500: Server error

Example error response:
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "phone_number": [
            "Please provide a valid Ugandan phone number."
        ]
    }
}
```
