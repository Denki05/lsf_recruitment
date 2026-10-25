@extends('layouts.admin')
@section('title', 'Link Pendek')
@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Link Pendek</h1>
    <p class="page-sub">URL share loker dengan kode kustom, masa berlaku, QR, dan statistik klik.</p>
  </div>
  <div class="page-actions">
    <form method="GET" action="{{ route('admin.shortlinks.index') }}" class="d-flex" style="gap:6px">
      <input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kode / posisi..." style="width:200px" aria-label="Cari link pendek">
      <button class="btn btn-sm btn-light border" title="Cari"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.shortlinks.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Buat Link</a>
  </div>
</div>

<div class="alert alert-light border py-2 px-3 mb-3 d-flex align-items-start" style="gap:8px">
  <i class="bi bi-info-circle text-primary" style="margin-top:1px"></i>
  <small class="text-muted">Tiap loker punya <strong>link utama</strong> yang tidak bisa dihapus, tetapi kodenya bisa diubah atau diacak. Link tambahan bisa dibuat lewat <strong>Buat Link</strong> dan dihapus kapan saja.</small>
</div>

<div class="content-card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Link Pendek</th>
          <th>Loker / Cabang</th>
          <th class="text-center">Klik</th>
          <th>Terakhir Diklik</th>
          <th>Status</th>
          <th>Berakhir</th>
          <th class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($links as $l)
        <tr>
          <td style="max-width:260px">
            <a href="{{ $l->url }}" target="_blank" rel="noopener" class="d-block text-truncate" title="{{ $l->url }}"><code style="font-size:12px">{{ $l->url }}</code></a>
            @if($l->is_default)<span class="badge badge-info badge-status" title="Link utama tidak bisa dihapus">Utama</span>@endif
          </td>
          <td>
            <strong class="text-dark">{{ $l->position->title ?? '-' }}</strong>
            @if($l->position && (!$l->position->is_active || ($l->position->branch && !$l->position->branch->is_active)))
              <span class="badge badge-secondary badge-status">Loker tutup</span>
            @endif
            <div class="text-muted" style="font-size:11px">{{ $l->position->branch->name ?? '-' }}</div>
          </td>
          <td class="text-center"><span class="badge badge-soft">{{ number_format($l->clicks, 0, ',', '.') }}</span></td>
          <td><small class="text-muted">{{ $l->last_clicked_at ? $l->last_clicked_at->diffForHumans() : '-' }}</small></td>
          <td>
            @if($l->isExpired())
              <span class="badge badge-warning badge-status">Kedaluwarsa</span>
            @elseif(!$l->is_active)
              <span class="badge badge-secondary badge-status">Nonaktif</span>
            @else
              <span class="badge badge-success badge-status">Aktif</span>
            @endif
          </td>
          <td><small class="text-muted">{{ $l->expires_at ? $l->expires_at->format('d/m/Y H:i') : 'Tanpa batas' }}</small></td>
          <td class="text-right">
            <div class="table-actions">
              <button type="button" class="btn btn-outline-secondary" data-copy="{{ $l->url }}" title="Salin link"><i class="bi bi-clipboard"></i></button>
              <button type="button" class="btn btn-outline-dark" data-qr="{{ $l->url }}" data-qr-code="{{ $l->code }}" data-toggle="modal" data-target="#qrModal" title="QR code"><i class="bi bi-qr-code"></i></button>
              <a href="{{ route('admin.shortlinks.edit', $l->id) }}" class="btn btn-outline-primary" title="Edit link"><i class="bi bi-pencil"></i></a>
              @if(!$l->is_default)
              <form method="POST" action="{{ route('admin.shortlinks.destroy', $l->id) }}" class="d-inline m-0" onsubmit="return confirm('Hapus link {{ $l->code }}?')">@csrf @method('DELETE')<button class="btn btn-outline-danger" title="Hapus link"><i class="bi bi-trash"></i></button></form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <i class="bi bi-link-45deg"></i>
              <strong>Belum ada link pendek</strong>
              <span>Buat link untuk loker yang sudah ada.</span>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($links->hasPages())
<div class="mt-3">{{ $links->links('pagination::bootstrap-4') }}</div>
@endif

<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title mb-0" id="qrModalLabel">QR Code <span id="qrCodeName" class="text-muted"></span></h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <div id="qrBox" class="d-inline-block bg-white p-2 border rounded"></div>
        <div class="mt-2"><small class="text-muted text-break" id="qrUrl"></small></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-outline-primary" id="qrDownload"><i class="bi bi-download"></i> Unduh PNG</button>
        <button type="button" class="btn btn-sm btn-light border" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
  function copyText(text, btn){
    var done = function(){
      var old = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-check"></i>';
      setTimeout(function(){ btn.innerHTML = old; }, 1500);
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
  }
  document.querySelectorAll('[data-copy]').forEach(function(btn){
    btn.addEventListener('click', function(){ copyText(btn.getAttribute('data-copy'), btn); });
  });

  var modal = document.getElementById('qrModal');
  if(modal){
    // Bootstrap memicu event lewat jQuery (namespaced), jadi pakai jQuery juga.
    jQuery(modal).on('show.bs.modal', function(e){
      var btn = e.relatedTarget;
      var url = btn.getAttribute('data-qr');
      var code = btn.getAttribute('data-qr-code');
      document.getElementById('qrUrl').textContent = url;
      document.getElementById('qrCodeName').textContent = '/' + code;
      var box = document.getElementById('qrBox');
      box.innerHTML = '';
      if(window.QRCode){
        new QRCode(box, { text: url, width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
      } else {
        box.textContent = 'QR gagal dimuat. Salin link manual.';
      }
    });
    jQuery(modal).on('hidden.bs.modal', function(){
      document.getElementById('qrBox').innerHTML = '';
    });
  }

  var download = document.getElementById('qrDownload');
  if(download){
    download.addEventListener('click', function(){
      var canvas = document.querySelector('#qrBox canvas');
      if(!canvas){ return; }
      var a = document.createElement('a');
      a.href = canvas.toDataURL('image/png');
      a.download = 'qr-' + document.getElementById('qrCodeName').textContent.replace('/', '') + '.png';
      a.click();
    });
  }
})();
</script>
@endpush
@endsection
