@extends('layouts.admin')
@section('title', 'Data Lamaran')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<style>
.screening-card{background:#fff;border:1px solid var(--border);border-radius:12px;position:sticky;top:60px;}
.screening-head{padding:7px 10px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.screening-head b{font-size:12px;text-transform:uppercase;letter-spacing:.5px;}
.screening-body{padding:8px 10px;}
.screening-body .form-group{margin-bottom:6px;}
.toolbar-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:6px 8px;margin-bottom:8px;}
.scr-label{font-size:11px;font-weight:700;color:var(--muted);margin-bottom:3px;display:block;}
.select2-container .select2-selection--single{height:31px!important;font-size:12.5px;}
.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:29px!important;}
.toolbar-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:8px 10px;margin-bottom:10px;}
.bulk-sticky{position:sticky;top:58px;z-index:60;box-shadow:0 4px 14px rgba(16,38,76,.10);}
.fbtn{position:relative;}
.fbadge{position:absolute;top:-7px;right:-7px;background:#c81e3a;color:#fff;font-size:10px;font-weight:800;border-radius:12px;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px;}
.crow{display:flex;gap:8px;align-items:center;border:1px solid var(--border);border-left:4px solid #c6d2e0;border-radius:10px;background:#fff;padding:5px 10px;margin-bottom:5px;transition:border-color .15s,box-shadow .15s;}
.crow:hover{border-color:#b9cdf5;box-shadow:0 3px 12px rgba(15,76,129,.12);}
.crow-baru{border-left-color:#0d6efd;}
.crow-seleksi{border-left-color:#e0a800;}
.crow-interview{border-left-color:#17a2b8;}
.crow-diterima{border-left-color:#28a745;}
.crow-ditolak{border-left-color:#dc3545;opacity:.72;}
.crow-main{flex:1;min-width:0;}
.crow-l1{display:flex;align-items:center;gap:7px;flex-wrap:wrap;}
.crow-name{font-weight:700;font-size:13px;color:var(--text);}
.crow-name:hover{color:var(--brand);}
.crow-l2{font-size:11.5px;color:var(--muted);margin-top:1px;display:flex;flex-wrap:wrap;gap:3px 7px;align-items:center;}
.crow-l2 i{color:#9aa7b5;}
.crow-l2 .sep{margin:0 1px;color:#c3ccd5;}
.crow-act{flex-shrink:0;display:flex;gap:4px;align-items:center;}
.btn-mini{font-size:11px;font-weight:700;padding:3px 8px;border-radius:7px;border:1px solid;background:#fff;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;gap:3px;text-decoration:none;}
.btn-mini:hover{text-decoration:none;}
.btn-mini.btn-outline-primary{color:var(--brand);border-color:var(--brand);}
.btn-mini.btn-outline-primary:hover{background:var(--brand);color:#fff;}
.btn-mini.btn-outline-success{color:#146c43;border-color:#146c43;}
.btn-mini.btn-outline-success:hover{background:#146c43;color:#fff;}
.btn-mini.btn-outline-success{color:#146c43;border-color:#146c43;}
.btn-mini.btn-outline-success:hover{background:#146c43;color:#fff;}
.btn-mini.btn-outline-danger{color:#c81e3a;border-color:#c81e3a;}
.btn-mini.btn-outline-danger:hover{background:#c81e3a;color:#fff;}
.btn-mini.btn-advance{color:#fff;background:#146c43;border-color:#146c43;}
.btn-mini.btn-advance:hover{background:#0f5335;color:#fff;}
.btn-mini:disabled{opacity:.5;cursor:default;}
.pill{font-size:10.5px;font-weight:800;border-radius:20px;padding:3px 10px;}
.pill-baru{background:#e3efff;color:#0d6efd;}
.pill-seleksi{background:#fff3d1;color:#96690a;}
.pill-interview{background:#ddf5f8;color:#0e7488;}
.pill-diterima{background:#dcf5e5;color:#146c43;}
.pill-ditolak{background:#fbdfe3;color:#c81e3a;}
.tag{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;border-radius:7px;padding:2px 8px;background:#eef2f6;color:#33465c;white-space:nowrap;}
.tag-pay{background:#e2f5e9;color:#146c43;font-weight:800;}
.tag-warn{background:#fff3d1;color:#96690a;}
.tag-info{background:#e3efff;color:#0d6efd;}
.tag-dark{background:#343a40;color:#fff;}
.cand-sub{font-size:11.5px;color:var(--muted);}
.cand-sub i{color:#9aa7b5;}
.cand-sub .sep{margin:0 2px;color:#c3ccd5;}
#lamaranList{transition:opacity .15s;}
.chip-x{font-size:11.5px;border-radius:16px;}
.lam-pagination .pagination{margin-bottom:4px;}
.lam-pagination .page-link{padding:.2rem .55rem;font-size:12px;}
#toTop{position:fixed;right:18px;bottom:22px;z-index:70;display:none;width:42px;height:42px;border-radius:50%;border:1px solid var(--border);background:#fff;color:var(--brand);box-shadow:0 4px 14px rgba(16,38,76,.18);font-size:18px;}
#toTop:hover{background:var(--brand);color:#fff;}
@media(max-width:767px){.screening-card{position:static;}}
</style>

<div class="row">
  <div class="col-md-3 mb-2">
    <form method="GET" action="{{ route('admin.applications.index') }}" id="filterForm">
      @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
      @endif
      <div class="screening-card">
        <div class="screening-head"><b><i class="bi bi-sliders"></i> Screening</b><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-link" title="Reset semua">Reset</a></div>
        <div class="screening-body">
          <div class="form-group mb-2"><span class="scr-label">Rentang umur (per hari ini)</span><select name="umur_range" id="umurRange" class="form-control form-control-sm" style="width:100%"><option value="">Semua umur</option><option value="15-20" {{ request('umur_range')=='15-20'?'selected':'' }}>≤ 20 thn</option><option value="21-25" {{ request('umur_range')=='21-25'?'selected':'' }}>21 – 25 thn</option><option value="26-30" {{ request('umur_range')=='26-30'?'selected':'' }}>26 – 30 thn</option><option value="31-35" {{ request('umur_range')=='31-35'?'selected':'' }}>31 – 35 thn</option><option value="36-40" {{ request('umur_range')=='36-40'?'selected':'' }}>36 – 40 thn</option><option value="41-70" {{ request('umur_range')=='41-70'?'selected':'' }}>41+ thn</option></select></div>
          <div class="form-group mb-2"><span class="scr-label">Rentang gaji diminta</span><select name="gaji_range" id="gajiRange" class="form-control form-control-sm" style="width:100%"><option value="">Semua gaji</option><option value="0-3000000" {{ request('gaji_range')=='0-3000000'?'selected':'' }}>&lt; Rp 3 jt</option><option value="3000000-5000000" {{ request('gaji_range')=='3000000-5000000'?'selected':'' }}>Rp 3 – 5 jt</option><option value="5000000-8000000" {{ request('gaji_range')=='5000000-8000000'?'selected':'' }}>Rp 5 – 8 jt</option><option value="8000000-12000000" {{ request('gaji_range')=='8000000-12000000'?'selected':'' }}>Rp 8 – 12 jt</option><option value="12000000-1000000000" {{ request('gaji_range')=='12000000-1000000000'?'selected':'' }}>&gt; Rp 12 jt</option></select></div>
          <div class="form-row">
            <div class="form-group col-6 mb-2"><span class="scr-label">SIM</span><select name="sim" class="form-control form-control-sm auto-filter"><option value="">Semua</option>@foreach(['A','B','C','A dan C','Tidak Punya'] as $s)<option {{ request('sim')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
            <div class="form-group col-6 mb-2"><span class="scr-label">Pendidikan</span><select name="education" class="form-control form-control-sm auto-filter"><option value="">Semua</option>@foreach(['SD','SMP','SMA/SMK','D3','D4/S1','S2','S3'] as $e)<option {{ request('education')==$e?'selected':'' }}>{{ $e }}</option>@endforeach</select></div>
          </div>
          <div class="form-group mb-2"><span class="scr-label">Lembur</span><select name="lembur" class="form-control form-control-sm auto-filter"><option value="">Semua</option><option value="1" {{ request('lembur')==='1'?'selected':'' }}>Bersedia lembur</option><option value="0" {{ request('lembur')==='0'?'selected':'' }}>Tidak lembur</option></select></div>
          <div style="border-top:1px dashed var(--border);margin:6px 0"></div>
          <span class="scr-label">⚡ AKSI MASSAL — SEMUA HASIL FILTER</span>
          <div class="form-group mb-1"><select id="targetStatus" class="form-control form-control-sm"><option value="">— Pilih aksi langsung jalan —</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option>{{ $s }}</option>@endforeach</select></div>
          <small class="text-muted d-block text-center">Kena semua hasil, tanpa centang</small>
          <div style="border-top:1px dashed var(--border);margin:6px 0"></div>
          <a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-sm btn-outline-success btn-block export-link" data-base="{{ route('admin.applications.export') }}"><i class="bi bi-file-earmark-excel"></i> Export CSV</a>
        </div>
      </div>

      <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
          <div class="modal-header py-2"><h6 class="modal-title font-weight-bold"><i class="bi bi-funnel"></i> Filter</h6><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
          <div class="modal-body pt-2">
            <div class="form-group mb-2"><span class="scr-label">Cari nama / HP</span><input name="q" id="modalQ" class="form-control form-control-sm" placeholder="cth. Rina / 0812…" value="{{ request('q') }}" autocomplete="off"></div>
            <div class="form-group mb-2"><span class="scr-label">Posisi</span><select name="position_id" id="modalPos" class="form-control form-control-sm"><option value="">Semua posisi</option>@foreach($positions as $p)<option value="{{ $p->id }}" {{ (string) request('position_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->full_title }}</option>@endforeach</select></div>
            <div class="form-row">
              <div class="form-group col-6 mb-0"><span class="scr-label">Status</span><select name="status" id="modalStatus" class="form-control form-control-sm"><option value="">Semua status</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
              <div class="form-group col-6 mb-0"><span class="scr-label">Tanggal masuk</span><input type="date" name="tanggal" id="modalTgl" class="form-control form-control-sm" value="{{ request('tanggal') }}"></div>
            </div>
          </div>
          <div class="modal-footer py-2">
            <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light border">Reset</a>
            <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><i class="bi bi-check"></i> Selesai</button>
          </div>
        </div></div>
      </div>
    </form>
  </div>

  <div class="col-md-9">
    <form method="POST" action="{{ route('admin.applications.bulk') }}" id="bulkForm">@csrf
    <div class="toolbar-card bulk-sticky">
      <div class="form-row align-items-center">
        <div class="col-auto"><small class="text-muted"><i class="bi bi-check2-square"></i> Centang untuk banding</small></div>
        <div class="col-auto ml-auto">
          @php($filterKeys = ['q','position_id','status','tanggal','umur_range','gaji_range','sim','education','lembur'])
          @php($activeCount = collect($filterKeys)->filter(function($k){ return request()->filled($k); })->count())
          <button type="button" class="btn btn-sm btn-outline-primary fbtn" data-toggle="modal" data-target="#filterModal"><i class="bi bi-funnel"></i> Filter
            @if($activeCount)
              <span class="fbadge" id="filterBadge">{{ $activeCount }}</span>
            @endif
            @if(!$activeCount)
              <span class="fbadge" id="filterBadge" style="display:none">0</span>
            @endif
          </button>
          @if(request('sort') === 'skor')
          <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}" class="btn btn-sm btn-secondary sort-link" data-sort=""><i class="bi bi-arrow-down-up"></i> Normal</a>
          @else
          <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'skor'])) }}" class="btn btn-sm btn-outline-secondary sort-link" data-sort="skor"><i class="bi bi-trophy"></i> Skor</a>
          @endif
          @if(config('recruitment.ai_enabled'))
            @if(request('sort') === 'ai')
            <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}" class="btn btn-sm btn-secondary sort-link" data-sort=""><i class="bi bi-arrow-down-up"></i> Normal</a>
            @else
            <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'ai'])) }}" class="btn btn-sm btn-outline-secondary sort-link" data-sort="ai"><i class="bi bi-stars"></i> AI</a>
            @endif
          @endif
          <button type="button" class="btn btn-sm btn-outline-primary" id="compareBtn"><i class="bi bi-columns-gap"></i> Banding</button>
        </div>
      </div>
    </div>
    <div id="lamaranList">
      @include('admin.applications.partials.list')
    </div>
    </form>
  </div>
</div>
<button type="button" id="toTop" title="Kembali ke atas"><i class="bi bi-arrow-up"></i></button>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  if(!window.Swal){
    window.Swal = {
      mixin: function(){ return { fire: function(o){ alert(o.title || ''); } }; },
      fire: function(o){
        o = o || {};
        if(o.showCancelButton){ return Promise.resolve({ isConfirmed: confirm((o.title || '') + '\n' + (o.text || '')) }); }
        alert((o.title || '') + '\n' + (o.text || ''));
        return Promise.resolve({ isConfirmed: true });
      }
    };
  }
  var Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2600, timerProgressBar: true });
  if(window.jQuery && jQuery.fn.select2){
    jQuery('#umurRange').select2({ width: '100%' });
    jQuery('#gajiRange').select2({ width: '100%' });
  }
  var form = document.getElementById('filterForm'),
      box = document.getElementById('lamaranList'),
      badge = document.getElementById('filterBadge'),
      expBase = document.querySelector('.export-link').dataset.base,
      filterKeys = ['q','position_id','status','tanggal','umur_range','gaji_range','sim','education','lembur'];
  function queryString(){
    return new URLSearchParams(new FormData(form)).toString();
  }
  function refreshBadge(){
    var p = new URLSearchParams(new FormData(form)), n = 0;
    filterKeys.forEach(function(k){ if((p.get(k) || '').trim() !== '') n++; });
    badge.textContent = n; badge.style.display = n ? 'inline-flex' : 'none';
  }
  function syncLinks(search){
    document.querySelectorAll('.export-link').forEach(function(a){ a.href = expBase + search; });
    document.querySelectorAll('.sort-link').forEach(function(a){
      var s = a.dataset.sort, p = new URLSearchParams(new FormData(form));
      p.delete('page');
      if(s) p.set('sort', s); else p.delete('sort');
      a.href = form.action + '?' + p.toString();
    });
  }
  function fetchList(){
    var qs = queryString();
    box.style.opacity = '.45';
    fetch(form.action + '?' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(r){ if(!r.ok) throw 0; return r.text(); })
      .then(function(html){
        box.innerHTML = html; box.style.opacity = '1';
        history.replaceState(null, '', form.action + '?' + qs);
        syncLinks('?' + qs); refreshBadge();
      })
      .catch(function(){ window.location.href = form.action + '?' + qs; });
  }
  var qt;
  // jQuery dipakai agar event change dari select2 ikut tertangkap
  jQuery(form).on('input', function(e){
    refreshBadge();
    clearTimeout(qt);
    qt = setTimeout(fetchList, e.target.name === 'q' ? 450 : 300);
  });
  jQuery(form).on('change', function(e){
    if(e.target && e.target.id === 'targetStatus') return;
    refreshBadge(); clearTimeout(qt); fetchList();
  });
  box.addEventListener('click', function(e){
    var qa = e.target.closest('.qa-btn');
    if(qa){
      if(qa.disabled) return;
      if(qa.dataset.status === 'Ditolak'){
        Swal.fire({ title: 'Tolak pelamar?', text: qa.dataset.nama + ' akan ditandai Ditolak.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#c81e3a', confirmButtonText: 'Ya, tolak', cancelButtonText: 'Batal' }).then(function(r){ if(r.isConfirmed) doQa(qa); });
      } else {
        doQa(qa);
      }
      return;
    }
    var pg = e.target.closest('.pagination a');
    if(pg){ e.preventDefault(); fetch(pg.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function(r){ return r.text(); }).then(function(html){ box.innerHTML = html; history.replaceState(null, '', pg.href); syncLinks('?' + pg.href.split('?')[1]); }).catch(function(){ window.location.href = pg.href; }); return; }
    var chip = e.target.closest('.chip-x');
    if(chip){
      var input = form.querySelector('[name="' + chip.dataset.key + '"]');
      if(input){
        if(input.tagName === 'SELECT'){ jQuery(input).val('').trigger('change.select2'); }
        else { input.value = ''; }
      }
      refreshBadge(); fetchList();
    }
  });
  document.getElementById('targetStatus').addEventListener('change', function(){
    var st = this.value;
    if(!st) return;
    var total = document.getElementById('resultTotal');
    var n = total ? total.dataset.total : '?';
    var sel = this;
    Swal.fire({ title: 'Terapkan ke hasil filter?', html: '<b>' + n + '</b> lamaran akan diubah ke "<b>' + st + '</b>".', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, jalankan', cancelButtonText: 'Batal' }).then(function(r){
      if(!r.isConfirmed){ sel.value = ''; return; }
      fetch('{{ route('admin.applications.bulkFiltered') }}?' + queryString(), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: new URLSearchParams({ target_status: st })
      })
        .then(function(rr){ if(!rr.ok) throw 0; return rr.json(); })
        .then(function(j){ sel.value = ''; Toast.fire({ icon: 'success', title: j.count + ' lamaran → ' + j.status }); fetchList(); })
        .catch(function(){ sel.value = ''; Toast.fire({ icon: 'error', title: 'Gagal menyimpan, coba lagi.' }); });
    });
  });
  function doQa(qa){
    qa.disabled = true;
    fetch(qa.dataset.url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: new URLSearchParams({ status: qa.dataset.status })
    })
      .then(function(r){ if(!r.ok) throw 0; return r.json(); })
      .then(function(j){ Toast.fire({ icon: 'success', title: j.nama + ' → ' + j.status }); fetchList(); })
      .catch(function(){ qa.disabled = false; Toast.fire({ icon: 'error', title: 'Gagal menyimpan, coba lagi.' }); });
  }
  document.getElementById('compareBtn').addEventListener('click', function(){
    var ids = Array.prototype.map.call(box.querySelectorAll('.row-check:checked'), function(c){ return c.value; });
    if(ids.length < 2){ Swal.fire({ icon: 'info', title: 'Centang 2–4 lamaran dulu', text: 'Banding butuh minimal 2 kandidat.' }); return; }
    if(ids.length > 4){ Swal.fire({ icon: 'info', title: 'Kebanyakan', text: 'Maksimal 4 lamaran untuk dibanding.' }); return; }
    window.location.href = '{{ route('admin.applications.compare') }}?' + ids.map(function(id){ return 'ids[]=' + encodeURIComponent(id); }).join('&');
  });
  var toTop = document.getElementById('toTop');
  window.addEventListener('scroll', function(){
    toTop.style.display = window.pageYOffset > 450 ? 'block' : 'none';
  });
  toTop.addEventListener('click', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });
})();
</script>
@endpush
@endsection
