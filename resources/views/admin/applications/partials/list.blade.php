@php
$stMap = ['Baru' => 'baru', 'Seleksi' => 'seleksi', 'Interview' => 'interview', 'Diterima' => 'diterima', 'Ditolak' => 'ditolak'];
$nextMap = ['Baru' => 'Seleksi', 'Seleksi' => 'Interview', 'Interview' => 'Diterima'];
$posTitle = optional($positions->firstWhere('id', (int) request('position_id')))->full_title;

$umurLabels = [
    '15-20' => '≤ 20 thn', '21-25' => '21–25 thn', '26-30' => '26–30 thn',
    '31-35' => '31–35 thn', '36-40' => '36–40 thn', '41-70' => '41+ thn',
];
$gajiLabels = [
    '0-3000000' => '< Rp 3 jt',
    '3000000-5000000' => 'Rp 3–5 jt',
    '5000000-8000000' => 'Rp 5–8 jt',
    '8000000-12000000' => 'Rp 8–12 jt',
    '12000000-1000000000' => '> Rp 12 jt',
];
$tanggalTs = request()->filled('tanggal') ? strtotime(request('tanggal')) : false;
$tanggalLabel = $tanggalTs ? date('d/m/Y', $tanggalTs) : request('tanggal');

$chipDefs = [
    ['key' => 'q', 'label' => request()->filled('q') ? 'Cari: "' . \Illuminate\Support\Str::limit(request('q'), 24) . '"' : null],
    ['key' => 'position_id', 'label' => $posTitle ? 'Posisi: ' . $posTitle : null],
    ['key' => 'status', 'label' => request()->filled('status') ? 'Status: ' . request('status') : null],
    ['key' => 'tanggal', 'label' => request()->filled('tanggal') ? 'Tanggal: ' . $tanggalLabel : null],
    ['key' => 'umur_range', 'label' => request()->filled('umur_range') ? 'Umur: ' . (isset($umurLabels[request('umur_range')]) ? $umurLabels[request('umur_range')] : request('umur_range')) : null],
    ['key' => 'gaji_range', 'label' => request()->filled('gaji_range') ? 'Gaji: ' . (isset($gajiLabels[request('gaji_range')]) ? $gajiLabels[request('gaji_range')] : request('gaji_range')) : null],
    ['key' => 'sim', 'label' => request()->filled('sim') ? 'SIM ' . request('sim') : null],
    ['key' => 'education', 'label' => request()->filled('education') ? 'Pendidikan: ' . request('education') : null],
    ['key' => 'lembur', 'label' => request('lembur') === '1' ? 'Bersedia lembur' : (request('lembur') === '0' ? 'Tidak lembur' : null)],
];
$activeChips = [];
foreach ($chipDefs as $c) {
    if (!empty($c['label'])) { $activeChips[] = $c; }
}
@endphp

<span id="resultTotal" data-total="{{ $applicants->total() }}" class="d-none">{{ $applicants->total() }}</span>

@if(count($activeChips))
<div class="lam-chip-row">
  <span class="lam-chip-label">Filter aktif:</span>
  @foreach($activeChips as $c)
    <button type="button" class="lam-chip chip-x" data-key="{{ $c['key'] }}" title="Hapus filter ini">
      {{ $c['label'] }} <i class="bi bi-x"></i>
    </button>
  @endforeach
  <button type="button" class="lam-chip lam-chip-clear chip-x" data-clear-all title="Hapus semua filter">
    <i class="bi bi-arrow-counterclockwise"></i> Hapus semua
  </button>
</div>
@endif

@forelse($applicants as $a)
@php
  $next = isset($nextMap[$a->status]) ? $nextMap[$a->status] : null;
  $branchName = $a->position && $a->position->branch ? $a->position->branch->name : 'LSF';
  $namaParts = preg_split('/\s+/', trim((string) $a->nama_lengkap));
  $initials = mb_strtoupper(mb_substr($namaParts[0], 0, 1) . (isset($namaParts[1]) ? mb_substr($namaParts[1], 0, 1) : ''));
@endphp

