<!DOCTYPE html>
<html lang="en">
<head>
    <title>@renderstage('title', 'Uh oh, something went wrong.')</title>

    <meta name="charset" content="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css?family=Comfortaa:300,400,700" rel="stylesheet">
    <link rel="icon" href="{{ url('favicon.ico') }}"/>

    <style>
        html, body {
            color: #11100E;
            font-family: 'Comfortaa', sans-serif !important;
            font-weight: 300;
            height: 100vh;
            margin: 0;
        }

        .coming-soon {
            text-align: center;
            max-width: 1000px;
            width: 100%;
            position: absolute;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
            padding: 50px;
        }

        img {
            max-width: 500px;
            height: auto;
        }

        h1 {
            font-size: 3rem;
            font-weight: 300;
            margin: 20px 0 10px;
            color: #EA8C68;
        }

        .home-btn {
            margin: 40px auto 0;
            padding: 10px 20px;
            border-radius: 5px;
            border: 1px solid #11100E;
            text-decoration: none;
            color: inherit;
            display: block;
            max-width: 100px;
            transition: 0.2s all ease-in-out;
        }

        .home-btn:hover {
            background: #11100E;
            color: #eeeeee;
        }
    </style>
</head>
<body>
<div class="coming-soon">
    <img src="{{ url('images/phanda-logo.png') }}">
    <h1>@renderstage('error', 'Uh, oh!')</h1>
    <h2>@renderstage('message', 'Something went wrong. Please try again later')</h2>
    <a href="{{ url()->previous('/') }}" class="home-btn">Go Back</a>
</div>
</body>
</html>