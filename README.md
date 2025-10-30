# PHP Intern Test

Repo ini berisi dua bagian:
- `x_o_pattern.php`: Skrip terpisah untuk soal No.1 pola X-O
- `employees-api/`: Aplikasi Laravel CRUD Employees dengan upload foto (S3/Local) dan cache Redis per `emp_<nomor>`

## Fitur (Employees API)
- CRUD `employees` (kolom: `nomor`, `nama`, `jabatan`, `talahir`, dst)
- Upload foto ke S3 atau storage lokal (pilih via `.env`)
- Simpan URL file di `photo_upload_path`
- Cache Redis per record key `emp_<nomor>`; endpoint GET by nomor membaca cache

## Prasyarat
- PHP 8.2+, Composer
- SQLite (atau DB lain)
- Redis berjalan lokal (atau Docker)
- cURL/Postman (Windows gunakan `curl.exe`)

## Setup Cepat
1) Masuk ke app Laravel dan siapkan `.env`:
```
cd employees-api
copy .env.example .env
```
2) Minimal `.env` (tanpa S3):
```
APP_URL=http://127.0.0.1:8000
EMPLOYEE_UPLOAD_DISK=public

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```
3) Migrasi, storage link, jalankan server:
```
php artisan migrate
php artisan storage:link
php artisan serve
```

## Endpoints
- GET    `/api/employees`
- GET    `/api/employees/{id}`
- GET    `/api/employees/nomor/{nomor}`
- POST   `/api/employees`
- PUT    `/api/employees/{id}`
- DELETE `/api/employees/{id}`

## Contoh Uji (PowerShell/Windows)
Create + upload:
```
curl.exe -X POST "http://127.0.0.1:8000/api/employees" `
  -F "nomor=EMP001" `
  -F "nama=John Doe" `
  -F "jabatan=Engineer" `
  -F "talahir=1995-01-02" `
  -F "photo=@C:\\Users\\<user>\\path\\to\\image.jpg"
```
List:
```
curl.exe "http://127.0.0.1:8000/api/employees"
```
Get by nomor (pakai Redis jika ada):
```
curl.exe "http://127.0.0.1:8000/api/employees/nomor/EMP001"
```
Tips quoting path dengan spasi:
```
-F "photo=@\"C:\\Path With Spaces\\file.jpg\""
```

## Opsi S3/MinIO (opsional)
Set `EMPLOYEE_UPLOAD_DISK=s3` lalu isi variabel AWS/endpoint MinIO di `.env` (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT=true`).

## Verifikasi Redis
```
redis-cli GET emp_EMP001
```
Setelah delete, key harus `nil`.