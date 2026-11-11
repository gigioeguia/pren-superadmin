<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcnooClientController extends Controller
{
    use HasAcnooActions;
    use HasUploader;

    public function index(Request $request)
    {
        $clients = Client::latest()->paginate(10);

        return view('admin.clients.index', compact('clients'));
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
            'image' => $request->image ? $this->upload($request, 'image') : null,
        ]);

        return response()->json([
            'message' => __('Client created successfully'),
            'redirect' => route('admin.clients.index'),
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
            'redirect' => route('admin.clients.index'),
        ]);
    }

    public function destroy(Client $client)
    {
        if (file_exists($client->image)) {
            Storage::delete($client->image);
        }
        $client->delete();

        return response()->json([
            'message' => __('Client deleted successfully'),
            'redirect' => route('admin.clients.index'),
        ]);
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Client::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return Client::when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%');
            });
        })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.clients.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['clients' => $rows];
    }

    protected function acnooDeletesRowByRow(): bool
    {
        return true;
    }

    protected function deletingAcnooRow($row): void
    {
        if (file_exists($row->image)) {
            Storage::delete($row->image);
        }
    }

    protected function acnooRedirectRoute(): string
    {
        return 'admin.clients.index';
    }

    protected function acnooStatusMessage(): string
    {
        return 'Client ';
    }

    protected function acnooDeleteMessage(): string
    {
        return __('Selected Client deleted successfully');
    }
}
