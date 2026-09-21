@extends('layouts.admin')
@section('title', ($position->exists ? 'Edit' : 'Tambah') . ' Loker')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<style>
.loker-sect{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--brand);margin:10px 0 8px;padding-top:10px;border-top:1px dashed var(--border);}
.loker-sect:first-of-type{border-top:none;padding-top:0;margin-top:0;}
.loker-sect .num{display:inline-flex;width:20px;height:20px;border-radius:50%;background:var(--brand);color:#fff;font-size:11px;align-items:center;justify-content:center;margin-right:6px;}
.select2-container .select2-selection--single{height:31px!important;font-size:12.5px;}
.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:29px!important;}
.select2-container--default .select2-selection--multiple{font-size:12.5px;min-height:31px;}
.select2-container .select2-selection--multiple .select2-selection__choice{font-size:12px;}
.req-row{display:flex;gap:6px;margin-bottom:6px;}
.req-row .req-num{flex-shrink:0;width:26px;height:31px;display:flex;align-items:center;justify-content:center;background:#f1f3f5;border-radius:6px;font-size:12px;font-weight:700;color:#6c757d;}
.save-bar{position:sticky;bottom:0;background:#fff;border:1px solid var(--border);border-radius:10px;padding:8px 10px;margin-top:10px;display:flex;gap:8px;box-shadow:0 -2px 10px rgba(0,0,0,.06);}
</style>
<div class="mb-2"><a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<form method="POST" action="{{ $position->exists ? route('admin.positions.update', $position->id) : route('admin.positions.store') }}" id="lokerForm">@csrf @if($position->exists) @method('PUT') @endif
<div class="content-card p-3 mb-2">
  @php($reqLines = old('requirements_lines', $position->requirements ? preg_split('/\r\n|\r|\n/', $position->requirements) : []))
  @php($simSelected = old('syarat_sim', $position->sim_list))

  <div class="loker-sect"><span class="num">1</span>Penempatan</div>
  <div class="form-row">
    <div class="form-group col-md-4 mb-2"><label>Cabang *</label><select name="branch_id" id="branchSelect" class="form-control form-control-sm" required style="width:100%"><option value="">-- pilih cabang --</option>@foreach($branches as $b)<option value="{{ $b->id }}" {{ (string) old('branch_id', $position->branch_id) === (string) $b->id ? 'selected' : '' }}>{{ $b->name }}{{ $b->location ? ' — '.$b->location : '' }}</option>@endforeach</select></div>
    <div class="form-group col-md-4 mb-2"><label>Judul Posisi *</label><input name="title" class="form-control form-control-sm" value="{{ old('title', $position->title) }}" required maxlength="100" placeholder="cth: Staf IT"></div>
    <div class="form-group col-md-4 mb-2"><label>Lokasi Kerja *</label><input name="location" class="form-control form-control-sm" value="{{ old('location', $position->location) }}" required maxlength="100" placeholder="cth: Surabaya"></div>
  </div>

  <div class="loker-sect"><span class="num">2</span>Kriteria</div>
  <div class="form-row">
    <div class="form-group col-md-3 mb-2"><label>Gaji / bulan (Rp)</label><input type="number" name="gaji" class="form-control form-control-sm" value="{{ old('gaji', $position->gaji) }}" min="0" max="1000000000" placeholder="cth: 5000000"><small class="text-muted">Kosong = nego / sesuai ekspektasi.</small></div>
    <div class="form-group col-md-3 mb-2"><label>Pendidikan Minimal</label><select name="pendidikan_minimal" id="eduSelect" class="form-control form-control-sm" style="width:100%"><option value="">Tidak disyaratkan</option>@foreach(['SD','SMP','SMA/SMK','D3','D4/S1','S2','S3'] as $e)<option value="{{ $e }}" {{ old('pendidikan_minimal', $position->pendidikan_minimal)==$e?'selected':'' }}>{{ $e }}</option>@endforeach</select></div>
    <div class="form-group col-md-2 mb-2"><label>Usia Min</label><input type="number" name="usia_min" class="form-control form-control-sm" value="{{ old('usia_min', $position->usia_min) }}" min="15" max="70" placeholder="18"></div>
    <div class="form-group col-md-2 mb-2"><label>Usia Maks</label><input type="number" name="usia_maks" class="form-control form-control-sm" value="{{ old('usia_maks', $position->usia_maks) }}" min="15" max="70" placeholder="35"></div>
    <div class="form-group col-md-2 mb-2"><label>SIM</label><select name="syarat_sim[]" id="simSelect" class="form-control form-control-sm" multiple="multiple" style="width:100%">@foreach(['A','B','C'] as $s)<option value="{{ $s }}" {{ in_array($s, (array) $simSelected) ? 'selected' : '' }}>SIM {{ $s }}</option>@endforeach</select><small class="text-muted">Kosong = tidak butuh SIM.</small></div>
  </div>
  <div class="form-check"><input type="checkbox" name="butuh_lembur" value="1" class="form-check-input" id="butuhLembur" {{ old('butuh_lembur', $position->butuh_lembur) ? 'checked' : '' }}><label class="form-check-label" for="butuhLembur">Posisi ini butuh <strong>bersedia lembur</strong> di luar jam kerja</label></div>

  <div class="loker-sect"><span class="num">3</span>Deskripsi & Requirement</div>
  <div class="form-group mb-2"><label>Deskripsi Pekerjaan</label><textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Tulis tugas harian dengan kata natural, cth: Memberikan dukungan teknis terkait komputer, printer, dan jaringan.">{{ old('description', $position->description) }}</textarea><small class="text-muted">Tampil di detail loker + kata kuncinya dipakai screening otomatis.</small></div>
  <div class="form-group mb-0">
    <label>Requirement <small class="text-muted">(satu baris = satu syarat, min. 3 karakter)</small></label>
    <div id="reqRows">
      @forelse((array) $reqLines as $line)
      <div class="req-row"><span class="req-num">{{ $loop->iteration }}</span><input name="requirements_lines[]" class="form-control form-control-sm" value="{{ $line }}" maxlength="500" placeholder="cth: Pendidikan minimal D3/S1 Teknologi Informasi"><button type="button" class="btn btn-sm btn-outline-danger req-del" title="Hapus baris"><i class="bi bi-x"></i></button></div>
      @empty
      <div class="req-row"><span class="req-num">1</span><input name="requirements_lines[]" class="form-control form-control-sm" maxlength="500" placeholder="cth: Pendidikan minimal D3/S1 Teknologi Informasi"><button type="button" class="btn btn-sm btn-outline-danger req-del" title="Hapus baris"><i class="bi bi-x"></i></button></div>
      <div class="req-row"><span class="req-num">2</span><input name="requirements_lines[]" class="form-control form-control-sm" maxlength="500" placeholder="cth: Memahami jaringan dan troubleshooting"><button type="button" class="btn btn-sm btn-outline-danger req-del" title="Hapus baris"><i class="bi bi-x"></i></button></div>
      @endforelse
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="reqAdd"><i class="bi bi-plus"></i> Tambah baris</button>
  </div>

  <input type="hidden" name="skill_tags" value="{{ old('skill_tags', $position->skill_tags) }}">
  <input type="hidden" name="requirements" value="{{ old('requirements', $position->requirements) }}" id="reqLegacy">
  <div class="form-check mt-2"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="aktif" {{ old('is_active', $position->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="aktif">Tampilkan di situs (aktif)</label></div>
</div>
<div class="save-bar">
  <button class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Simpan Loker</button>
  <a href="{{ route('admin.positions.index') }}" class="btn btn-sm btn-light border">Batal</a>
</div>
</form>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
(function(){
  if(window.jQuery && jQuery.fn.select2){
    jQuery('#branchSelect').select2({ placeholder: '-- pilih cabang --', allowClear: false, width: '100%' });
    jQuery('#eduSelect').select2({ placeholder: 'Tidak disyaratkan', allowClear: true, width: '100%' });
    jQuery('#simSelect').select2({ placeholder: 'Tidak butuh SIM (boleh multi-pilih)', allowClear: true, width: '100%' });
  }
  var rows = document.getElementById('reqRows');
  function renumber(){
    rows.querySelectorAll('.req-row').forEach(function(r, i){
      r.querySelector('.req-num').textContent = i + 1;
    });
  }
  document.getElementById('reqAdd').addEventListener('click', function(){
    var div = document.createElement('div');
    div.className = 'req-row';
    div.innerHTML = '<span class="req-num"></span><input name="requirements_lines[]" class="form-control form-control-sm" maxlength="500" placeholder="cth: Fresh graduate dipersilakan melamar"><button type="button" class="btn btn-sm btn-outline-danger req-del" title="Hapus baris"><i class="bi bi-x"></i></button>';
    rows.appendChild(div); renumber();
    div.querySelector('input').focus();
  });
  rows.addEventListener('click', function(e){
    var btn = e.target.closest('.req-del');
    if(!btn) return;
    if(rows.querySelectorAll('.req-row').length <= 1){
      rows.querySelector('input').value = '';
      return;
    }
    btn.closest('.req-row').remove(); renumber();
  });
  renumber();
})();
</script>
@endpush
@endsection
