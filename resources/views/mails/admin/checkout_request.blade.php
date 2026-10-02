@php
    $copy = 'emails.checkout_request_admin';
    $font = "font-family:'Inter',Arial,Helvetica,sans-serif;";
    $heading = "font-family:'Archivo',Arial,Helvetica,sans-serif;";
    $mono = "font-family:'IBM Plex Mono','Courier New',Courier,monospace;";
    $section = 'padding:0 0 8px 0;'.$font.'font-size:11px;line-height:16px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#0E5A61;';
    $box = 'border:1px solid #ECE5DA;border-radius:12px;border-collapse:separate;background:#ffffff;';
    $kvLabel = $font.'font-size:14px;line-height:20px;color:#3E4C50;';
    $kvValue = $font.'font-size:14px;line-height:20px;font-weight:500;color:#1D2A2E;text-align:right;';
    $link = 'color:#0E5A61;text-decoration:underline;';
    $strong = fn (string $value) => '<strong style="font-weight:600;">'.e($value).'</strong>';
    $guestRows = array_values(array_filter([
        ['label' => __($copy.'.name'), 'value' => $guestName],
        $guestEmail !== '' ? ['label' => __($copy.'.email'), 'value' => $guestEmail, 'href' => 'mailto:'.$guestEmail, 'wrap' => 'word-break:break-all;'] : null,
        $guestPhone !== '' ? ['label' => __($copy.'.phone'), 'value' => $guestPhone, 'href' => $guestPhoneHref, 'wrap' => 'white-space:nowrap;'] : null,
    ]));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no, address=no, email=no, date=no">
<title>{{ $subject }}</title>
<style>
body{margin:0!important;padding:0!important;width:100%!important;background:#F6F4F0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table{border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0}
a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important}
@media only screen and (max-width:620px){
.wrap{width:100%!important}
.outer{padding:0!important}
.card{border-radius:0!important}
.px{padding-left:20px!important;padding-right:20px!important}
.stack{display:block!important;width:100%!important;box-sizing:border-box}
.half-l{padding-right:0!important;border-right:0!important}
.half-r{padding-left:0!important;border-top:1px solid #ECE5DA!important}
.btn-gap{padding:12px 0 0 0!important}
.btn{width:100%!important}
.h1{font-size:24px!important;line-height:30px!important}
}
</style>
<!--[if mso]><style>*{font-family:Arial,Helvetica,sans-serif!important}</style><![endif]-->
</head>
<body style="margin:0;padding:0;background:#F6F4F0;">
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F6F4F0;">{{ $preheader }} &#847; &#847; &#847; &#847; &#847; &#847;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F6F4F0;">
<tr><td class="outer" align="center" style="padding:24px 0;">
<table role="presentation" class="wrap card" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background:#ffffff;border-radius:12px;border-collapse:separate;overflow:hidden;">

{{-- Header --}}
<tr><td class="px" style="background:#0A2F35;padding:24px 32px 26px 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="middle"><span style="{{ $heading }}font-size:17px;line-height:20px;font-weight:700;color:#ffffff;letter-spacing:-0.01em;">Catch&nbsp;A&nbsp;Guide</span></td>
<td align="right" valign="middle"><span style="display:inline-block;background:#E3F0EF;color:#0E5A61;{{ $font }}font-size:12px;line-height:16px;font-weight:600;padding:4px 10px;border-radius:999px;">{{ $badge }}</span></td>
</tr></table>
<h1 class="h1" style="margin:22px 0 0 0;{{ $heading }}font-size:28px;line-height:34px;font-weight:700;color:#ffffff;">{{ __($copy.'.title') }}</h1>
</td></tr>

{{-- Summary --}}
<tr><td class="px" style="padding:28px 32px 28px 32px;{{ $font }}font-size:16px;line-height:24px;font-weight:400;color:#1D2A2E;">
<p style="margin:0 0 8px 0;">{{ __($copy.'.greeting') }}</p>
<p style="margin:0;">{!! __($copy.'.intro_'.$productType, ['title' => $strong($shortTitle), 'name' => e($guestName), 'deadline' => $strong($deadline)]) !!}</p>
</td></tr>

{{-- Guest --}}
<tr><td class="px" style="padding:0 32px 28px 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td style="{{ $section }}">{{ __($copy.'.section_guest') }}</td></tr>
<tr><td><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $box }}"><tr><td style="padding:4px 16px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($guestRows as $row)
@php $rule = $loop->first ? '' : 'border-top:1px solid #ECE5DA;'; @endphp
<tr><td width="40%" valign="top" style="padding:12px 12px 12px 0;{{ $rule }}{{ $kvLabel }}">{{ $row['label'] }}</td><td width="60%" valign="top" align="right" style="padding:12px 0;{{ $rule }}{{ $kvValue }}">@if(!empty($row['href']))<a href="{{ $row['href'] }}" style="{{ $link }}{{ $row['wrap'] }}">{{ $row['value'] }}</a>@else{{ $row['value'] }}@endif</td></tr>
@endforeach
</table></td></tr></table></td></tr>
</table></td></tr>

