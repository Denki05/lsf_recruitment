@extends('layouts.admin')
@section('title', 'Kelola Loker')
@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Kelola Loker</h1>
    <p class="page-sub">Daftar posisi yang dibuka beserta status dan jumlah pelamarnya.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('jobs.index') }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border"><i class="bi bi-box-arrow-up-right"></i> Lihat Situs</a>
    <a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah Loker</a>
  </div>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Posisi</th>
          <th>Cabang</th>
          <th>Lokasi</th>
          <th>SIM</th>
          <th>Gaji</th>
          <th>Status</th>
          <th class="text-center">Pelamar</th>
          <th class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($positions as $p)
        <tr>
          <td style="min-width:220px">
            <strong class="text-dark">{{ $p->title }}</strong>
            <div class="text-muted" style="font-size:11px">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</div>
          </td>
          <td><span class="badge badge-soft">{{ $p->branch->name ?? '-' }}</span></td>
          <td>{{ $p->location }}</td>
          <td>{{ $p->sim_list ? implode(' / ', $p->sim_list) : '-' }}</td>
          <td class="text-nowrap">{{ $p->gaji ? 'Rp '.number_format($p->gaji, 0, ',', '.') : '-' }}</td>
          <td>{!! $p->is_active ? '<span class="badge badge-success badge-status">Aktif</span>' : '<span class="badge badge-secondary badge-status">Tutup</span>' !!}</td>
          <td class="text-center"><span class="badge badge-soft">{{ $p->applicants_count }}</span></td>
          <td class="text-right">
            <div class="table-actions">
              <a href="{{ route('admin.positions.edit', $p->id) }}" class="btn btn-outline-primary" title="Edit loker"><i class="bi bi-pencil"></i> Edit</a>
              <a href="{{ route('jobs.show', $p) }}" target="_blank" rel="noopener" class="btn btn-outline-info" title="Buka detail publik"><i class="bi bi-eye"></i> Detail</a>
              <button type="button" class="btn btn-outline-secondary" data-copy-link="{{ $p->share_url }}" title="Salin link share pendek"><i class="bi bi-clipboard"></i> Link</button>
              <form method="POST" action="{{ route('admin.positions.destroy', $p->id) }}" class="d-inline m-0" onsubmit="return confirm('Hapus loker ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger" title="Hapus loker"><i class="bi bi-trash"></i> Hapus</button></form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8">
            <div class="empty-state">
              <i class="bi bi-megaphone"></i>
              <strong>Belum ada loker</strong>
              <span>Buat lowongan pertama untuk mulai menerima lamaran.</span>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
(function(){
  document.querySelectorAll('[data-copy-link]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var text = btn.getAttribute('data-copy-link');
      var done = function(){
        var old = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
        setTimeout(function(){ btn.innerHTML = old; }, 1600);
      };
      var fallback = function(){
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch(e) {}
        document.body.removeChild(ta);
      };
      if(navigator.clipboard && window.isSecureContext){
        navigator.clipboard.writeText(text).then(done).catch(fallback);
      } else {
        fallback();
      }
    });
  });
})();
</script>
@endpush
@endsection
