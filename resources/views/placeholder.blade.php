@extends('layouts.app')
@section('title', $judul . ' - ' . config('ikpa.name'))

@section('content')
    <h4>{{ $judul }}</h4>
    <p class="text-muted">Halaman ini sedang dalam proses migrasi (Tahap {{ $tahap }}).</p>
@endsection
