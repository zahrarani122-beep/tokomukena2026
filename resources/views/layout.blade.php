<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts/header')
</head>
<body>
    Selamat Datang {{ $nama }} <hr>

    <!-- Masukkan body dari layouts\body.blade.php -->
     @include('layouts.body')
     
    <hr>

    <!-- Masukkan footer -->
     @include('layouts.footer')
</body>
</html>