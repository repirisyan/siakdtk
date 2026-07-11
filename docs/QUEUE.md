# Queue email SIAKDTK

Email dikirim melalui Laravel queue dengan `QUEUE_CONNECTION=database`. Jalankan migrasi sebelum memulai worker:

```bash
php artisan migrate
php artisan queue:work --tries=3 --backoff=60,300,900
```

Job yang gagal dicatat pada tabel `failed_jobs`. Pantau dan jalankan ulang dengan:

```bash
php artisan queue:failed
php artisan queue:retry all
```

Contoh Supervisor untuk VPS/Linux (`/etc/supervisor/conf.d/siakdtk-worker.conf`):

```ini
[program:siakdtk-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/siakdtk/artisan queue:work --sleep=3 --tries=3 --backoff=60,300,900 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/siakdtk/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Setelah menambah konfigurasi Supervisor, jalankan `supervisorctl reread`, `supervisorctl update`, dan `supervisorctl start siakdtk-worker:*`.
