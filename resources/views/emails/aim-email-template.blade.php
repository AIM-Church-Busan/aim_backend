<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'AIM Church' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
  <!-- Outer wrapper -->
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding:32px 16px;">
    <tr>
      <td align="center">
        <!-- Email container -->
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden;">

          <!-- Logo -->
          <tr>
            <td style="padding:32px 40px 24px 40px;" align="left">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <!-- 로고 이미지 -->
                    <img src="{{ $logoUrl ?? 'https://i.ibb.co/WWnPkKgX/AIM-Logo-Wide.png' }}" alt="AIM" width="130" height="36" style="display:block;">
                  </td>
                  <td style="padding-left:10px; vertical-align:middle;">
                    <span style="font-size:14px; color:#555555; margin-left:4px;">Antioch International Ministry</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Divider under header -->
          <tr>
            <td style="padding:0 40px;">
              <div style="border-top:1px solid #eeeeee;"></div>
            </td>
          </tr>

          <!-- Greeting -->
          <tr>
            <td style="padding:32px 40px 0 40px;">
              <p style="margin:0; font-size:16px; color:#111111; line-height:1.6;">
                Hi, {{ $name ?? 'there' }}
              </p>
            </td>
          </tr>

          <!-- Body content (replace per use case) -->
          <tr>
            <td style="padding:16px 40px 0 40px;">
              <div style="font-size:15px; color:#444444; line-height:1.7;">
                {!! $content ?? 'Your content goes here.' !!}
              </div>
            </td>
          </tr>

          <!-- CTA Button -->
          <tr>
            <td style="padding:28px 40px 8px 40px;">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="border-radius:6px; background-color:#8D85FF;">
                    <a href="{{ $buttonUrl ?? '#' }}"
                       style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:400; color:#ffffff; text-decoration:none; border-radius:6px;">
                      {{ $buttonText ?? 'Learn More' }}
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
                — The AIM Team
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
                © {{ date('Y') }} Antioch International Ministry. All rights reserved.<br>
                <a href="{{ $unsubscribeUrl ?? '#' }}" style="color:#999999; text-decoration:underline;">Unsubscribe</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
