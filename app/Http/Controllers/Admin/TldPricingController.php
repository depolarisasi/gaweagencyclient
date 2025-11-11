<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TldPricing;
use Illuminate\Http\Request;

class TldPricingController extends Controller
{
    public function index()
    {
        $search = request('search');
        $tlds = TldPricing::query()
            ->when($search, function ($q) use ($search) {
                $q->where('tld', 'like', "%$search%");
            })
            ->orderBy('tld')
            ->paginate(15);

        return view('admin.tld-pricings.index', compact('tlds', 'search'));
    }

    public function create()
    {
        return view('admin.tld-pricings.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tld' => ['required', 'string', 'max:50', 'unique:tld_pricings,tld'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        TldPricing::create($data);

        return redirect()->route('admin.tld-pricings.index')->with('success', 'TLD Pricing berhasil ditambahkan');
    }

    public function edit(TldPricing $tld_pricing)
    {
        return view('admin.tld-pricings.edit', ['tld' => $tld_pricing]);
    }

    public function update(Request $request, TldPricing $tld_pricing)
    {
        $data = $request->validate([
            'tld' => ['required', 'string', 'max:50', 'unique:tld_pricings,tld,' . $tld_pricing->id],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $tld_pricing->update($data);

        return redirect()->route('admin.tld-pricings.index')->with('success', 'TLD Pricing berhasil diperbarui');
    }

    public function destroy(TldPricing $tld_pricing)
    {
        $tld_pricing->delete();
        return redirect()->route('admin.tld-pricings.index')->with('success', 'TLD Pricing dihapus');
    }
}