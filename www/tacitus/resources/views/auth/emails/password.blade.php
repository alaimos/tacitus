<p>Dear User,</p>

<blockquote>
    <p>Your request for password reset has been processed.</p>

    <p>Please, click the following link to set a new password for your account.</p>

    <p>
        <a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}">
            {{ $link }}
        </a>
    </p>
</blockquote>

<p>Thank you,<br>&nbsp;&nbsp;&nbsp;The TACITuS team.</p>
