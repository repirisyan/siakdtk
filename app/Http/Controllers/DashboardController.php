<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\Spp;
use App\Models\SppPembayaran;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing(['role', 'guru', 'siswas.kelas']);

        return Inertia::render('Dashboard', [
            'dashboard' => match (true) {
                $user->hasRole('Guru') && $user->guru => $this->guruDashboard($user),
                $user->hasRole('Orangtua Siswa') && $user->siswas->isNotEmpty() => $this->orangtuaDashboard($user),
                default => $this->schoolDashboard($user),
            },
        ]);
    }

    private function schoolDashboard(User $user): array
    {
        abort_unless(in_array($user->role?->role_name, ['Admin', 'Staff Akademik', 'Staff Administrasi', 'Kepsek'], true), 403);

        $data = [
            'type' => 'school',
            'stats' => [
                'total_siswa_aktif' => Siswa::where('status', 'aktif')->count(),
                'total_kelas_aktif' => Kelas::active()->count(),
                'total_guru_aktif' => Guru::whereHas('user', fn ($query) => $query->where('status', true))->count(),
                'total_jadwal_hari_ini' => Jadwal::whereDate('tanggal', today())->count(),
                'total_tema_tahun_ajaran_berjalan' => Tema::active()->whereHas('jadwal.kelas', fn ($query) => $query->where('thn_ajaran', now()->year))->count(),
            ],
            'studentChart' => Siswa::query()
                ->join('kelas', 'kelas.id', '=', 'siswas.kelas_id')
                ->where('siswas.status', 'aktif')
                ->selectRaw('kelas.thn_ajaran as tahun_ajaran, COUNT(siswas.id) as total')
                ->groupBy('kelas.thn_ajaran')
                ->orderBy('kelas.thn_ajaran')
                ->get(),
        ];

        if ($user->hasRole('Admin') || $user->hasRole('Staff Administrasi') || $user->hasRole('Kepsek')) {
            $data['financeStats'] = [
                'total_tagihan_periode' => (float) Spp::whereYear('tanggal_tagihan', now()->year)
                    ->whereMonth('tanggal_tagihan', now()->month)
                    ->sum('nominal'),
                'total_pembayaran_periode' => (float) SppPembayaran::where('status_verifikasi', 'approved')
                    ->whereYear('tanggal_bayar', now()->year)
                    ->whereMonth('tanggal_bayar', now()->month)
                    ->sum('jumlah_bayar'),
            ];
        }

        return $data;
    }

    private function guruDashboard(User $user): array
    {
        $guruId = $user->guru->id;
        $today = today();
        $weekStart = Carbon::today()->startOfWeek();
        $weekEnd = Carbon::today()->endOfWeek();
        $jadwalHariIni = Jadwal::where('guru_id', $guruId)->whereDate('tanggal', $today);
        $jadwalTerisi = Absen::whereHas('jadwal', fn ($query) => $query->where('guru_id', $guruId)->whereDate('tanggal', $today))
            ->distinct('jadwal_id')
            ->count('jadwal_id');
        $totalJadwalHariIni = (clone $jadwalHariIni)->count();

        return [
            'type' => 'guru',
            'stats' => [
                'total_jadwal_hari_ini' => $totalJadwalHariIni,
                'total_jadwal_minggu_ini' => Jadwal::where('guru_id', $guruId)->whereBetween('tanggal', [$weekStart, $weekEnd])->count(),
                'total_absensi_sudah_diisi' => $jadwalTerisi,
                'total_absensi_belum_diisi' => max($totalJadwalHariIni - $jadwalTerisi, 0),
                'total_penilaian_sudah_diisi' => Nilai::whereHas('absen.jadwal', fn ($query) => $query->where('guru_id', $guruId))->count(),
            ],
        ];
    }

    private function orangtuaDashboard(User $user): array
    {
        $siswas = $user->siswas;
        $siswaIds = $siswas->pluck('id');
        $sppQuery = Spp::whereIn('siswa_id', $siswaIds);
        $totalTagihan = (float) (clone $sppQuery)->sum('nominal');
        $totalDibayar = (float) SppPembayaran::whereHas('spp', fn ($query) => $query->whereIn('siswa_id', $siswaIds))
            ->where('status_verifikasi', 'approved')
            ->sum('jumlah_bayar');

        return [
            'type' => 'orangtua',
            'stats' => [
                'total_anak' => $siswas->count(),
                'total_tagihan_aktif' => (clone $sppQuery)
                    ->whereRaw("(SELECT COALESCE(SUM(jumlah_bayar), 0) FROM spp_pembayarans WHERE spp_pembayarans.spp_id = spps.id AND status_verifikasi = 'approved') < spps.nominal")
                    ->count(),
                'total_tunggakan' => max($totalTagihan - $totalDibayar, 0),
                'total_pembayaran_tahun_ini' => (float) SppPembayaran::whereHas('spp', fn ($query) => $query->whereIn('siswa_id', $siswaIds))
                    ->where('status_verifikasi', 'approved')
                    ->whereYear('tanggal_bayar', now()->year)
                    ->sum('jumlah_bayar'),
                'total_rapor_tersedia' => Rapor::whereIn('siswa_id', $siswaIds)->where('status', 'disetujui')->count(),
            ],
        ];
    }
}
