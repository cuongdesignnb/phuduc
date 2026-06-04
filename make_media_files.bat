@echo off
php artisan make:controller Admin\MediaLibraryController --api
php artisan make:job ProcessMediaUpload
