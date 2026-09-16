@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="content-card p-2 mb-3">
  <div class="row text-center no-gutters" style="gap:0">
    <div class="col-6 col-md p-1"><div class="stat"><div class="h4 mb-0">{{ $total }}</div><small>Total</small></div></div>
    <div class="col-6 col-md p-1"><div class="stat"><div class="h4 mb-0 text-primary">{{ $baru }}</div><small>Baru</small></div></div>
    <div class="col-6 col-md p-1"><div class="stat"><div class="h4 mb-0 text-warning">{{ $proses }}</div><small>Seleksi/Interview</small></div></div>
    <div class="col-6 col-md p-1"><div class="stat"><div class="h4 mb-0 text-success">{{ $diterima }}</div><small>Diterima</small></div></div>
    <div class="col-6 col-md p-1"><div class="stat"><div class="h4 mb-0 text-danger">{{ $ditolak }}</div><small>Ditolak</small></div></div>
  </div>
</div>
<div class="row">
  <div class="col-md-8 mb-3"><div class="content-card p-2">
    <h6 class="font-weight-bold">Tren Lamaran (14 hari)</h6>
    <canvas id="trendChart" height="110"></canvas>
  </div></div>
  <div class="col-md-4 mb-3"><div class="content-card p-2">
    <h6 class="font-weight-bold">Aksi Cepat</h6>
    <div class="d-flex flex-column" style="gap:6px">
      <a href="{{ route('admin.applications.index', ['status' => 'Baru']) }}" class="btn btn-sm btn-primary btn-block"><i class="bi bi-inbox"></i> Lamaran Baru</a>
      <a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-outline-primary btn-block"><i class="bi bi-plus"></i> Tambah Loker</a>
      <a href="{{ route('admin.applications.export') }}" class="btn btn-sm btn-outline-success btn-block"><i class="bi bi-file-earmark-excel"></i> Export</a>
      <a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-outline-secondary btn-block"><i class="bi bi-megaphone"></i> Kelola Loker</a>
    </div>
  </div></div>
</div>
<div class="row">
  <div class="col-md-5 mb-3"><div class="content-card p-2">
    <h6 class="font-weight-bold">Per Posisi</h6>
    <ul class="list-group list-group-flush">@foreach($perPosisi as $p)<li class="list-group-item d-flex justify-content-between align-items-center">{{ $p->full_title }}<span class="badge badge-primary badge-pill">{{ $p->applicants_count }}</span></li>@endforeach</ul>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-outline-primary mt-2">Kelola Loker</a>
  </div></div>
</div>
<div class="row">
  <div class="col-12 mb-3"><div class="content-card p-2">
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
