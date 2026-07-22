<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveSiswaRequest;
use App\Http\Requests\ImportSiswaRequest;
use App\Http\Requests\MoveSiswaClassRequest;
use App\Http\Requests\SiswaRequest;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\AccountCreatedNotification;
use App\Notifications\StudentRegistrationNotification;
use App\Services\ProfilePhotoService;
use App\Services\StudentDocumentService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SiswaController extends Controller
{
    private const IMPORT_COLUMNS = [
        'name', 'email', 'password', 'password_confirmation', 'kelas_id',
        'nis', 'nisn', 'nik', 'nomor_kk', 'nama', 'nama_pgl', 'tmp_lahir',
        'tgl_lahir', 'jk', 'agama', 'tinggi_bdn', 'berat_bdn', 'anak_ke',
        'jml_sdr', 'alamat', 'desa', 'kecamatan', 'kabupaten', 'provinsi',
        'nama_ayah', 'nohp_ayah', 'ttl_ayah', 'agama_ayah', 'pekerjaan',
        'penghasilan', 'nama_ibu', 'nohp_ibu', 'ttl_ibu', 'agama_ibu',
        'pekerjaan_ibu', 'penghasilan_ibu', 'nama_wali', 'nohp_wali',
        'ttl_wali', 'agama_wali', 'pekerjaan_wali', 'penghasilan_wali',
        'alamat_wali',
    ];

    private const REQUIRED_IMPORT_COLUMNS = [
        'name', 'email', 'password', 'password_confirmation', 'kelas_id',
        'nik', 'nomor_kk', 'nama', 'tmp_lahir', 'tgl_lahir', 'jk', 'agama',
        'alamat',
    ];

    private const AGAMA_OPTIONS = [
        'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->currentSiswaManager();

        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $status = request()->query('status');

        $relationSorts = ['user_name', 'email', 'nama_kelas', 'thn_ajaran'];

        $siswas = Siswa::with([
            'user:id,name,email,status,foto_profil',
            'kelas:id,nama_kelas,thn_ajaran',
            'approver:id,name,email',
        ])->withCount(['absens', 'spps', 'rapor', 'raporAkhirs'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('kelas', function ($query) use ($search) {
                            $query
                                ->where('nama_kelas', 'like', "%{$search}%")
                                ->orWhere('thn_ajaran', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, ['pending', 'aktif', 'ditolak'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($sort === 'user_name', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('name')->whereColumn('users.id', 'siswas.user_id'),
                    $direction,
                );
            })
            ->when($sort === 'email', function ($query) use ($direction) {
                $query->orderBy(
                    User::select('email')->whereColumn('users.id', 'siswas.user_id'),
                    $direction,
                );
            })
            ->when($sort === 'nama_kelas', function ($query) use ($direction) {
                $query->orderBy(
                    Kelas::select('nama_kelas')->whereColumn('kelas.id', 'siswas.kelas_id'),
                    $direction,
                );
            })
            ->when($sort === 'thn_ajaran', function ($query) use ($direction) {
                $query->orderBy(
                    Kelas::select('thn_ajaran')->whereColumn('kelas.id', 'siswas.kelas_id'),
                    $direction,
                );
            })
            ->when(! in_array($sort, $relationSorts), function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Siswa/Index', [
            'siswas' => $siswas,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'status' => $status,
            ],
            'kelasOptions' => Kelas::active()->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran']),
            'canMoveClass' => auth()->user()?->loadMissing('role')->hasRole('Admin') ?? false,
            'canApproveSiswa' => true,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->currentSiswaManager();

        $tahun = request()->query('thn_ajaran');
        $semester = request()->integer('semester') ?: null;

        return Inertia::render('Siswa/Create', [
            'tahunAjarans' => Kelas::active()->select('thn_ajaran')
                ->distinct()
                ->orderByDesc('thn_ajaran')
                ->get(),
            'kelas' => $tahun && $semester
                ? Kelas::active()->where('thn_ajaran', $tahun)->where('semester', $semester)
                    ->orderBy('nama_kelas')
                    ->get(['id', 'nama_kelas', 'thn_ajaran', 'semester'])
                : [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SiswaRequest $request, ProfilePhotoService $profilePhotoService, StudentDocumentService $studentDocumentService)
    {
        $data = $request->validated();
        $manager = $this->currentSiswaManager();

        $user = DB::transaction(function () use ($data, $manager, $request, $profilePhotoService, $studentDocumentService) {
            $roleOrangtua = Role::where('role_name', 'Orangtua Siswa')->firstOrFail();

            $user = User::create([
                'role_id' => $roleOrangtua->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => true,
                'email_verified_at' => now(),
                'foto_profil' => $request->hasFile('foto_profil')
                    ? $profilePhotoService->replace($request->file('foto_profil'))
                    : null,
            ]);

            Siswa::create([
                ...Arr::except($data, [
                    'name',
                    'email',
                    'password',
                    'password_confirmation',
                    'thn_ajaran',
                    'semester',
                    'foto_profil',
                    'akta_kelahiran_file',
                    'kartu_keluarga_file',
                ]),
                'akta_kelahiran_file' => $request->hasFile('akta_kelahiran_file')
                    ? $studentDocumentService->store($request->file('akta_kelahiran_file'))
                    : null,
                'kartu_keluarga_file' => $request->hasFile('kartu_keluarga_file')
                    ? $studentDocumentService->store($request->file('kartu_keluarga_file'))
                    : null,
                'user_id' => $user->id,
                'tanggal_registrasi' => today(),
                'status' => 'aktif',
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);

            return $user;
        });

        $user->notify(new AccountCreatedNotification);

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        $this->currentSiswaManager();

        return Inertia::render('Siswa/Show', [
            'siswa' => $siswa->load([
                'user:id,name,email,status,foto_profil',
                'kelas:id,nama_kelas,thn_ajaran',
                'approver:id,name,email',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        $this->currentSiswaManager();

        $siswa->load(['user:id,name,email,status,foto_profil', 'kelas:id,nama_kelas,thn_ajaran,semester']);
        $tahun = request()->query('thn_ajaran', $siswa->kelas->thn_ajaran);
        $semester = request()->integer('semester') ?: $siswa->kelas->semester;

        return Inertia::render('Siswa/Edit', [
            'siswa' => $siswa,
            'tahunAjarans' => Kelas::active()->select('thn_ajaran')
                ->distinct()
                ->orderByDesc('thn_ajaran')
                ->get(),
            'kelas' => Kelas::active()->where('thn_ajaran', $tahun)->where('semester', $semester)
                ->orderBy('nama_kelas')
                ->get(['id', 'nama_kelas', 'thn_ajaran', 'semester']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SiswaRequest $request, Siswa $siswa, ProfilePhotoService $profilePhotoService, StudentDocumentService $studentDocumentService)
    {
        $data = $request->validated();
        $this->currentSiswaManager();
        $siswa->load('user');

        DB::transaction(function () use ($data, $siswa, $request, $profilePhotoService, $studentDocumentService) {
            $userData = Arr::only($data, ['name', 'email']);

            if ($data['password']) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($request->hasFile('foto_profil')) {
                $userData['foto_profil'] = $profilePhotoService->replace(
                    $request->file('foto_profil'),
                    $siswa->user->foto_profil,
                );
            }

            $siswa->user->update($userData);
            $siswaData = Arr::except($data, [
                'name',
                'email',
                'password',
                'password_confirmation',
                'thn_ajaran',
                'semester',
                'foto_profil',
                'akta_kelahiran_file',
                'kartu_keluarga_file',
            ]);

            if ($request->hasFile('akta_kelahiran_file')) {
                $siswaData['akta_kelahiran_file'] = $studentDocumentService->store($request->file('akta_kelahiran_file'));
            }

            if ($request->hasFile('kartu_keluarga_file')) {
                $siswaData['kartu_keluarga_file'] = $studentDocumentService->store($request->file('kartu_keluarga_file'));
            }

            $siswa->update($siswaData);
        });

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $this->currentSiswaManager();

        if ($siswa->absens()->exists() || $siswa->spps()->exists() || $siswa->rapor()->exists() || $siswa->raporAkhirs()->exists()) {
            return redirect()->route('siswa.index')->with('error', 'Siswa sudah memiliki data akademik atau pembayaran dan tidak dapat dihapus.');
        }

        $siswa->delete();

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    public function exportData()
    {
        $this->currentSiswaManager();

        $search = request()->query('search');
        $status = request()->query('status');

        return response()->streamDownload(function () use ($search, $status) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_values(array_filter(self::IMPORT_COLUMNS, fn ($column) => ! in_array($column, ['password', 'password_confirmation'], true))));

            Siswa::with(['user:id,name,email', 'kelas:id,nama_kelas,thn_ajaran,semester'])
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('nama', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                    });
                })
                ->when(in_array($status, ['pending', 'aktif', 'ditolak'], true), fn ($query) => $query->where('status', $status))
                ->orderBy('id')
                ->chunkById(200, function ($siswas) use ($handle) {
                    foreach ($siswas as $siswa) {
                        $row = [];

                        foreach (self::IMPORT_COLUMNS as $column) {
                            if (in_array($column, ['password', 'password_confirmation'], true)) {
                                continue;
                            }

                            $value = match ($column) {
                                'name' => $siswa->user?->name,
                                'email' => $siswa->user?->email,
                                'kelas_id' => $siswa->kelas_id,
                                default => $siswa->{$column},
                            };

                            $row[] = $this->safeCsvValue($value);
                        }

                        fputcsv($handle, $row);
                    }
                });

            fclose($handle);
        }, 'data-siswa-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadImportTemplate()
    {
        $this->currentSiswaManager();

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::IMPORT_COLUMNS);
            fputcsv($handle, [
                'Nama Akun Orangtua', 'orangtua@example.test', 'Password123!', 'Password123!', '1',
                '2026001', '0012345678', '3200000000000001', '3200000000000002', 'Nama Siswa', 'Panggilan', 'Cianjur',
                '2021-01-01', 'Laki-laki', 'Islam', '100', '15', '1', '0', 'Alamat Siswa', 'Desa', 'Kecamatan',
                'Kabupaten', 'Provinsi', 'Nama Ayah', '081234567890', '1970-01-01', 'Islam', 'Pekerjaan Ayah',
                'Penghasilan Ayah', 'Nama Ibu', '081234567891', '1975-01-01', 'Islam', 'Pekerjaan Ibu',
                'Penghasilan Ibu', 'Nama Wali', '081234567892', '1970-01-01', 'Islam', 'Pekerjaan Wali',
                'Penghasilan Wali', 'Alamat Wali',
            ]);
            fclose($handle);
        }, 'template-import-siswa.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importData(ImportSiswaRequest $request)
    {
        $manager = $this->currentSiswaManager();
        $rows = $this->csvRows($request->file('file')->getRealPath());

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'File CSV tidak memiliki data untuk diimpor.']);
        }

        $headers = array_keys($rows[0]);
        $missingColumns = array_diff(self::REQUIRED_IMPORT_COLUMNS, $headers);

        if ($missingColumns !== []) {
            throw ValidationException::withMessages([
                'file' => 'Kolom wajib tidak ditemukan: '.implode(', ', $missingColumns).'.',
            ]);
        }

        $kelasById = Kelas::active()->whereIn('id', collect($rows)->pluck('kelas_id')->filter()->unique())
            ->get(['id', 'thn_ajaran', 'semester'])->keyBy('id');
        $roleOrangtua = Role::where('role_name', 'Orangtua Siswa')->firstOrFail();
        $users = [];

        DB::transaction(function () use ($rows, $kelasById, $roleOrangtua, $manager, &$users) {
            foreach ($rows as $index => $row) {
                $row = Arr::only($row, self::IMPORT_COLUMNS);
                $kelas = $kelasById->get((int) ($row['kelas_id'] ?? 0));

                if (! $kelas) {
                    throw ValidationException::withMessages([
                        'file' => 'Baris '.($index + 2).': kelas_id harus mengarah ke kelas yang aktif.',
                    ]);
                }

                $validator = Validator::make($row, $this->importRules());

                if ($validator->fails()) {
                    $firstError = $validator->errors()->first();

                    throw ValidationException::withMessages([
                        'file' => 'Baris '.($index + 2).": {$firstError}",
                    ]);
                }

                $data = $validator->validated();
                $user = User::create([
                    'role_id' => $roleOrangtua->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'status' => true,
                    'email_verified_at' => now(),
                ]);

                Siswa::create([
                    ...Arr::except($data, ['name', 'email', 'password', 'password_confirmation']),
                    'kelas_id' => $kelas->id,
                    'user_id' => $user->id,
                    'tanggal_registrasi' => today(),
                    'status' => 'aktif',
                    'approved_by' => $manager->id,
                    'approved_at' => now(),
                ]);
                $users[] = $user;
            }
        });

        foreach ($users as $user) {
            $user->notify(new AccountCreatedNotification);
        }

        return redirect()
            ->route('siswa.index')
            ->with('success', count($users).' data siswa berhasil diimpor.');
    }

    public function moveClass(MoveSiswaClassRequest $request, Siswa $siswa)
    {
        $data = $request->validated();

        DB::transaction(function () use ($siswa, $data) {
            $siswa->update([
                'kelas_id' => $data['kelas_id'],
            ]);
        });

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Siswa berhasil dipindahkan ke kelas baru.');
    }

    public function approve(ApproveSiswaRequest $request, Siswa $siswa)
    {
        $manager = $this->currentSiswaManager();

        abort_unless(in_array($siswa->status, ['pending', 'ditolak'], true), 403);

        $data = $request->validated();

        DB::transaction(function () use ($siswa, $manager, $data) {
            $siswa->update([
                'status' => 'aktif',
                'kelas_id' => $data['kelas_id'],
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);
            $siswa->user()->update(['status' => true]);
        });

        $siswa->loadMissing('user');
        $siswa->user?->notify(new StudentRegistrationNotification($siswa->nama, 'approved'));

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Pendaftaran siswa berhasil disetujui.');
    }

    public function reject(Siswa $siswa)
    {
        $manager = $this->currentSiswaManager();

        abort_unless(in_array($siswa->status, ['pending', 'aktif'], true), 403);

        DB::transaction(function () use ($siswa, $manager) {
            $siswa->update([
                'status' => 'ditolak',
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);

            $siswa->loadMissing('user.siswas');

            if (! $siswa->user?->siswas->contains('status', 'aktif')) {
                $siswa->user?->update(['status' => false]);
            }
        });

        $siswa->loadMissing('user');
        $siswa->user?->notify(new StudentRegistrationNotification($siswa->nama, 'rejected'));

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Pendaftaran siswa berhasil ditolak.');
    }

    private function currentSiswaManager(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing('role');

        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Akademik'), 403);

        return $user;
    }

    private function csvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        $firstLine = fgets($handle);

        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);
        $headers = fgetcsv($handle, 0, $delimiter);
        $headers = array_map(fn ($header) => trim(Str::lower(ltrim((string) $header, "\xEF\xBB\xBF"))), $headers ?: []);
        $rows = [];

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($values === [null] || $values === []) {
                continue;
            }

            $row = [];

            foreach ($headers as $position => $header) {
                if ($header !== '') {
                    $value = trim((string) ($values[$position] ?? ''));
                    $row[$header] = $value === '' ? null : $value;
                }
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function importRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'kelas_id' => ['required', 'integer'],
            'nis' => ['nullable', 'string', 'max:20'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nik' => ['required', 'string', 'max:20', Rule::unique('siswas', 'nik')],
            'nomor_kk' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'nama_pgl' => ['nullable', 'string', 'max:25'],
            'tmp_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],
            'jk' => ['required', 'string', 'max:20'],
            'agama' => ['required', Rule::in(self::AGAMA_OPTIONS)],
            'tinggi_bdn' => ['nullable', 'numeric'],
            'berat_bdn' => ['nullable', 'numeric'],
            'anak_ke' => ['nullable', 'string', 'max:2'],
            'jml_sdr' => ['nullable', 'string', 'max:2'],
            'alamat' => ['required', 'string', 'max:1000'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nohp_ayah' => ['nullable', 'string', 'max:15'],
            'ttl_ayah' => ['nullable', 'date'],
            'agama_ayah' => ['nullable', Rule::in(self::AGAMA_OPTIONS)],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'penghasilan' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nohp_ibu' => ['nullable', 'string', 'max:15'],
            'ttl_ibu' => ['nullable', 'date'],
            'agama_ibu' => ['nullable', Rule::in(self::AGAMA_OPTIONS)],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'nohp_wali' => ['nullable', 'string', 'max:15'],
            'ttl_wali' => ['nullable', 'date'],
            'agama_wali' => ['nullable', Rule::in(self::AGAMA_OPTIONS)],
            'pekerjaan_wali' => ['nullable', 'string', 'max:255'],
            'penghasilan_wali' => ['nullable', 'string', 'max:255'],
            'alamat_wali' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (! is_string($value) || ! in_array($value[0] ?? '', ['=', '+', '-', '@'], true)) {
            return $value;
        }

        return "'{$value}";
    }
}
