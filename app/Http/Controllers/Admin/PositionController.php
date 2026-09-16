<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('applicants')->orderBy('title')->get();
        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('admin.positions.form', ['position' => new Position()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Position::create($data);
        return redirect()->route('admin.positions.index')->with('success', 'Loker ditambahkan.');
    }

    public function edit($id)
    {
        $position = Position::findOrFail($id);
        return view('admin.positions.form', compact('position'));
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'location' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $position->update($data);
        return redirect()->route('admin.positions.index')->with('success', 'Loker diperbarui.');
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        if ($position->applicants()->count() > 0) {
            return back()->withErrors(['msg' => 'Tidak bisa dihapus: sudah ada pelamar pada posisi ini. Nonaktifkan saja.']);
        }
        $position->delete();
        return redirect()->route('admin.positions.index')->with('success', 'Loker dihapus.');
    }
}
