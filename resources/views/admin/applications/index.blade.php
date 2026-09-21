@extends('layouts.admin')
@section('title', 'Data Lamaran')
@section('content')
<style>
.filter-side{position:sticky;top:60px;}
.filter-sect{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin:8px 0 6px;}
.filter-sect:first-child{margin-top:0;}
.candidate-card{border:1px solid var(--border);border-radius:10px;background:#fff;padding:8px 10px;margin-bottom:8px;display:flex;gap:8px;align-items:flex-start;}
.candidate-card:hover{border-color:var(--brand);box-shadow:0 1px 5px rgba(15,76,129,.15);}
.candidate-main{flex:1;min-width:0;}
.candidate-name{font-weight:700;font-size:13px;}
.candidate-name a{color:var(--text);}
.candidate-sub{font-size:11.5px;color:var(--muted);}
.candidate-tags{margin-top:3px;display:flex;flex-wrap:wrap;gap:4px;}
.candidate-side{text-align:right;flex-shrink:0;display:flex;flex-direction:column;gap:4px;align-items:flex-end;}
@media(max-width:767px){.filter-side{position:static;}}
</style>
<div class="row">
  <div class="col-md-3 mb-2">
    <div class="filter-side">
      <form method="GET" id="filterForm">
        <div class="content-card p-2 mb-2">
          <div class="filter-sect">Filter</div>
          <div class="form-group mb-2"><input name="q" class="form-control form-control-sm" placeholder="Cari nama / HP" value="{{ request('q') }}"></div>
          <div class="form-group mb-2"><select name="position_id" class="form-control form-control-sm"><option value="">Semua posisi</option>@foreach($positions as $p)<option value="{{ $p->id }}" {{ request('position_id')==$p->id?'selected':'' }}>{{ $p->full_title }}</option>@endforeach</select></div>
          <div class="form-group mb-2"><select name="status" class="form-control form-control-sm"><option value="">Semua status</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
          <div class="form-group mb-0"><input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal') }}" title="Tanggal melamar"></div>
        </div>
        <div class="content-card p-2 mb-2" style="border-left:3px solid var(--brand)">
          <div class="filter-sect">Screening · umur per hari ini</div>
          <div class="form-row">
            <div class="form-group col-6 mb-2"><label style="font-size:11px">Umur min</label><input type="number" name="umur_min" class="form-control form-control-sm" value="{{ request('umur_min') }}" min="15" max="70" placeholder="18"></div>
            <div class="form-group col-6 mb-2"><label style="font-size:11px">Umur maks</label><input type="number" name="umur_maks" class="form-control form-control-sm" value="{{ request('umur_maks') }}" min="15" max="70" placeholder="35"></div>
          </div>
          <div class="form-row">
            <div class="form-group col-6 mb-2"><label style="font-size:11px">Gaji min</label><input type="number" name="gaji_min" class="form-control form-control-sm" value="{{ request('gaji_min') }}" min="0" placeholder="Rp"></div>
            <div class="form-group col-6 mb-2"><label style="font-size:11px">Gaji maks</label><input type="number" name="gaji_max" class="form-control form-control-sm" value="{{ request('gaji_max') }}" min="0" placeholder="Rp"></div>
          </div>
          <div class="form-row">
            <div class="form-group col-6 mb-2"><label style="font-size:11px">SIM</label><select name="sim" class="form-control form-control-sm"><option value="">Semua</option>@foreach(['A','B','C','A dan C','Tidak Punya'] as $s)<option {{ request('sim')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
            <div class="form-group col-6 mb-2"><label style="font-size:11px">Pendidikan</label><select name="education" class="form-control form-control-sm"><option value="">Semua</option>@foreach(['SD','SMP','SMA/SMK','D3','D4/S1','S2','S3'] as $e)<option {{ request('education')==$e?'selected':'' }}>{{ $e }}</option>@endforeach</select></div>
          </div>
          <div class="form-group mb-2"><label style="font-size:11px">Lembur</label><select name="lembur" class="form-control form-control-sm"><option value="">Semua</option><option value="1" {{ request('lembur')==='1'?'selected':'' }}>Bersedia</option><option value="0" {{ request('lembur')==='0'?'selected':'' }}>Tidak</option></select></div>
          @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
          <button class="btn btn-sm btn-primary btn-block">Terapkan</button>
          <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light border btn-block mt-1">Reset</a>
          <a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-sm btn-outline-success btn-block mt-1"><i class="bi bi-file-earmark-excel"></i> Export CSV</a>
        </div>
      </form>
    </div>
  </div>
  <div class="col-md-9">
    <form method="POST" action="{{ route('admin.applications.bulk') }}" id="bulkForm">@csrf
    <div class="content-card p-2 mb-2">
      <div class="form-row align-items-center">
        <div class="col-auto"><div class="form-check"><input type="checkbox" class="form-check-input" id="checkAll"><label class="form-check-label" for="checkAll" style="font-size:12px">Pilih semua ({{ $applicants->total() }})</label></div></div>
        <div class="col-auto"><select name="status" class="form-control form-control-sm" required><option value="">Ubah status ke…</option>@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option>{{ $s }}</option>@endforeach</select></div>
        <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Terapkan</button></div>
        <div class="col-auto ml-auto">
          @if(request('sort') === 'skor' || request('sort') === 'ai')
          <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-down-up"></i> Normal</a>
          @else
          <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'skor'])) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-trophy"></i> Skor</a>
          <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'ai'])) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-stars"></i> AI</a>
          @endif
          <button type="button" class="btn btn-sm btn-outline-primary" id="compareBtn"><i class="bi bi-columns-gap"></i> Banding</button>
        </div>
      </div>
    </div>
    @forelse($applicants as $a)
    <div class="candidate-card">
      <div class="pt-1"><input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check"></div>
      <div class="candidate-main">
        <div class="candidate-name"><a href="{{ route('admin.applications.show', $a->id) }}">{{ $a->nama_lengkap }}</a> <small class="text-muted">#{{ $a->id }} · {{ $a->created_at->format('d/m/Y') }}</small></div>
        <div class="candidate-sub">{{ $a->no_hp }} · {{ $a->domisili ?: '-' }}</div>
        <div class="candidate-tags">
          @if($a->position && $a->position->branch)<span class="badge badge-dark badge-status">{{ $a->position->branch->name }}</span>@endif
          <span class="badge badge-light border badge-status">{{ $a->position->title ?? '-' }}</span>
          <span class="badge badge-info badge-status">{{ $a->status }}</span>
          <span class="badge badge-light border badge-status">{{ $a->umur !== null ? $a->umur.' thn' : 'umur -' }}</span>
          <span class="badge badge-light border badge-status">{{ $a->education ?: '-' }}</span>
          <span class="badge badge-light border badge-status">{{ $a->sim ?: '-' }}</span>
          <span class="badge badge-{{ $a->willing_overtime ? 'warning' : 'light' }} badge-status">{{ $a->willing_overtime ? 'Lembur Ya' : 'Lembur Tdk' }}</span>
          <span class="badge badge-success badge-status">Rp {{ $a->expected_salary !== null ? number_format($a->expected_salary, 0, ',', '.') : '-' }}</span>
          @if(isset($a->skor) && $a->skor !== null)<span class="badge badge-secondary badge-status">skor {{ $a->skor }}%</span>@endif
          @if(!is_null($a->ai_score))<span class="badge badge-primary badge-status">AI {{ $a->ai_score }}%</span>@endif
        </div>
      </div>
      <div class="candidate-side">
        <a href="{{ route('admin.applications.show', $a->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
        <a href="{{ route('admin.applications.download', $a->id) }}" class="btn btn-sm btn-outline-success">File</a>
      </div>
    </div>
    @empty
    <div class="content-card p-3 text-center text-muted">Tidak ada data.</div>
    @endforelse
    </form>
    {{ $applicants->appends(request()->query())->links() }}
  </div>
</div>
@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function(){
  document.querySelectorAll('.row-check').forEach(function(c){ c.checked = document.getElementById('checkAll').checked; });
});
document.getElementById('compareBtn').addEventListener('click', function(){
  var ids = Array.prototype.map.call(document.querySelectorAll('.row-check:checked'), function(c){ return c.value; });
  if(ids.length < 2){ alert('Centang minimal 2 lamaran untuk dibandingkan.'); return; }
  if(ids.length > 4){ alert('Maksimal 4 lamaran.'); return; }
  var q = ids.map(function(id){ return 'ids[]=' + encodeURIComponent(id); }).join('&');
  window.location.href = '{{ route('admin.applications.compare') }}?' + q;
});
</script>
@endpush
@endsection
