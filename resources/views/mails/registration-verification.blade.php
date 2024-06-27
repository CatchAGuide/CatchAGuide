<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
@php
$url = route('registration-verfication') . '?hash=' . Crypt::encrypt($user->id);
@endphp
<body style="margin: 0; padding: 0;">
<p>@lang('registration-verification.hello') {{ $user->firstname }}</p>
<p>@lang('registration-verification.confirmEmail')</p>
<a href="{{ $url }}"><button>@lang('registration-verification.button')</button></a>
<p>@lang('registration-verification.cantClick')</p>
{{ $url }}
<p>@lang('registration-verification.verifyingEmail')</p>
<p>@lang('registration-verification.thankYou')</p><br>
<p>@lang('registration-verification.regards')<br>
@lang('registration-verification.cag')</p>
</body>
</html>
