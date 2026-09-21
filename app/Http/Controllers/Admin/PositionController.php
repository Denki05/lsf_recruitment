<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use App\Http\Controllers\Controller;
use App\Position;
use App\Services\BranchAccess;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = BranchAccess::scopePositions(Position::with('branch')->withCount('applicants'))
            ->orderBy('title')->get();
        return view('admin.positions.index', compact('positions'));
    }

    protected function branchOptions()
    {
        return BranchAccess::accessibleBranches();
    }

    protected function regionOptions()
    {
        return \App\Region::ordered()->get();
    }

    protected function validateData(Request $request)
    {
        $user = auth()->user();
        $branchRule = 'required|exists:branches,id';
        $data = $request->validate([
            'branch_id' => $branchRule,
            'title' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'gaji' => 'nullable|integer|min:0|max:1000000000',
            'pendidikan_minimal' => 'nullable|in:SD,SMP,SMA/SMK,D3,D4/S1,S2,S3',
            'usia_range' => 'nullable|regex:/^\d{1,2}\s*-\s*\d{1,2}$/',
            'usia_min' => 'nullable|integer|min:15|max:70',
            'usia_maks' => 'nullable|integer|min:15|max:70|gte:usia_min',
            'butuh_lembur' => 'nullable|boolean',
            'butuh_sim' => 'nullable|boolean',
            'syarat_sim' => 'nullable|array',
            'syarat_sim.*' => 'in:A,B,C',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'requirements_lines' => 'nullable|array',
            'requirements_lines.*' => 'nullable|string|max:500',
            'skill_tags' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);
        // Akun cabang tidak boleh membuat loker di cabang lain
        if (!$user->isSuperadmin() && !$user->canAccessBranch($data['branch_id'])) {
            abort(403, 'Anda tidak punya akses ke cabang ini.');
        }
        // Rentang usia 1 input ("18-35") diurai ke usia_min / usia_maks
        if (!empty($data['usia_range']) && trim($data['usia_range']) !== '') {
            [$umin, $umaks] = array_map('intval', preg_split('/\s*-\s*/', trim($data['usia_range'])));
            if ($umin < 15 || $umin > 70 || $umaks < 15 || $umaks > 70 || $umin > $umaks) {
                return back()->withErrors(['usia_range' => 'Rentang usia tidak valid (cth: 18-35, 15–70).'])->withInput();
            }
            $data['usia_min'] = $umin;
            $data['usia_maks'] = $umaks;
        } else {
            $data['usia_min'] = $data['usia_min'] ?? null;
            $data['usia_maks'] = $data['usia_maks'] ?? null;
        }
        unset($data['usia_range']);
        // Requirement per-baris (dynamic rows) digabung jadi 1 teks per baris
        if (!empty($data['requirements_lines'])) {
            $joined = collect($data['requirements_lines'])->map(function ($l) {
                return trim((string) $l);
            })->filter(function ($l) {
                return $l !== '';
            })->values()->implode("\n");
            $data['requirements'] = $joined !== '' ? $joined : ($data['requirements'] ?? null);
        }
        unset($data['requirements_lines']);
        // Requirement baku: tiap baris min. 3 karakter
        if (!empty($data['requirements'])) {
            $lines = preg_split('/\r\n|\r|\n/', $data['requirements']);
            foreach ($lines as $l) {
                if (mb_strlen(trim($l)) > 0 && mb_strlen(trim($l)) < 3) {
                    return back()->withErrors(['requirements' => 'Tiap baris requirement min. 3 karakter, 1 poin per baris.'])->withInput();
                }
            }
        }
        return $data;
    }

    protected function normalize(array $data, Request $request)
    {
        // SIM multi-pilih: disimpan bila ada pilihan (checkbox butuh_sim opsional, auto-ikut bila ada pilihan)
        if (!empty($data['syarat_sim'])) {
            $data['syarat_sim'] = Position::normalizeSim($data['syarat_sim']);
        } else {
            $data['syarat_sim'] = null;
        }
        unset($data['butuh_sim']);
        $data['butuh_lembur'] = $request->boolean('butuh_lembur');
        $data['is_active'] = $request->boolean('is_active', true);
        if (empty($data['pendidikan_minimal'])) {
            $data['pendidikan_minimal'] = null;
        }
        if (empty($data['usia_min'])) {
            $data['usia_min'] = null;
        }
        if (empty($data['usia_maks'])) {
            $data['usia_maks'] = null;
        }
        if (empty($data['gaji'])) {
            $data['gaji'] = null;
        }
        return $data;
    }

    public function create()
    {
        $branches = $this->branchOptions();
        if ($branches->isEmpty()) {
            return redirect()->route('admin.positions.index')->withErrors(['msg' => 'Belum ada cabang yang bisa Anda kelola. Hubungi superadmin.']);
        }
        $regions = $this->regionOptions();
        return view('admin.positions.form', ['position' => new Position(), 'branches' => $branches, 'regions' => $regions]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $data = $this->normalize($data, $request);
        // update(): is_active default true saat create bila checkbox tidak dikirim
        if (!$request->has('is_active')) {
            $data['is_active'] = true;
        }
        Position::create($data);
        return redirect()->route('admin.positions.index')->with('success', 'Loker ditambahkan.');
    }

    public function edit($id)
    {
        $position = Position::findOrFail($id);
        BranchAccess::ensurePositionAccess($position);
        $branches = $this->branchOptions();
        $regions = $this->regionOptions();
        return view('admin.positions.form', compact('position', 'branches', 'regions'));
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        BranchAccess::ensurePositionAccess($position);
        $data = $this->validateData($request);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $data = $this->normalize($data, $request);
        $data['is_active'] = $request->boolean('is_active');
        $position->update($data);
        return redirect()->route('admin.positions.index')->with('success', 'Loker diperbarui.');
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        BranchAccess::ensurePositionAccess($position);
        if ($position->applicants()->count() > 0) {
            return back()->withErrors(['msg' => 'Tidak bisa dihapus: sudah ada pelamar pada posisi ini. Nonaktifkan saja.']);
        }
        $position->delete();
        return redirect()->route('admin.positions.index')->with('success', 'Loker dihapus.');
    }
}
