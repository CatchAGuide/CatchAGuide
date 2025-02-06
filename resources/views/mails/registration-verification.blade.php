<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>@lang('registration-verification.emailTitle')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
@php
$url = route('registration-verfication') . '?hash=' . Crypt::encrypt($user->id);
@endphp
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #2d3748; background-color: #f7fafc;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <!-- Header with Logo Space -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('assets/images/logo/CatchAGuide_Logo_JPEG.jpg') }}" alt="CAG Logo" style="max-width: 150px; height: auto;">
        </div>
        
        <!-- Main Content -->
        <div style="background-color: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
            <!-- Decorative Top Border -->
            <div style="width: 100%; height: 4px; background: linear-gradient(to right, #4CAF50, #45a049); margin-bottom: 30px; border-radius: 2px;"></div>
            
            <h1 style="color: #1a202c; margin-bottom: 24px; font-size: 24px; font-weight: 600;">@lang('registration-verification.hello') {{ $user->firstname }}! 👋</h1>
            
            <p style="margin-bottom: 24px; color: #4a5568; font-size: 16px;">@lang('registration-verification.confirmEmail')</p>
            
            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $url }}" style="background: linear-gradient(to right, #4CAF50, #45a049); color: white; padding: 14px 35px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);">
                    @lang('registration-verification.button')
                </a>
            </div>
            
            <p style="margin-bottom: 12px; color: #4a5568; font-size: 14px;">@lang('registration-verification.cantClick')</p>
            <div style="background-color: #f8fafc; padding: 16px; border-radius: 8px; margin-bottom: 24px; word-break: break-all; border: 1px solid #e2e8f0; font-family: monospace; font-size: 14px; color: #4a5568;">
                {{ $url }}
            </div>
            
            <p style="color: #4a5568; font-size: 16px;">@lang('registration-verification.verifyingEmail')</p>
            
            <!-- Info Box -->
            <div style="background-color: #ebf8ff; border-left: 4px solid #4299e1; padding: 16px; margin: 24px 0; border-radius: 4px;">
                <p style="margin: 0; color: #2b6cb0; font-size: 14px;">⚡ @lang('registration-verification.verifyingEmail')</p>
            </div>
            
            <hr style="border: none; border-top: 1px solid #edf2f7; margin: 30px 0;">
            
            <p style="margin-bottom: 8px; color: #4a5568;">@lang('registration-verification.thankYou')</p>
            <p style="margin-bottom: 0; color: #4a5568;">
                @lang('registration-verification.regards')<br>
                <strong style="color: #2d3748;">@lang('registration-verification.cag')</strong>
            </p>
        </div>
        
        <!-- Footer -->
        <div style="text-align: center; margin-top: 20px; color: #718096; font-size: 12px;">
            <p style="margin-bottom: 10px;">© {{ date('Y') }} Catch A Guide. All rights reserved.</p>
            <div style="margin-top: 10px;">
                <a href="#" style="color: #718096; text-decoration: none; margin: 0 10px;">Privacy Policy</a>
                <a href="#" style="color: #718096; text-decoration: none; margin: 0 10px;">Terms of Service</a>
            </div>
        </div>
    </div>
</body>
</html>
