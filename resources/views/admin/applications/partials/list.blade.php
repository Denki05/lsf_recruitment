<style>
/* Clean & Minimalist Applicant Card Row */
.crow { 
  display: flex; gap: 12px; align-items: center; background: #ffffff; 
  border: 1px solid #e2e8f0; border-left: 4px solid #cbd5e1; 
  border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; 
  transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.01);
}
.crow:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(15,76,129,0.06); transform: translateY(-1px); }

/* Status Color Borders */
.crow-baru { border-left-color: #2563eb; }
.crow-seleksi { border-left-color: #d97706; }
.crow-interview { border-left-color: #0891b2; }
.crow-diterima { border-left-color: #059669; }
.crow-ditolak { border-left-color: #ef4444; opacity: 0.75; }

.crow-main { flex: 1; min-width: 0; }
.crow-l1 { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
.crow-name { font-weight: 700; font-size: 14px; color: #1e293b; text-decoration: none; }
.crow-name:hover { color: #0f4c81; text-decoration: underline; }

/* Sub-data Line (Lebih Bersih dengan Icon) */
.crow-l2 { font-size: 12px; color: #64748b; display: flex; flex-wrap: wrap; gap: 6px 12px; align-items: center; }
.crow-l2 i { color: #94a3b8; margin-right: 2px; }

/* Status Pill */
.pill { font-size: 10px; font-weight: 700; border-radius: 4px; padding: 2px 8px; text-transform: uppercase; letter-spacing: 0.3px; }
.pill-baru { background: #eff6ff; color: #2563eb; }
.pill-seleksi { background: #fef3c7; color: #d97706; }
.pill-interview { background: #ecfeff; color: #0891b2; }
.pill-diterima { background: #ecfdf5; color: #059669; }
.pill-ditolak { background: #fef2f2; color: #ef4444; }

/* Neutral Tags (Mengurangi Kejenuhan Warna) */
.tag-attr { font-size: 11px; font-weight: 500; border-radius: 4px; padding: 2px 7px; background: #f1f5f9; color: #475569; display: inline-flex; align-items: center; }
.tag-pay { background: #ecfdf5; color: #059669; font-weight: 700; border: 1px solid #a7f3d0; }
.tag-warn { background: #fef3c7; color: #b45309; font-weight: 600; }
.tag-score { background: #eff6ff; color: #1d4ed8; font-weight: 700; }

/* Action Button Group */
.crow-act { flex-shrink: 0; display: flex; gap: 4px; align-items: center; }
.btn-action-group { display: inline-flex; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; }
.btn-action-group .btn-act { border: none; background: #ffffff; color: #475569; font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-right: 1px solid #e2e8f0; text-decoration: none; transition: background 0.15s; }
.btn-action-group .btn-act:last-child { border-right: none; }
.btn-action-group .btn-act:hover { background: #f8fafc; color: #0f4c81; text-decoration: none; }

.btn-advance-soft { background: #eef6ff !important; color: #0f4c81 !important; font-weight: 700 !important; }
.btn-advance-soft:hover { background: #dbeafe !important; }
.btn-reject-icon { color: #94a3b8 !important; }
.btn-reject-icon:hover { background: #fef2f2 !important; color: #ef4444 !important; }

.chip-x { background: #ffffff; color: #475569; padding: 2px 10px; display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; }
.chip-x:hover { background: #f1f5f9; color: #0f4c81; border-color: #cbd5e1; }
.lam-pagination .pagination { margin-bottom: 4px; }
.lam-pagination .page-link { font-size: 12px; padding: 4px 10px; color: #0f4c81; }
.lam-pagination .page-item.active .page-link { background: #0f4c81; border-color: #0f4c81; }

@media (max-width: 767.98px) {
  .crow { flex-wrap: wrap; align-items: flex-start; padding: 12px; gap: 10px; }
  .crow > div:first-child { margin-top: 3px; }
  .crow-main { flex: 1 1 calc(100% - 26px); }
  .crow-l1 .ml-auto { margin-left: 0 !important; }
  .crow-act { width: 100%; padding-left: 26px; }
  .crow-act .btn-action-group { width: 100%; }
  .crow-act .btn-act { flex: 1 1 auto; justify-content: center; display: inline-flex; align-items: center; padding: 8px 10px; }
  .crow-act .btn-reject-icon { flex: 0 0 44px; }
}
</style>

@php
$stMap = ['Baru' => 'baru', 'Seleksi' => 'seleksi', 'Interview' => 'interview', 'Diterima' => 'diterima', 'Ditolak' => 'ditolak'];
$nextMap = ['Baru' => 'Seleksi', 'Seleksi' => 'Interview', 'Interview' => 'Diterima'];
$posTitle = optional($positions->firstWhere('id', (int) request('position_id')))->title;
$chipDefs = [
    ['key' => 'q', 'label' => request()->filled('q') ? 'Cari: ' . request('q') : null],
    ['key' => 'position_id', 'label' => $posTitle ? 'Posisi: ' . $posTitle : null],
    ['key' => 'status', 'label' => request()->filled('status') ? 'Status: ' . request('status') : null],
    ['key' => 'tanggal', 'label' => request()->filled('tanggal') ? 'Tgl: ' . request('tanggal') : null],
    ['key' => 'umur_range', 'label' => request()->filled('umur_range') ? 'Umur: ' . request('umur_range') : null],
    ['key' => 'gaji_range', 'label' => request()->filled('gaji_range') ? 'Gaji: ' . request('gaji_range') : null],
    ['key' => 'sim', 'label' => request()->filled('sim') ? 'SIM ' . request('sim') : null],
    ['key' => 'education', 'label' => request()->filled('education') ? request('education') : null],
    ['key' => 'lembur', 'label' => request('lembur') === '1' ? 'Bersedia lembur' : (request('lembur') === '0' ? 'Tidak lembur' : null)],
];
$activeChips = [];
foreach ($chipDefs as $c) { if (!empty($c['label'])) $activeChips[] = $c; }
@endphp

<!-- Header Total -->
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:6px">
  <small class="text-muted" style="font-size: 11.5px;">
    Total <strong id="resultTotal" data-total="{{ $applicants->total() }}" class="text-dark">{{ $applicants->total() }}</strong> pelamar ditemukan
  </small>
  @if(request('sort') === 'skor')
    <small class="text-warning font-weight-bold" style="font-size: 11px;"><i class="bi bi-trophy-fill"></i> Urut berdasarkan skor</small>
  @endif
</div>

<!-- Active Filter Chips -->
@if(count($activeChips))
  <div class="mb-2 d-flex flex-wrap" style="gap:6px">
    @foreach($activeChips as $c)
      <button type="button" class="btn btn-sm border chip-x shadow-sm" data-key="{{ $c['key'] }}" style="font-size:11px;border-radius:12px" title="Hapus filter ini">
        {{ $c['label'] }} <i class="bi bi-x-circle-fill ml-1 text-muted"></i>
      </button>
    @endforeach
  </div>
@endif

<!-- List Pelamar -->
@forelse($applicants as $a)
@php
  $next = isset($nextMap[$a->status]) ? $nextMap[$a->status] : null;
  $branchName = $a->position && $a->position->branch ? $a->position->branch->name : 'LSF';
@endphp

<div class="crow crow-{{ strtolower($a->status) }}">
  <!-- Checkbox Banding -->
  <div>
    <input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check" title="Centang untuk banding" style="cursor: pointer; width: 14px; height: 14px;">
  </div>
  
  <div class="crow-main">
    <!-- Baris 1: Nama, Status, Cabang, Tanggal -->
    <div class="crow-l1">
      <a href="{{ route('admin.applications.show', $a->id) }}" class="crow-name">{{ $a->nama_lengkap }}</a>
      <span class="pill pill-{{ $stMap[$a->status] ?? 'baru' }}">{{ $a->status }}</span>
      <span class="badge bg-light text-dark border" style="font-size: 10px; font-weight: 600;">{{ $branchName }}</span>
      <span class="text-muted ml-auto mr-1" style="font-size: 11px;"><i class="bi bi-clock"></i> {{ $a->created_at->format('d/m/Y') }}</span>
    </div>
    
    <!-- Baris 2: Atribut & Metadata (Ikon Netral) -->
    <div class="crow-l2">
      <span><i class="bi bi-telephone"></i> {{ $a->no_hp }}</span>
      <span><i class="bi bi-geo-alt"></i> {{ $a->domisili ?: '-' }}</span>
      @if(!is_null($a->umur))
        <span><i class="bi bi-person"></i> {{ $a->umur }} thn</span>
      @endif
      
      <span class="text-dark font-weight-bold"><i class="bi bi-briefcase"></i> {{ $a->position->title ?? '-' }}</span>
      
      @if($a->education)
        <span class="tag-attr"><i class="bi bi-mortarboard"></i> {{ $a->education }}</span>
      @endif

      @if($a->sim && strtolower($a->sim) !== 'tidak punya') 
        <span class="tag-attr">SIM {{ $a->sim }}</span> 
      @endif

      @if($a->willing_overtime) 
        <span class="tag-attr tag-warn"><i class="bi bi-moon-stars"></i> Lembur</span> 
      @endif

      @if($a->expected_salary !== null) 
        <span class="tag-attr tag-pay">Rp {{ number_format($a->expected_salary, 0, ',', '.') }}</span> 
      @endif
      
      @if(isset($a->skor) && $a->skor !== null) 
        <span class="tag-attr tag-score"><i class="bi bi-trophy-fill"></i> {{ $a->skor }}%</span> 
      @endif

      @if(config('recruitment.ai_enabled') && !is_null($a->ai_score)) 
        <span class="tag-attr tag-score"><i class="bi bi-stars"></i> {{ $a->ai_score }}%</span> 
      @endif
    </div>
  </div>
  
  <!-- Tombol Aksi Kanan (Unified Button Group) -->
  <div class="crow-act">
    <div class="btn-action-group shadow-sm">
      <a href="{{ route('admin.applications.show', $a->id) }}" class="btn-act" title="Detail Profil">Detail</a>
      <a href="{{ route('admin.applications.download', $a->id) }}" class="btn-act" title="Download CV">CV</a>
      
      @if($next)
        <button type="button" class="btn-act btn-advance-soft qa-btn" data-url="{{ route('admin.applications.status', $a->id) }}" data-status="{{ $next }}" data-nama="{{ $a->nama_lengkap }}" title="Maju ke tahap {{ $next }}">
          Maju ke {{ $next }} &rarr;
        </button>
      @endif
      
      @if($a->status !== 'Ditolak')
        <button type="button" class="btn-act btn-reject-icon qa-btn" data-url="{{ route('admin.applications.status', $a->id) }}" data-status="Ditolak" data-nama="{{ $a->nama_lengkap }}" title="Tolak Pelamar Ini"><i class="bi bi-x-lg"></i></button>
      @endif
    </div>
  </div>
</div>
@empty
<div class="compact-card p-5 text-center text-muted mt-2">
  <div style="font-size:36px; color:#cbd5e1; margin-bottom: 8px;"><i class="bi bi-search"></i></div>
  <strong class="d-block text-dark mb-1">Tidak ada pelamar yang cocok.</strong>
  <small style="font-size: 12px;">Coba atur ulang atau hilangkan beberapa kriteria filter di sebelah kiri.</small>
</div>
@endforelse

<!-- Pagination -->
<div class="mt-3 d-flex justify-content-center lam-pagination">
  {{ $applicants->appends(request()->query())->links() }}
</div>