@extends('layouts.public')
@section('title', 'Recruitment | LSF')
@section('hero_title', 'Recruitment')
@section('hero_sub', 'Silakan pilih posisi yang sesuai, klik Lamar — formulir cepat kurang dari 2 menit.')

@section('content')
<style>
.job-search{position:relative;margin-bottom:4px;}
.job-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#8a94a3;}
.job-search input{padding-left:34px;border-radius:10px;}
.job-count{font-size:12.5px;color:#6c757d;margin:8px 2px 12px;}
.job-card{display:flex;gap:14px;background:#fff;border:1px solid #e3e8ef;border-radius:16px;padding:16px 18px;margin-bottom:12px;color:inherit;transition:transform .15s,box-shadow .15s,border-color .15s;}
.job-card:hover{text-decoration:none;color:inherit;transform:translateY(-2px);box-shadow:0 10px 26px rgba(15,76,129,.12);border-color:#b9cdf5;}
.job-main{flex:1;min-width:0;}
.job-title{font-size:16.5px;font-weight:800;color:#14202e;margin-bottom:1px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.job-new{background:#dcf5e5;color:#146c43;font-size:11px;font-weight:700;border-radius:20px;padding:2px 10px;white-space:nowrap;}
.job-co{font-size:13px;color:#42546a;margin-bottom:8px;}
.job-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;}
.job-chips span{font-size:12px;background:#f1f4f8;color:#33465c;border-radius:8px;padding:3px 9px;white-space:nowrap;}
.job-chips span.pay{background:#e7f6ec;color:#146c43;font-weight:700;}
.job-points{margin:2px 0 4px;padding-left:18px;font-size:13px;color:#42546a;}
.job-points li{margin-bottom:2px;}
.job-foot{display:flex;justify-content:space-between;align-items:center;margin-top:8px;font-size:12px;color:#8a94a3;}
.job-more{font-weight:700;color:var(--brand);white-space:nowrap;}
.job-avatar{width:52px;height:52px;border-radius:14px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;background:linear-gradient(135deg,var(--brand),#3b82c4);}
</style>

<div class="job-search">
  <i class="bi bi-search"></i>
  <input id="jobSearch" class="form-control" placeholder="Cari posisi atau perusahaan…" autocomplete="off">
</div>
<div class="job-count" id="jobCount">{{ $positions->count() }} lowongan tersedia</div>

@if($positions->isEmpty())
<div class="text-center py-5">
  <div style="font-size:48px;color:#adb5bd"><i class="bi bi-inbox"></i></div>
  <h5 class="font-weight-bold mt-2">Belum ada lowongan dibuka</h5>
  <p class="text-muted">Silakan kembali lagi nanti.</p>
</div>
@else
<div id="jobList">
  @foreach($positions as $p)
  <a class="job-card" href="{{ route('jobs.show', $p->id) }}" data-key="{{ mb_strtolower($p->title . ' ' . ($p->branch ? $p->branch->name : '') . ' ' . $p->location) }}">
    <div class="job-main">
      <div class="job-title">{{ $p->title }}
        @if($p->created_at && $p->created_at->gt(now()->subDays(7)))<span class="job-new">Baru untuk kamu</span>@endif
      </div>
      <div class="job-co"><i class="bi bi-building"></i> {{ $p->branch ? $p->branch->name : 'LSF' }}</div>
      <div class="job-chips">
        <span><i class="bi bi-geo-alt"></i> {{ $p->location }}</span>
        @if($p->gaji)<span class="pay">Rp {{ number_format($p->gaji, 0, ',', '.') }}/bln</span>@endif
        @if($p->pendidikan_minimal)<span>Min. {{ $p->pendidikan_minimal }}</span>@endif
        @if($p->sim_list)<span>SIM {{ implode(' / ', $p->sim_list) }}</span>@endif
      </div>
      @if($p->requirement_list)
      <ul class="job-points">
        @foreach(array_slice($p->requirement_list, 0, 3) as $r)<li>{{ \Illuminate\Support\Str::limit($r, 75) }}</li>@endforeach
      </ul>
      @endif
      <div class="job-foot"><span>{{ $p->created_at ? $p->created_at->diffForHumans() : '' }}</span><span class="job-more">Lihat Detail →</span></div>
    </div>
    <div class="job-avatar">{{ mb_strtoupper(mb_substr($p->branch ? $p->branch->name : 'L', 0, 1)) }}</div>
  </a>
  @endforeach
</div>
<div class="text-center py-4" id="jobEmpty" style="display:none">
  <div style="font-size:40px;color:#adb5bd"><i class="bi bi-search"></i></div>
  <p class="text-muted mb-1">Tidak ketemu lowongan yang cocok.</p>
  <button type="button" class="btn btn-sm btn-light border" id="jobReset">Tampilkan semua</button>
</div>
@push('scripts')
<script>
(function(){
  var input = document.getElementById('jobSearch'),
      count = document.getElementById('jobCount'),
      empty = document.getElementById('jobEmpty'),
      cards = Array.prototype.slice.call(document.querySelectorAll('#jobList .job-card')),
      total = cards.length;
  function apply(){
    var k = input.value.trim().toLowerCase(), shown = 0;
    cards.forEach(function(c){
      var hit = !k || c.dataset.key.indexOf(k) !== -1;
      c.style.display = hit ? 'flex' : 'none';
      if(hit) shown++;
    });
    count.textContent = shown + ' dari ' + total + ' lowongan';
    empty.style.display = shown ? 'none' : 'block';
  }
  input.addEventListener('input', apply);
  document.getElementById('jobReset').addEventListener('click', function(){ input.value = ''; apply(); input.focus(); });
})();
</script>
@endpush
@endif
@endsection
