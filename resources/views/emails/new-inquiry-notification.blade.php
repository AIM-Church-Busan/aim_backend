<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Inquiry Received</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden;">

          <!-- Logo -->
          <tr>
            <td style="padding:32px 40px 24px 40px;" align="left">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <img src="{{ $logoUrl ?? 'https://i.ibb.co/WWnPkKgX/AIM-Logo-Wide.png' }}" alt="AIM" width="130" height="36" style="display:block; border-radius:8px;">
                  </td>
                  <td style="padding-left:10px; vertical-align:middle;">
                    <span style="font-size:14px; color:#555555; margin-left:4px;">Antioch International Ministry</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding:0 40px;">
              <div style="border-top:1px solid #eeeeee;"></div>
            </td>
          </tr>

          <!-- Title -->
          <tr>
            <td style="padding:32px 40px 0 40px;">
              <p style="margin:0; font-size:16px; color:#111111; line-height:1.6; font-weight:700;">
                📩 New Inquiry Received
              </p>
              <p style="margin:6px 0 0 0; font-size:14px; color:#777777; line-height:1.6;">
                A new message was submitted through the website contact form.
              </p>
            </td>
          </tr>

          <!-- Inquiry details table -->
          <tr>
            <td style="padding:24px 40px 0 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee; border-radius:6px; overflow:hidden;">
                <tr>
                  <td style="padding:12px 16px; background-color:#fafafa; font-size:13px; color:#888888; width:110px; border-bottom:1px solid #eeeeee;">Name</td>
                  <td style="padding:12px 16px; font-size:14px; color:#111111; border-bottom:1px solid #eeeeee;">{{ $inquirerName }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; background-color:#fafafa; font-size:13px; color:#888888; border-bottom:1px solid #eeeeee;">Email</td>
                  <td style="padding:12px 16px; font-size:14px; color:#111111; border-bottom:1px solid #eeeeee;">
                    <a href="mailto:{{ $inquirerEmail }}" style="color:#8D85FF; text-decoration:none;">{{ $inquirerEmail }}</a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; background-color:#fafafa; font-size:13px; color:#888888; border-bottom:1px solid #eeeeee;">Phone</td>
                  <td style="padding:12px 16px; font-size:14px; color:#111111; border-bottom:1px solid #eeeeee;">{{ $inquirerPhone ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 16px; background-color:#fafafa; font-size:13px; color:#888888; vertical-align:top;">Submitted</td>
                  <td style="padding:12px 16px; font-size:14px; color:#111111;">{{ $submittedAt }}</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Message body -->
          <tr>
            <td style="padding:24px 40px 0 40px;">
              <p style="margin:0 0 8px 0; font-size:13px; color:#888888;">Message</p>
              <div style="font-size:15px; color:#444444; line-height:1.7; background-color:#fafafa; border-radius:6px; padding:16px;">
                {{ $inquiryMessage }}
              </div>
            </td>
          </tr>

          <!-- CTA Button -->
          <tr>
            <td style="padding:28px 40px 8px 40px;">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="border-radius:6px; background-color:#8D85FF;">
                    <a href="mailto:{{ $inquirerEmail }}"
                       style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:400; color:#ffffff; text-decoration:none; border-radius:6px;">
                      Reply to {{ $inquirerName }}
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Signature -->
          <tr>
            <td style="padding:32px 40px 32px 40px;">
              <p style="margin:0; font-size:14px; color:#555555;">
                — AIM Website System
              </p>
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding:0 40px;">
              <div style="border-top:1px solid #eeeeee;"></div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:20px 40px 32px 40px;">
              <p style="margin:0; font-size:12px; color:#999999; line-height:1.6;">
                © {{ date('Y') }} Antioch International Ministry. This is an automated notification, please do not reply directly to this email.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
