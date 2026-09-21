@extends('layouts.public')
@section('title', $position->title . ' | LSF')
@section('hero_title', $position->branch ? $position->branch->name : 'Recruitment')
@section('hero_sub', $position->title . ' — ' . $position->location)

@section('content')
<style>
.job-detail-title{font-size:24px;font-weight:800;margin-bottom:2px;color:#111;}
.job-company-line{font-size:14px;color:#333;margin-bottom:12px;}
.job-company-line a{color:#111;text-decoration:underline;}
.job-meta-row{display:flex;align-items:flex-start;gap:8px;font-size:13.5px;color:#333;margin-bottom:6px;}
.job-meta-row i{color:#6c757d;font-size:15px;margin-top:1px;}
.job-salary-sub{color:#0d7a43;font-size:12.5px;margin:2px 0 0 23px;}
.job-posted{font-size:12.5px;color:#6c757d;margin:10px 0;}
.btn-lamar-cepat{background:#d6336c;border-color:#d6336c;color:#fff;font-weight:700;padding:8px 26px;border-radius:8px;}
.btn-lamar-cepat:hover{background:#b52a5b;border-color:#b52a5b;color:#fff;}
.btn-simpan{background:#e9eefb;border-color:#e9eefb;color:#3b5bdb;font-weight:700;padding:8px 22px;border-radius:8px;}
.btn-simpan.saved{background:#dbe4fb;}
.match-card{border:1px solid #dfe4ea;border-radius:12px;padding:14px 16px;margin:16px 0;background:#fff;}
.match-title{font-weight:700;font-size:15px;margin-bottom:2px;}
.match-sub{font-size:12.5px;color:#6c757d;margin-bottom:10px;}
.match-chip{display:inline-block;background:#e9f2fb;color:#333;font-size:12.5px;border-radius:16px;padding:5px 12px;margin:0 6px 6px 0;}
.match-more{background:none;border:none;color:#333;font-size:13px;text-decoration:underline;cursor:pointer;padding:0;}
.job-section{font-size:14px;color:#222;line-height:1.65;margin-top:14px;}
.job-section h6{font-weight:700;font-size:14px;margin:14px 0 6px;}
.job-section ul{padding-left:18px;margin-bottom:8px;}
.job-section li{margin-bottom:6px;}
</style>

<div class="mb-2"><a href="{{ route('jobs.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Semua Lowongan</a></div>

<div class="job-detail-title">{{ $position->title }}</div>
<div class="job-company-line">
  {{ $position->branch ? $position->branch->name : 'LSF' }}
  <i class="bi bi-patch-check-fill" style="color:#6c757d" title="Terverifikasi"></i>
  @if($position->branch)&nbsp; <a href="{{ route('jobs.branch', $position->branch->name) }}">Lihat semua lowongan kerja</a>@endif
</div>

<div class="job-meta-row"><i class="bi bi-geo-alt"></i><span>{{ $position->location }}@if($position->branch && $position->branch->location && $position->branch->location !== $position->location), {{ $position->branch->location }}@endif</span></div>
<div class="job-meta-row"><i class="bi bi-clock"></i><span>Full time · {{ $position->butuh_lembur ? 'Bersedia lembur di luar jam kerja' : 'Jam kerja normal' }}</span></div>
@if($position->sim_list)<div class="job-meta-row"><i class="bi bi-card-checklist"></i><span>Wajib SIM {{ implode(' / ', $position->sim_list) }}</span></div>@endif
@if($position->pendidikan_minimal)<div class="job-meta-row"><i class="bi bi-mortarboard"></i><span>Pendidikan minimal {{ $position->pendidikan_minimal }}</span></div>@endif
@if($position->usia_min || $position->usia_maks)<div class="job-meta-row"><i class="bi bi-person"></i><span>Usia {{ $position->usia_min ?: '…' }}–{{ $position->usia_maks ?: '…' }} tahun</span></div>@endif
@if($position->gaji)<div class="job-meta-row"><i class="bi bi-wallet2"></i><span><strong>Rp {{ number_format($position->gaji, 0, ',', '.') }}</strong> per month</span></div><div class="job-salary-sub">Gaji sesuai ekspektasi ⓘ</div>@endif

<div class="job-posted">Diposting {{ $position->created_at ? $position->created_at->diffForHumans() : '-' }} &nbsp; {{ $position->applicants_count > 10 ? 'Pelamar sangat banyak' : ($position->applicants_count > 0 ? $position->applicants_count . ' pelamar' : '') }}</div>

<div class="d-flex mb-1" style="gap:10px;flex-wrap:wrap">
  <a href="{{ route('lamaran.form', $position->id) }}" class="btn btn-lamar-cepat">Lamaran Cepat</a>
</div>

@php
$chips = collect($position->skill_list)->pluck('tag')->map(function ($t) { return ucwords($t); })->merge(collect($position->requirement_list)->map(function ($l) { $l = trim(preg_replace('/^[-•*\d.)\s]+/u', '', $l)); return \Illuminate\Support\Str::limit(ucwords(mb_strtolower(mb_substr($l, 0, 40))), 40); }))->unique()->values();
@endphp
@if($chips->count())
<div class="match-card">
  <div class="match-title">Bagaimana Anda cocok ⓘ</div>
  <div class="match-sub">Kecocokan berdasarkan riwayat karir Anda</div>
  <div id="matchChips">
    @foreach($chips as $i => $c)
    <span class="match-chip" @if($i >= 4) style="display:none" data-extra="1" @endif>{{ $c }} &nbsp;+</span>
    @endforeach
  </div>
  @if($chips->count() > 4)<button type="button" class="match-more" id="matchMore">+{{ $chips->count() - 4 }} selengkapnya ▾</button>@endif
</div>
@endif

<div class="job-section">
  @if($position->description)
  <h6>Deskripsi Pekerjaan</h6>
  <ul>
    @foreach(preg_split('/\r\n|\r|\n/', $position->description) as $line)
    @if(trim($line) !== '')<li>{{ trim($line) }}</li>@endif
    @endforeach
  </ul>
  @endif
  <h6>Kualifikasi</h6>
  <ul>
    @if($position->pendidikan_minimal)<li>Pendidikan minimal {{ $position->pendidikan_minimal }}.</li>@endif
    @if($position->usia_min || $position->usia_maks)<li>Usia {{ $position->usia_min ?: '…' }}–{{ $position->usia_maks ?: '…' }} tahun.</li>@endif
    @if($position->sim_list)<li>Wajib memiliki SIM {{ implode(' / ', $position->sim_list) }} aktif.</li>@endif
    @if($position->butuh_lembur)<li>Bersedia lembur di luar jam kerja.</li>@endif
    @foreach($position->requirement_list as $r)<li>{{ $r }}</li>@endforeach
    @if($position->gaji)<li>Gaji Rp {{ number_format($position->gaji, 0, ',', '.') }} per month (sesuai ekspektasi).</li>@endif
  </ul>
</div>

@if($others->count())
<h6 class="font-weight-bold mt-3">Loker lain di {{ $position->branch ? $position->branch->name : 'cabang ini' }}</h6>
<div class="row">
  @foreach($others as $o)
  <div class="col-md-4 mb-2"><a href="{{ route('jobs.show', $o->id) }}" class="btn btn-sm btn-light border btn-block text-left"><strong>{{ $o->title }}</strong><br><small class="text-muted">{{ $o->location }}</small></a></div>
  @endforeach
</div>
@endif

@push('scripts')
<script>
(function(){
  var more = document.getElementById('matchMore');
  if(more) more.addEventListener('click', function(){
    var extras = document.querySelectorAll('#matchChips [data-extra]');
    var hidden = Array.prototype.some.call(extras, function(e){ return e.style.display === 'none'; });
    extras.forEach(function(e){ e.style.display = hidden ? 'inline-block' : 'none'; });
    more.textContent = hidden ? 'Ringkas ▴' : '+' + extras.length + ' selengkapnya ▾';
  });
})();
</script>
@endpush
@endsection
