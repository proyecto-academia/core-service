<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassModelController extends ApiController
{
    private function getAvailableCourses(Request $request)
    {
        return $request->get('available_courses', []);
    }

    public function index(Request $request)
    {
        $query = ClassModel::query();

        // Lista blanca de filtros y ordenables
        $filterable = ['course_id', 'title'];
        $orderable = ['id', 'title', 'position', 'course_id'];

        // Filtros dinámicos
        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        // Filtrar por cursos disponibles
        $availableCourses = $this->getAvailableCourses($request);
        if (!empty($availableCourses)) {
            $query->whereIn('course_id', $availableCourses);
        }

        // Orden dinámico
        $orderBy = $request->input('orderBy', 'position');
        $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($orderBy, $orderable)) {
            $query->orderBy($orderBy, $order);
        }

        $perPage = 24;
        $classes = $query->paginate($perPage);

        return $this->success([
            'available_courses' => $availableCourses,
            'classes' => $classes->items(),
            'pagination' => [
                'current_page' => $classes->currentPage(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
                'next_page' => $classes->nextPageUrl(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $class = ClassModel::create($validated);

        return $this->success($class, 'Class created', 201);
    }

    public function show($id)
    {
        $availableCourses = $this->getAvailableCourses(request());
        $class = ClassModel::find($id);
        if ($class && !in_array($class->course_id, $availableCourses)) {
            return $this->error('You don\'t have access to this class.', 401);
        }

        if (!$class) {
            return $this->error('Class not found', 404);
        }

        return $this->success($class);
    }

    public function update(Request $request, $id)
    {
        $class = ClassModel::find($id);

        if (!$class) {
            return $this->error('Class not found', 404);
        }

        $validated = $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $class->update($validated);

        return $this->success($class, 'Class updated');
    }

    public function destroy($id)
    {
        $class = ClassModel::find($id);

        if (!$class) {
            return $this->error('Class not found', 404);
        }

        $class->delete();

        return $this->success(null, 'Class deleted', 204);
    }
}
