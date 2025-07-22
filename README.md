## Instalasi
1. Buat file .env di root folder, copy isi dari file .env.example ke file .env
2. composer install / composer update
3. php artisan migrate
4. php artisan storage:link
5. php artisan serve

## Catatan
Reset code saat implementasi forgot password seharusnya dikirimkan lewat email menggunakan SMTP. Tapi saat ini untuk mempercepat proses development, saya sertakan di response.

# astronacci_backend
