@extends('layouts.public')
@section('title', 'Form Lamaran | LSF')

@section('content')
<style>
  /* Container diperlebar sedikit agar muat 2 kolom di desktop */
  .apply-container { max-width: 960px; margin: 0 auto; }
  
  /* Penipisan Padding & Margin pada Card */
  .form-section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
  }
  .form-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #f1f5f9;
  }
  .form-section-header i { font-size: 15px; color: #0f4c81; background: #eef6ff; padding: 6px; border-radius: 6px; }
  .form-section-title { font-size: 13.5px; font-weight: 700; color: #1e293b; margin: 0; }
  .form-section-subtitle { font-size: 11px; color: #64748b; margin: 0; }

  /* Penipisan Input & Label */
  .form-label-custom { font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 2px; }
  .form-control-custom {
    border-radius: 6px; border: 1px solid #cbd5e1;
    padding: 4px 8px; font-size: 12.5px; height: 32px;
  }
  .form-control-custom:focus { border-color: #0f4c81; box-shadow: 0 0 0 2px rgba(15, 76, 129, 0.1); }

  /* Pengalaman Dinamis */
  .exp-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 8px; }
  .exp-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }

  /* Dropzone */
  .dz-custom {
    border: 1.5px dashed #cbd5e1; background: #f8fafc; border-radius: 8px;
    padding: 14px; text-align: center; cursor: pointer; transition: all 0.2s;
  }
  .dz-custom:hover { border-color: #0f4c81; background: #f0f7ff; }
</style>

<div class="apply-container">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-light border py-1 px-2" style="font-size:12px;"><i class="bi bi-arrow-left"></i> Kembali</a>
    <span class="text-muted" style="font-size: 12px;">Isi singkat (< 2 menit) + upload CV.</span>
  </div>

  <form method="POST" action="{{ route('lamaran.store') }}" enctype="multipart/form-data" id="lamaranForm">
    @csrf
    <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden" aria-hidden="true">
      <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
    </div>

    <!-- SEKSI 1: POSISI DILAMAR (Full Width tapi Tipis) -->
    <div class="form-section-card py-2 px-3">
      <div class="row align-items-center">
        <div class="col-md-3 col-12 mb-2 mb-md-0"><strong style="font-size:13px;"><i class="bi bi-briefcase-fill text-primary"></i> Posisi Dilamar:</strong></div>
        <div class="col-md-9 col-12">
          @if(!empty($selectedPosition))
            <div class="d-flex justify-content-between align-items-center bg-light border rounded px-2 py-1">
              <span style="font-size:13px; font-weight:600;">{{ $selectedPosition->full_title }} @if($selectedPosition->branch) <small class="text-muted font-weight-normal">— {{ $selectedPosition->branch->name }}</small> @endif</span>
              <i class="bi bi-check-circle-fill text-success"></i>
            </div>
            <input type="hidden" name="position_id" value="{{ $selectedPosition->id }}">
          @else
            <select name="position_id" class="form-control form-control-custom" required>
              <option value="">-- Pilih Lowongan --</option>
              @foreach($positions as$p) <option value="{{ $p->id }}" {{ old('position_id')==$p->id?'selected':'' }}>{{$p->full_title }}</option> @endforeach
            </select>
          @endif
        </div>
      </div>
    </div>

    <div class="row">
      <!-- KOLOM KIRI (Data Pribadi & Pengalaman) -->
      <div class="col-lg-7">
        
        <!-- DATA PRIBADI -->
        <div class="form-section-card">
          <div class="form-section-header">
            <i class="bi bi-person-badge-fill"></i>
            <div><h6 class="form-section-title">Informasi Pribadi</h6></div>
          </div>
          <div class="form-row">
            <div class="col-md-6 mb-2"><label class="form-label-custom required">Nama Lengkap</label><input name="nama_lengkap" class="form-control form-control-custom" value="{{ old('nama_lengkap') }}" required maxlength="100"></div>
            <div class="col-md-6 mb-2"><label class="form-label-custom">Email</label><input type="email" name="email" class="form-control form-control-custom" value="{{ old('email') }}" placeholder="opsional" maxlength="150"></div>
            <div class="col-md-4 mb-2"><label class="form-label-custom required">No HP / WA</label><input name="no_hp" class="form-control form-control-custom" value="{{ old('no_hp') }}" required></div>
            <div class="col-md-4 mb-2"><label class="form-label-custom required">Tgl Lahir</label><input type="date" name="tanggal_lahir" class="form-control form-control-custom" value="{{ old('tanggal_lahir') }}" required max="{{ date('Y-m-d') }}"></div>
            <div class="col-md-4 mb-2"><label class="form-label-custom required">Kelamin</label><select name="jenis_kelamin" class="form-control form-control-custom" required><option value="">Pilih</option><option {{ old('jenis_kelamin')=='Laki-laki'?'selected':'' }}>Laki-laki</option><option {{ old('jenis_kelamin')=='Perempuan'?'selected':'' }}>Perempuan</option></select></div>
            <div class="col-md-6 mb-1"><label class="form-label-custom required">Domisili</label><input name="domisili" class="form-control form-control-custom" value="{{ old('domisili') }}" placeholder="cth. Surabaya" required></div>
            <div class="col-md-6 mb-1"><label class="form-label-custom required">SIM</label><select name="sim" class="form-control form-control-custom" required><option value="">Pilih</option>@foreach(['A','B','C','A dan C','Tidak Punya'] as $s)<option {{ old('sim')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
          </div>
        </div>

        <!-- PENGALAMAN KERJA -->
        <div class="form-section-card">
          <div class="form-section-header">
            <i class="bi bi-clock-history"></i>
            <div><h6 class="form-section-title">Pengalaman Kerja</h6></div>
          </div>
          <div id="dynamic-pengalaman-wrapper">
            <div id="pengalaman-list"></div>
            <button type="button" id="btnAddPengalaman" class="btn btn-sm btn-outline-primary py-0 px-2 mt-1" style="font-size:11px;"><i class="bi bi-plus-lg"></i> Tambah</button>
          </div>
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" name="belum_berpengalaman" value="1" id="belumPengalaman" class="custom-control-input" {{ old('belum_berpengalaman') ? 'checked' : '' }}>
            <label class="custom-control-label text-muted" for="belumPengalaman" style="font-size:12px">Belum punya pengalaman kerja</label>
          </div>
          <textarea name="pengalaman_kerja" id="pengalaman_hidden" style="display:none;" required>{{ old('pengalaman_kerja') }}</textarea>
        </div>
      </div>

      <!-- KOLOM KANAN (Kualifikasi, CV, Submit) -->
      <div class="col-lg-5">
        
        <!-- KUALIFIKASI -->
        <div class="form-section-card">
          <div class="form-section-header">
            <i class="bi bi-card-checklist"></i>
            <div><h6 class="form-section-title">Kualifikasi & Gaji</h6></div>
          </div>
          <div class="form-group mb-2"><label class="form-label-custom required">Pendidikan Terakhir</label><select name="education" class="form-control form-control-custom" required><option value="">-- Pilih --</option>@foreach($educations as$e)<option value="{{ $e }}" {{ old('education')==$e?'selected':'' }}>{{ $e }}</option>@endforeach</select></div>
          <div class="form-group mb-2"><label class="form-label-custom required">Permintaan Gaji / Bulan (Rp)</label><input type="number" name="expected_salary" class="form-control form-control-custom" value="{{ old('expected_salary') }}" required min="0" placeholder="cth. 5000000"></div>
          <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" name="willing_overtime" value="1" id="lembur" class="custom-control-input" {{ old('willing_overtime') ? 'checked' : '' }}>
            <label class="custom-control-label text-secondary" for="lembur" style="font-size:12px">Bersedia lembur di luar jam kerja</label>
          </div>
        </div>

        <!-- UPLOAD CV -->
        <div class="form-section-card">
          <div class="form-section-header">
            <i class="bi bi-file-earmark-arrow-up-fill"></i>
            <div><h6 class="form-section-title">Lampiran CV</h6><p class="form-section-subtitle">PDF/DOC/ZIP (Maks 5 MB)</p></div>
          </div>
          <div class="dz-custom" id="dropzone">
            <i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size:24px;"></i>
            <div class="mt-1 font-weight-bold" style="font-size:12px; color:#334155;">Klik / Seret file CV ke sini</div>
            <small class="text-muted d-block mt-1" id="fileInfo" style="font-size:10.5px;">Wajib melampirkan CV.</small>
          </div>
          <input type="file" name="berkas" id="berkas" accept=".pdf,.doc,.docx,.zip" required hidden>
        </div>

        <!-- SUBMIT AREA -->
        <div class="form-section-card bg-light border-0 shadow-sm text-center">
          <div class="custom-control custom-checkbox mb-2 text-left">
            <input type="checkbox" name="ai_consent" value="1" id="aiConsent" class="custom-control-input" required {{ old('ai_consent') ? 'checked' : '' }}>
            <label class="custom-control-label text-secondary" for="aiConsent" style="font-size:11.5px">Data benar & saya menyetujui CV diproses.</label>
          </div>
          <button type="submit" class="btn btn-success btn-block font-weight-bold" style="font-size: 14px; padding: 8px;"><i class="bi bi-send-fill mr-1"></i> Kirim Lamaran</button>
        </div>

      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
// --- Script Upload CV ---
var berkasInput = document.getElementById('berkas');
var dropzone = document.getElementById('dropzone');

function handleFile(f){
  if(!f) return;
  var okExt = /\.(pdf|doc|docx|zip)$/i.test(f.name);
  if(!okExt){ alert('File harus PDF / DOC / DOCX / ZIP.'); berkasInput.value=''; return; }
  if(f.size > 5*1024*1024){ alert('File melebihi 5 MB. Kecilkan file Anda.'); berkasInput.value=''; return; }
  document.getElementById('fileInfo').innerHTML = '<span class="badge badge-success px-2 py-1"><i class="bi bi-check-circle"></i> '+f.name+'</span>';
  dropzone.style.borderColor = '#10b981';
}

dropzone.addEventListener('click', function(){ berkasInput.click(); });
berkasInput.addEventListener('change', function(){ handleFile(this.files[0]); });
['dragenter','dragover'].forEach(function(ev){ dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.classList.add('dragover'); }); });
['dragleave','drop'].forEach(function(ev){ dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.classList.remove('dragover'); }); });
dropzone.addEventListener('drop', function(e){
  if(!e.dataTransfer.files.length) return;
  berkasInput.files = e.dataTransfer.files;
  handleFile(berkasInput.files[0]);
});

// --- Script Pengalaman Dinamis ---
var wrapper = document.getElementById('dynamic-pengalaman-wrapper');
var container = document.getElementById('pengalaman-list');
var btnAdd = document.getElementById('btnAddPengalaman');
var belumCb = document.getElementById('belumPengalaman');
var hiddenInput = document.getElementById('pengalaman_hidden');
var lamaranForm = document.getElementById('lamaranForm');

function reindexPengalaman() {
  var items = container.querySelectorAll('.exp-item');
  items.forEach(function(item, index) {
    var titleEl = item.querySelector('.exp-title');
    if (titleEl) titleEl.textContent = 'Pengalaman #' + (index + 1);
    var btnRemove = item.querySelector('.btn-remove');
    if (btnRemove) btnRemove.style.display = (items.length === 1) ? 'none' : 'inline-block';
  });
}

function createPengalamanItem() {
  var div = document.createElement('div');
  div.className = 'exp-item';
  div.innerHTML = `
    <div class="exp-item-header">
      <span class="exp-title font-weight-bold text-dark" style="font-size:11.5px;">Pengalaman</span>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove py-0 px-2" style="font-size:10px;"><i class="bi bi-trash"></i></button>
    </div>
    <div class="form-row mb-1">
      <div class="col-6 mb-1"><input type="text" class="form-control form-control-custom exp-perusahaan" placeholder="Perusahaan"></div>
      <div class="col-6 mb-1"><input type="text" class="form-control form-control-custom exp-jabatan" placeholder="Jabatan"></div>
      <div class="col-12"><input type="text" class="form-control form-control-custom exp-periode mb-1" placeholder="Periode (cth. 2021-2023)"></div>
    </div>
    <textarea class="form-control form-control-custom exp-tugas" rows="1" placeholder="Tugas utama..." style="height:auto; padding:6px 8px;"></textarea>
  `;

  div.querySelector('.btn-remove').addEventListener('click', function() { div.remove(); reindexPengalaman(); });
  container.appendChild(div);
  reindexPengalaman();
}

btnAdd.addEventListener('click', createPengalamanItem);

function togglePengalaman() {
  if (belumCb.checked) {
    wrapper.style.display = 'none'; hiddenInput.removeAttribute('required');
  } else {
    wrapper.style.display = 'block'; hiddenInput.setAttribute('required', 'required');
    if (container.children.length === 0) createPengalamanItem();
  }
}

belumCb.addEventListener('change', togglePengalaman);
togglePengalaman();

// --- Submit Validator ---
lamaranForm.addEventListener('submit', function(e) {
  if (!berkasInput.files.length) { 
    e.preventDefault(); alert('Silakan lampirkan file CV terlebih dahulu.'); return; 
  }

  if (!belumCb.checked) {
    var items = container.querySelectorAll('.exp-item');
    var resultText = [];
    items.forEach(function(item, index) {
      var pt = item.querySelector('.exp-perusahaan').value.trim();
      var jbt = item.querySelector('.exp-jabatan').value.trim();
      var prd = item.querySelector('.exp-periode').value.trim();
      var tgs = item.querySelector('.exp-tugas').value.trim();
      if (pt || jbt) resultText.push((index + 1) + ". " + pt + " – " + jbt + " (" + (prd || '–') + ")\n   Tugas: " + tgs);
    });
    
    if (resultText.length === 0) {
        e.preventDefault(); alert('Isi data pengalaman kerja, atau centang "Belum punya pengalaman".'); return;
    }
    hiddenInput.value = resultText.join('\n\n');
  }
});
</script>
@endpush
@endsection