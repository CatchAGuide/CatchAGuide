# Calendly Integration Documentation

## Overview

This document describes the Calendly OAuth integration implemented in the CatchAGuide application. The integration allows users to connect their Calendly accounts, sync events, and receive ICS calendar files for booking confirmations.

## Features

- **OAuth Authentication**: Secure OAuth 2.0 flow for connecting Calendly accounts
- **Calendar Sync**: Automatic synchronization of Calendly events to local calendar
- **ICS File Generation**: Automatic generation and emailing of ICS files for booking confirmations
- **Webhook Support**: Real-time updates via Calendly webhooks
- **Event Listener Integration**: ICS files sent automatically when bookings are accepted

## Architecture

### SOLID Principles Implementation

The integration follows SOLID principles:

1. **Single Responsibility Principle (SRP)**:
   - `CalendlyService`: Handles Calendly API interactions
   - `OAuthService`: Generic OAuth functionality
   - `OAuthController`: Handles OAuth flow
   - `WebhookController`: Handles webhook events

2. **Open/Closed Principle (OCP)**:
   - `OAuthService` is abstract and can be extended for other OAuth providers
   - `CalendlyService` extends `OAuthService` for Calendly-specific functionality

3. **Liskov Substitution Principle (LSP)**:
   - `CalendlyService` properly extends `OAuthService`

4. **Interface Segregation Principle (ISP)**:
   - Services have focused interfaces for specific responsibilities

5. **Dependency Inversion Principle (DIP)**:
   - Controllers depend on service abstractions
   - Services are injected via dependency injection

## Database Schema

### OAuth Tokens Table

```sql
CREATE TABLE oauth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NULL,
    token_type VARCHAR(255) DEFAULT 'Bearer',
    expires_at TIMESTAMP NULL,
    provider_user_id VARCHAR(255) NULL,
    provider_data JSON NULL,
    status ENUM('pending', 'active', 'expired', 'revoked') DEFAULT 'pending',
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_user_type (user_id, type),
    INDEX idx_type_status (type, status),
    UNIQUE KEY unique_user_type (user_id, type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
CALENDLY_CLIENT_ID=your_calendly_client_id
CALENDLY_CLIENT_SECRET=your_calendly_client_secret
CALENDLY_REDIRECT_URI=https://app.catchaguide.com/oauth/calendly/callback
CALENDLY_WEBHOOK_SIGNING_KEY=your_webhook_signing_key
```

### Services Configuration

The Calendly configuration is automatically loaded from `config/services.php`:

```php
'calendly' => [
    'client_id' => env('CALENDLY_CLIENT_ID'),
    'client_secret' => env('CALENDLY_CLIENT_SECRET'),
    'redirect_uri' => env('CALENDLY_REDIRECT_URI'),
    'webhook_signing_key' => env('CALENDLY_WEBHOOK_SIGNING_KEY'),
],
```

## API Endpoints

### OAuth Endpoints

- `GET /oauth/calendly` - Initiate Calendly OAuth flow
- `GET /oauth/calendly/callback` - OAuth callback handler
- `POST /oauth/calendly/disconnect` - Disconnect Calendly account
- `POST /oauth/calendly/sync` - Manually sync Calendly events

### Webhook Endpoints

- `POST /webhooks/calendly` - Calendly webhook handler

## Usage

### For Users

1. **Connect Calendly Account**:
   - Go to Profile → Account Settings
   - Click "Connect Calendly" button
   - Authorize the application
   - Account will show as "Connected"

2. **Sync Events**:
   - Click "Sync Now" to manually sync events
   - Events are automatically synced periodically

3. **Receive ICS Files**:
   - When a booking is accepted, an ICS file is automatically sent
   - ICS file can be imported into any calendar application

### For Developers

#### Manual Sync Command

```bash
# Sync all users
php artisan calendly:sync-events

# Sync specific user
php artisan calendly:sync-events --user-id=123

# Force sync (ignore last sync time)
php artisan calendly:sync-events --force
```

