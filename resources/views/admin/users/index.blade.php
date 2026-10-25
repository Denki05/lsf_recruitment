@extends('layouts.admin')
@section('title', 'Kelola Pengguna')
@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Pengguna</h1>
    <p class="page-sub">Kelola akun admin/HRD, peran, akses cabang, dan link login bersama.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pengguna</a>
  </div>
</div>

@if(session('magic_link'))
<div class="content-card mb-3" style="border-left:4px solid #f0ad4e">
  <div class="d-flex flex-wrap align-items-center" style="gap:10px">
    <div style="flex:1 1 280px">
      <strong class="text-dark"><i class="bi bi-shield-lock"></i> Link login bersama</strong>
      @if(session('magic_link_user'))
      <div class="text-dark" style="font-size:12px">Untuk: <strong>{{ session('magic_link_user') }}</strong></div>
      @endif
      <div class="text-muted" style="font-size:12px">
        Bisa dipakai berulang @if(session('magic_link_expires'))sampai <strong>{{ session('magic_link_expires') }}</strong>@endif. Membuat link baru otomatis mematikan link lama. Perangkat yang sudah membuka link tetap login &plusmn;5 tahun. Salin sekarang, link tidak ditampilkan lagi. Kirim hanya ke owner/manajer, jangan ke grup publik.
      </div>
    </div>
    <div class="input-group" style="flex:1 1 320px;max-width:520px">
      <input id="magicLinkUrl" type="text" class="form-control" value="{{ session('magic_link') }}" readonly onclick="this.select()" aria-label="Link login bersama">
      <div class="input-group-append">
        <button type="button" class="btn btn-primary" id="copyMagicLink"><i class="bi bi-clipboard"></i> Salin</button>
      </div>
    </div>
  </div>
</div>
@endif

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
              <form method="POST" action="{{ route('admin.users.magic', $u->id) }}" class="d-inline m-0">@csrf<button class="btn btn-outline-dark" title="Buat link login bersama (bisa dipakai berulang, aktif 7 hari)"><i class="bi bi-box-arrow-in-right"></i> Link Login</button></form>
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
@push('scripts')
<script>
(function(){
  var btn = document.getElementById('copyMagicLink');
  var input = document.getElementById('magicLinkUrl');
  if(!btn || !input){ return; }
  function done(){
    var old = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Tersalin';
    setTimeout(function(){ btn.innerHTML = old; }, 1800);
  }
  function fallback(){
    // Jangan andalkan seleksi lama di halaman; salin dari textarea sementara.
    var ta = document.createElement('textarea');
    ta.value = input.value;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    ta.style.top = '0';
    document.body.appendChild(ta);
    ta.select();
    ta.setSelectionRange(0, ta.value.length);
    var ok = false;
    try { ok = document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
    if(ok){ done(); } else { input.focus(); input.select(); }
  }
  btn.addEventListener('click', function(){
    input.select();
    input.setSelectionRange(0, input.value.length);
    if(navigator.clipboard && window.isSecureContext){
      navigator.clipboard.writeText(input.value).then(done).catch(fallback);
    } else {
      fallback();
    }
  });
})();
</script>
@endpush
@endsection
