# Konvensi CRUD Master Data

`TemaController` adalah reference implementation (single source of truth) untuk CRUD seluruh master data pada aplikasi Laravel + Inertia + Vue ini. Implementasi baru mengikuti konvensi ini kecuali kebutuhan domain secara eksplisit mengharuskan perbedaan.

## Backend

- Controller berada di namespace `App\Http\Controllers`, extends `Controller`, dan resource route dideklarasikan dengan `Route::resource()`.
- Setiap controller menyediakan tujuh method resource: `index`, `create`, `store`, `show`, `edit`, `update`, dan `destroy`.
- `index` membaca query parameter `search`, `sort` (default `id`), dan `direction` (default `desc`).
- Query listing memakai pola berikut:

```php
$items = Model::query()
    ->when($search, function ($query, $search) {
        $query->where('nama_kolom', 'like', "%{$search}%");
    })
    ->orderBy($sort, $direction)
    ->paginate(10)
    ->withQueryString();
```

- `index` merender Inertia dengan data paginasi dan prop `filters` berisi `search`, `sort`, serta `direction`.
- `create`, `show`, dan `edit` selalu mengembalikan `Inertia::render('NamaMaster/Halaman', [...])`.
- `store` dan `update` menerima Form Request, lalu hanya memakai `$request->validated()` untuk `create()` atau `update()`.
- `show`, `edit`, `update`, dan `destroy` memakai route model binding, misalnya `public function edit(Model $model)`.
- Setelah create, update, atau delete, redirect ke route index dan gunakan flash success yang konsisten:
  - `Nama Master berhasil dibuat.`
  - `Nama Master berhasil diperbarui.`
  - `Nama Master berhasil dihapus.`

Contoh struktur method mutasi:

```php
public function store(NamaMasterRequest $request)
{
    NamaMaster::create($request->validated());

    return redirect()->route('nama-master.index')
        ->with('success', 'Nama Master berhasil dibuat.');
}

public function update(NamaMasterRequest $request, NamaMaster $namaMaster)
{
    $namaMaster->update($request->validated());

    return redirect()->route('nama-master.index')
        ->with('success', 'Nama Master berhasil diperbarui.');
}

public function destroy(NamaMaster $namaMaster)
{
    $namaMaster->delete();

    return redirect()->route('nama-master.index')
        ->with('success', 'Nama Master berhasil dihapus.');
}
```

Setiap master data memiliki Form Request sendiri di `App\Http\Requests`; request tersebut menentukan authorization dan validation rules. Tema mereferensikan request dengan fully-qualified class name pada signature method (`\App\Http\Requests\TemaRequest`); pertahankan gaya tersebut bila menyalin struktur controller secara langsung.

## Halaman Vue

- Sediakan empat halaman TypeScript: `resources/js/pages/NamaMaster/Index.vue`, `Create.vue`, `Edit.vue`, dan `Show.vue`.
- Gunakan Inertia (`Head`, `Link`, `router`, `useForm`, atau `usePage`) dan route/action Wayfinder yang digenerate dari controller.
- Gunakan komponen UI bawaan, terutama `Button`, `Input`, dan `InputError`; jangan menggantinya dengan elemen/form control baru tanpa alasan domain.
- Gunakan design token starter kit: `bg-card`, `bg-background`, `border-border`, `text-foreground`, `text-muted-foreground`, `text-card-foreground`, `bg-muted`, `bg-primary`, dan `text-primary-foreground`.
- Jangan gunakan warna hardcoded seperti `bg-white`, `text-gray-700`, atau `dark:bg-gray-900`. Halaman harus otomatis cocok dengan light dan dark mode.

### Index

- Gunakan TanStack Table dengan data `data` dari paginator Laravel.
- Search dan sort dilakukan server-side dengan `router.get()` ke route `index`; kirim `search`, `sort`, dan `direction`, serta gunakan `preserveState` dan `replace` seperti halaman Tema.
- Toggle arah sort: jika kolom yang sama sedang `asc`, ubah menjadi `desc`; selain itu gunakan `asc`.
- Navigasi paginator menggunakan `links` dari respons Laravel, sehingga URL dengan filter tetap dipertahankan oleh `withQueryString()`.
- Aksi Edit menggunakan route action `edit(id)`. Aksi Delete meminta konfirmasi, lalu memanggil `router.delete(destroy(id).url)`.

### Create dan Edit

- Gunakan `useForm()` untuk state form dan `form.post()`/`form.put()` ke action controller.
- Tampilkan validasi tiap field melalui `InputError` dengan nilai dari `form.errors`.
- Tombol submit memakai `Button` dan disabled saat `form.processing`.
- Tombol batal adalah `Link` Inertia yang dibungkus `Button` dengan `as-child` dan variant `outline`, menuju index.
- Edit menginisialisasi form dari prop model dan memperbaruinya saat prop berubah.

## Catatan terhadap Tema saat ini

`TemaController` sudah menerapkan seluruh method resource, Inertia, Form Request, route model binding, server-side search/sort, pagination `paginate(10)->withQueryString()`, dan flash success. `TemaRequest` saat ini memvalidasi `nama_tema` sebagai required string maksimum 255 karakter.

Controller merender `Tema/Show`, tetapi `resources/js/pages/Tema/Show.vue` belum tersedia. Semua CRUD master data berikutnya wajib menyertakan halaman Show agar kontrak resource lengkap; halaman Tema dapat dilengkapi saat halaman detail tersebut diperlukan.
