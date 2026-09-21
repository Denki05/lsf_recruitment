@extends('layouts.admin')
@section('title', 'Kelola Login')
@section('content')
<div class="content-card p-2">
  <div class="d-flex justify-content-between mb-2"><h5 class="font-weight-bold mb-0">Login / Pengguna</h5><a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Tambah</a></div>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th>Nama</th><th>Email</th><th>Peran</th><th>Cabang</th><th>Aksi</th></tr>
    @foreach($users as $u)<tr>
      <td><strong>{{ $u->name }}</strong></td>
      <td>{{ $u->email }}</td>
      <td>{!! $u->is_superadmin ? '<span class="badge badge-danger">Superadmin</span>' : '<span class="badge badge-info">Admin cabang</span>' !!}</td>
      <td><small>{{ $u->is_superadmin ? 'Semua cabang' : ($u->branches->pluck('name')->implode(', ') ?: '-') }}</small></td>
      <td class="text-nowrap"><a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="d-inline" onsubmit="return confirm('Hapus login ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form></td>
    </tr>@endforeach
  </table></div>
  <small class="text-muted">Superadmin (developer): lihat semua cabang + buat cabang & login baru. Admin cabang: hanya cabang yang dicentang, plus switcher cabang di navbar.</small>
</div>
@endsection
