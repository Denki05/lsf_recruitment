@extends('layouts.admin')
@section('title', 'Kelola Cabang')
@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Cabang</h1>
    <p class="page-sub">Kelola cabang/perusahaan, lokasi, dan akses lokernya.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.branches.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah Cabang</a>
  </div>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Lokasi</th>
          <th>Alamat</th>
          <th>Status</th>
          <th class="text-center">Loker</th>
          <th class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($branches as $b)
        <tr>
          <td><strong class="text-dark">{{ $b->name }}</strong></td>
          <td>{{ $b->location ?: '-' }}</td>
          <td><small class="text-muted">{{ $b->address ?: '-' }}</small></td>
          <td>{!! $b->is_active ? '<span class="badge badge-success badge-status">Aktif</span>' : '<span class="badge badge-secondary badge-status">Tutup</span>' !!}</td>
          <td class="text-center"><span class="badge badge-soft">{{ $b->positions_count }}</span></td>
          <td class="text-right">
            <div class="table-actions">
              <a href="{{ route('admin.branches.edit', $b->id) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
              <form method="POST" action="{{ route('admin.branches.destroy', $b->id) }}" class="d-inline m-0" onsubmit="return confirm('Hapus cabang ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <i class="bi bi-diagram-3"></i>
              <strong>Belum ada cabang</strong>
              <span>Tambahkan cabang untuk memisahkan loker dan lamaran.</span>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="text-muted mt-2" style="font-size:11.5px">Cabang dipakai untuk memisahkan loker dan lamaran per perusahaan. Lokasi bisa diubah kapan saja.</div>
@endsection