<div class="lam-row {{ $a->status === 'Ditolak' ? 'is-ditolak' : '' }}">
  <label class="lam-pick" title="Pilih untuk dibandingkan">
    <input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check" aria-label="Pilih {{ $a->nama_lengkap }}">
  </label>

  <div class="lam-avatar" aria-hidden="true">{{ $initials ?: '?' }}</div>

  <div class="lam-main">
    <div class="lam-head">
      <a href="{{ route('admin.applications.show', $a->id) }}" class="lam-name">{{ $a->nama_lengkap }}</a>
      <span class="lam-status is-{{ $stMap[$a->status] ?? 'baru' }}">{{ $a->status }}</span>
      <span class="lam-branch">{{ $branchName }}</span>
      <span class="lam-date" title="Tanggal masuk"><i class="bi bi-clock"></i> {{ $a->created_at->format('d/m/Y') }}</span>
    </div>

    <div class="lam-meta">
      <span class="lam-meta-item lam-pos"><i class="bi bi-briefcase"></i> {{ $a->position->title ?? '-' }}</span>
      @if($a->domisili)
        <span class="lam-meta-item"><i class="bi bi-geo-alt"></i> {{ $a->domisili }}</span>
      @endif
      @if(!is_null($a->umur))
        <span class="lam-meta-item"><i class="bi bi-person"></i> {{ $a->umur }} thn</span>
      @endif
      <span class="lam-meta-item"><i class="bi bi-telephone"></i> {{ $a->no_hp }}</span>
      @if($a->education)
        <span class="lam-meta-item"><i class="bi bi-mortarboard"></i> {{ $a->education }}</span>
      @endif
      @if($a->sim && strtolower($a->sim) !== 'tidak punya')
        <span class="lam-meta-item"><i class="bi bi-car-front"></i> SIM {{ $a->sim }}</span>
      @endif
      @if($a->willing_overtime)
        <span class="lam-meta-item"><i class="bi bi-moon-stars"></i> Bersedia lembur</span>
      @endif
      @if($a->expected_salary !== null)
        <span class="lam-meta-item lam-salary"><i class="bi bi-cash-stack"></i> Rp {{ number_format($a->expected_salary, 0, ',', '.') }}</span>
      @endif
      @if(isset($a->skor) && $a->skor !== null)
        <span class="lam-meta-item lam-score" title="Skor kecocokan keyword"><i class="bi bi-trophy"></i> {{ $a->skor }}%</span>
      @endif
      @if(config('recruitment.ai_enabled') && !is_null($a->ai_score))
        <span class="lam-meta-item lam-ai" title="Skor evaluasi AI"><i class="bi bi-stars"></i> AI {{ $a->ai_score }}%</span>
      @endif
    </div>
  </div>

  <div class="lam-actions">
    <a href="{{ route('admin.applications.show', $a->id) }}" class="lam-btn-detail">Detail</a>
    <a href="{{ route('admin.applications.download', $a->id) }}" class="lam-btn-cv" title="Unduh CV"><i class="bi bi-download"></i> CV</a>

    @if($next || $a->status !== 'Ditolak')
    <div class="dropdown">
      <button type="button" class="lam-btn-proses dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Ubah status pelamar">
        <i class="bi bi-three-dots"></i> Proses
      </button>
      <div class="dropdown-menu dropdown-menu-right">
        @if($next)
          <button type="button" class="dropdown-item qa-btn" data-url="{{ route('admin.applications.status', $a->id) }}" data-status="{{ $next }}" data-nama="{{ $a->nama_lengkap }}">
            <i class="bi bi-arrow-right-circle"></i> Maju ke {{ $next }}
          </button>
        @endif
        @if($a->status !== 'Ditolak')
          <button type="button" class="dropdown-item qa-btn text-danger" data-url="{{ route('admin.applications.status', $a->id) }}" data-status="Ditolak" data-nama="{{ $a->nama_lengkap }}">
            <i class="bi bi-x-circle"></i> Tolak pelamar
          </button>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>
@empty
<div class="lam-empty">
  <i class="bi bi-search"></i>
  @if(count($activeChips))
    <strong>Tidak ada pelamar yang cocok</strong>
    <span>Coba longgarkan filter atau hapus beberapa kriteria.</span>
    <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-counterclockwise"></i> Hapus semua filter</a>
  @else
    <strong>Belum ada lamaran masuk</strong>
    <span>Lamaran baru akan muncul di sini.</span>
  @endif
</div>
@endforelse

@if($applicants->hasPages())
<div class="lam-pagination">
  {{ $applicants->appends(request()->query())->links() }}
</div>
@endif
