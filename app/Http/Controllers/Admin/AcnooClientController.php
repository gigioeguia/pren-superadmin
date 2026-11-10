<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcnooClientController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $clients = Client::latest()->paginate(10);
        return view('admin.clients.index', compact('clients'));
    }


    public function acnooFilter(Request $request)
    {
        $clients = Client::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('title', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.clients.datas', compact('clients'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required',
            'title' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Client::create($request->except('image') + [
            'image' => $request->image ? $this->upload($request, 'image') : NULL
        ]);

        return response()->json([
            'message' => __('Client created successfully'),
            'redirect' => route('admin.clients.index')
        ]);
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'status' => 'required',
            'title' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $client->update($request->except('image') + [
            'image' => $request->image ? $this->upload($request, 'image', $client->image) : $client->image,
        ]);

        return response()->json([
            'message' => __('Client updated successfully'),
            'redirect' => route('admin.clients.index')
        ]);
    }

    public function destroy(Client $client)
    {
        if (file_exists($client->image)) {
            Storage::delete($client->image);
        }
        $client->delete();

        return response()->json([
            'message'   => __('Client deleted successfully'),
            'redirect'  => route('admin.clients.index')
        ]);
    }

    public function status(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->update(['status' => $request->status]);
        return response()->json(['message' => 'Client ']);
    }


    public function deleteAll(Request $request)
    {
        $clients = Client::whereIn('id', $request->ids)->get();
        foreach ($clients as $client) {
            if (file_exists($client->image)) {
                Storage::delete($client->image);
            }
        }

        $clients->each->delete();

        return response()->json([
            'message' => __('Selected Client deleted successfully'),
            'redirect' => route('admin.clients.index')
        ]);
    }
}
