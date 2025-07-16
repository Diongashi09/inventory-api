<?php

namespace App\Http\Controllers;

use App\Models\VendorCompany;
use Illuminate\Http\Request;
use App\Http\Resources\VendorCompanyResource;
use Illuminate\Validation\Rule;


class VendorCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(VendorCompany::class,'vendor_company');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VendorCompany::query();

        // --- NEW: Handle search query ---
        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('contact_person', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            // Add more fields to search if needed, e.g., ->orWhere('address', 'like', '%' . $search . '%')
        }

        $vendorCompanies = $query->get(); // Fetch results after applying filters
        return VendorCompanyResource::collection($vendorCompanies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_companies,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:vendor_companies,email',
            'address' => 'nullable|string|max:500',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        $vendorCompany = VendorCompany::create($data);
        return response()->json(new VendorCompanyResource($vendorCompany), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorCompany $vendorCompany)
    {
        return new VendorCompanyResource($vendorCompany);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorCompany $vendorCompany)
    {
        $data = $request->validate([
            'name' => [
                'sometimes', // 'name' is optional for update
                'required',  // but if present, it must be required
                'string',
                'max:255',
                Rule::unique('vendor_companies')->ignore($vendorCompany->id), // Ignore current vendor company ID
            ],
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('vendor_companies')->ignore($vendorCompany->id), // Ignore current vendor company ID
            ],
            'address' => 'nullable|string|max:500',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        $vendorCompany->update($data);
        return response()->json(new VendorCompanyResource($vendorCompany));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorCompany $vendorCompany)
    {
        $vendorCompany->delete();
        return response()->json(null, 204); // 204 No Content
    }
}