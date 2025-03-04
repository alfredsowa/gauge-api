<html>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@200..800&family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <style>
        html,
        body {
            font-size: 14.5px;
            line-height: 1.5;
            color: #333333;
            background-color: #fff;
            border: 0;
            margin: 0;
            padding: 0;
            overflow-x: auto;
            font-family: Barlow, Assistant, -apple-system, BlinkMacSystemFont, Segoe UI, Ubuntu, Helvetica, Arial, sans-serif !important;
            margin: 0;
            -webkit-text-size-adjust: auto;
            word-wrap: break-word;
            -webkit-nbsp-mode: space;
            -webkit-line-break: after-white-space;
        }

        h1 {
            /* font-size: 2em !important; */
            font-size: 25px;
            font-weight: 400;
        }

        strong,
        b,
        .bold {
            font-weight: 600;
        }

        body {
            overflow-y: auto;
            word-break: break-word;
            -webkit-font-smoothing: antialiased;
            filter: none;
            --image-filter: grayscale(20%);
        }

        .theme-emoji {
            filter: none;
        }

        a {
            color: #038C65;
        }

        a:hover {
            color: #038C65;
        }

        a:visited {
            color: #038C65;
        }

        a img {
            border-bottom: 0;
        }

        body.heightDetermined {
            overflow-y: auto;
        }

        div,
        pre {
            max-width: 100%;
        }

        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        pre.flockmail-plaintext {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        img {
            border: 0;
        }

        img:not([src*='.svg']) {
            filter: none;
        }

        search-match,
        .search-match {
            background: #fff000;
            border-radius: 4px;
            box-shadow: 0 0.5px 0.5px rgba(0, 0, 0, 0.25);

            &.current-match {
                background: #ff8b1a;
            }
        }

        table {
            word-break: initial;
            border-collapse: collapse;
        }

        a.mk-unsubscribe:not([href]),
        a[fr-original-class=mk-unsubscribe]:not([href]) {
            pointer-events: none !important;
        }

        p.MsoNormal,
        li.MsoNormal,
        div.MsoNormal {
            margin: 0px;
        }

        ::-webkit-scrollbar-corner {
            background-color: transparent;
        }

        ::-webkit-scrollbar {
            width: 14px;
            height: 14px;
            cursor: default;
        }

        ::-webkit-scrollbar-thumb {
            border-radius: 14px;
            background-clip: content-box;
            border: 3px solid transparent;
            background: transparent;
            box-shadow: inset 0 0 15px 15px rgba(136, 136, 136, 0.4);
        }

        ::-webkit-scrollbar-thumb:hover {
            box-shadow: inset 0 0 15px 15px #bdbdbd;
        }
    </style>
</head>

<body data-new-gr-c-s-check-loaded="14.1171.0" data-gr-ext-installed="">
    <div id="inbox-html-wrapper">
        <!-- <div class="preheader plaintext_ignore" style="font-size:1px;display:none !important">
            <div>Confirm your email address. Your confirmation code is below — enter it in your open browser window and
                we'll help you get signed in. </div>
        </div> -->

        <table
            style="background-color:#ffffff;padding-top:20px;width:100%;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;border:0;text-align:center;border-collapse:collapse"
            class="background_main">
            <tbody>
                <tr>
                    <td style="vertical-align:top;padding:0">
                        <center>
                            <table id="body" class="card"
                                style="border:0;border-collapse:collapse;margin:0 auto;background:white;border-radius:8px;margin-bottom:16px">
                                <tbody>
                                    <tr>
                                        <td style="width:546px;vertical-align:top;padding-top:32px">
                                            <div style="max-width:600px;margin:0 auto">
                                                <div style="margin-left:50px;margin-right:50px;margin-bottom:72px;margin-bottom:30px"
                                                    class="lg_margin_left_right xl_margin_bottom">
                                                    {{ $header ?? '' }}
                                                </div>
                                                <div style="margin-bottom: 4px">
                                                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                                                
                                                    {{ $subcopy ?? '' }}
                                                </div>
                                                    
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </center>
                    </td>
                </tr>
                {{ $footer ?? '' }}
            </tbody>
        </table>
</body>

</html>
