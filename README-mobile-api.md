# Centrooh Mobile API

## Overview

This API provides authentication and data endpoints for the Centrooh mobile application, enabling agents to:

1. Authenticate using one-time passwords (OTPs)
2. View assigned billboards
3. Upload billboard images
4. Update their profile information

## Getting Started

### Prerequisites

- Laravel Sanctum is used for API token authentication
- AfricasTalking is used for sending OTP messages

### Environment Setup

Make sure your `.env` file contains the following variables:

```
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
SANCTUM_TOKEN_PREFIX=

# OTP Settings
OTP_EXPIRY_MINUTES=10
OTP_MAX_ATTEMPTS=3

# AfricasTalking Credentials
AT_USERNAME=your_username
AT_API_KEY=your_api_key
AT_FROM=INSYTMEDIA
```

## Authentication Flow

1. The agent requests an OTP using their phone number
2. The system sends an OTP via SMS
3. The agent submits the OTP along with their device name
4. If verified, the system checks if the phone number belongs to a registered agent
5. If it does, the system issues a token for API authentication

### Security Measures

- Rate limiting to prevent brute force attacks
- Token expiration after 30 days
- Agent-specific middleware to protect agent-only routes
- Ability to logout from current device or all devices

## API Documentation

For full API documentation, see the [Mobile Authentication Documentation](./docs/mobile-authentication.md).

## Implementation Details

### Key Files

- `app/Http/Controllers/AuthController.php` - Handles OTP-based authentication
- `app/Http/Controllers/AgentApiController.php` - Agent-specific endpoints
- `app/Http/Middleware/EnsureUserIsAgent.php` - Middleware to verify agent access
- `app/Models/OneTimePassword.php` - OTP generation and verification
- `app/Jobs/SendOneTimePassword.php` - Queued job for sending OTPs

### Database Tables

- `users` - User accounts
- `agents` - Agent profiles
- `one_time_passwords` - OTP records
- `personal_access_tokens` - Sanctum tokens
- `billboards` - Billboard information
- `billboard_images` - Images uploaded by agents

## Development Guidelines

1. Always validate input data
2. Apply appropriate rate limiting
3. Use queue for SMS sending to prevent blocking
4. Keep responses consistent with the established format
5. Document all new endpoints in the API documentation

## Testing

To test the authentication flow:

```bash
php artisan test --filter=AuthenticationTest
```
