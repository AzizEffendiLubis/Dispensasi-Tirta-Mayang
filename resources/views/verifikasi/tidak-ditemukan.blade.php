@extends('layouts.app')

@section('title', 'Surat Tidak Ditemukan')

@section('content')
<div class="max-w-md mx-auto py-12 text-center">
    <span class="badge badge-ditolak inline-flex items-center gap-1.5 px-3 py-1.5 mb-4">
        <i class="fas fa-circle-xmark"></i> Tidak Valid
    </span>
    <h1 class="font-display text-2xl text-ink mb-2">Surat Tidak Ditemukan</h1>
    <p class="text-sm text-ink-soft">
        Kode verifikasi pada tautan ini tidak cocok dengan surat mana pun yang tercatat
        di sistem. Surat mungkin belum diterbitkan, atau tautan/QR Code yang dipindai tidak valid.
    </p>
</div>
@endsection