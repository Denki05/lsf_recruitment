@extends('layouts.public')
@section('title', 'Lowongan Kerja')
@section('hero_title', 'Lowongan Kerja')
@section('hero_sub', 'Pilih posisi yang cocok, klik Lamar — form cepat kurang dari 2 menit.')

@section('content')
<style>
.job-card{border:1px solid #e4e6ea;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.08);height:100%;display:flex;flex-direction:column;background:#fff;}
.job-card .card-body{padding:14px;display:flex;flex-direction:column;flex:1;}
.job-title{font-size:15.5px;font-weight:700;margin-bottom:6px;}
.job-badges{margin-bottom:8px;display:flex;flex-wrap:wrap;gap:6px;}
.job-desc{font-size:13px;color:#333;margin-bottom:8px;}
.job-req{font-size:12.5px;color:#495057;background:#f8f9fa;border-radius:8px;padding:8px 10px;margin-bottom:10px;white-space:pre-line;flex:1;}
.job-req-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#6c757d;margin-bottom:4px;}
.badge-sim{background:#fff3cd;color:#856404;border:1px solid #ffeeba;}
.clamp{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden; }
.clamp-3{ -webkit-line-clamp:3; } .clamp-5{ -webkit-line-clamp:5; }
.clamp.expanded{ display:block; }
.more-btn{background:none;border:none;color:#0d6efd;font-size:12px;font-weight:600;padding:0;margin-top:4px;cursor:pointer;}
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
  <div class="col-md-6 col-lg-4 mb-3">
    <div class="job-card">
      <div class="card-body">
        <div class="job-title">{{ $p->title }}</div>
        <div class="job-badges">
          <span class="badge badge-primary"><i class="bi bi-geo-alt"></i> {{ $p->location }}</span>
          @if($p->syarat_sim)<span class="badge badge-sim"><i class="bi bi-card-checklist"></i> Wajib SIM {{ $p->syarat_sim }}</span>@endif
          <span class="badge badge-success"><i class="bi bi-circle-fill" style="font-size:7px"></i> Dibuka</span>
        </div>
        @if($p->description)
        <div class="job-desc clamp clamp-3" data-full="{{ $p->description }}">{{ \Illuminate\Support\Str::limit($p->description, 140) }}</div>
        @if(strlen($p->description) > 140)<button type="button" class="more-btn" data-target="desc">Selengkapnya ▾</button>@endif
        @endif
        @if($p->requirements)
        <div class="job-req"><div class="job-req-title">Requirement</div><div class="clamp clamp-5" data-full="{{ $p->requirements }}">{{ \Illuminate\Support\Str::limit($p->requirements, 200) }}</div>@if(strlen($p->requirements) > 200)<button type="button" class="more-btn" data-target="req">Selengkapnya ▾</button>@endif</div>
        @endif
        <a href="{{ route('lamaran.form', $p->id) }}" class="btn btn-primary btn-block mt-auto"><i class="bi bi-send"></i> Lamar Sekarang</a>
      </div>
    </div>
  </div>
  @endforeach
</div>
@push('scripts')
<script>
document.querySelectorAll('.more-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    var box = btn.parentElement.querySelector('.clamp');
    var expanded = box.classList.toggle('expanded');
    if(expanded){ box.textContent = box.dataset.full; btn.innerHTML = 'Ringkas ▴'; }
    else {
      var limit = box.classList.contains('clamp-3') ? 140 : 200;
      var full = box.dataset.full;
      box.textContent = full.length > limit ? full.substring(0, limit) + '...' : full;
      btn.innerHTML = 'Selengkapnya ▾';
    }
  });
});
</script>
@endpush
@endif
@endsection
