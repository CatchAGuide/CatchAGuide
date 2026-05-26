<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <link href="https://fonts.cdnfonts.com/css/morrison" rel="stylesheet">
</head>
<body style="font-family: 'Morrison', Arial, sans-serif; margin: 0; padding: 0;">

<div style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 6px 3px rgba(0, 0, 0, 0.1);">
    <div style="text-align: center; padding: 20px;">
        <a href="{{ route('welcome') }}" target="_blank">
            <img src="https://catchaguide.com/assets/images/logo/CatchAGuide2_Logo_JPEG.jpg" alt="Catch A Guide" style="max-width: 150px; padding-top: 10px; display: block; margin: 0 auto;">
        </a>
        @if(!empty($title))
        <h2 style="color: #313041; font-size: 20px; font-weight: 700; margin: 16px 0 0;">{{ $title }}</h2>
        @endif
    </div>
    <div style="padding: 0 20px 10px;">
