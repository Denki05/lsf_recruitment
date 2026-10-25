@extends('layouts.admin')
@section('title', ($link->exists ? 'Edit' : 'Buat') . ' Link Pendek')
@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">{{ $link->exists ? 'Edit Link Pendek' : 'Buat Link Pendek' }}</h1>
    <p class="page-sub">Tentukan loker tujuan, kode kustom, dan masa berlaku link.</p>
  </div>
  <div class="d-flex" style="gap:6px">
    <a href="{{ route('admin.shortlinks.create') }}" class="btn btn-sm btn-light border"><i class="bi bi-plus-lg"></i> Buat Link</a>
    <a href="{{ route('admin.shortlinks.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>
</div>
<form method="POST" action="{{ $link->exists ? route('admin.shortlinks.update', $link->id) : route('admin.shortlinks.store') }}">
  @csrf
  @if($link->exists) @method('PUT') @endif
  <div class="content-card p-3">
    <div class="form-row">
      <div class="form-group col-md-6 mb-2">
        <label>Loker *</label>
        @if($link->exists && $link->is_default)
          <select class="form-control form-control-sm" disabled>
            @foreach($positions as $p)
              @if((string) $p->id === (string) $link->position_id)
                <option selected>{{ $p->title }} — {{ $p->branch->name ?? '-' }}</option>
              @endif
            @endforeach
          </select>
          <input type="hidden" name="position_id" value="{{ $link->position_id }}">
          <small class="text-muted">Link utama terikat pada loker ini.</small>
        @else
          <select name="position_id" class="form-control form-control-sm" required>
            <option value="">-- pilih loker --</option>
            @foreach($positions as $p)
              <option value="{{ $p->id }}" {{ (string) old('position_id', $link->position_id) === (string) $p->id ? 'selected' : '' }}>{{ $p->title }} — {{ $p->branch->name ?? '-' }}{{ $p->is_active ? '' : ' (tutup)' }}</option>
            @endforeach
          </select>
          <small class="text-muted">Satu loker boleh punya beberapa link (mis. per kampanye).</small>
        @endif
      </div>
      <div class="form-group col-md-6 mb-2">
        <label for="linkCode">Kode Link</label>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text">{{ rtrim(url('/'), '/') }}/s/</span></div>
          <input id="linkCode" name="code" value="{{ old('code', $link->code) }}" class="form-control form-control-sm" maxlength="40" autocomplete="off" placeholder="{{ $link->exists ? 'kosongkan = tetap' : 'kosongkan = acak' }}" pattern="[A-Za-z0-9][A-Za-z0-9-]*" title="Huruf, angka, dan tanda hubung">
          <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" id="randomCodeBtn" title="Isi kode acak 5 karakter"><i class="bi bi-shuffle"></i> Acak</button>
          </div>
        </div>
        <small class="text-muted">
          Klik <strong>Acak</strong> untuk kode 5 karakter, lalu Simpan.
          @if($link->exists) Mengubah kode mematikan link lama. @else Kosongkan juga otomatis acak. @endif
        </small>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-4 mb-2">
        <label>Berlaku Sampai</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $link->expires_at ? $link->expires_at->format('Y-m-d\TH:i') : '') }}" class="form-control form-control-sm">
        <small class="text-muted">Kosongkan = tanpa batas waktu.</small>
      </div>
      <div class="form-group col-md-8 mb-2">
        <label>Akses</label>
        <div class="form-check mt-1">
          <input type="checkbox" name="is_active" value="1" class="form-check-input" id="linkAktif" {{ old('is_active', $link->exists ? $link->is_active : true) ? 'checked' : '' }}>
          <label class="form-check-label" for="linkAktif">Link aktif (bisa dibuka publik)</label>
        </div>
        <small class="text-muted">Link nonaktif atau kedaluwarsa akan menampilkan halaman 404.</small>
      </div>
    </div>

    @if($link->exists)
    <div class="alert alert-light border mb-2">
      <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:8px">
        <div class="text-break">
          <small class="text-muted d-block">Link saat ini</small>
          <a href="{{ $link->url }}" target="_blank" rel="noopener"><code>{{ $link->url }}</code></a>
          <span class="text-muted mx-1">&rarr;</span>
          <small>{{ $link->target_url }}</small>
          <div class="mt-1"><small class="text-muted">{{ number_format($link->clicks, 0, ',', '.') }} klik &middot; terakhir {{ $link->last_clicked_at ? $link->last_clicked_at->diffForHumans() : 'belum pernah' }}</small></div>
          @if($link->is_default)<span class="badge badge-info badge-status mt-1">Link utama</span>@endif
        </div>
        <a href="{{ $link->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Tes</a>
      </div>
    </div>
    @endif

    <div class="d-flex" style="gap:6px">
      <button class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Simpan</button>
      <a href="{{ route('admin.shortlinks.index') }}" class="btn btn-sm btn-light border">Batal</a>
      @if($link->exists && $link->is_default)
        <small class="text-muted ml-auto align-self-center"><i class="bi bi-lock"></i> Link utama tidak bisa dihapus; ubah kode atau nonaktifkan.</small>
      @elseif($link->exists)
        <button type="submit" form="deleteLinkForm" class="btn btn-sm btn-outline-danger ml-auto" onclick="return confirm('Hapus link {{ $link->code }}?')"><i class="bi bi-trash"></i> Hapus</button>
      @endif
    </div>
  </div>
</form>
@if($link->exists && !$link->is_default)
<form id="deleteLinkForm" method="POST" action="{{ route('admin.shortlinks.destroy', $link->id) }}">@csrf @method('DELETE')</form>
@endif
@push('scripts')
<script>
(function () {
  var btn = document.getElementById('randomCodeBtn');
  var input = document.getElementById('linkCode');
  if (!btn || !input) return;

  btn.addEventListener('click', function () {
    btn.disabled = true;
    fetch(@json(route('admin.shortlinks.randomCode')), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (res) {
        if (!res.ok) throw new Error('request failed');
        return res.json();
      })
      .then(function (data) {
        if (!data.code) throw new Error('empty code');
        input.value = data.code;
        input.focus();
      })
      .catch(function () {
        alert('Gagal membuat kode acak. Coba lagi.');
      })
      .finally(function () {
        btn.disabled = false;
      });
  });
})();
</script>
@endpush
@endsection
