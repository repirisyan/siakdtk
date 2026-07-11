@php($school = \App\Models\SchoolSetting::current())
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #dce7f2;">
    <tr>
        <td style="padding:24px 32px;color:#64748b;font-family:Arial,sans-serif;font-size:12px;line-height:19px;text-align:center;">
            <strong style="color:#334155;">{{ $school->nama_sekolah }}</strong><br>
            Email ini dikirim secara otomatis oleh Sistem Informasi Akademik Sekolah. Mohon tidak membalas email ini.<br>
            Jika membutuhkan bantuan, silakan hubungi pihak sekolah melalui {{ $school->email ?: config('mail.from.address') }}@if($school->nomor_telepon), {{ $school->nomor_telepon }}@endif.
        </td>
    </tr>
</table>
