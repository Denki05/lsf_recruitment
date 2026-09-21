@extends('layouts.public')

<!-- DYNAMIC HEADER LAYOUT (White-label, tidak terikat LSF) -->
@section('title', isset($branch) ? 'Lowongan ' . $branch->name : 'Portal Karir & Rekrutmen')
@section('hero_title', isset($branch) ? 'Karir di ' . $branch->name : 'Eksplorasi Peluang Karir')
@section('hero_sub', isset($branch) ? 'Temukan peluang karir terbaik untuk penempatan ' . ($branch->location ?? 'cabang ini') . '.' : 'Temukan posisi yang sesuai dan kembangkan potensi Anda bersama kami. Proses rekrutmen cepat & transparan.')

@section('content')
<style>
.job-list-container { max-width: 960px; margin: 0 auto; }

/* Dynamic Header Internal */
.page-internal-header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; }
.header-title { font-size: 22px; font-weight: 800; color: #1e293b; margin: 0 0 4px 0; }
.header-subtitle { font-size: 13.5px; color: #64748b; margin: 0; }

/* Search Bar */
.search-wrapper { position: relative; margin-bottom: 16px; }
.search-wrapper i.bi-search { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 16px; }
.search-input { width: 100%; padding: 10px 14px 10px 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
.search-input:focus { border-color: #0f4c81; outline: none; box-shadow: 0 0 0 3px rgba(15, 76, 129, 0.1); }
.job-count-info { font-size: 12px; color: #64748b; margin-bottom: 12px; font-weight: 500; }

/* GRID SYSTEM KARTU LOWONGAN */
#jobList { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }

/* Card Compact */
.job-card { display: flex; flex-direction: column; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-decoration: none; color: inherit; transition: all 0.2s; height: 100%; }
.job-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(15, 76, 129, 0.08); border-color: #b9cdf5; text-decoration: none; color: inherit; }

.job-card-header { display: flex; gap: 12px; margin-bottom: 12px; }
.job-avatar { width: 42px; height: 42px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #0f4c81, #3b82c4); }
.job-main { flex: 1; min-width: 0; }
.job-title { font-size: 14.5px; font-weight: 800; color: #1e293b; margin: 0 0 2px 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.job-company { font-size: 11.5px; color: #64748b; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.job-new-badge { background: #d1fae5; color: #059669; font-size: 9px; font-weight: 700; border-radius: 20px; padding: 2px 6px; text-transform: uppercase; margin-left: 6px; vertical-align: middle; }

/* Chips */
.job-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.job-chips span { font-size: 11px; font-weight: 500; background: #f1f5f9; color: #475569; border-radius: 4px; padding: 3px 8px; }
.job-chips span.pay-chip { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: 600; }

/* Footer Card */
.job-foot { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 10px; border-top: 1px dashed #e2e8f0; font-size: 11px; color: #94a3b8; }
.job-more-btn { font-weight: 600; color: #0f4c81; }
</style>

<div class="job-list-container">
  
  <!-- INTERNAL HEADER -->
  <div class="page-internal-header">
    <div>
      @if(isset($branch))
        <span class="badge badge-primary mb-1 px-2 py-1" style="font-size: 10.5px;"><i class="bi bi-building"></i> Etalase Cabang</span>
        <h2 class="header-title">Lowongan di {{ $branch->name }}</h2>
        <p class="header-subtitle">Menampilkan posisi yang tersedia khusus untuk penempatan cabang ini.</p>
      @else
        <h2 class="header-title">Daftar Lowongan Pekerjaan</h2>
        <p class="header-subtitle">Eksplorasi peluang karir dari berbagai cabang kami.</p>
      @endif
    </div>
    
    @if(isset($branch))
      <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 12px; font-weight: 600;">
        <i class="bi bi-grid"></i> Lihat Semua Cabang
      </a>
    @endif
  </div>

  <!-- Search Area -->
  <div class="search-wrapper">
    <i class="bi bi-search"></i>
    <input id="jobSearch" class="search-input" placeholder="{{ isset($branch) ? 'Cari posisi di cabang ini...' : 'Cari posisi atau nama cabang...' }}" autocomplete="off">
  </div>
  
  <div class="job-count-info" id="jobCount">
    Menampilkan {{ $positions->count() }} lowongan tersedia
  </div>

  <!-- Empty State -->
  @if($positions->isEmpty())
  <div class="text-center py-5 bg-white border rounded shadow-sm mt-2">
    <div style="font-size:40px; color:#cbd5e1"><i class="bi bi-inbox-fill"></i></div>
    <h6 class="font-weight-bold mt-2 text-dark">Belum ada lowongan dibuka</h6>
    @if(isset($branch))
      <p class="text-muted" style="font-size: 13px;">Saat ini cabang {{ $branch->name }} belum membuka lowongan baru.</p>
      <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-primary mt-2">Cek Cabang Lainnya</a>
    @endif
  </div>
  @else
  
  <!-- Job List (Grid) -->
  <div id="jobList">
    @foreach($positions as $p)
    <a class="job-card" href="{{ route('jobs.show', $p->id) }}" data-key="{{ mb_strtolower($p->title . ' ' . ($p->branch ? $p->branch->name : '') . ' ' . $p->location) }}">
      
      <div class="job-card-header">
        <div class="job-avatar">
          {{ mb_strtoupper(mb_substr($p->branch ? $p->branch->name : 'C', 0, 1)) }}
        </div>
        <div class="job-main">
          <h3 class="job-title">
            {{ $p->title }}
            @if($p->created_at && $p->created_at->gt(now()->subDays(7)))
              <span class="job-new-badge">Baru</span>
            @endif
          </h3>
          <!-- Menggunakan config app.name agar otomatis mengikuti nama aplikasi di .env -->
          <div class="job-company"><i class="bi bi-building"></i> {{ $p->branch ? $p->branch->name : config('app.name', 'Perusahaan Kami') }}</div>
        </div>
      </div>
      
      <div class="job-chips">
        <span><i class="bi bi-geo-alt-fill text-muted"></i> {{ $p->location }}</span>
        @if($p->pendidikan_minimal)
          <span><i class="bi bi-mortarboard-fill text-muted"></i> {{ $p->pendidikan_minimal }}</span>
        @endif
        @if($p->sim_list)
          <span><i class="bi bi-card-checklist text-muted"></i> SIM {{ implode('/', $p->sim_list) }}</span>
        @endif
        @if($p->gaji)
          <span class="pay-chip"><i class="bi bi-cash-stack"></i> Rp {{ number_format($p->gaji, 0, ',', '.') }}</span>
        @endif
      </div>
      
      <div class="job-foot">
        <span>{{ $p->created_at ? $p->created_at->diffForHumans() : '' }}</span>
        <span class="job-more-btn">Lihat Detail &rarr;</span>
      </div>
    </a>
    @endforeach
  </div>

  <!-- Search Not Found State -->
  <div class="text-center py-4 bg-white border rounded shadow-sm" id="jobEmpty" style="display:none; grid-column: 1 / -1;">
    <div style="font-size:32px; color:#cbd5e1"><i class="bi bi-search"></i></div>
    <h6 class="font-weight-bold mt-2 text-dark">Pencarian tidak ditemukan</h6>
    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="jobReset">Reset Pencarian</button>
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
      count.innerHTML = shown > 0 
        ? 'Menampilkan <strong class="text-dark">' + shown + '</strong> dari ' + total + ' lowongan'
        : 'Menampilkan 0 lowongan';
      empty.style.display = shown ? 'none' : 'block';
    }

    input.addEventListener('input', apply);
    document.getElementById('jobReset').addEventListener('click', function(){ 
      input.value = ''; 
      apply(); 
      input.focus(); 
    });
  })();
  </script>
  @endpush
  @endif
</div>
@endsection