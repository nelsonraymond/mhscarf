@extends('emails.transactional.layout')

@section('title', 'Atur Ulang Kata Sandi — MH SCARF')

@section('content')
    <h1 class="heading">Atur ulang kata sandi</h1>
    <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun MH SCARF Anda.</p>
    <p><a class="button" href="{{ $resetUrl }}">Buat kata sandi baru</a></p>
    <p class="muted">Tautan ini berlaku selama {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} menit dan hanya dapat digunakan satu kali.</p>
    <p class="muted">Jika Anda tidak meminta perubahan ini, abaikan email ini. Kata sandi Anda tetap sama.</p>
@endsection
