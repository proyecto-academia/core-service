<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Enrollment;
use Closure;
use Illuminate\Http\Request;

class CheckValidEnrollmentsForClassModel
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->get('auth_user')['data']['id']?? null;
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if($request->get('auth_user')['data']['role'] !== 'student') {
            $allCourses = Course::pluck('id')->toArray();
            $request->merge(['available_courses' => $allCourses]);
            return $next($request);
        }



        $courseEnrollments = Enrollment::where('user_id', $userId)
            ->where('enrollable_type', 'App\Models\Course')
            ->pluck('enrollable_id')
            ->toArray();

        $packEnrollments = Enrollment::where('user_id', $userId)
            ->where('enrollable_type', 'App\Models\Pack')
            ->pluck('enrollable_id')
            ->toArray();

        $packCourses = Course::whereHas('packs', function ($query) use ($packEnrollments) {
            $query->whereIn('packs.id', $packEnrollments);
        })->pluck('id')->toArray();


        $allCourses = array_unique(array_merge($courseEnrollments, $packCourses));

        $request->merge(['available_courses' => $allCourses]);
        return $next($request);
    }
}
