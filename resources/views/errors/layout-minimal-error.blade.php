<?php
/**
 * @package Yivic Base
 */
?>

@php
    $locale = config( 'app.locale' );
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <style>
        @import url('https://fonts.googleapis.com/css?family=Raleway');

        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .code {
            border-right: 2px solid;
            font-size: 66px;
            padding: 0 15px 0 15px;
            text-align: center;
        }

        .message {
            font-size: 24px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="code">
        @yield('code')
    </div>

    <div class="message" style="padding: 10px;">
        @yield('message')
    </div>
</div>
</body>
</html>