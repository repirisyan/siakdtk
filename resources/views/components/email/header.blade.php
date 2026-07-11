@php($school = \App\Models\SchoolSetting::current())
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#1479d1;">
    <tr>
        <td style="padding:28px 32px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="width:42px;height:42px;border-radius:12px;background:#ffffff;color:#1479d1;text-align:center;font-family:Arial,sans-serif;font-size:22px;font-weight:700;overflow:hidden;">@if($school->logo_url)<img src="{{ $school->logo_url }}" alt="Logo {{ $school->nama_sekolah }}" width="42" height="42" style="display:block;object-fit:contain;">@else S @endif</td>
                    <td style="padding-left:12px;color:#ffffff;font-family:Arial,sans-serif;">
                        <div style="font-size:18px;font-weight:700;line-height:22px;">{{ $school->nama_sekolah }}</div>
                        <div style="font-size:12px;line-height:18px;color:#dbeeff;">{{ $school->tagline ?? 'Sistem Informasi Akademik Sekolah' }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
