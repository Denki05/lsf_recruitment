@extends('layouts.admin')
@section('title', 'Data Lamaran')
@section('content')
<div class="content-card p-2">
  <form method="GET">
  <div class="form-row align-items-end">
    <div class="form-group col-md-3 mb-2"><input name="q" class="form-control form-control-sm" placeholder="Cari nama / HP" value="{{ request('q') }}"></div>
    <div class="form-group col-md-3 mb-2"><select name="position_id" class="form-control form-control-sm"><option value="">Semua posisi</option>@foreach($positions as $p)<option value="{{ $p->id }}" {{ request('position_id')==$p->id?'selected':'' }}>{{ $p->full_title }}</option>@endforeach</select></div>
    <div class="form-group col-md-2 mb-2"><select name="status" class="form-control form-control-sm"><option value="">Semua status</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="form-group col-md-2 mb-2"><input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal') }}"></div>
    <div class="form-group col-md-1 mb-2"><button class="btn btn-sm btn-primary btn-block">Filter</button></div>
    <div class="form-group col-md-1 mb-2"><a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-sm btn-success btn-block" title="Export CSV"><i class="bi bi-file-earmark-excel"></i></a></div>
  </div>
  </form>
  <form method="POST" action="{{ route('admin.applications.bulk') }}" id="bulkForm">@csrf
  <div class="form-row align-items-center mb-2">
    <div class="col-auto"><div class="form-check"><input type="checkbox" class="form-check-input" id="checkAll"><label class="form-check-label" for="checkAll" style="font-size:12px">Pilih semua</label></div></div>
    <div class="col-auto"><select name="status" class="form-control form-control-sm" required><option value="">Ubah status ke…</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option>{{ $s }}</option>@endforeach</select></div>
    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Terapkan</button></div>
    <div class="col-auto ml-auto">
      @if(request('sort') === 'skor')
      <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-down-up"></i> Urutan normal</a>
      @else
      <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'skor'])) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-trophy"></i> Urut skor</a>
      @endif
    </div>
  </div>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th></th><th>#</th><th>Tanggal</th><th>Nama</th><th>Posisi</th><th>Status</th><th>Aksi</th></tr>
    @forelse($applicants as $a)<tr>
      <td><input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check"></td>
      <td>{{ $a->id }}</td><td>{{ $a->created_at->format('d/m/Y') }}</td>
      <td><strong>{{ $a->nama_lengkap }}</strong><br><small class="text-muted">{{ $a->no_hp }}</small></td>
      <td><small>{{ $a->position->full_title ?? '-' }}</small></td>
      <td><span class="badge badge-info badge-status">{{ $a->status }}</span>@if(isset($a->skor) && $a->skor !== null)<br><small class="badge badge-light border mt-1">{{ $a->skor }}%</small>@endif</td>
      <td class="text-nowrap">
        <a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
        <a href="{{ route('admin.applications.download', $a->id) }}" class="btn btn-sm btn-outline-success">File</a>
      </td>
    </tr>@empty<tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>@endforelse
  </table></div>
  </form>
  {{ $applicants->appends(request()->query())->links() }}
</div>
@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function(){
  document.querySelectorAll('.row-check').forEach(function(c){ c.checked = document.getElementById('checkAll').checked; });
});
</script>
@endpush
@endsection
