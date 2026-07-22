<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapor Akhir</title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; color: #141414; font-family: Arial, Helvetica, sans-serif; font-size: 11pt; line-height: 1.5; }
        .print-actions { margin: 16px; }
        .report-set { position: relative; }
        .print-page { height: 297mm; overflow: hidden; padding: 12mm; position: relative; }
        /* .print-page::before { color: rgba(87, 177, 210, .12); content: 'SIAKDTK'; font-size: 108pt; font-weight: 700; left: 50%; letter-spacing: 8px; position: absolute; top: 50%; transform: translate(-50%, -50%) rotate(-28deg); white-space: nowrap; z-index: 0; } */
        .print-page > * { position: relative; z-index: 1; }
        .cover { align-items: center; display: flex; flex-direction: column; justify-content: center; text-align: center; }
        .school-logo, .school-mark { align-items: center; display: flex; height: 165px; justify-content: center; margin-bottom: 36px; width: 165px; }
        .school-logo { object-fit: contain; }
        .school-mark { border: 2px solid #141414; border-radius: 50%; font-size: 24px; font-weight: 700; }
        .cover h1 { font-size: 27pt; line-height: 1.15; margin: 0; }
        .cover h2 { font-size: 17pt; line-height: 1.3; margin: 12px 0 0; text-transform: uppercase; }
        .cover .school-name { font-size: 18pt; font-weight: 700; margin: 14px 0 42px; text-transform: uppercase; }
        .school-profile { margin-top: 10px; text-align: left; width: min(100%, 135mm); }
        .school-profile p { border-bottom: 1px dotted #141414; margin: 8px 0; padding-bottom: 3px; }
        .school-profile span { display: inline-block; font-weight: 700; width: 43mm; }
        .page-title { font-size: 17pt; margin: 0 0 18px; text-align: center; text-transform: uppercase; }
        .identity-grid { display: grid; gap: 6px 18px; grid-template-columns: 1fr 1.1fr; }
        .identity-grid dt { font-weight: 700; }
        .identity-grid dd { border-bottom: 1px dotted #141414; line-height: 1.25; margin: 0; min-height: 18px; }
        .identity-footer { align-items: start; break-inside: avoid; display: grid; grid-template-columns: 35mm 1fr; margin-top: 18px; page-break-inside: avoid; }
        .photo-placeholder { align-items: center; border: 1px solid #141414; display: flex; font-size: 10pt; height: 45mm; justify-content: center; margin: 0; text-align: center; width: 35mm; }
        .principal-signature { justify-self: end; text-align: center; width: 80mm; }
        .principal-signature p { margin: 0; }
        .principal-signature .signature-name { margin-top: 42px !important; }
        .report-heading { border-bottom: 2px solid #141414; margin-bottom: 16px; padding-bottom: 10px; text-align: center; }
        .report-heading h1 { font-size: 16pt; margin: 0; text-transform: uppercase; }
        .student-summary { display: grid; gap: 8px 24px; grid-template-columns: 1fr 1fr; margin: 18px 0; }
        .student-summary p { border-bottom: 1px dotted #141414; margin: 0; padding-bottom: 2px; }
        .student-summary span { display: inline-block; font-weight: 700; min-width: 31mm; }
        .achievement { border: 1px solid #141414; break-inside: avoid; margin-top: 14px; page-break-inside: avoid; }
        .achievement h2 { background: #edf7fb; border-bottom: 1px solid #141414; font-size: 12pt; margin: 0; padding: 8px 10px; text-transform: uppercase; }
        .achievement p { margin: 0; min-height: 54mm; padding: 12px 14px; white-space: pre-line; }
        .empty-note { color: #444; font-style: italic; }
        .section-title { border: 1px solid #141414; border-bottom: 0; font-size: 12pt; font-weight: 700; margin: 20px 0 0; padding: 8px 10px; text-transform: uppercase; }
        .summary-table { border-collapse: collapse; width: 100%; }
        .summary-table th, .summary-table td { border: 1px solid #141414; padding: 7px 9px; text-align: left; vertical-align: top; }
        .summary-table th { background: #edf7fb; font-weight: 700; }
        .signature-grid { display: grid; gap: 55mm; grid-template-columns: 1fr 1fr; margin-top: 32px; text-align: center; }
        .signature-grid p { margin: 0; }
        .signature-name { border-bottom: 1px dotted #141414; display: inline-block; margin-top: 40px !important; min-width: 58mm; }
        .signature-role { font-size: 10pt; }
        .page-number { bottom: 8mm; font-size: 9pt; left: 0; position: absolute; text-align: center; width: 100%; }

        @media print {
            html, body { height: auto; width: 100%; }
            .print-actions { display: none !important; }
            .achievement, tr { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    @forelse ($rapors as $rapor)
        @php
            $siswa = $rapor->siswa;
            $guru = $rapor->details->first()?->guru;
            $attendanceSummary = $attendance->get($siswa->id);
        @endphp

        <article class="report-set">
            <section class="print-page cover">
                <img class="school-logo" src="/assets/img/garuda.png" alt="Logo Garuda Pancasila">
                <header class="report-heading">
                    <h1>LAPORAN</h1>
                    <h2>CAPAIAN PERKEMBANGAN PEMBELAJARAN ANAK</h2>
                    <h2>TAMAN KANAK-KANAK</h2>
                    <h2>KABUPATEN {{ $school->kabupaten ?: 'Cianjur' }}</h2>
                </header>

                <div class="school-profile">
                    <p><span>Nama Sekolah</span>: {{ $school->nama_sekolah }}</p>
                    <p><span>Alamat</span>: {{ $school->alamat ?: '-' }}</p>
                    <p><span>Desa / Kelurahan</span>: {{ $school->desa ?: '-' }}</p>
                    <p><span>Kecamatan</span>: {{ $school->kecamatan ?: '-' }}</p>
                    <p><span>Kabupaten / Kota</span>: {{ $school->kabupaten ?: '-' }}</p>
                    <p><span>Provinsi</span>: {{ $school->provinsi ?: '-' }}</p>
                    <p><span>Nomor Telepon</span>: {{ $school->nomor_telepon ?: '-' }}</p>
                    <p><span>Email</span>: {{ $school->email ?: '-' }}</p>
                    <p><span>Tahun Ajaran</span>: {{ $rapor->thn_ajaran }}</p>
                </div>
                <span class="page-number">1</span>
            </section>

            <section class="print-page">
                <h1 class="page-title">Identitas Peserta Didik</h1>
                <dl class="identity-grid">
                    <dt>Nama Peserta Didik</dt><dd>{{ $siswa->nama }}</dd>
                    <dt>NISN / NIS</dt><dd>{{ $siswa->nisn ?: '-' }} / {{ $siswa->nis ?: '-' }}</dd>
                    <dt>Jenis Kelamin</dt><dd>{{ $siswa->jk === 'L' ? 'Laki-laki' : ($siswa->jk === 'P' ? 'Perempuan' : '-') }}</dd>
                    <dt>Tempat, Tanggal Lahir</dt><dd>{{ $siswa->tmp_lahir }}, {{ \Illuminate\Support\Carbon::parse($siswa->tgl_lahir)->translatedFormat('d F Y') }}</dd>
                    <dt>Agama</dt><dd>{{ $siswa->agama ?: '-' }}</dd>
                    <dt>Anak Ke</dt><dd>{{ $siswa->anak_ke ?: '-' }}</dd>
                    <dt>Nama Ayah</dt><dd>{{ $siswa->nama_ayah ?: '-' }}</dd>
                    <dt>Nama Ibu</dt><dd>{{ $siswa->nama_ibu ?: '-' }}</dd>
                    <dt>Nomor Telepon / HP</dt><dd>{{ $siswa->nohp_ayah ?: $siswa->nohp_ibu ?: '-' }}</dd>
                    <dt>Pekerjaan Orang Tua</dt><dd>{{ $siswa->pekerjaan ?: '-' }} / {{ $siswa->pekerjaan_ibu ?: '-' }}</dd>
                    <dt>Alamat Orang Tua / Wali</dt><dd>{{ $siswa->alamat ?: '-' }}</dd>
                    <dt>Desa / Kelurahan Orang Tua / Wali</dt><dd>{{ $siswa->desa ?: '-' }}</dd>
                    <dt>Kecamatan Orang Tua / Wali</dt><dd>{{ $siswa->kecamatan ?: '-' }}</dd>
                    <dt>Kabupaten / Kota Orang Tua / Wali</dt><dd>{{ $siswa->kabupaten ?: '-' }}</dd>
                    <dt>Provinsi Orang Tua / Wali</dt><dd>{{ $siswa->provinsi ?: '-' }}</dd>
                </dl>
                <div class="identity-footer">
                    <div class="photo-placeholder">Pas Foto<br>3 x 4</div>
                    <div class="principal-signature">
                        <p>{{ $school->kabupaten ?: 'Cianjur' }}, {{ now()->translatedFormat('d F Y') }}</p>
                        <p>Kepala Taman Kanak-Kanak</p>
                        <p class="signature-name">{{ $rapor->approver?->name ?? '-' }}</p>
                        <p>NIP {{ $rapor->approver?->guru?->nip ?: '-' }}</p>
                    </div>
                </div>
                <span class="page-number">2</span>
            </section>

            <section class="print-page final-page">
                <header class="report-heading">
                    <h1>Laporan Capaian Pembelajaran Anak</h1>
                    <p>Tahun Ajaran {{ $rapor->thn_ajaran }}</p>
                </header>
                <div class="student-summary">
                    <p><span>Nama</span>: {{ $siswa->nama }}</p>
                    <p><span>Kelompok</span>: {{ $kelas->nama_kelas }}</p>
                    <p><span>NISN / NIS</span>: {{ $siswa->nisn ?: '-' }} / {{ $siswa->nis ?: '-' }}</p>
                    <p><span>Semester</span>: {{ $kelas->semester === 2 ? 'Dua' : 'Satu' }}</p>
                </div>


                <h2 class="section-title">Pertumbuhan, Kesehatan, dan Kehadiran</h2>
                <table class="summary-table">
                    <tbody>
                        <tr><th>Berat Badan</th><td>{{ $siswa->berat_bdn ? $siswa->berat_bdn . ' kg' : '-' }}</td><th>Tinggi Badan</th><td>{{ $siswa->tinggi_bdn ? $siswa->tinggi_bdn . ' cm' : '-' }}</td></tr>
                        <tr><th>Hadir</th><td>{{ $attendanceSummary?->hadir ?? 0 }} hari</td><th>Izin</th><td>{{ $attendanceSummary?->izin ?? 0 }} hari</td></tr>
                        <tr><th>Sakit</th><td>{{ $attendanceSummary?->sakit ?? 0 }} hari</td><th>Tanpa Keterangan</th><td>{{ $attendanceSummary?->alfa ?? 0 }} hari</td></tr>
                    </tbody>
                </table>

                <div class="signature-grid">
                    <div>
                        <p>Mengetahui,</p><p>Kepala Sekolah</p>
                        <p class="signature-name">{{ $rapor->approver?->name ?? '-' }}</p>
                        <p class="signature-role">NIP / NUPTK: -</p>
                    </div>
                    <div>
                        <p>{{ now()->translatedFormat('d F Y') }}</p><p>Guru Kelompok {{ $kelas->nama_kelas }}</p>
                        <p class="signature-name">{{ $guru?->nama ?? '-' }}</p>
                        <p class="signature-role">NIP / NUPTK: {{ $guru?->nip ?? '-' }}</p>
                    </div>
                </div>
                <span class="page-number">3</span>
            </section>
        </article>
    @empty
        <p>Belum ada Rapor Akhir yang dapat dicetak.</p>
    @endforelse
</body>
</html>