{{-- Request: product + rows + price estimate --}}
<tr><td class="px" style="padding:0 32px 28px 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td style="{{ $section }}">{{ __($copy.'.section_request') }}</td></tr>
<tr><td>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $box }}">
<tr><td style="padding:0 16px 4px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
@if($image)
<td width="64" valign="top" style="padding:16px 0;"><img src="{{ $image }}" width="64" height="64" alt="" style="display:block;width:64px;height:64px;object-fit:cover;border-radius:8px;border:0;"></td>
@endif
<td valign="top" style="padding:16px 0 16px {{ $image ? '14px' : '0' }};">
<p style="margin:0;{{ $font }}font-size:15px;line-height:21px;font-weight:700;color:#1D2A2E;">{{ $title }}</p>
@if($location !== '')
<p style="margin:2px 0 0 0;{{ $font }}font-size:13px;line-height:19px;color:#56656A;">{{ $location }}</p>
@endif
@if($url)
<p style="margin:6px 0 0 0;{{ $font }}font-size:13px;line-height:19px;"><a href="{{ $url }}" style="{{ $link }}font-weight:500;">{{ __($copy.'.open_listing') }}</a></p>
@endif
</td>
</tr></table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($rows as $row)
@if(isset($row['pair']))
<tr><td colspan="2" style="padding:0;border-top:1px solid #ECE5DA;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
@foreach($row['pair'] as $half)
<td class="stack {{ $loop->first ? 'half-l' : 'half-r' }}" width="50%" valign="top" style="{{ $loop->first ? 'padding:12px 16px 12px 0;border-right:1px solid #ECE5DA;' : 'padding:12px 0 12px 16px;' }}"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="40%" style="{{ $kvLabel }}">{{ $half['label'] }}</td><td align="right" style="{{ $kvValue }}">{{ $half['value'] }}</td></tr></table></td>
@endforeach
</tr></table></td></tr>
@else
<tr><td width="40%" valign="top" style="padding:12px 12px 12px 0;border-top:1px solid #ECE5DA;{{ $kvLabel }}">{{ $row['label'] }}</td><td width="60%" valign="top" align="right" style="padding:12px 0;border-top:1px solid #ECE5DA;{{ $kvValue }}">{{ $row['value'] }}</td></tr>
@endif
@endforeach
</table>
</td></tr>
<tr><td style="background:#F4EEE4;border-top:1px solid #E0D5C4;border-radius:0 0 11px 11px;padding:16px;">
<p style="margin:0 0 4px 0;{{ $font }}font-size:11px;line-height:16px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#0E5A61;">{{ __($copy.'.section_price') }}</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($priceLines as $line)
<tr><td valign="top" style="padding:8px 12px 8px 0;{{ $kvLabel }}">{{ $line['label'] }}</td><td valign="top" align="right" style="padding:8px 0;{{ $mono }}font-size:14px;line-height:20px;color:#1D2A2E;text-align:right;white-space:nowrap;">{{ $line['amount'] }}</td></tr>
@endforeach
<tr><td style="padding:12px 12px 0 0;{{ $priceLines ? 'border-top:1px solid #E0D5C4;' : '' }}{{ $font }}font-size:14px;line-height:24px;font-weight:600;color:#1D2A2E;">{{ __($copy.'.total') }}</td><td align="right" style="padding:12px 0 0 0;{{ $priceLines ? 'border-top:1px solid #E0D5C4;' : '' }}{{ $mono }}font-size:18px;line-height:24px;font-weight:600;color:#1D2A2E;text-align:right;white-space:nowrap;">{{ $total }}</td></tr>
</table>
</td></tr>
</table>
<p style="margin:8px 0 0 0;{{ $font }}font-size:12px;line-height:18px;color:#56656A;">{{ __($copy.'.estimate_note') }}</p>
</td></tr>
</table></td></tr>

{{-- Guest message --}}
@if($guestNote !== '')
<tr><td class="px" style="padding:0 32px 28px 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td style="{{ $section }}">{{ __($copy.'.section_message') }}</td></tr>
<tr><td><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $box }}"><tr><td style="padding:16px;"><p style="margin:0;{{ $font }}font-size:14px;line-height:22px;font-style:italic;color:#1D2A2E;">{!! nl2br(e(__($copy.'.quote', ['message' => $guestNote]))) !!}</p></td></tr></table></td></tr>
</table></td></tr>
@endif

{{-- Actions --}}
<tr><td class="px" style="padding:4px 32px 32px 32px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" class="wrap"><tr>
<td class="stack" valign="top"><table role="presentation" class="btn" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;"><tr><td height="44" align="center" valign="middle" style="height:44px;border-radius:10px;background:#C9472C;"><a href="{{ $requestUrl }}" style="display:block;padding:0 22px;{{ $font }}font-size:15px;line-height:44px;font-weight:600;color:#ffffff;text-decoration:none;white-space:nowrap;">{{ __($copy.'.open_request') }}</a></td></tr></table></td>
@if($replyUrl)
<td class="stack btn-gap" valign="top" style="padding-left:12px;"><table role="presentation" class="btn" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;"><tr><td height="44" align="center" valign="middle" style="height:44px;border-radius:10px;background:#ffffff;border:1.5px solid #0E5A61;"><a href="{{ $replyUrl }}" style="display:block;padding:0 22px;{{ $font }}font-size:15px;line-height:41px;font-weight:600;color:#0E5A61;text-decoration:none;white-space:nowrap;">{{ __($copy.'.reply_to_guest') }}</a></td></tr></table></td>
@endif
</tr></table>
@if($guestEmail !== '')
<p style="margin:12px 0 0 0;{{ $font }}font-size:12px;line-height:18px;color:#56656A;">{{ __($copy.'.reply_hint') }}</p>
@endif
</td></tr>

{{-- Footer --}}
<tr><td class="px" style="background:#0A2F35;padding:24px 32px;">
<span style="{{ $heading }}font-size:17px;line-height:20px;font-weight:700;color:#ffffff;letter-spacing:-0.01em;">Catch&nbsp;A&nbsp;Guide</span>
<p style="margin:10px 0 0 0;{{ $font }}font-size:12px;line-height:18px;color:#ffffff;">{{ __($copy.'.footer') }}</p>
<p style="margin:2px 0 0 0;{{ $font }}font-size:12px;line-height:18px;color:#ffffff;opacity:0.75;">© {{ date('Y') }} Catch A Guide</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
