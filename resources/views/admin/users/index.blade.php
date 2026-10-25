@extends('layouts.admin')
@section('title', 'Kelola Pengguna')
@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Pengguna</h1>
    <p class="page-sub">Kelola akun admin/HRD, peran, dan akses cabangnya.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pengguna</a>
  </div>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Peran</th>
          <th>Cabang</th>
          <th class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
        <tr>
          <td>
            <strong class="text-dark">{{ $u->name }}</strong>
            @if(Auth::id() === $u->id)<span class="badge badge-soft badge-status ml-1">Anda</span>@endif
          </td>
          <td class="text-muted">{{ $u->email }}</td>
          <td>
            @if($u->is_superadmin)
              <span class="badge badge-danger badge-status">Manajer/Developer</span>
            @else
              <span class="badge badge-info badge-status">Admin/HRD</span>
            @endif
          </td>
          <td>
            <small class="text-muted">{{ $u->is_superadmin ? 'Semua cabang' : ($u->branches->pluck('name')->implode(', ') ?: '-') }}</small>
          </td>
          <td class="text-right">
            <div class="table-actions">
              <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
              <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="d-inline m-0" onsubmit="return confirm('Hapus login ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5">
            <div class="empty-state">
              <i class="bi bi-people"></i>
              <strong>Belum ada pengguna</strong>
              <span>Tambahkan akun untuk tim rekrutmen.</span>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="text-muted mt-2" style="font-size:11.5px">Manajer/Developer dapat mengakses semua cabang dan mengelola cabang serta pengguna. Admin/HRD hanya mengakses cabang yang dicentang.</div>
@endsection
