@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="content-card p-3 mb-3">
  <div class="row text-center">
    <div class="col-6 col-md mb-2"><div class="border rounded p-3"><div class="h4 mb-0">{{ $total }}</div><small class="text-muted">Total</small></div></div>
    <div class="col-6 col-md mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-primary">{{ $baru }}</div><small class="text-muted">Baru</small></div></div>
    <div class="col-6 col-md mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-warning">{{ $proses }}</div><small class="text-muted">Seleksi/Interview</small></div></div>
    <div class="col-6 col-md mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-success">{{ $diterima }}</div><small class="text-muted">Diterima</small></div></div>
    <div class="col-6 col-md mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-danger">{{ $ditolak }}</div><small class="text-muted">Ditolak</small></div></div>
  </div>
</div>
<div class="row">
  <div class="col-md-7 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Flag SIM</h6>
    <div class="row text-center">
      <div class="col-4"><a href="{{ route('admin.applications.index', ['flag' => 'Cocok']) }}" class="text-decoration-none"><div class="border rounded p-2"><div class="h5 mb-0 text-success">{{ $flags['Cocok'] }}</div><small class="text-muted">Cocok</small></div></a></div>
      <div class="col-4"><a href="{{ route('admin.applications.index', ['flag' => 'Ditinjau']) }}" class="text-decoration-none"><div class="border rounded p-2"><div class="h5 mb-0 text-warning">{{ $flags['Ditinjau'] }}</div><small class="text-muted">Ditinjau</small></div></a></div>
      <div class="col-4"><a href="{{ route('admin.applications.index', ['flag' => 'Kurang']) }}" class="text-decoration-none"><div class="border rounded p-2"><div class="h5 mb-0 text-danger">{{ $flags['Kurang'] }}</div><small class="text-muted">Kurang</small></div></a></div>
    </div>
  </div></div>
  <div class="col-md-5 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Aksi Cepat</h6>
    <div class="d-flex flex-wrap" style="gap:6px">
      <a href="{{ route('admin.applications.index', ['status' => 'Baru']) }}" class="btn btn-sm btn-primary"><i class="bi bi-inbox"></i> Lamaran Baru</a>
      <a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i> Tambah Loker</a>
      <a href="{{ route('admin.applications.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Export</a>
    </div>
  </div></div>
</div>
<div class="row">
  <div class="col-md-7 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Tren Lamaran (14 hari)</h6>
    <canvas id="trendChart" height="110"></canvas>
  </div></div>
  <div class="col-md-5 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Per Posisi</h6>
    <ul class="list-group list-group-flush">@foreach($perPosisi as $p)<li class="list-group-item d-flex justify-content-between align-items-center">{{ $p->full_title }}<span class="badge badge-primary badge-pill">{{ $p->applicants_count }}</span></li>@endforeach</ul>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-outline-primary mt-2">Kelola Loker</a>
  </div></div>
</div>
<div class="row">
  <div class="col-12 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Lamaran Terbaru</h6>
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
