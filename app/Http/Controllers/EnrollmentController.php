<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Pack;

class EnrollmentController extends ApiController
{
    /**
     * Crear una nueva inscripción.
     */
    public function store(Request $request)
    {
        $userId = $request->get('auth_user')['data']['id'] ?? null;
        if (!$userId) {
            return $this->error('Unauthorized', 401);
        }

        $data = $request->validate([
            'type' => 'required|in:course,pack',
            'id' => 'required|integer',
        ]);

        // Determinar el modelo
        $modelClass = $data['type'] === 'course' ? Course::class : Pack::class;
        $item = $modelClass::find($data['id']);
        if (!$item) {
            return $this->error(ucfirst($data['type']) . ' not found', 404);
        }

        // Verificar si ya existe inscripción
        $existing = Enrollment::where('user_id', $userId)
            ->where('enrollable_type', $modelClass)
            ->where('enrollable_id', $item->id)
            ->first();

        if ($existing) {
            return $this->success(['enrollment' => $existing], 'Already enrolled');
        }

        // Crear inscripción
        $enrollment = Enrollment::create([
            'user_id' => $userId,
            'enrollable_type' => $modelClass,
            'enrollable_id' => $item->id,
            'enrolled_at' => now(),
        ]);

        return $this->success(['enrollment' => $enrollment], 'Enrolled successfully');
    }

    /**
     * Mostrar una inscripción por ID.
     */
    public function show($id)
    {

        dd(Enrollment::all()->toArray());
        $userId = request()->get('auth_user')['data']['id'] ?? null;
        if (!$userId) {
            return $this->error('Unauthorized', 401);
        }

        $enrollment = Enrollment::with('enrollable')
            ->where('user_id', $userId)
            ->findOrFail($id);

        return $this->success(['enrollment' => $enrollment]);
    }

    public function getUserCourses(Request $request)
    {
        $userId = $request->get('auth_user')['data']['id'] ?? null;
        if (!$userId) {
            return $this->error('Unauthorized', 401);
        }

        $individualCourses = Course::whereHas('enrollments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        $userPacks = Pack::whereHas('enrollments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
        $packCourses = $userPacks->flatMap(function ($pack) {
            return $pack->courses;
        })->unique('id'); 

        $allCourses = $individualCourses->merge($packCourses)->unique('id');

        return $this->success([
            'success' => true,
            'courses' => $allCourses,
        ]);

    }
}
