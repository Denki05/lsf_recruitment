@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
  $currentBranch = \App\Services\BranchAccess::currentBranch();
  $accessibleBranches = \App\Services\BranchAccess::accessibleBranches();
  $branchLabel = $currentBranch ? $currentBranch->name : ($accessibleBranches->count() === 1 ? $accessibleBranches->first()->name : 'Semua cabang');
  $statusToneMap = ['Baru' => 'baru', 'Seleksi' => 'seleksi', 'Interview' => 'interview', 'Diterima' => 'diterima', 'Ditolak' => 'ditolak'];
  $hasTrend = array_sum($trendData) > 0;
@endphp
<style>
.dash-container { width: 100%; }

/* Kartu panel */
.dash-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 2px rgba(15,23,42,.04); padding: 16px 18px; }
.dash-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.dash-card-title { display: flex; align-items: center; gap: 7px; margin: 0; font-size: 13px; font-weight: 800; color: var(--text); }
.dash-card-title i { color: var(--brand); font-size: 14px; }
.dash-card-sub { margin-top: 2px; font-size: 11px; color: #94a3b8; }
.dash-link { display: inline-flex; align-items: center; gap: 4px; margin-top: 2px; font-size: 11.5px; font-weight: 600; color: var(--brand)!important; text-decoration: none!important; white-space: nowrap; }
.dash-link:hover { text-decoration: underline!important; }

/* Kartu statistik */
.stat-row { margin-bottom: 4px; }
.stat-card { position: relative; display: block; height: 100%; padding: 14px 16px; background: #fff; border: 1px solid var(--border); border-radius: 12px; color: var(--text)!important; text-decoration: none!important; box-shadow: 0 1px 2px rgba(15,23,42,.04); transition: transform .15s, box-shadow .15s, border-color .15s; }
.stat-card:hover { transform: translateY(-2px); border-color: #cbd5e1; box-shadow: 0 10px 24px rgba(15,23,42,.08); }
.stat-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.stat-label { font-size: 10.5px; font-weight: 700; letter-spacing: .55px; text-transform: uppercase; color: var(--muted); }
.stat-ic { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 9px; font-size: 15px; flex-shrink: 0; }
.stat-num { margin-top: 10px; font-size: 25px; font-weight: 800; line-height: 1; letter-spacing: -.5px; color: var(--tone); }
.stat-foot { margin-top: 5px; font-size: 10.5px; color: #94a3b8; }
.tone-total { --tone: #475569; --tone-bg: #f1f5f9; }
.tone-baru { --tone: #2563eb; --tone-bg: #eff6ff; }
.tone-proses { --tone: #d97706; --tone-bg: #fffbeb; }
.tone-terima { --tone: #059669; --tone-bg: #ecfdf5; }
.tone-tolak { --tone: #dc2626; --tone-bg: #fef2f2; }
.stat-card .stat-ic { background: var(--tone-bg); color: var(--tone); }

/* Chart & daftar posisi */
.chart-wrapper { position: relative; height: 230px; }
.chart-empty { height: 230px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; text-align: center; color: #94a3b8; background: #fbfdff; border: 1px dashed #dbe4ee; border-radius: 10px; }
.chart-empty i { font-size: 26px; color: #cbd5e1; margin-bottom: 4px; }
.chart-empty strong { font-size: 12.5px; color: #64748b; }
.chart-empty span { font-size: 11.5px; }
.pos-list { max-height: 230px; overflow-y: auto; padding-right: 5px; }
.pos-list::-webkit-scrollbar { width: 4px; }
.pos-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.pos-row + .pos-row { margin-top: 12px; }
.pos-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 5px; }
.pos-name { font-size: 12px; font-weight: 600; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pos-count { flex-shrink: 0; padding: 1px 6px; border-radius: 5px; background: var(--brand-soft); color: var(--brand); font-size: 10.5px; font-weight: 700; }
.pos-bar { height: 5px; border-radius: 999px; background: #eef2f7; overflow: hidden; }
.pos-bar span { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #0f4c81, #2f7ab8); }

/* Tabel lamaran terbaru */
.dash-table-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 18px 12px; border-bottom: 1px solid #eef2f7; }
.dash-table th { padding: 9px 18px; }
.dash-table td { padding: 11px 18px; }
.dash-avatar { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 9px; border-radius: 50%; background: linear-gradient(135deg, #e8f1fa, #d4e6f5); color: var(--brand); font-size: 12px; font-weight: 800; flex-shrink: 0; }
.status-pill { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; letter-spacing: .35px; text-transform: uppercase; }
.status-baru { background: #eff6ff; color: #1d4ed8; }
.status-seleksi { background: #fffbeb; color: #b45309; }
.status-interview { background: #ecfeff; color: #0e7490; }
.status-diterima { background: #ecfdf5; color: #047857; }
.status-ditolak { background: #fef2f2; color: #b91c1c; }
.dash-empty { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 28px 12px; text-align: center; color: #94a3b8; }
.dash-empty i { font-size: 24px; color: #cbd5e1; margin-bottom: 4px; }
.dash-empty strong { font-size: 12.5px; color: #64748b; }
.dash-empty span { font-size: 11.5px; }
.btn-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; }
.btn-amber:hover, .btn-amber:focus { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
</style>

<div class="dash-container">
  <div class="page-head">
    <div>
      <h1 class="page-title">Ringkasan Rekrutmen</h1>
      <p class="page-sub">Pantau lamaran masuk, tren 14 hari, dan performa tiap posisi &middot; {{ $branchLabel }} &middot; {{ now()->format('d M Y') }}</p>
    </div>
    <div class="page-actions">
      @if($baru > 0)
        <a href="{{ route('admin.applications.index', ['status' => 'Baru']) }}" class="btn btn-sm btn-amber"><i class="bi bi-inbox-fill"></i> {{ $baru }} lamaran baru</a>
      @endif
      <a href="{{ route('admin.applications.export') }}" class="btn btn-sm btn-light border"><i class="bi bi-download"></i> Export CSV</a>
      <a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Buka Lowongan</a>
    </div>
  </div>

  <div class="row stat-row">
    <div class="col-6 col-md-4 col-xl mb-3">
      <a class="stat-card tone-total" href="{{ route('admin.applications.index') }}">
        <div class="stat-top">
          <span class="stat-label">Total Pelamar</span>
          <span class="stat-ic"><i class="bi bi-people-fill"></i></span>
        </div>
        <div class="stat-num">{{ $total }}</div>
        <div class="stat-foot">Semua lamaran masuk</div>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl mb-3">
      <a class="stat-card tone-baru" href="{{ route('admin.applications.index', ['status' => 'Baru']) }}">
        <div class="stat-top">
          <span class="stat-label">Baru</span>
          <span class="stat-ic"><i class="bi bi-inbox-fill"></i></span>
        </div>
        <div class="stat-num">{{ $baru }}</div>
        <div class="stat-foot">Perlu ditinjau</div>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl mb-3">
      <a class="stat-card tone-proses" href="{{ route('admin.applications.index', ['status' => 'Seleksi']) }}">
        <div class="stat-top">
          <span class="stat-label">Seleksi</span>
          <span class="stat-ic"><i class="bi bi-hourglass-split"></i></span>
        </div>
        <div class="stat-num">{{ $proses }}</div>
        <div class="stat-foot">Seleksi &amp; interview</div>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl mb-3">
      <a class="stat-card tone-terima" href="{{ route('admin.applications.index', ['status' => 'Diterima']) }}">
        <div class="stat-top">
          <span class="stat-label">Diterima</span>
          <span class="stat-ic"><i class="bi bi-check-circle-fill"></i></span>
        </div>
        <div class="stat-num">{{ $diterima }}</div>
        <div class="stat-foot">Lolos seleksi</div>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl mb-3">
      <a class="stat-card tone-tolak" href="{{ route('admin.applications.index', ['status' => 'Ditolak']) }}">
        <div class="stat-top">
          <span class="stat-label">Ditolak</span>
          <span class="stat-ic"><i class="bi bi-x-circle-fill"></i></span>
        </div>
        <div class="stat-num">{{ $ditolak }}</div>
        <div class="stat-foot">Tidak dilanjutkan</div>
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8 mb-3">
      <div class="dash-card h-100">
        <div class="dash-card-head">
          <div>
            <h2 class="dash-card-title"><i class="bi bi-graph-up-arrow"></i> Tren Lamaran</h2>
            <div class="dash-card-sub">Jumlah lamaran masuk 14 hari terakhir</div>
          </div>
          <a class="dash-link" href="{{ route('admin.applications.index') }}">Lihat lamaran <i class="bi bi-arrow-right"></i></a>
        </div>
        @if($hasTrend)
          <div class="chart-wrapper"><canvas id="trendChart"></canvas></div>
        @else
          <div class="chart-empty">
            <i class="bi bi-graph-up"></i>
            <strong>Belum ada lamaran baru</strong>
            <span>Tidak ada lamaran dalam 14 hari terakhir.</span>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-4 mb-3">
      <div class="dash-card h-100 d-flex flex-column">
        <div class="dash-card-head">
          <div>
            <h2 class="dash-card-title"><i class="bi bi-bar-chart-steps"></i> Per Posisi</h2>
            <div class="dash-card-sub">Distribusi pelamar</div>
          </div>
          <a class="dash-link" href="{{ route('admin.positions.index') }}">Kelola <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="pos-list">
          @forelse($perPosisi as $p)
          <div class="pos-row">
            <div class="pos-head">
              <span class="pos-name" title="{{ $p->full_title }}">{{ $p->full_title }}</span>
              <span class="pos-count">{{ $p->applicants_count }}</span>
            </div>
            <div class="pos-bar"><span style="width:{{ $p->applicants_count > 0 ? max(3, $total > 0 ? round($p->applicants_count / $total * 100) : 0) : 0 }}%"></span></div>
          </div>
          @empty
          <div class="dash-empty">
            <i class="bi bi-briefcase"></i>
            <strong>Belum ada posisi</strong>
            <span>Buka lowongan pertama Anda.</span>
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-2">
      <div class="dash-card p-0" style="overflow:hidden">
        <div class="dash-table-head">
          <div>
            <h2 class="dash-card-title"><i class="bi bi-list-task"></i> Lamaran Terbaru</h2>
            <div class="dash-card-sub">5 pelamar yang paling baru masuk</div>
          </div>
          <a class="dash-link" href="{{ route('admin.applications.index') }}">Lihat semua <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover dash-table mb-0">
            <thead>
              <tr>
                <th>Pelamar</th>
                <th>Posisi Dilamar</th>
                <th>Kontak</th>
                <th>Status</th>
                <th>Masuk</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($terbaru as $a)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="dash-avatar">{{ mb_strtoupper(mb_substr($a->nama_lengkap, 0, 1)) }}</span>
                    <strong class="text-dark">{{ $a->nama_lengkap }}</strong>
                  </div>
                </td>
                <td>
                  <span class="d-block font-weight-bold text-dark">{{ $a->position->title ?? '-' }}</span>
                  @if($a->position && $a->position->branch)
                    <span class="text-muted" style="font-size:10.5px">{{ $a->position->branch->name }}</span>
                  @endif
                </td>
                <td><span class="text-muted">{{ $a->no_hp }}</span></td>
                <td><span class="status-pill status-{{ $statusToneMap[$a->status] ?? 'baru' }}">{{ $a->status }}</span></td>
                <td>
                  <span class="d-block text-dark" style="font-size:12px">{{ $a->created_at->format('d/m/Y') }}</span>
                  <span class="text-muted" style="font-size:10.5px">{{ $a->created_at->diffForHumans() }}</span>
                </td>
                <td class="text-right">
                  <a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i> Detail</a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6">
                  <div class="dash-empty">
                    <i class="bi bi-inbox"></i>
                    <strong>Belum ada lamaran</strong>
                    <span>Lamaran baru akan tampil di sini.</span>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@if($hasTrend)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
(function(){
  var el = document.getElementById('trendChart');
  if(!el || typeof Chart === 'undefined') return;
  var ctx = el.getContext('2d');

  var g = ctx.createLinearGradient(0, 0, 0, 230);
  g.addColorStop(0, 'rgba(15, 76, 129, 0.22)');
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
        lineTension: 0.32,
        pointRadius: 2.5,
        pointHoverRadius: 4,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#0f4c81',
        pointBorderWidth: 1.5,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: { display: false },
      layout: { padding: { left: -4, right: 4, top: 4, bottom: -4 } },
      tooltips: {
        backgroundColor: '#152238',
        titleFontSize: 11,
        bodyFontSize: 12,
        padding: 9,
        displayColors: false,
        callbacks: { label: function(item){ return item.yLabel + ' lamaran'; } }
      },
      scales: {
        xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#94a3b8', fontSize: 9, maxRotation: 0, autoSkipPadding: 12 } }],
        yAxes: [{
          gridLines: { color: '#f1f5f9', drawBorder: false, zeroLineColor: '#e2e8f0' },
          ticks: {
            beginAtZero: true,
            maxTicksLimit: 5,
            fontColor: '#94a3b8',
            fontSize: 10,
            padding: 6,
            callback: function(value){ return Number.isInteger(value) ? value : ''; }
          }
        }]
      }
    }
  });
})();
</script>
@endpush
@endif
@endsection
