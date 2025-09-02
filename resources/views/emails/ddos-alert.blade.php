<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DDoS Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .alert-type { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { margin: 10px 0; }
        .label { font-weight: bold; color: #495057; }
        .value { color: #dc3545; }
        .footer { text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px; }
        .ip-address { font-family: monospace; background: #e9ecef; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-type">🚨 DDoS Alert</div>
            <div>{{ $alertType }}</div>
        </div>
        
        <div class="content">
            <p><strong>Alert Details:</strong></p>
            
            <div class="details">
                <div class="detail-row">
                    <span class="label">Alert Type:</span>
                    <span class="value">{{ $alertType }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Timestamp:</span>
                    <span class="value">{{ $timestamp->format('Y-m-d H:i:s T') }}</span>
                </div>
                
                @if(isset($details['ip']))
                <div class="detail-row">
                    <span class="label">IP Address:</span>
                    <span class="ip-address">{{ $details['ip'] }}</span>
                </div>
                @endif
                
                @if(isset($details['user_agent']))
                <div class="detail-row">
                    <span class="label">User Agent:</span>
                    <span class="value">{{ $details['user_agent'] }}</span>
                </div>
                @endif
                
                @if(isset($details['endpoint']))
                <div class="detail-row">
                    <span class="label">Endpoint:</span>
                    <span class="value">{{ $details['endpoint'] }}</span>
                </div>
                @endif
                
                @if(isset($details['violations']))
                <div class="detail-row">
                    <span class="label">Violations:</span>
                    <span class="value">{{ $details['violations'] }}</span>
                </div>
                @endif
                
                @if(isset($details['requests_per_minute']))
                <div class="detail-row">
                    <span class="label">Requests/Minute:</span>
                    <span class="value">{{ $details['requests_per_minute'] }}</span>
                </div>
                @endif
                
                @if(isset($details['daily_usage']))
                <div class="detail-row">
                    <span class="label">Daily API Usage:</span>
                    <span class="value">{{ $details['daily_usage'] }} requests</span>
                </div>
                @endif
                
                @if(isset($details['estimated_cost']))
                <div class="detail-row">
                    <span class="label">Estimated Cost:</span>
                    <span class="value">${{ number_format($details['estimated_cost'], 4) }}</span>
                </div>
                @endif
                
                @if(isset($details['block_duration']))
                <div class="detail-row">
                    <span class="label">Block Duration:</span>
                    <span class="value">{{ $details['block_duration'] }} minutes</span>
                </div>
                @endif
            </div>
            
            <p><strong>Recommended Actions:</strong></p>
            <ul>
                <li>Monitor the IP address for continued suspicious activity</li>
                <li>Check server logs for additional attack patterns</li>
                <li>Consider implementing additional security measures if attacks persist</li>
                <li>Review and adjust rate limiting thresholds if needed</li>
            </ul>
            
            <p><strong>System Status:</strong> The DDoS protection system is actively blocking malicious requests and protecting your application.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated alert from your DDoS protection system.</p>
            <p>Generated at {{ $timestamp->format('Y-m-d H:i:s T') }}</p>
        </div>
    </div>
</body>
</html>
