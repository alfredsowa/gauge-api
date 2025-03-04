<x-mail::message>
    <div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:10px"
            class="lg_margin_left_right xl_margin_bottom">
            <h1>Successfully Email Address Update</h1>
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph">
                Your account information has been securely updated in our system,
                and you can now use your new email address to log in and access our services.
            </p>
        </div>
        <div style="padding:0 23px;margin-left:50px;margin-right:50px;margin-bottom:0"
            class="lg_margin_left_right xl_margin_bottom grey_box_container">
            <div style="text-align:center; vertical-align:middle;">
                <img src="{{ asset('images/security.svg') }}" style="width: 100%;height:100%" alt="happy person to join">
            </div>
        </div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:30px"
            class="lg_margin_left_right xl_margin_bottom">
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">
                If you did not make this change or believe your account security may have been compromised,
                please contact our support team immediately at <span style="color: #038c65">{{ env('APP_SUPPORT_EMAIL') }}</span>.
                We take the security of your account very seriously and will assist you promptly in
                resolving any concerns.
            </p>
            <p style="font-size:14px;line-height:22px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">If you have any questions or need assistance along the way, our support team is here to help. Feel free to reach out to us at
                <span style="font-weight: 500">{{ env('APP_SUPPORT_EMAIL') }}</span>, and we'll be happy to assist you.</p>
        </div>
    </div>
</x-mail::message>
