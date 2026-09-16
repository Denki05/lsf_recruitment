@extends('layouts.public')
@section('title', 'Lowongan Kerja')
@section('hero_title', 'Lowongan Kerja')
@section('hero_sub', 'Pilih posisi yang cocok, klik Lamar — form cepat kurang dari 2 menit.')

@section('content')
<style>
.job-card{border:1px solid #e4e6ea;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.08);height:100%;display:flex;flex-direction:column;background:#fff;}
.job-card .card-body{padding:18px;display:flex;flex-direction:column;flex:1;}
.job-title{font-size:17px;font-weight:700;margin-bottom:6px;}
.job-badges{margin-bottom:10px;display:flex;flex-wrap:wrap;gap:6px;}
.job-desc{font-size:13.5px;color:#333;margin-bottom:8px;}
.job-req{font-size:13px;color:#495057;background:#f8f9fa;border-radius:8px;padding:10px 12px;margin-bottom:12px;white-space:pre-line;flex:1;}
.job-req-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#6c757d;margin-bottom:4px;}
.badge-sim{background:#fff3cd;color:#856404;border:1px solid #ffeeba;}
</style>

@if($positions->isEmpty())
<div class="text-center py-5">
  <div style="font-size:48px;color:#adb5bd"><i class="bi bi-inbox"></i></div>
  <h5 class="font-weight-bold mt-2">Belum ada lowongan dibuka</h5>
  <p class="text-muted">Silakan kembali lagi nanti.</p>
</div>
@else
<div class="row">
  @foreach($positions as $p)
  <div class="col-md-6 mb-3">
    <div class="job-card">
      <div class="card-body">
        <div class="job-title">{{ $p->title }}</div>
        <div class="job-badges">
          <span class="badge badge-primary"><i class="bi bi-geo-alt"></i> {{ $p->location }}</span>
          @if($p->syarat_sim)<span class="badge badge-sim"><i class="bi bi-card-checklist"></i> Wajib SIM {{ $p->syarat_sim }}</span>@endif
          <span class="badge badge-success"><i class="bi bi-circle-fill" style="font-size:7px"></i> Dibuka</span>
        </div>
        @if($p->description)<div class="job-desc">{{ $p->description }}</div>@endif
        @if($p->requirements)
        <div class="job-req"><div class="job-req-title">Requirement</div>{{ $p->requirements }}</div>
        @endif
        <a href="{{ route('lamaran.form', $p->id) }}" class="btn btn-primary btn-block mt-auto"><i class="bi bi-send"></i> Lamar Sekarang</a>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif
@endsection
