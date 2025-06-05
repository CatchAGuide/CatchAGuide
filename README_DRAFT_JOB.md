# Guiding Draft Job Implementation

## Overview

The `saveDraft()` method has been converted to use a hybrid approach combining synchronous file processing with asynchronous database operations via Laravel jobs.

## Architecture

### Files Created:
- `app/Jobs/SaveGuidingDraftJob.php` - Job class handling database operations
- `app/Http/Controllers/GuidingsController.php` - Modified controller with job dispatch

### How It Works:

1. **Synchronous Phase** (Controller):
   - File uploads are processed immediately
   - Images are uploaded and processed synchronously
   - Data is prepared and serialized for the job

2. **Asynchronous Phase** (Job):
   - Database operations are queued
   - Heavy operations like seasonal blocking are handled in background
   - Transactions ensure data consistency

## Usage

### Option 1: Asynchronous (Recommended for production)
```php
// Uses the job queue - fast response, background processing
POST /save-draft
```

### Option 2: Synchronous (For immediate feedback)
```php
// For when you need immediate guiding_id response
POST /save-draft-sync
```

## Benefits

1. **Performance**: File uploads happen immediately, but heavy DB operations are queued
2. **User Experience**: Users get faster responses
3. **Scalability**: Database operations can be distributed across queue workers
4. **Error Handling**: Better error isolation between file processing and DB operations

## Considerations

### File Uploads
- Files are still processed synchronously (required for web uploads)
- File paths are passed to the job after successful upload
- Failed file uploads prevent job dispatch

### Queue Requirements
- Ensure you have queue workers running: `php artisan queue:work`
- Consider using Redis or database queues for production
- Failed jobs can be retried automatically

### Error Handling
- File upload errors return immediately
- Job errors are logged and can be monitored via `failed_jobs` table
- Users get immediate feedback for validation errors

## Configuration

### Queue Configuration
In `.env`:
```
QUEUE_CONNECTION=redis  # or database
```

### Job Configuration
The job includes:
- Automatic retry on failure
- Database transactions
- Comprehensive error logging
- Support for both new and update operations

## Migration Strategy

1. **Phase 1**: Deploy with job-based `saveDraft()` 
2. **Phase 2**: Monitor performance and error rates
3. **Phase 3**: Remove `saveDraftSync()` if job version performs well

## Monitoring

Monitor job performance:
```bash
# Check queue status
php artisan queue:monitor

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Rollback Plan

If issues arise, you can quickly rollback by:
1. Routing draft saves to `saveDraftSync()` method
2. This method maintains the original synchronous behavior
3. All file processing remains unchanged 