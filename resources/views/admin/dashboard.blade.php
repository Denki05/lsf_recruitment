@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<style>
.dash-hero{background:linear-gradient(120deg,#0f4c81 0%,#1565a8 60%,#1b7fc1 100%);border-radius:16px;color:#fff;padding:18px 22px;margin-bottom:12px;position:relative;overflow:hidden;box-shadow:0 6px 20px rgba(15,76,129,.25);}
.dash-hero:after{content:"";position:absolute;right:-60px;top:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.08);}
.dash-hero:before{content:"";position:absolute;right:60px;bottom:-90px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.06);}
.dash-hero h4{font-weight:800;margin-bottom:2px;position:relative;z-index:1;}
.dash-hero p{margin-bottom:0;opacity:.85;font-size:12.5px;position:relative;z-index:1;}
.dash-hero .hero-actions{position:relative;z-index:1;}
.stat-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:12px 14px;display:flex;align-items:center;gap:12px;height:100%;transition:transform .15s,box-shadow .15s;}
a.stat-link:hover .stat-card{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,76,129,.14);border-color:#b9cdf5;}
.stat-ic{width:46px;height:46px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;}
.g-total{background:linear-gradient(135deg,#0f4c81,#3b82c4);}
.g-baru{background:linear-gradient(135deg,#0d6efd,#5aa2ff);}
.g-proses{background:linear-gradient(135deg,#d99a06,#f2c94c);}
.g-terima{background:linear-gradient(135deg,#146c43,#3ec97f);}
.g-tolak{background:linear-gradient(135deg,#c81e3a,#f4687e);}
.stat-num{font-size:22px;font-weight:800;line-height:1.1;}
.stat-lbl{font-size:11px;color:var(--muted);}
.panel{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 1px 3px rgba(16,38,76,.06);}
.panel-h{padding:10px 14px;border-bottom:1px solid var(--border);font-weight:800;font-size:13px;display:flex;justify-content:space-between;align-items:center;}
.panel-b{padding:12px 14px;}
.pos-row{margin-bottom:10px;}
.pos-row:last-child{margin-bottom:0;}
.pos-top{display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px;}
.progress{height:7px;border-radius:6px;background:#eef1f5;}
.progress-bar{border-radius:6px;background:linear-gradient(90deg,#0f4c81,#3b82c4);}
.pill{font-size:10.5px;font-weight:700;border-radius:20px;padding:2px 9px;}
.avatar-sm{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#0f4c81,#3b82c4);color:#fff;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}
.empty-mini{text-align:center;color:var(--muted);font-size:12.5px;padding:14px 0;}
</style>

<div class="dash-hero">
  <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
    <div>
      <h4>Halo, {{ Auth::user()->name ?? 'HRD' }} 👋</h4>
      <p>{{ \App\Services\BranchAccess::currentBranch() ? 'Cabang aktif: ' . \App\Services\BranchAccess::currentBranch()->display : 'Semua cabang yang Anda kelola' }} · {{ now()->format('d M Y') }} · {{ $baru }} lamaran baru menunggu</p>
    </div>
    <div class="hero-actions d-flex" style="gap:6px;flex-wrap:wrap">
      <a href="{{ route('admin.applications.index', ['status' => 'Baru']) }}" class="btn btn-sm btn-light"><i class="bi bi-inbox"></i> Proses Baru</a>
      <a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-warning"><i class="bi bi-plus"></i> Buka Loker</a>
    </div>
  </div>
</div>

<div class="row mb-2" style="margin-left:-4px;margin-right:-4px">
  <div class="col-6 col-md px-1 mb-2"><a class="stat-link" href="{{ route('admin.applications.index') }}"><div class="stat-card"><span class="stat-ic g-total"><i class="bi bi-people-fill"></i></span><span><span class="stat-num">{{ $total }}</span><br><span class="stat-lbl">Total Lamaran</span></span></div></a></div>
  <div class="col-6 col-md px-1 mb-2"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Baru']) }}"><div class="stat-card"><span class="stat-ic g-baru"><i class="bi bi-inbox-fill"></i></span><span><span class="stat-num text-primary">{{ $baru }}</span><br><span class="stat-lbl">Baru Masuk</span></span></div></a></div>
  <div class="col-6 col-md px-1 mb-2"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Seleksi']) }}"><div class="stat-card"><span class="stat-ic g-proses"><i class="bi bi-hourglass-split"></i></span><span><span class="stat-num text-warning">{{ $proses }}</span><br><span class="stat-lbl">Seleksi / Interview</span></span></div></a></div>
  <div class="col-6 col-md px-1 mb-2"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Diterima']) }}"><div class="stat-card"><span class="stat-ic g-terima"><i class="bi bi-check-circle-fill"></i></span><span><span class="stat-num text-success">{{ $diterima }}</span><br><span class="stat-lbl">Diterima</span></span></div></a></div>
  <div class="col-6 col-md px-1 mb-2"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Ditolak']) }}"><div class="stat-card"><span class="stat-ic g-tolak"><i class="bi bi-x-circle-fill"></i></span><span><span class="stat-num text-danger">{{ $ditolak }}</span><br><span class="stat-lbl">Ditolak</span></span></div></a></div>
</div>

<div class="row">
  <div class="col-md-8 mb-2"><div class="panel" style="height:100%">
    <div class="panel-h">Tren Lamaran · 14 hari <a href="{{ route('admin.applications.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Export</a></div>
    <div class="panel-b"><div style="position:relative;height:190px"><canvas id="trendChart"></canvas></div></div>
  </div></div>
  <div class="col-md-4 mb-2"><div class="panel" style="height:100%">
    <div class="panel-h">Per Posisi <a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-link">Kelola →</a></div>
    <div class="panel-b">
      @forelse($perPosisi as $p)
      @php($pct = $total > 0 ? round($p->applicants_count / $total * 100) : 0)
      <div class="pos-row">
        <div class="pos-top"><span class="text-truncate" style="max-width:75%" title="{{ $p->full_title }}">{{ $p->full_title }}</span><strong>{{ $p->applicants_count }}</strong></div>
        <div class="progress"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
      </div>
      @empty<div class="empty-mini">Belum ada posisi.</div>@endforelse
    </div>
  </div></div>
</div>

<div class="row">
  <div class="col-12 mb-2"><div class="panel">
    <div class="panel-h">Lamaran Terbaru <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-link">Lihat semua →</a></div>
    <div class="table-responsive"><table class="table table-sm table-hover mb-0">
      @forelse($terbaru as $a)<tr>
        <td><span class="avatar-sm">{{ mb_strtoupper(mb_substr($a->nama_lengkap, 0, 1)) }}</span></td>
        <td><strong>{{ $a->nama_lengkap }}</strong><br><small class="text-muted">{{ $a->no_hp }}</small></td>
        <td><small>{{ $a->position->title ?? '-' }}</small>@if($a->position && $a->position->branch)<br><small class="text-muted">{{ $a->position->branch->name }}</small>@endif</td>
        <td><span class="pill badge-{{ $a->status === 'Diterima' ? 'success' : ($a->status === 'Ditolak' ? 'danger' : ($a->status === 'Baru' ? 'primary' : 'warning')) }}">{{ $a->status }}</span><br><small class="text-muted">{{ $a->created_at->diffForHumans() }}</small></td>
        <td class="text-right"><a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
      </tr>@empty<tr><td colspan="5"><div class="empty-mini">Belum ada data.</div></td></tr>@endforelse
    </table></div>
  </div></div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
(function(){
  var el = document.getElementById('trendChart');
  if(!el || typeof Chart === 'undefined') return;
  var ctx = el.getContext('2d');
  var g = ctx.createLinearGradient(0, 0, 0, 190);
  g.addColorStop(0, 'rgba(21,101,168,.35)'); g.addColorStop(1, 'rgba(21,101,168,.02)');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: {!! json_encode($trendLabels) !!},
      datasets: [{ label: 'Lamaran', data: {!! json_encode($trendData) !!}, borderColor: '#1565a8', backgroundColor: g, fill: true, lineTension: .35, pointRadius: 3, pointBackgroundColor: '#1565a8', borderWidth: 2 }]
    },
    options: { responsive: true, maintainAspectRatio: false, legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }] } }
  });
})();
</script>
@endpush
@endsection
