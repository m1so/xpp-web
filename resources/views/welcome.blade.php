<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>XPPWeb</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Gulp'd & Elixir'd stuff -->
    <link href="{{ asset('css/vendor.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/main.css') }}" rel="stylesheet" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->



    <style>
        .centeredPrompt {
            /**
             * Lay out the children of this container with
             * flexbox.
             */
            display: flex;

            /**
             * Rotate the main axis so that the children
             * are laid out vertically, the same as in the
             * above "Side bar" example.
             */
            flex-direction: column;

            /**
             * Align the children in the center, along
             * the main axis. Because the direction is
             * "column" this has a similar effect as setting
             * text-align: center.
             */
            align-items: center;

            /**
             * Instead of pushing the children apart, as in
             * previous examples, we will group them together
             * in the center of their container.
             */
            justify-content: center;

            min-height: 300px;
            padding: 10px;
        }

        .centeredPrompt__item + .centeredPrompt__item {
            margin-top: 5px;
        }

        .centeredPromptIcon {
            font-size: 60px;
            line-height: 0;
        }

        .centeredPromptLabel {
            color: #86969C;
            font-size: 30px;
            font-weight: 700;
            text-align: center;
        }

        .centeredPromptDetails {
            color: #86969C;
            font-size: 16px;
            margin-bottom: 10px;
            text-align: center;
        }

        .icon {
            color: #BCD2DA;
        }
    </style>
</head>
<body>
    <div class="centeredPrompt">
        <div class="centeredPrompt__item centeredPromptIcon">
            <div class="icon fa fa-rocket"></div>
        </div>
        <div class="centeredPrompt__item centeredPromptLabel">Welcome to the XPPWeb Beta!</div>
        <div class="centeredPrompt__item centeredPromptDetails">Careful, things might break.</div>
        <div class="centeredPrompt__item">
            <a href="/login" type="button" class="btn btn-default">Login</a>
        </div>
    </div>

    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

</body>
</html>
