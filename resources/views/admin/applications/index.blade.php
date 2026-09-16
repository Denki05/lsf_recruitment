@extends('layouts.admin')
@section('title', 'Data Lamaran')
@section('content')
<div class="content-card p-2">
  <form method="GET">
  <div class="form-row">
    <div class="form-group col-md-3"><input name="q" class="form-control form-control-sm" placeholder="Cari nama / HP" value="{{ request('q') }}"></div>
    <div class="form-group col-md-3"><select name="position_id" class="form-control form-control-sm"><option value="">Semua posisi</option>@foreach($positions as $p)<option value="{{ $p->id }}" {{ request('position_id')==$p->id?'selected':'' }}>{{ $p->full_title }}</option>@endforeach</select></div>
    <div class="form-group col-md-2"><select name="status" class="form-control form-control-sm"><option value="">Semua status</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="form-group col-md-2"><input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal') }}"></div>
    <div class="form-group col-md-2"><button class="btn btn-sm btn-primary btn-block">Filter</button></div>
  </div>
  </form>
  <div class="mb-2"><a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel"></i> Export CSV/Excel</a></div>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th>#</th><th>Tanggal</th><th>Nama</th><th>Posisi</th><th>SIM</th><th>Status</th><th>Aksi</th></tr>
    @forelse($applicants as $a)<tr>
      <td>{{ $a->id }}</td><td>{{ $a->created_at->format('d/m/Y') }}</td>
      <td><strong>{{ $a->nama_lengkap }}</strong><br><small class="text-muted">{{ $a->no_hp }}</small></td>
      <td><small>{{ $a->position->full_title ?? '-' }}</small></td>
      <td><small>{{ $a->sim ?: '-' }}</small></td>
      <td><span class="badge badge-info badge-status">{{ $a->status }}</span></td>
      <td class="text-nowrap"><a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-outline-primary">Detail</a> <a href="{{ route('admin.applications.download', $a->id) }}" class="btn btn-sm btn-outline-success">File</a></td>
    </tr>@empty<tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>@endforelse
  </table></div>
  {{ $applicants->links() }}
</div>
@endsection
