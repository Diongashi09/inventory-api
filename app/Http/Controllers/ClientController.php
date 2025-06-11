<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Client::class, 'client');
    }

    public function index()
    {
        return Client::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string',
            'client_type'     => 'required|in:individual,company',
            'contact_person'  => 'nullable|string',
            'phone'           => 'nullable|string',
            'email'           => 'nullable|email',
            'address'         => 'nullable|string',
            'additional_info' => 'nullable|string',
        ]);

        return response()->json(Client::create($data), 201);
    }

    public function show(Client $client)
    {
        return $client;
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'            => 'sometimes|required|string',
            'client_type'     => 'in:individual,company',
            'contact_person'  => 'nullable|string',
            'phone'           => 'nullable|string',
            'email'           => 'nullable|email',
            'address'         => 'nullable|string',
            'additional_info' => 'nullable|string',
        ]);

        $client->update($data);
        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(null, 204);
    }
}