#### Service Usage

```php
// Get Calendly service
$calendlyService = app(CalendlyService::class);

// Check if user has active connection
$hasConnection = $calendlyService->hasActiveConnection($userId);

// Sync user events
$events = $calendlyService->syncUserEvents($userId);

// Generate ICS content
$icsContent = $calendlyService->generateICSContent($booking, $guiding);
```

## Event Flow

### Booking Acceptance Flow

1. Guide accepts booking via `BookingController::accept()`
2. `BookingStatusChanged` event is fired
3. `BookingAcceptedListener` handles the event
4. ICS file is generated and sent to both guest and guide (if connected)
5. Email with ICS attachment is sent

### Calendly Webhook Flow

1. Calendly sends webhook to `/webhooks/calendly`
2. `WebhookController::calendly()` verifies signature
3. Event is processed based on type:
   - `invitee.created`: Creates calendar entry
   - `invitee.canceled`: Removes calendar entry
   - `invitee.updated`: Updates calendar entry

## Security

### OAuth Security

- State parameter prevents CSRF attacks
- Tokens are encrypted and stored securely
- Refresh tokens are handled automatically
- Expired tokens are marked and refreshed

### Webhook Security

- HMAC-SHA256 signature verification
- Webhook signing key must match Calendly's key
- Invalid signatures return 401 Unauthorized

## Error Handling

### OAuth Errors

- Invalid state parameters are logged and rejected
- Token exchange failures are logged with details
- User-friendly error messages are displayed

### API Errors

- HTTP errors are logged with response details
- Rate limiting is handled gracefully
- Network timeouts are retried

### Webhook Errors

- Invalid signatures are logged and rejected
- Malformed payloads are logged and ignored
- Processing errors are logged with full context

## Monitoring

### Logs

Key log entries to monitor:

- `Calendly OAuth successful` - Successful OAuth connections
- `Calendly event synced to calendar` - Successful event syncs
- `Calendly webhook received` - Webhook events
- `Failed to send ICS file` - ICS file sending errors

### Metrics

Track these metrics:

- OAuth connection success rate
- Event sync success rate
- Webhook processing success rate
- ICS file generation success rate

## Troubleshooting

### Common Issues

1. **OAuth Connection Fails**:
   - Check client ID and secret
   - Verify redirect URI matches Calendly settings
   - Check logs for specific error messages

2. **Events Not Syncing**:
   - Verify token is active and not expired
   - Check Calendly API rate limits
   - Run manual sync command to test

3. **ICS Files Not Sent**:
   - Check email configuration
   - Verify booking acceptance flow
   - Check logs for mail errors

4. **Webhooks Not Working**:
   - Verify webhook URL in Calendly settings
   - Check webhook signing key
   - Monitor webhook endpoint logs

### Debug Commands

```bash
# Test OAuth flow
php artisan tinker
>>> app(\App\Services\CalendlyService::class)->getAuthorizationUrl()

# Test ICS generation
php artisan tinker
>>> $booking = \App\Models\Booking::first();
>>> $guiding = $booking->guiding;
>>> app(\App\Services\CalendlyService::class)->generateICSContent($booking, $guiding);

# Check OAuth tokens
php artisan tinker
>>> \App\Models\OAuthToken::where('type', 'calendly')->get();
```

## Future Enhancements

1. **Additional OAuth Providers**: Google Calendar, Outlook, etc.
2. **Two-way Sync**: Create Calendly events from local bookings
3. **Advanced Scheduling**: Use Calendly for guide availability
4. **Bulk Operations**: Sync multiple users at once
5. **Analytics**: Track integration usage and success rates

## Support

For issues or questions:

1. Check the logs in `storage/logs/laravel.log`
2. Run the debug commands above
3. Verify configuration settings
4. Test with a fresh OAuth connection 