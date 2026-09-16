<h3>Lamaran Baru Masuk</h3>
<p>Nama: <strong>{{ $applicant->nama_lengkap }}</strong><br>
Posisi: <strong>{{ $applicant->position->full_title }}</strong><br>
HP: {{ $applicant->no_hp }} | Domisili: {{ $applicant->domisili }} | SIM: {{ $applicant->sim }}<br>
Tanggal: {{ $applicant->created_at->format('d M Y H:i') }}</p>
<p>Lihat di panel admin: {{ url('/admin/lamaran/' . $applicant->id) }}</p>
