<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Queue Stalled Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .summary { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .summary-row { margin: 10px 0; }
        .label { font-weight: bold; color: #495057; }
        .value { color: #dc3545; font-weight: bold; }
        .action-box { background: #fff3cd; border: 1px solid #ffe69c; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .tech-section { margin-top: 25px; border-top: 2px dashed #ccc; padding-top: 15px; }
        .tech-section summary { cursor: pointer; font-weight: bold; color: #495057; }
        .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { margin: 8px 0; font-size: 14px; }
        .footer { text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px; }
        code { font-family: monospace; background: #e9ecef; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 24px; font-weight: bold;">⚠️ Some booking emails haven't gone out yet</div>
        </div>

        <div class="content">
            <p>The website's automatic email system has stalled. Guests and guides who booked recently may not
            have received their confirmation emails yet.</p>

            <div class="summary">
                <div class="summary-row">
                    <span class="label">Emails waiting to be sent:</span>
                    <span class="value">{{ $staleCount }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Longest wait so far:</span>
                    <span class="value">{{ $oldestAgeMinutes }} minutes</span>
                </div>
            </div>

            <div class="action-box">
                <strong>What to do:</strong> Forward this email to your developer so they can restart the email
                system and get these bookings sent out. The technical steps they need are below.
            </div>

            <details class="tech-section">
                <summary>Technical details (for your developer)</summary>

                <div class="details" style="margin-top: 15px;">
                    <div class="detail-row">
                        <span class="label">Stuck jobs:</span>
                        <span class="value">{{ $staleCount }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Oldest job age:</span>
                        <span class="value">{{ $oldestAgeMinutes }} minutes</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Alert threshold:</span>
                        <span class="value">{{ $staleThresholdMinutes }} minutes</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Detected at:</span>
                        <span class="value">{{ $timestamp->format('Y-m-d H:i:s T') }}</span>
                    </div>
                </div>

                <p><strong>Likely cause:</strong> the scheduled <code>queue:work</code> run is stuck, often because a
                previous run was killed mid-execution and left its <code>withoutOverlapping()</code> lock in place.</p>

                <p><strong>To fix on the production server:</strong></p>
                <ul>
                    <li><code>cd ~/www/catchaguide.de/public_html</code></li>
                    <li><code>php artisan schedule:clear-cache</code></li>
                    <li><code>php artisan queue:work --queue=default --stop-when-empty --tries=3 --max-time=50</code></li>
                </ul>
            </details>
        </div>

        <div class="footer">
            <p>This is an automated alert from the website's email health check.</p>
            <p>Generated at {{ $timestamp->format('Y-m-d H:i:s T') }}</p>
        </div>
    </div>
</body>
</html>
