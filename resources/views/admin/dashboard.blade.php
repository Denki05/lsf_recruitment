@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="content-card p-3 mb-3">
  <div class="row text-center">
    <div class="col-6 col-md-3 mb-2"><div class="border rounded p-3"><div class="h4 mb-0">{{ $total }}</div><small class="text-muted">Total</small></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-primary">{{ $baru }}</div><small class="text-muted">Baru</small></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-warning">{{ $proses }}</div><small class="text-muted">Seleksi/Interview</small></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="border rounded p-3 bg-light"><div class="h4 mb-0 text-success">{{ $diterima }}</div><small class="text-muted">Diterima</small></div></div>
  </div>
</div>
<div class="row">
  <div class="col-md-5 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Per Posisi</h6>
    <ul class="list-group list-group-flush">@foreach($perPosisi as $p)<li class="list-group-item d-flex justify-content-between align-items-center">{{ $p->full_title }}<span class="badge badge-primary badge-pill">{{ $p->applicants_count }}</span></li>@endforeach</ul>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-outline-primary mt-2">Kelola Loker</a>
  </div></div>
  <div class="col-md-7 mb-3"><div class="content-card p-3">
    <h6 class="font-weight-bold">Lamaran Terbaru</h6>
    <div class="table-responsive"><table class="table table-sm table-hover mb-0">
      <tr><th>Nama</th><th>Posisi</th><th>Status</th><th></th></tr>
      @forelse($terbaru as $a)<tr><td>{{ $a->nama_lengkap }}</td><td>{{ $a->position->title ?? '-' }}</td><td><span class="badge badge-info">{{ $a->status }}</span></td><td><a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-outline-primary">Detail</a></td></tr>
      @empty<tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>@endforelse
    </table></div>
  </div></div>
</div>
@endsection
