@extends('layouts.admin')
@section('title', ($position->exists ? 'Edit' : 'Tambah') . ' Loker')
@section('content')
<div class="mb-2"><a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<form method="POST" action="{{ $position->exists ? route('admin.positions.update', $position->id) : route('admin.positions.store') }}">@csrf @if($position->exists) @method('PUT') @endif
<div class="row">
  <div class="col-md-7 mb-2"><div class="content-card p-2" style="height:100%">
    <h6 class="font-weight-bold">1 · Info Dasar</h6>
    <div class="form-row">
      <div class="form-group col-md-7 mb-2"><label>Judul Posisi *</label><input name="title" class="form-control form-control-sm" value="{{ old('title', $position->title) }}" required maxlength="100" placeholder="cth: Koordinator Gudang & GA"></div>
      <div class="form-group col-md-5 mb-2"><label>Lokasi *</label><input name="location" class="form-control form-control-sm" value="{{ old('location', $position->location) }}" required maxlength="100" placeholder="cth: Surabaya"></div>
    </div>
    <div class="form-group mb-2"><label>Deskripsi Pekerjaan</label><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Gambaran tugas & tanggung jawab (tampil di kartu loker)">{{ old('description', $position->description) }}</textarea></div>
    <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="aktif" {{ old('is_active', $position->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="aktif">Tampilkan di situs (aktif)</label></div>
  </div></div>
  <div class="col-md-5 mb-2"><div class="content-card p-2" style="height:100%;border-left:3px solid var(--brand)">
    <h6 class="font-weight-bold">2 · Bahan Screening <small style="color:var(--muted)">(jadi dasar saran kecocokan)</small></h6>
    <div class="form-group mb-2"><label>Skill tags *</label><input name="skill_tags" class="form-control form-control-sm" value="{{ old('skill_tags', $position->skill_tags) }}" maxlength="500" placeholder="gudang, forklift, stock opname"><small style="color:var(--muted)">Pisahkan koma. Sumber skor utama — pakai kata/frasa yang biasa muncul di CV.</small></div>
    <div class="form-group mb-2"><label>Requirement <small style="color:var(--muted)">(1 poin per baris)</small></label><textarea name="requirements" class="form-control form-control-sm" rows="4" placeholder="Min. pengalaman 2 tahun di logistik&#10;Wajib SIM B aktif&#10;Bersedia kerja shift">{{ old('requirements', $position->requirements) }}</textarea></div>
    <div class="form-check mb-1"><input type="checkbox" name="butuh_sim" value="1" class="form-check-input" id="butuhSim" {{ old('butuh_sim', $position->syarat_sim) ? 'checked' : '' }}><label class="form-check-label" for="butuhSim">Butuh SIM <small style="color:var(--muted)">(opsional, bukan penolakan)</small></label></div>
    <div class="form-group col-md-6 mb-0 px-0"><select name="syarat_sim" id="syaratSim" class="form-control form-control-sm"><option value="">Jenis SIM…</option>@foreach(['A','B','C'] as $s)<option value="{{ $s }}" {{ old('syarat_sim', $position->syarat_sim)==$s?'selected':'' }}>SIM {{ $s }}</option>@endforeach</select></div>
  </div></div>
</div>
<button class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Simpan Loker</button>
<a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-light border">Batal</a>
</form>
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
