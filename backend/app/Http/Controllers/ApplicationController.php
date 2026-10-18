<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApplicationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ApplicationResource::collection(Application::latest('id')->get());
    }

    public function store(ApplicationRequest $request): JsonResponse
    {
        $application = Application::create($request->validated());

        return (new ApplicationResource($application))
            ->response()
            ->setStatusCode(201);
    }

    // {application} di route otomatis dicari berdasarkan id; tidak ketemu -> 404
    public function show(Application $application): ApplicationResource
    {
        return new ApplicationResource($application);
    }

    public function update(ApplicationRequest $request, Application $application): ApplicationResource
    {
        $application->update($request->validated());

        return new ApplicationResource($application);
    }

    public function destroy(Application $application): JsonResponse
    {
        $application->delete();

        return response()->json(['message' => 'Lamaran dihapus.']);
    }
}