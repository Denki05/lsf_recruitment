@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="content-card p-2 mb-2">
  <div class="row no-gutters">
    <div class="col-6 col-md p-1"><a class="stat-link" href="{{ route('admin.applications.index') }}"><div class="stat"><span class="stat-icon si-total"><i class="bi bi-people-fill"></i></span><span><span class="h4">{{ $total }}</span><br><small>Total</small></span></div></a></div>
    <div class="col-6 col-md p-1"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Baru']) }}"><div class="stat"><span class="stat-icon si-baru"><i class="bi bi-inbox-fill"></i></span><span><span class="h4 text-primary">{{ $baru }}</span><br><small>Baru</small></span></div></a></div>
    <div class="col-6 col-md p-1"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Seleksi']) }}"><div class="stat"><span class="stat-icon si-proses"><i class="bi bi-hourglass-split"></i></span><span><span class="h4 text-warning">{{ $proses }}</span><br><small>Seleksi/Interview</small></span></div></a></div>
    <div class="col-6 col-md p-1"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Diterima']) }}"><div class="stat"><span class="stat-icon si-terima"><i class="bi bi-check-circle-fill"></i></span><span><span class="h4 text-success">{{ $diterima }}</span><br><small>Diterima</small></span></div></a></div>
    <div class="col-6 col-md p-1"><a class="stat-link" href="{{ route('admin.applications.index', ['status' => 'Ditolak']) }}"><div class="stat"><span class="stat-icon si-tolak"><i class="bi bi-x-circle-fill"></i></span><span><span class="h4 text-danger">{{ $ditolak }}</span><br><small>Ditolak</small></span></div></a></div>
  </div>
</div>
</div>
<div class="row">
  <div class="col-md-8 mb-2"><div class="content-card p-2">
    <h6 class="font-weight-bold">Tren Lamaran (14 hari)</h6>
    <canvas id="trendChart" height="72"></canvas>
  </div></div>
  <div class="col-md-4 mb-2">
    <div class="content-card p-2 mb-2">
      <h6 class="font-weight-bold">Aksi Cepat</h6>
      <div class="d-flex" style="gap:6px;flex-wrap:wrap">
        <a href="{{ route('admin.applications.index', ['status' => 'Baru']) }}" class="btn btn-sm btn-primary"><i class="bi bi-inbox"></i> Baru</a>
        <a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i> Loker</a>
        <a href="{{ route('admin.applications.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Export</a>
      </div>
    </div>
    <div class="content-card p-2">
      <h6 class="font-weight-bold">Per Posisi</h6>
      <ul class="list-group list-group-flush">@foreach($perPosisi as $p)<li class="list-group-item d-flex justify-content-between align-items-center py-1 px-0" style="font-size:12.5px">{{ $p->full_title }}<span class="badge badge-primary badge-pill">{{ $p->applicants_count }}</span></li>@endforeach</ul>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-12 mb-2"><div class="content-card p-2">
    <div class="d-flex justify-content-between align-items-center mb-1"><h6 class="font-weight-bold mb-0">Lamaran Terbaru</h6><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-link">Lihat semua →</a></div>
    <div class="table-responsive"><table class="table table-sm table-hover mb-0">
      <tr><th>Nama</th><th>Posisi</th><th>Status</th><th></th></tr>
      @forelse($terbaru as $a)<tr><td>{{ $a->nama_lengkap }}</td><td>{{ $a->position->title ?? '-' }}</td><td><span class="badge badge-info">{{ $a->status }}</span></td><td><a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
      @empty<tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>@endforelse
    </table></div>
  </div></div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
(function(){
  var el = document.getElementById('trendChart');
  if(!el || typeof Chart === 'undefined') return;
  new Chart(el.getContext('2d'), {
    type: 'line',
    data: {
      labels: {!! json_encode($trendLabels) !!},
      datasets: [{ label: 'Lamaran', data: {!! json_encode($trendData) !!}, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', fill: true, lineTension: .3, pointRadius: 3 }]
    },
    options: { legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }] } }
  });
})();
</script>
@endpush
@endsection
