@extends('layouts.public')
@section('title', 'Terlalu Banyak Permintaan | LSF')
@section('content')
<div class="text-center py-4">
  <div style="font-size:56px;color:#fd7e14"><i class="bi bi-hourglass-split"></i></div>
  <h4 class="font-weight-bold mt-2">Mohon Tunggu Sebentar</h4>
  <p class="text-muted">Terlalu banyak pengiriman dari jaringan Anda dalam waktu singkat. Silakan coba lagi dalam 1 menit.</p>
  <a href="{{ route('jobs.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Kembali ke Loker</a>
</div>
@endsection