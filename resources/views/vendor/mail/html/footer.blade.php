{{-- <tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr> --}}

@props(['app_name'])
<tr>
    <td class="email_footer" style="font-size:15px;color:#717274;text-align:center;width:100%">
        {{-- <center> --}}
            <table
                style="margin:0 auto 0;background-color:white;border:0;text-align:center;border-collapse:collapse">
                <tbody>
                    <tr>
                        <td style="width:546px;vertical-align:top;padding:0 45px">
                            <div style="max-width:600px;margin:0 auto">
                                <div class="lg_margin_left_right xl_margin_bottom">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td class="slack_logo_small_icon"
                                                    style="vertical-align:top;text-align:left"><img
                                                        width="100" height="auto"
                                                        style="margin: 0px 0px 32px; max-width: 100vw; max-height: 30vw;"
                                                        src="{{ asset('logos/gauge-primary.png') }}"
                                                        alt="{{ env('APP_NAME') }} logo"></td>
                                                <td style="vertical-align:top;text-align:right">
                                                    <a
                                                        href="#"
                                                        data-qa="twitter_link"
                                                        class="social_icon_margin"
                                                        style="margin-left:20px"
                                                        title="https://twitter.com/#">
                                                        <img
                                                            class="small_icon"
                                                            src="https://a.slack-edge.com/b8be608/marketing/img/icons/icon_colored_twitter.png"
                                                            width="32" height="32"
                                                            title="Twitter"
                                                            style="max-width: 100vw; max-height: 100vw;">
                                                        </a>
                                                        <a
                                                        href="#"
                                                        data-qa="fb_link" class="social_icon_margin"
                                                        style="margin-left:20px"
                                                        title="https://facebook.com/slackhq"><img
                                                            class="small_icon"
                                                            src="https://a.slack-edge.com/b8be608/marketing/img/icons/icon_colored_facebook.png"
                                                            width="32" height="32"
                                                            title="Facebook"
                                                            style="max-width: 100vw; max-height: 100vw;">
                                                        </a>
                                                        <a
                                                        href="#"
                                                        data-qa="linkedin_link"
                                                        class="social_icon_margin"
                                                        style="margin-left:20px"
                                                        title="https://www.linkedin.com/company/tiny-spec-inc/"><img
                                                            class="small_icon"
                                                            src="https://a.slack-edge.com/b8be608/marketing/img/icons/icon_colored_linkedin.png"
                                                            width="32" height="32"
                                                            title="LinkedIn"
                                                            style="max-width: 100vw; max-height: 100vw;"></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div
                                        style="font-size:14px;color:#888888;text-align:left;line-height:17px;margin-bottom:50px;text-align:left">
                                        {{-- <a class="footer_link" href="#"
                                            data-qa="gauge_blog" style="color:#696969 !important"
                                            title="Blog">Our
                                            Blog</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; --}}
                                            <a
                                            class="footer_link" href="#"
                                            data-qa="gauge_legal" style="color:#696969 !important"
                                            title="Legal">Policies</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                                            <a
                                            href="#" class="footer_link"
                                            data-qa="gauge_help" style="color:#696969 !important"
                                            title="Help">Help
                                            Center</a>
                                            {{-- &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; --}}
                                            {{-- <a
                                            href="#" class="footer_link"
                                            data-qa="gauge_commmunity" style="color:#696969 !important"
                                            title="Slack">Slack
                                            Community</a> --}}
                                            <br><br>
                                        <div>©2024 - {{ env('APP_NAME') }} by Resoura, a productivity tool for small manufacturing businesses.</div><br>All rights
                                        reserved.
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        {{-- </center> --}}
    </td>
</tr>