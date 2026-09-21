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
foreach ($chipDefs as $c) {
    if (!empty($c['label'])) {
        $activeChips[] = $c;
    }
}
@endphp
<div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap:6px">
  <small class="text-muted">Menampilkan <strong id="resultTotal" data-total="{{ $applicants->total() }}">{{ $applicants->total() }}</strong> lamaran · 10/halaman</small>
  @if(request('sort') === 'skor')
    <small class="text-muted"><i class="bi bi-trophy"></i> Urut skor keyword</small>
  @endif
</div>
@if(count($activeChips))
  <div class="mb-1 d-flex flex-wrap" style="gap:4px">
    @foreach($activeChips as $c)
      <button type="button" class="btn btn-sm btn-light border chip-x" data-key="{{ $c['key'] }}" style="font-size:11.5px;border-radius:16px" title="Hapus filter ini">{{ $c['label'] }} <i class="bi bi-x"></i></button>
    @endforeach
  </div>
@endif
@forelse($applicants as $a)
@php($next = isset($nextMap[$a->status]) ? $nextMap[$a->status] : null)
@php($branchName = $a->position && $a->position->branch ? $a->position->branch->name : 'LSF')
@php($meta = '<i class="bi bi-telephone"></i> ' . e($a->no_hp) . ($a->domisili ? ' <span class="sep">·</span> <i class="bi bi-geo-alt"></i> ' . e($a->domisili) : '') . (!is_null($a->umur) ? ' <span class="sep">·</span> <i class="bi bi-person"></i> ' . $a->umur . ' thn' : '') . ' <span class="sep">·</span> <i class="bi bi-briefcase"></i> ' . e($a->position->title ?? '-') . ($a->education ? ' <span class="sep">·</span> <i class="bi bi-mortarboard"></i> ' . e($a->education) : ''))
<div class="crow crow-{{ strtolower($a->status) }}">
  <div><input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check" title="Centang untuk banding"></div>
  <div class="crow-main">
    <div class="crow-l1">
      <a href="{{ route('admin.applications.show', $a->id) }}" class="crow-name">{{ $a->nama_lengkap }}</a>
      <span class="pill pill-{{ $stMap[$a->status] ?? 'baru' }}">{{ $a->status }}</span>
      <span class="tag tag-dark">{{ $branchName }}</span>
      <small class="text-muted">{{ $a->created_at->format('d/m/Y') }}</small>
    </div>
    <div class="crow-l2">
      <span>{!! $meta !!}</span>
      @if($a->sim && strtolower($a->sim) !== 'tidak punya')
        <span class="tag">SIM {{ $a->sim }}</span>
      @endif
      @if($a->willing_overtime)
        <span class="tag tag-warn"><i class="bi bi-clock-history"></i> Lembur</span>
      @endif
      @if($a->expected_salary !== null)
        <span class="tag tag-pay">Rp {{ number_format($a->expected_salary, 0, ',', '.') }}</span>
      @endif
      @if(isset($a->skor) && $a->skor !== null)
        <span class="tag tag-info"><i class="bi bi-trophy"></i> {{ $a->skor }}%</span>
      @endif
      @if(config('recruitment.ai_enabled') && !is_null($a->ai_score))
        <span class="tag tag-info"><i class="bi bi-stars"></i> {{ $a->ai_score }}%</span>
      @endif
    </div>
  </div>
  <div class="crow-act">
    <a href="{{ route('admin.applications.show', $a->id) }}" class="btn-mini btn-outline-primary" title="Buka detail">Detail</a>
    <a href="{{ route('admin.applications.download', $a->id) }}" class="btn-mini btn-outline-success" title="Unduh berkas">File</a>
    @if($next)
      <button type="button" class="btn-mini btn-advance qa-btn" data-url="{{ route('admin.applications.status', $a->id) }}" data-status="{{ $next }}" data-nama="{{ $a->nama_lengkap }}" title="Lanjut ke {{ $next }}">→ {{ $next }}</button>
    @endif
    @if($a->status !== 'Ditolak')
      <button type="button" class="btn-mini btn-outline-danger qa-btn" data-url="{{ route('admin.applications.status', $a->id) }}" data-status="Ditolak" data-nama="{{ $a->nama_lengkap }}" title="Tolak langsung">✕</button>
    @endif
  </div>
</div>
@empty
<div class="content-card p-3 text-center text-muted">
  <div style="font-size:30px"><i class="bi bi-inbox"></i></div>
  Tidak ada lamaran yang cocok. <small>Coba longgarkan filter.</small>
  @if(request()->filled('umur_range') || request()->filled('gaji_range'))
    <br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Filter umur/gaji menyembunyikan pelamar lama yang belum punya tanggal lahir / gaji.</small>
  @endif
</div>
@endforelse
<div class="mt-1 d-flex justify-content-center lam-pagination">{{ $applicants->appends(request()->query())->links() }}</div>
