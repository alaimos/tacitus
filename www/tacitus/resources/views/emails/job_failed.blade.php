<p>Dear {{ $user->name }},</p>

<blockquote>
    <p>Your job request failed.</p>

    <p>Please, click <a href="{{ url('/') }}">here</a> to access the application and review the results.</p>

    @if ($retry)
        <p>Your request will be automatically re-submitted. If the problem persists, check the job logs, and contact the
            administration if you believe there is a bug.</p>
    @endif

</blockquote>

<p>Thank you,<br>&nbsp;&nbsp;&nbsp;The TACITuS team.</p>
