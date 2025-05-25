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

        // Obtener inscripciones individuales a cursos
        $individualCourses = Course::whereHas('enrollments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        // Obtener inscripciones a packs y los cursos asociados
        $userPacks = Pack::whereHas('enrollments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
        $packCourses = $userPacks->flatMap(function ($pack) {
            return $pack->courses;
        })->unique('id'); 

        // Combinar ambos conjuntos de cursos y eliminar duplicados
        $allCourses = $individualCourses->merge($packCourses)->unique('id');

        return $this->success([
            'success' => true,
            'courses' => $allCourses,
        ]);






        // return $this->success([
        //     'courses' => $individualCourses,
        // ], 'User courses retrieved successfully');

        // [
		// 	{
		// 		"id": 3,
		// 		"name": "Versatile regional securedline",
		// 		"description": "Odio nulla est eaque quisquam sit non. At ut qui fugiat sit modi. Enim voluptas ut sint nihil quidem. Molestiae autem atque alias aspernatur illum. Fuga nihil corrupti esse natus.",
		// 		"estimated_hours": 37,
		// 		"is_free": 0,
		// 		"price": "34.32",
		// 		"published": 1,
		// 		"created_at": "2025-05-25T09:49:47.000000Z",
		// 		"updated_at": "2025-05-25T09:49:47.000000Z"
		// 	},
		// 	{
		// 		"id": 6,
		// 		"name": "Distributed user-facing GraphicalUserInterface",
		// 		"description": "Consequatur dolorem sapiente nihil expedita. Modi delectus ut voluptas qui voluptatem. Quia sint id sit repellendus pariatur nihil est voluptatem.",
		// 		"estimated_hours": 38,
		// 		"is_free": 1,
		// 		"price": "0.00",
		// 		"published": 1,
		// 		"created_at": "2025-05-25T09:49:50.000000Z",
		// 		"updated_at": "2025-05-25T09:49:50.000000Z"
		// 	},
		// 	{
		// 		"id": 1,
		// 		"name": "Synchronised 6thgeneration software",
		// 		"description": "Aut quos velit officia a aspernatur quasi distinctio. Blanditiis veniam architecto molestiae dolores adipisci. Consequatur quis sit id dolor.",
		// 		"estimated_hours": 26,
		// 		"is_free": 1,
		// 		"price": "0.00",
		// 		"published": 0,
		// 		"created_at": "2025-05-25T09:49:44.000000Z",
		// 		"updated_at": "2025-05-25T09:49:44.000000Z"
		// 	},
		// 	{
		// 		"id": 10,
		// 		"name": "User-friendly executive methodology",
		// 		"description": "Maiores delectus sed qui odio voluptates. Consequuntur animi nobis iste nulla non assumenda beatae modi. Nesciunt aut rerum laboriosam inventore ipsum ea. Dolores et culpa laudantium cum.",
		// 		"estimated_hours": 28,
		// 		"is_free": 0,
		// 		"price": "17.05",
		// 		"published": 1,
		// 		"created_at": "2025-05-25T09:49:54.000000Z",
		// 		"updated_at": "2025-05-25T09:49:54.000000Z"
		// 	},
		// 	{
		// 		"id": 8,
		// 		"name": "Proactive multi-state capacity",
		// 		"description": "Molestias at quia sunt illo error velit. Suscipit nesciunt fuga est quaerat. Velit quia deleniti quia ipsum qui dignissimos. Dolorum earum incidunt et autem.",
		// 		"estimated_hours": 12,
		// 		"is_free": 1,
		// 		"price": "0.00",
		// 		"published": 1,
		// 		"created_at": "2025-05-25T09:49:52.000000Z",
		// 		"updated_at": "2025-05-25T09:49:52.000000Z"
		// 	}
		// ]

    }
}
