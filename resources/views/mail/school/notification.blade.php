<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#eef4f9;">
    <span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;">{{ $preheader }}</span>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef4f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr><td><x-email.header /></td></tr>
                    <tr>
                        <td style="padding:34px 32px;color:#1e293b;font-family:Arial,sans-serif;font-size:15px;line-height:24px;">
                            <h1 style="margin:0 0 18px;color:#0f172a;font-size:24px;line-height:32px;">{{ $heading }}</h1>
                            <p style="margin:0 0 16px;">Halo {{ $recipientName }},</p>
                            @foreach ($lines as $line)
                                <p style="margin:0 0 16px;">{{ $line }}</p>
                            @endforeach
                            @if (! empty($details))
                                <x-email.content-section>
                                    @foreach ($details as $label => $value)
                                        <div style="margin:0 0 6px;"><strong style="color:#334155;">{{ $label }}:</strong> {{ $value }}</div>
                                    @endforeach
                                </x-email.content-section>
                            @endif
                            @if ($actionUrl && $actionText)
                                <x-email.button :url="$actionUrl">{{ $actionText }}</x-email.button>
                            @endif
                            @if ($note)
                                <p style="margin:20px 0 0;color:#64748b;font-size:13px;line-height:20px;">{{ $note }}</p>
                            @endif
                        </td>
                    </tr>
                    <tr><td><x-email.footer /></td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
