@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<style>
/* CSS Ultra-Compact: Memaksimalkan Ruang Layar */
.dash-container { max-width: 1200px; margin: 0 auto; }

/* Panel Dasar */
.compact-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
.compact-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 10px; font-weight: 700; font-size: 12.5px; color: #1e293b; text-transform: uppercase; letter-spacing: 0.3px; }

/* Mini Stats (Padat & Ramping) */
.stat-link { text-decoration: none !important; color: inherit !important; display: block; }
.mini-stat { display: flex; align-items: center; gap: 10px; transition: transform 0.15s; }
.stat-link:hover .mini-stat { transform: translateY(-2px); }
.mini-stat-ic { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; flex-shrink: 0; }
.mini-stat-num { font-size: 18px; font-weight: 800; line-height: 1; color: #1e293b; display: block; margin-bottom: 2px; }
.mini-stat-lbl { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

/* Warna Latar Ikon */
.ic-total  { background: #f1f5f9; color: #475569; }
.ic-baru   { background: #eff6ff; color: #2563eb; }
.ic-proses { background: #fef3c7; color: #d97706; }
.ic-terima { background: #ecfdf5; color: #059669; }
.ic-tolak  { background: #fef2f2; color: #dc2626; }

/* Chart & Posisi List */
.chart-wrapper { height: 160px; position: relative; } /* Tinggi dikunci */
.pos-list { max-height: 160px; overflow-y: auto; padding-right: 4px; } /* Scroll internal jika kepanjangan */
.pos-list::-webkit-scrollbar { width: 4px; }
.pos-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* Progress Bar Mini */
.pos-row { margin-bottom: 10px; }
.pos-row:last-child { margin-bottom: 0; }
.pos-top { display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; }
.progress-mini { height: 4px; border-radius: 4px; background: #e2e8f0; }
.progress-bar-mini { border-radius: 4px; background: #0f4c81; }

/* Table Padat */
.table-compact th, .table-compact td { padding: 6px 10px; font-size: 12px; vertical-align: middle; border-top: 1px solid #f1f5f9; }
.table-compact thead th { border-bottom: 1px solid #e2e8f0; border-top: none; color: #64748b; font-weight: 600; }
.avatar-xs { width: 24px; height: 24px; border-radius: 6px; background: #eef2f6; color: #0f4c81; font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 8px; }
.pill-status { font-size: 10px; font-weight: 700; border-radius: 4px; padding: 2px 6px; }
.empty-mini { text-align: center; color: #94a3b8; font-size: 12px; padding: 16px 0; font-weight: 500; }

/* Custom Buttons */
.btn-xs { font-size: 11px; padding: 2px 8px; font-weight: 600; border-radius: 4px; }
.btn-primary-soft { background: #eef6ff; color: #0f4c81; border: none; }
.btn-primary-soft:hover { background: #dbeafe; color: #0c3e6a; }
.btn-solid-main { background: #0f4c81; color: #fff; border: none; }
.btn-solid-main:hover { background: #0c3e6a; color: #fff; }
</style>

<div class="dash-container">
  
  <!-- 1. Top Toolbar (Sangat Tipis) -->
  <div class="compact-card py-2 px-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
    <div class="d-flex align-items-center" style="gap: 12px; font-size: 12.5px;">
      <strong class="text-dark"><i class="bi bi-person-circle text-primary mr-1"></i> {{ Auth::user()->name ?? 'HRD' }}</strong>
      <span class="text-muted">|</span>
      <span class="text-secondary"><i class="bi bi-building"></i> {{ \App\Services\BranchAccess::currentBranch() ? \App\Services\BranchAccess::currentBranch()->display : 'Semua Cabang' }}</span>
      <span class="text-muted">|</span>
      <span class="text-secondary">{{ now()->format('d M Y') }}</span>
    </div>
    <div class="d-flex" style="gap: 6px;">
      @if($baru > 0)
        <a href="{{ route('admin.applications.index', ['status' => 'Baru']) }}" class="btn btn-xs btn-primary-soft text-danger mr-2">
          <i class="bi bi-bell-fill"></i> {{ $baru }} Baru!
        </a>
      @endif
      <a href="{{ route('admin.positions.create') }}" class="btn btn-xs btn-solid-main"><i class="bi bi-plus-lg"></i> Buka Lowongan</a>
    </div>
  </div>

  <!-- 2. Statistik 5 Kolom (Satu Baris Padat) -->
  <div class="row mb-1" style="margin-left: -6px; margin-right: -6px;">
    <div class="col-6 col-lg px-1 mb-2">
      <a class="stat-link" href="{{ route('admin.applications.index') }}">
        <div class="compact-card mini-stat m-0 h-100">
          <div class="mini-stat-ic ic-total"><i class="bi bi-people-fill"></i></div>
          <div><span class="mini-stat-num">{{ $total }}</span><span class="mini-stat-lbl">Total</span></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg px-1 mb-2">
      <a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Baru']) }}">
        <div class="compact-card mini-stat m-0 h-100 border-primary" style="background: #f8fbff;">
          <div class="mini-stat-ic ic-baru"><i class="bi bi-inbox-fill"></i></div>
          <div><span class="mini-stat-num text-primary">{{ $baru }}</span><span class="mini-stat-lbl">Baru</span></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg px-1 mb-2">
      <a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Seleksi']) }}">
        <div class="compact-card mini-stat m-0 h-100">
          <div class="mini-stat-ic ic-proses"><i class="bi bi-hourglass-split"></i></div>
          <div><span class="mini-stat-num text-warning">{{ $proses }}</span><span class="mini-stat-lbl">Seleksi</span></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg px-1 mb-2">
      <a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Diterima']) }}">
        <div class="compact-card mini-stat m-0 h-100">
          <div class="mini-stat-ic ic-terima"><i class="bi bi-check-circle-fill"></i></div>
          <div><span class="mini-stat-num text-success">{{ $diterima }}</span><span class="mini-stat-lbl">Terima</span></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg px-1 mb-2">
      <a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Ditolak']) }}">
        <div class="compact-card mini-stat m-0 h-100">
          <div class="mini-stat-ic ic-tolak"><i class="bi bi-x-circle-fill"></i></div>
          <div><span class="mini-stat-num text-danger">{{ $ditolak }}</span><span class="mini-stat-lbl">Tolak</span></div>
        </div>
      </a>
    </div>
  </div>

  <!-- 3. Grafik & Posisi (Tinggi Dikunci) -->
  <div class="row mb-1" style="margin-left: -6px; margin-right: -6px;">
    <!-- Grafik Tren -->
    <div class="col-lg-8 px-1 mb-2">
      <div class="compact-card m-0 h-100">
        <div class="compact-header">
          <span><i class="bi bi-graph-up text-muted mr-1"></i> Tren Lamaran (14 Hari)</span>
          <a href="{{ route('admin.applications.export') }}" class="text-success text-decoration-none" style="text-transform: none; font-size: 11px;"><i class="bi bi-file-earmark-excel"></i> Export</a>
        </div>
        <div class="chart-wrapper">
          <canvas id="trendChart"></canvas>
        </div>
      </div>
    </div>
    
    <!-- List Per Posisi -->
    <div class="col-lg-4 px-1 mb-2">
      <div class="compact-card m-0 h-100">
        <div class="compact-header">
          <span><i class="bi bi-bar-chart-steps text-muted mr-1"></i> Per Posisi</span>
          <a href="{{ route('admin.positions.index') }}" class="text-primary text-decoration-none" style="text-transform: none; font-size: 11px;">Kelola &rarr;</a>
        </div>
        <div class="pos-list">
          @forelse($perPosisi as $p)
          @php($pct = $total > 0 ? round($p->applicants_count / $total * 100) : 0)
          <div class="pos-row">
            <div class="pos-top">
              <span class="text-truncate" style="max-width:85%" title="{{ $p->full_title }}">{{ $p->full_title }}</span>
              <span class="text-dark">{{ $p->applicants_count }}</span>
            </div>
            <div class="progress-mini"><div class="progress-bar-mini" style="width:{{ $pct }}%"></div></div>
          </div>
          @empty
          <div class="empty-mini">Belum ada posisi.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <!-- 4. Tabel Lamaran Terbaru (Super Padat) -->
  <div class="row" style="margin-left: -6px; margin-right: -6px;">
    <div class="col-12 px-1">
      <div class="compact-card m-0">
        <div class="compact-header mb-1">
          <span><i class="bi bi-list-task text-muted mr-1"></i> Lamaran Terbaru Masuk</span>
          <a href="{{ route('admin.applications.index') }}" class="text-primary text-decoration-none" style="text-transform: none; font-size: 11px;">Lihat Semua &rarr;</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-compact mb-0">
            <thead>
              <tr>
                <th style="width: 250px;">Nama Pelamar</th>
                <th>Posisi Dilamar</th>
                <th>Kontak</th>
                <th>Masuk</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($terbaru as $a)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="avatar-xs">{{ mb_strtoupper(mb_substr($a->nama_lengkap, 0, 1)) }}</span>
                    <strong class="text-dark">{{ $a->nama_lengkap }}</strong>
                  </div>
                </td>
                <td>
                  <span class="d-block font-weight-bold text-dark">{{ $a->position->title ?? '-' }}</span>
                  @if($a->position && $a->position->branch)
                    <span class="text-muted" style="font-size: 10.5px;">{{ $a->position->branch->name }}</span>
                  @endif
                </td>
                <td><span class="text-muted">{{ $a->no_hp }}</span></td>
                <td>
                  <span class="pill-status badge-{{ $a->status === 'Diterima' ? 'success' : ($a->status === 'Ditolak' ? 'danger' : ($a->status === 'Baru' ? 'primary' : 'warning')) }}">{{ $a->status }}</span>
                  <span class="text-muted ml-2" style="font-size: 10.5px;">{{ $a->created_at->diffForHumans() }}</span>
                </td>
                <td class="text-right">
                  <a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-xs btn-light border">Detail</a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5"><div class="empty-mini">Belum ada lamaran baru.</div></td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
(function(){
  var el = document.getElementById('trendChart');
  if(!el || typeof Chart === 'undefined') return;
  var ctx = el.getContext('2d');
  
  var g = ctx.createLinearGradient(0, 0, 0, 160);
  g.addColorStop(0, 'rgba(15, 76, 129, 0.2)');
  g.addColorStop(1, 'rgba(15, 76, 129, 0.0)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: {!! json_encode($trendLabels) !!},
      datasets: [{ 
        data: {!! json_encode($trendData) !!}, 
        borderColor: '#0f4c81', 
        backgroundColor: g, 
        fill: true, 
        lineTension: 0.3,
        pointRadius: 3, 
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#0f4c81',
        borderWidth: 2 
      }]
    },
    options: { 
      responsive: true, maintainAspectRatio: false, legend: { display: false },
      layout: { padding: { left: -5, bottom: -5 } }, /* Menipiskan margin chart */
      tooltips: { backgroundColor: '#1e293b', titleFontSize: 11, bodyFontSize: 12, padding: 8, displayColors: false },
      scales: { 
        xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#94a3b8', fontSize: 9 } }],
        yAxes: [{ gridLines: { color: '#f1f5f9', drawBorder: false }, ticks: { beginAtZero: true, stepSize: 1, fontColor: '#94a3b8', fontSize: 10, padding: 5 } }] 
      } 
    }
  });
})();
</script>
@endpush
@endsection