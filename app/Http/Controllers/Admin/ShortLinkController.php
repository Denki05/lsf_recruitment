<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Position;
use App\Services\BranchAccess;
use App\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShortLinkController extends Controller
{
    public function index(Request $request)
    {
        $ids = BranchAccess::scopePositions(Position::query()->select('positions.id'))->pluck('id');
        $query = ShortLink::with('position.branch')->whereIn('position_id', $ids);

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($sub) use ($term) {
                $sub->where('code', 'like', '%' . $term . '%')
                    ->orWhereHas('position', function ($position) use ($term) {
                        $position->where('title', 'like', '%' . $term . '%');
                    });
            });
        }

        $links = $query->orderByDesc('is_default')->orderByDesc('id')
            ->paginate(20)->appends($request->query());

        return view('admin.shortlinks.index', compact('links'));
    }

    public function create()
    {
        $positions = $this->positionOptions();
        if ($positions->isEmpty()) {
            return redirect()->route('admin.shortlinks.index')
                ->withErrors(['msg' => 'Belum ada loker yang bisa Anda kelola.']);
        }
        return view('admin.shortlinks.form', ['link' => new ShortLink(), 'positions' => $positions]);
    }

    public function randomCode()
    {
        return response()->json(['code' => ShortLink::generateCode()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateLink($request);
        $position = Position::findOrFail($data['position_id']);
        BranchAccess::ensurePositionAccess($position);

        $code = !empty($data['code']) ? $data['code'] : ShortLink::generateCode();
        if (ShortLink::isReservedCode($code)) {
            return back()->withErrors(['code' => 'Kode "' . $code . '" dipakai halaman sistem. Pilih kode lain.'])->withInput();
        }

        ShortLink::create([
            'position_id' => $position->id,
            'code' => $code,
            'is_active' => $request->boolean('is_active', true),
            'expires_at' => $data['expires_at'] ?? null,
            'is_default' => false,
        ]);

        return redirect()->route('admin.shortlinks.index')
            ->with('success', 'Link pendek dibuat: ' . route('jobs.short', $code));
    }

    public function edit($id)
    {
        $link = ShortLink::with('position.branch')->findOrFail($id);
        BranchAccess::ensurePositionAccess($link->position);

        return view('admin.shortlinks.form', [
            'link' => $link,
            'positions' => $this->positionOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $link = ShortLink::with('position')->findOrFail($id);
        BranchAccess::ensurePositionAccess($link->position);

        $data = $this->validateLink($request, $link);
        if ($link->is_default && (int) $data['position_id'] !== (int) $link->position_id) {
            return back()->withErrors(['position_id' => 'Link utama tidak bisa dipindah ke loker lain.'])->withInput();
        }
        $position = Position::findOrFail($data['position_id']);
        BranchAccess::ensurePositionAccess($position);

        if (!empty($data['code'])) {
            if (ShortLink::isReservedCode($data['code'])) {
                return back()->withErrors(['code' => 'Kode "' . $data['code'] . '" dipakai halaman sistem. Pilih kode lain.'])->withInput();
            }
            $link->code = $data['code'];
        }

        $link->position_id = $position->id;
        $link->is_active = $request->boolean('is_active');
        $link->expires_at = $data['expires_at'] ?? null;
        $link->save();

        return redirect()->route('admin.shortlinks.index')->with('success', 'Link pendek diperbarui.');
    }

    public function destroy($id)
    {
        $link = ShortLink::with('position')->findOrFail($id);
        BranchAccess::ensurePositionAccess($link->position);

        if ($link->is_default) {
            return back()->withErrors(['msg' => 'Link utama tiap loker tidak bisa dihapus. Gunakan tombol Acak untuk mengganti kode atau nonaktifkan link.']);
        }

        $link->delete();
        return redirect()->route('admin.shortlinks.index')->with('success', 'Link pendek dihapus.');
    }

    protected function positionOptions()
    {
        return BranchAccess::scopePositions(Position::with('branch')->orderBy('title')->orderBy('id'))->get();
    }

    protected function validateLink(Request $request, ShortLink $link = null)
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtolower(trim((string) $request->input('code')))]);
        }

        $codeRules = ['nullable', 'string', 'max:40', 'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/'];
        if ($link) {
            $codeRules[] = Rule::unique('short_links', 'code')->ignore($link->id);
        } else {
            $codeRules[] = 'unique:short_links,code';
        }

        return $request->validate([
            'position_id' => 'required|exists:positions,id',
            'code' => $codeRules,
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ], [
            'code.regex' => 'Kode hanya boleh huruf, angka, dan tanda hubung (-).',
            'code.unique' => 'Kode ini sudah dipakai link lain.',
        ]);
    }
}
