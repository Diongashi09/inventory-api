<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Client::class, 'client', [
            'except' => ['showClientProfile', 'updateClientProfile']
        ]);
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
            'contact_person'  => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($request->input('client_type') === 'company'), // contact_person required if company
            ],
            'phone'           => 'nullable|string',
            'email'           => 'nullable|email',
            'address'         => 'nullable|string',
            'additional_info' => 'nullable|string',
        ]);

        $client = Client::create($data);

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
            'contact_person'  => [
                'nullable',
                'string',
                'max:255',
                // Rule::requiredIf($request->input('client_type') === 'company'), // This validation needs careful consideration for updates
            ],
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


    public function showClientProfile(Request $request)
    {
        $user = $request->user();

        // Load the client relationship when fetching the user's data
        // The /user endpoint should also load 'client' relationship for consistency
        $user->loadMissing('client'); // Ensures 'client' is loaded if not already

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'isClient' => $user->isClient(),
            'hasClientProfile' => $user->client !== null,
            'client' => $user->client,
        ]);

        // if ($user->isClient() && $user->client) {
        //     // Eager load related data if needed for the profile view
        //     return response()->json($user->client->load('invoices', 'supplies')); // Assuming these relations are needed
        // }

        // // If user is not a client or doesn't have a linked client profile
        // return response()->json(['message' => 'Client profile not found or not authorized.'], 404);
    }

    public function updateClientProfile(Request $request)
    {
        $user = $request->user();

        // Ensure the authenticated user has the 'Client' role
        if (!$user->isClient()) {
            return response()->json(['message' => 'Unauthorized. Not a client user.'], 403);
        }

        // Ensure the authenticated user has a linked client profile
        if (!$user->client) {
            return response()->json(['message' => 'Client profile not found for this user.'], 404);
        }

        $client = $user->client;

        // Manually authorize this specific action if the Policy doesn't cover this specific method name
        $this->authorize('update', $client); // Ensure your ClientPolicy has an 'update' method

        $validatedData = $request->validate([
            // 'email' has been REMOVED from here to prevent client from updating it.
            'name'            => 'sometimes|required|string|max:255', // If client_type is individual, this is the client's name
            'contact_person'  => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($client->client_type === 'company' && $request->input('contact_person') !== null), // If company, contact_person is required if it's being set/updated
            ],
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string',
            'additional_info' => 'nullable|string',
        ]);

        // Sync users.name with clients.name/contact_person if updated
        // This part needs to remain as it updates the user's name for consistency
        if ($client->client_type === 'individual' && isset($validatedData['name']) && $user->name !== $validatedData['name']) {
            $user->update(['name' => $validatedData['name']]);
        } elseif ($client->client_type === 'company' && isset($validatedData['contact_person']) && $user->name !== $validatedData['contact_person']) {
            $user->update(['name' => $validatedData['contact_person']]);
        }

        $client->update($validatedData);

        return response()->json(['message' => 'Profile updated successfully.', 'client' => $client->fresh()], 200);
    }
}