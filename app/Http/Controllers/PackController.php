<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use Illuminate\Http\Request;

class PackController extends ApiController
{
    public function index(Request $request)
    {
        $query = Pack::query();

        // Campos permitidos para filtrar y ordenar
        $filterable = ['name', 'published', 'is_free', 'price'];
        $orderable = ['id', 'name', 'published', 'is_free', 'price'];

        // Filtrado dinámico
        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        // Orden dinámico
        $orderBy = $request->input('orderBy', 'id');
        $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($orderBy, $orderable)) {
            $query->orderBy($orderBy, $order);
        }

        // Paginación (25 por página)
        $packs = $query->paginate(25);

        return $this->success([
            'packs' => $packs->items(),
            'pagination' => [
                'current_page' => $packs->currentPage(),
                'per_page' => $packs->perPage(),
                'total' => $packs->total(),
                'next_page' => $packs->nextPageUrl(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'published' => 'boolean',
        ]);

        $pack = Pack::create($validated);

        return $this->success($pack, 'Pack created', 201);
    }

    public function show($id)
    {
        $pack = Pack::find($id);

        if (!$pack) {
            return $this->error('Pack not found', 404);
        }

        return $this->success($pack);
    }

    public function update(Request $request, $id)
    {
        $pack = Pack::find($id);

        if (!$pack) {
            return $this->error('Pack not found', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'published' => 'boolean',
        ]);

        $pack->update($validated);

        return $this->success($pack, 'Pack updated');
    }

    public function destroy($id)
    {
        $pack = Pack::find($id);

        if (!$pack) {
            return $this->error('Pack not found', 404);
        }

        $pack->delete();

        return $this->success(null, 'Pack deleted', 204);
    }
}
