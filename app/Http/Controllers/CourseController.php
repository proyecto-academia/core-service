<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends ApiController
{
    public function index(Request $request)
    {
        $query = Course::query();

        // Lista blanca de campos permitidos para filtrar y ordenar
        $filterable = ['name', 'published', 'is_free', 'price'];
        $orderable = ['id', 'name', 'published', 'is_free', 'price', 'estimated_hours'];

        // Filtrado dinámico solo si el campo es válido
        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                if ($field === 'name') {
                    // Usar LIKE para el campo 'name'
                    $query->where($field, 'LIKE', '%' . $request->input($field) . '%');
                } else {
                    $query->where($field, $request->input($field));
                }
            }
        }

        // Filtrar por rango de precios
        if ($request->filled('minPrice')) {
            $query->where('price', '>=', $request->input('minPrice'));
        }

        if ($request->filled('maxPrice')) {
            $query->where('price', '<=', $request->input('maxPrice'));
        }

        // Orden dinámico solo si el campo es válido
        $orderBy = $request->input('orderBy', 'id');
        $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($orderBy, $orderable)) {
            $query->orderBy($orderBy, $order);
        }

        // Paginación
        $perPage = 25;
        $courses = $query->paginate($perPage);

        return $this->success([
            'courses' => $courses,
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'next_page' => $courses->nextPageUrl(),
            ]
        ]);
    }

    public function getLatestCourses(Request $request)
    {
        $courses = Course::where('published', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return $this->success($courses);
    }


    public function getCoursesMinPrice()
    {
        $minPrice = Course::where('published', true)
            ->whereNotNull('price')
            ->min('price');

        return $this->success(['minPrice' => $minPrice]);
    }

    public function getCoursesMaxPrice()
    {
        $maxPrice = Course::where('published', true)
            ->whereNotNull('price')
            ->max('price');

        return $this->success(['maxPrice' => $maxPrice]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|integer',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'published' => 'boolean',
        ]);

        $course = Course::create($validated);

        return $this->success($course, 'Course created', 201);
    }

    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->error('Course not found', 404);
        }

        return $this->success($course);
    }

    public function update(Request $request, $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->error('Course not found', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|integer',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'published' => 'boolean',
        ]);

        $course->update($validated);

        return $this->success($course, 'Course updated');
    }

    public function destroy($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->error('Course not found', 404);
        }

        $course->delete();

        return $this->success(null, 'Course deleted', 204);
    }
}
