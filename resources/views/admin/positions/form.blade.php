@extends('layouts.admin')
@section('title', ($position->exists ? 'Edit' : 'Tambah') . ' Loker')
@section('content')
<div class="content-card p-3">
  <h5 class="font-weight-bold">{{ $position->exists ? 'Edit' : 'Tambah' }} Loker</h5>
  <form method="POST" action="{{ $position->exists ? route('admin.positions.update', $position->id) : route('admin.positions.store') }}">@csrf @if($position->exists) @method('PUT') @endif
    <div class="form-row">
      <div class="form-group col-md-8"><label>Judul Posisi</label><input name="title" class="form-control" value="{{ old('title', $position->title) }}" required placeholder="cth: Koordinator Gudang & GA"></div>
      <div class="form-group col-md-4"><label>Lokasi</label><input name="location" class="form-control" value="{{ old('location', $position->location) }}" required placeholder="cth: Surabaya"></div>
    </div>
    <div class="form-check mb-2"><input type="checkbox" name="butuh_sim" value="1" class="form-check-input" id="butuhSim" {{ old('butuh_sim', $position->syarat_sim) ? 'checked' : '' }}><label class="form-check-label" for="butuhSim">Posisi ini butuh SIM <small class="text-muted">(opsional — hanya jadi dasar flag screening, bukan penolakan otomatis)</small></label></div>
    <div class="form-row">
      <div class="form-group col-md-4"><label>Jenis SIM yang disyaratkan</label><select name="syarat_sim" id="syaratSim" class="form-control"><option value="">-- pilih --</option>@foreach(['A','B','C'] as $s)<option value="{{ $s }}" {{ old('syarat_sim', $position->syarat_sim)==$s?'selected':'' }}>Wajib SIM {{ $s }}</option>@endforeach</select></div>
    </div>
    <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="3">{{ old('description', $position->description) }}</textarea></div>
    <div class="form-check mb-3"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="aktif" {{ old('is_active', $position->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="aktif">Aktif (tampil di form pelamar)</label></div>
    <button class="btn btn-primary">Simpan</button> <a href="{{ route('admin.positions.index') }}" class="btn btn-light border">Batal</a>
  </form>
</div>
@push('scripts')
<script>
(function(){
  var cb = document.getElementById('butuhSim'), sel = document.getElementById('syaratSim');
  function sync(){ sel.disabled = !cb.checked; if(!cb.checked) sel.value = ''; }
  cb.addEventListener('change', sync); sync();
})();
</script>
@endpush
@endsection
