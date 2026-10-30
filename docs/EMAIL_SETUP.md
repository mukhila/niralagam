# Email (SMTP) setup

Niralagam sends email for **brand invitations** and **OTP login codes**. Out of the box
`MAIL_MAILER=log` writes emails to `storage/logs/laravel.log` instead of sending them.

## 1. Pick a provider and put its settings in `.env`

Never commit real passwords; `.env` is git-ignored.

**Gmail / Google Workspace** (fine for testing and low volume, ~500 emails/day).
Turn on 2-Step Verification, then create an App Password at
https://myaccount.google.com/apppasswords and use it as `MAIL_PASSWORD`.

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD="your 16-char app password"
MAIL_FROM_ADDRESS="you@gmail.com"
MAIL_FROM_NAME="Niralagam"
```

**Brevo, Mailgun, SendGrid, Amazon SES, Zoho…** (recommended for production). Use
the SMTP host, port, username and key from the provider's dashboard. They have you
verify your sending domain (SPF/DKIM DNS records) so mail doesn't land in spam.

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com   # from your provider
MAIL_PORT=587
MAIL_USERNAME=your-smtp-login
MAIL_PASSWORD=your-smtp-key
MAIL_FROM_ADDRESS="no-reply@niralagam.com"   # an address on your verified domain
MAIL_FROM_NAME="Niralagam"
```

**Mailtrap / Mailpit** (development: catches every email in a fake inbox, nothing is
delivered). Use the host/port/credentials they show.

Port 587 uses STARTTLS (`MAIL_SCHEME=null`); port 465 needs `MAIL_SCHEME=smtps`.

## 2. Point links at the right site

```dotenv
FRONTEND_URL=https://niralagam.com   # where the React app is served; invite links use it
```

## 3. Apply and test

```bash
php artisan config:clear
php artisan mail:test you@example.com
```

`mail:test` prints which mailer and host it used and either confirms the send or
shows the SMTP error (wrong password, blocked port, unverified sender, …).

## Notes

- Requests wait at most `MAIL_TIMEOUT` seconds (default 10) for the SMTP server.
  If sending fails, the invite is still created and the admin gets a "Copy link"
  button to share it by hand.
- Emails are sent during the request. For high volume, switch to a queue
  (`QUEUE_CONNECTION=database`, run `php artisan queue:work`) and make the
  mailables implement `ShouldQueue`.
