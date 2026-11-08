<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ApplicationResource::collection(Application::latest('id')->get());
    }

    public function store(ApplicationRequest $request): JsonResponse
    {
        $application = Application::create($request->validated());

        Log::info('Application created', [
            'user_id' => $request->user()->id,
            'action' => 'application.create',
            'application_id' => $application->id,
            'status' => $application->status,
        ]);

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

        Log::info('Application updated', [
            'user_id' => $request->user()->id,
            'action' => 'application.update',
            'application_id' => $application->id,
            'changed_fields' => array_values(array_diff(array_keys($application->getChanges()), ['updated_at'])),
        ]);

        return new ApplicationResource($application);
    }

    public function destroy(Request $request, Application $application): JsonResponse
    {
        $application->delete();

        Log::info('Application deleted', [
            'user_id' => $request->user()->id,
            'action' => 'application.delete',
            'application_id' => $application->id,
        ]);

        return response()->json(['message' => 'Lamaran dihapus.']);
    }
}