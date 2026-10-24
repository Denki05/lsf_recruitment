@extends('layouts.admin')
@section('title', 'Kelola Loker')
@section('content')
<div class="content-card p-2">
  <div class="d-flex justify-content-between mb-2"><h5 class="font-weight-bold mb-0">Loker / Posisi</h5><a href="{{ route('admin.positions.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Tambah</a></div>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th>Posisi</th><th>Cabang</th><th>Lokasi</th><th>SIM</th><th>Gaji</th><th>Status</th><th>Pelamar</th><th>Aksi</th></tr>
    @foreach($positions as $p)<tr>
      <td><strong>{{ $p->title }}</strong><br><small class="text-muted">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</small></td>
      <td><span class="badge badge-dark">{{ $p->branch->name ?? '-' }}</span></td>
      <td>{{ $p->location }}</td>
      <td>{{ $p->sim_list ? 'Wajib '.implode(' / ', $p->sim_list) : '-' }}</td>
      <td>{{ $p->gaji ? 'Rp '.number_format($p->gaji, 0, ',', '.') : '-' }}</td>
      <td>{!! $p->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tutup</span>' !!}</td>
      <td><span class="badge badge-primary">{{ $p->applicants_count }}</span></td>
      <td class="text-nowrap"><a href="{{ route('admin.positions.edit', $p->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        <a href="{{ route('jobs.show', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Buka detail publik">Detail</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-copy-link="{{ $p->share_url }}" title="Salin link share pendek">Link</button>
        <form method="POST" action="{{ route('admin.positions.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('Hapus loker ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form></td>
    </tr>@endforeach
  </table></div>
</div>
@push('scripts')
<script>
(function(){
  document.querySelectorAll('[data-copy-link]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var text = btn.getAttribute('data-copy-link');
      var done = function(){
        var old = btn.textContent;
        btn.textContent = 'Tersalin!';
        setTimeout(function(){ btn.textContent = old; }, 1600);
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
