<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function edit()
    {
        $company = auth()->user()->company;
        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'ruc' => 'required|string|max:11',
            'business_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $company = auth()->user()->company;

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($validated);

        return back()->with('success', 'Datos de la empresa actualizados.');
    }
}
