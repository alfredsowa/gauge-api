<x-mail::message>
    <div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:10px"
            class="lg_margin_left_right xl_margin_bottom">
            <h1>Great news – your password has been successfully reset!</h1>
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph"> You're now back in the driver's seat and ready to dive back into your account with full confidence.
            </p>
        </div>
        <div style="padding:0 23px;margin-left:50px;margin-right:50px;margin-bottom:0"
            class="lg_margin_left_right xl_margin_bottom grey_box_container">
            <div style="text-align:center; vertical-align:middle;">
                <img src="{{ asset('images/safe-data.svg') }}" style="width: 100%;height:100%" alt="sitting relaxed">
            </div>
        </div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:30px"
            class="lg_margin_left_right xl_margin_bottom">
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph"> We're thrilled to have helped you regain access to your account.
            </p>
            <p style="font-size:14px;line-height:22px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">If you have any questions or need assistance along the way, our support team is here to help. Feel free to reach out to us at
                <span style="font-weight:500">{{ env('APP_SUPPORT_EMAIL') }}</span>, and we'll be happy to assist you.</p>
        </div>
    </div>
</x-mail::message>
