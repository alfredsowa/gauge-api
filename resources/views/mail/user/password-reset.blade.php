<x-mail::message>
    <div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:30px"
        class="lg_margin_left_right xl_margin_bottom">
        <h1>Secure Password.</h1>
        <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
            class="hero_paragraph"> To regain access to your account and continue enjoying our services, use the code below to help confirm reset your password.
        </p>
        </div>
        <div style="padding:10px 23px;margin-left:50px;margin-right:50px;margin-bottom:30px"
            class="lg_margin_left_right xl_margin_bottom grey_box_container">
            <div style="text-align:center; vertical-align:middle; font-size:40px; letter-spacing: 20px; color:#333">
                {{ $code }}
            </div>
        </div>
        <div style="margin-left:50px; margin-right:50px;  margin-bottom:30px"
            class="lg_margin_left_right xl_margin_bottom">
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">
                This code expires after {{ $time }}. Your account security is important to us. If you did not request this password reset, please ignore this email. </p>
            <p style="font-size:14px;line-height:22px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">If you did not request this email,
                there’s nothing to worry about — you can safely ignore it.</p>
        </div>
    </div>
</x-mail::message>
