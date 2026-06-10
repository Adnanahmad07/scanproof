<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You've Been Invited to ScanProof</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .card { background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 32px; text-align: center; }
        .logo { width: 48px; height: 48px; background-color: rgba(255,255,255,0.2); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .logo svg { width: 28px; height: 28px; color: white; }
        .header h1 { color: white; margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 32px; }
        .greeting { font-size: 16px; color: #18181b; margin-bottom: 16px; }
        .message { font-size: 14px; color: #71717a; line-height: 1.6; margin-bottom: 24px; }
        .button-container { text-align: center; margin: 32px 0; }
        .button { display: inline-block; background-color: #3b82f6; color: white; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 600; font-size: 14px; transition: background-color 0.2s; }
        .button:hover { background-color: #2563eb; }
        .expiry { background-color: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px 16px; margin: 24px 0; }
        .expiry p { margin: 0; font-size: 13px; color: #92400e; }
        .divider { border-top: 1px solid #e4e4e7; margin: 24px 0; }
        .footer { padding: 24px 32px; background-color: #f9fafb; text-align: center; }
        .footer p { margin: 0; font-size: 12px; color: #a1a1aa; }
        .role-badge { display: inline-block; background-color: #e0e7ff; color: #3730a3; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="logo">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1>You're Invited!</h1>
            </div>

            <div class="content">
                <p class="greeting">Hello,</p>

                <p class="message">
                    <strong>{{ $invitedByName }}</strong> has invited you to join <strong>ScanProof</strong> as a
                </p>

                <span class="role-badge">Supervisor</span>

                <p class="message">
                    ScanProof is a Facility Operations Platform that helps you manage cleaning, maintenance, and facility tasks using QR-based scanning.
                </p>

                <p class="message">
                    Click the button below to set your password and access your supervisor dashboard:
                </p>

                <div class="button-container">
                    <a href="{{ $setPasswordUrl }}" class="button">Set Your Password</a>
                </div>

                <div class="expiry">
                    <p><strong>⏰ This link expires on:</strong> {{ $expiresAt }}</p>
                </div>

                <div class="divider"></div>

                <p class="message">
                    If you didn't expect this invitation, you can safely ignore this email. No account will be created without your action.
                </p>

                <p class="message">
                    If the button above doesn't work, copy and paste this link into your browser:
                </p>
                <p style="word-break: break-all; font-size: 12px; color: #3b82f6;">{{ $setPasswordUrl }}</p>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} ScanProof. All rights reserved.</p>
                <p style="margin-top: 8px;">Facility Operations Platform</p>
            </div>
        </div>
    </div>
</body>
</html>
