<!DOCTYPE html>
<html>
<head>
    <title>403 - Forbidden</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
            font-family: 'Lato', serif;
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 72px;
        }

        .sub-title {
            font-family: Verdana, serif;
            font-size: 20px;
        }

        .redirect {
            font-family: Verdana, serif;
            font-size: 12pt;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">403 - Unauthorized</div>
        @if ($exception->getMessage())
            <div class="sub-title">{{$exception->getMessage()}}</div>
        @endif
        <div>
            <img src="{{ url('/images/403.png') }}" title="You shall not pass" alt="You shall not pass">
        </div>
        <div class="redirect">
            You will be redirected in 5 seconds.
        </div>
    </div>
</div>
<script type="text/javascript">
    setTimeout(function () {
        location.href = "{{ url('/login') }}";
    }, 5000);
</script>
</body>
</html>
