<?php

namespace App\Services;

use App\Models\MasterKomponenPenilaian;
use App\Models\SubTema;

class SubTemaKomponenPenilaianService
{
    public function generateFromMaster(SubTema $subTema): void
    {
        MasterKomponenPenilaian::query()
            ->active()
            ->orderBy('nama_komponen')
            ->get(['nama_komponen', 'deskripsi', 'status'])
            ->each(function (MasterKomponenPenilaian $masterKomponen) use ($subTema): void {
                $subTema->komponenPenilaians()->firstOrCreate(
                    ['nama_komponen' => $masterKomponen->nama_komponen],
                    [
                        'deskripsi' => $masterKomponen->deskripsi,
                        'status' => $masterKomponen->status,
                    ],
                );
            });
    }
}
