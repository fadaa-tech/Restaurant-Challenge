{{-- resources/views/emails/invoice.blade.php --}}
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Invoice {{ $invoice_id }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
      <td align="center" style="padding:24px 12px;">
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
          <!-- Header -->
          <tr>
            <td style="padding:20px 24px;border-bottom:1px solid #e9eef3;">
              <table width="100%" role="presentation">
                <tr>
                  <td style="vertical-align:middle;">
                    <!-- Logo placeholder -->
                    <img src="{{ $company_logo ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRsxmC8onh626Xg1fuH4jzj7FnYIdVyPNaF-g&s' }}" alt="{{ $company_name ?? 'Company' }}" style="height:100px;display:block;">
                  </td>
                  <td align="right" style="color:#6b7280;font-size:13px;">
                    Invoice: <strong style="color:#0b63d6;">{{ $invoice_id }}</strong><br>
                    Due: <strong>{{ $due_date ?? now()->toDateString() }}</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Hero / Title -->
          <tr>
            <td style="padding:28px 24px 12px;">
              <h1 style="margin:0;font-size:22px;color:#0b63d6;">Here's your invoice</h1>
              <p style="margin:8px 0 0;color:#475569;font-size:14px;">
                Hi {{ $payer_name ?? 'Customer' }},<br>
                Thanks for your order — attached is your invoice.
              </p>
            </td>
          </tr>

          <!-- Invoice details -->
          <tr>
            <td style="padding:12px 24px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;">
                <tr>
                  <td style="vertical-align:top;padding-top:8px;">
                    <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%;border:1px solid #eef2f7;border-radius:6px;">
                      <tr>
                        <td style="padding:14px 16px;">
                          <p style="margin:0;color:#6b7280;font-size:13px;">Amount requested</p>
                          <p style="margin:6px 0 0;font-size:18px;color:#111827;"><strong>{{ $amount ?? '0.00' }} {{ $currency ?? 'USD' }}</strong></p>
                        </td>
                      </tr>

                      <tr>
                        <td style="padding:14px 16px;background:#fafbfc;">
                          <p style="margin:0;color:#6b7280;font-size:13px;">Invoice number</p>
                          <p style="margin:6px 0 0;font-size:14px;color:#111827;"><strong>{{ $invoice_id }}</strong></p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- CTA button -->
                <tr>
                  <td align="left" style="padding-top:18px;">
                    <a href="{{ $view_url ?? '#' }}" target="_blank" style="display:inline-block;padding:12px 20px;background:#0b63d6;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;">
                      View and Pay Invoice
                    </a>
                  </td>
                </tr>

                <!-- Small note / report link -->
                <tr>
                  <td style="padding-top:14px;color:#6b7280;font-size:12px;">
                    <p style="margin:0;">
                      Don't recognize this invoice? <a href="{{ $report_url ?? '#' }}" style="color:#0b63d6;text-decoration:none;">Report this invoice</a>
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:18px 24px;border-top:1px solid #eef2f7;background:#fbfdff;">
              <table width="100%" role="presentation">
                <tr>
                  <td style="font-size:13px;color:#6b7280;vertical-align:top;">
                    <strong style="color:#111827;">{{ $company_name ?? 'Your Company' }}</strong><br>
                    <span style="color:#6b7280;">{{ $company_address ?? '123 Business St, City' }}</span>
                  </td>
                  <td align="right" style="font-size:12px;color:#94a3b8;vertical-align:top;">
                    <p style="margin:0;">&copy; {{ date('Y') }} {{ $company_name ?? 'Your Company' }}</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>

        <!-- Optional tiny footer under card -->
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:12px;">
          <tr>
            <td align="center" style="font-size:12px;color:#9aa4b2;">
              <p style="margin:0;">Please make sure you recognize this invoice. If you don't, report it to us.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
