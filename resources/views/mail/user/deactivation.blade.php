<x-mail::message>
    <div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:10px"
            class="lg_margin_left_right xl_margin_bottom">
            <h1>We're sad to see you leave us.</h1>
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph">
                This is to confirm that your account with {{ env('APP_NAME') }}
                has been successfully deactivated as per your request.
            </p>
        </div>
        <div style="padding:0 23px;margin-left:50px;margin-right:50px;margin-bottom:0"
            class="lg_margin_left_right xl_margin_bottom grey_box_container">
            <div style="text-align:center; vertical-align:middle;">
                <img src="{{ asset('images/Worried.svg') }}" style="width: 100%;height:100%" alt="happy person to join">
            </div>
        </div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:30px"
            class="lg_margin_left_right xl_margin_bottom">
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">
                We have completed the deactivation process, and your account is no longer active.
                You will no longer be seen in the {{ env('APP_NAME') }} environment. To reactivate your account,
                simply login with your credentials.
            </p>
            <p style="font-size:14px;line-height:22px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">If you have any questions or concerns regarding this
                deactivation or if you believe this deactivation was made in error,
                our support team is here to help. Feel free to reach out to us at
                <span style="font-weight:500">{{ env('APP_SUPPORT_EMAIL') }}</span>,
                and we'll be happy to assist you.</p>
        </div>
    </div>
</x-mail::message>
