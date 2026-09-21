@extends('layouts.admin')
@section('title', 'Kelola Cabang')
@section('content')
<div class="content-card p-2">
  <div class="d-flex justify-content-between mb-2"><h5 class="font-weight-bold mb-0">Cabang</h5><a href="{{ route('admin.branches.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Tambah</a></div>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th>Nama</th><th>Lokasi</th><th>Alamat</th><th>Status</th><th>Loker</th><th>Aksi</th></tr>
    @foreach($branches as $b)<tr>
      <td><strong>{{ $b->name }}</strong></td>
      <td>{{ $b->location ?: '-' }}</td>
      <td><small>{{ $b->address ?: '-' }}</small></td>
      <td>{!! $b->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tutup</span>' !!}</td>
      <td><span class="badge badge-primary">{{ $b->positions_count }}</span></td>
      <td class="text-nowrap"><a href="{{ route('admin.branches.edit', $b->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        <form method="POST" action="{{ route('admin.branches.destroy', $b->id) }}" class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form></td>
    </tr>@endforeach
  </table></div>
  <small class="text-muted">Cabang dipakai untuk memisahkan loker + lamaran per perusahaan. Lokasi bisa diubah kapan saja.</small>
</div>
@endsection
