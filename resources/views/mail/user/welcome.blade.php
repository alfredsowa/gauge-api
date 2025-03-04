<x-mail::message>
    <div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:10px"
            class="lg_margin_left_right xl_margin_bottom">
            <h1>Welcome To {{ env('APP_NAME') }}</h1>
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph">
                We are thrilled to have you on board as one of our valued early users.
                Your decision to join us means the world, and we are committed to making this journey as
                impactful and rewarding as possible for your business.
            </p>
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph">
                As you explore our MVP (Minimum Viable Product), you’ll experience the core features designed to
                streamline your operations and enhance productivity. But this is just the beginning.
                Your feedback and ideas are crucial in helping us shape the future of  {{ env('APP_NAME') }} to
                better serve you and your business needs.
            </p>
        </div>
        <div style="padding:0 23px;margin-left:50px;margin-right:50px;margin-bottom:0"
            class="lg_margin_left_right xl_margin_bottom grey_box_container">
            <div style="text-align:center; vertical-align:middle;">
                <img src="{{ asset('images/happy-me.svg') }}" style="max-width: 250px;height:auto" alt="happy person to join">
            </div>
        </div>
        <div style="margin-left:50px;margin-right:50px;margin-bottom:30px"
            class="lg_margin_left_right xl_margin_bottom">
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph"> To get started, simply log in to your account to set up your business profile and start exploring everything we have to offer.
            </p>
            <p style="font-size:16px;line-height:24px;letter-spacing:-0.2px;margin-bottom:28px;word-break:break-word"
                class="hero_paragraph"> Thank you for joining us on this exciting journey.
                Together, we can build something truly special that empowers your business to thrive.
            </p>
            <p style="font-size:14px;line-height:22px;letter-spacing:-0.2px;margin-bottom:28px"
                class="content_paragraph">If you have any questions or need assistance along the way, our support team is here to help. Feel free to reach out to us at
                <span style="font-weight:500">{{ env('APP_SUPPORT_EMAIL') }}</span>, and we'll be happy to assist you.</p>
        </div>
    </div>
</x-mail::message>
