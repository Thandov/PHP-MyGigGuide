<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $shareData['title'] }}</title>
    <meta name="description" content="{{ $shareData['description'] }}">

    <meta property="og:title" content="{{ $shareData['title'] }}">
    <meta property="og:description" content="{{ $shareData['description'] }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $shareData['url'] }}">
    <meta property="og:site_name" content="My Gig Guide">
    @if(!empty($shareData['image']))
        <meta property="og:image" content="{{ $shareData['image'] }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $shareData['title'] }}">
    <meta name="twitter:description" content="{{ $shareData['description'] }}">
    @if(!empty($shareData['image']))
        <meta name="twitter:image" content="{{ $shareData['image'] }}">
    @endif

    @php($loginUrl = route('login', ['continue' => $shareData['url']]))
    <meta http-equiv="refresh" content="0;url={{ $loginUrl }}">
</head>
<body>
    <p>Redirecting you to the secure event page…</p>
    <noscript>
        <p><a href="{{ $loginUrl }}">Click here to continue</a></p>
    </noscript>
</body>
</html>

