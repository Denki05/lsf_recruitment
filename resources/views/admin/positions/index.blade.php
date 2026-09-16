@extends('layouts.admin')
@section('title', 'Kelola Loker')
@section('content')
<div class="content-card p-3">
  <div class="d-flex justify-content-between mb-2"><h5 class="font-weight-bold mb-0">Loker / Posisi</h5><a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Tambah</a></div>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th>Posisi</th><th>Lokasi</th><th>Status</th><th>Pelamar</th><th>Aksi</th></tr>
    @foreach($positions as $p)<tr>
      <td><strong>{{ $p->title }}</strong><br><small class="text-muted">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</small></td>
      <td>{{ $p->location }}</td>
      <td>{!! $p->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tutup</span>' !!}</td>
      <td><span class="badge badge-primary">{{ $p->applicants_count }}</span></td>
      <td class="text-nowrap"><a href="{{ route('admin.positions.edit', $p->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        <form method="POST" action="{{ route('admin.positions.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('Hapus loker ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form></td>
    </tr>@endforeach
  </table></div>
</div>
@endsection
