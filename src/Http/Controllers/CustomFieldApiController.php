<?php

namespace Salah\LaravelCustomFields\Http\Controllers;

use Salah\LaravelCustomFields\Filters\FilterEngine;
use Salah\LaravelCustomFields\Http\Requests\FilterCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\StoreCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\UpdateCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Resources\CustomFieldResource;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Services\CustomFieldsMetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CustomFieldApiController extends Controller
{
    public function __construct(
        protected CustomFieldsMetaService $metaService
    ) {}

    public function index(FilterCustomFieldRequest $request): JsonResponse
    {
        $customFields = (new FilterEngine)
            ->apply($request->all())
            ->latest()
            ->paginate($request->get('limit', 15));

        return response()->json([
            'success' => true,
            'data' => CustomFieldResource::collection($customFields->items()),
            'meta' => [
                'pagination' => [
                    'total' => $customFields->total(),
                    'count' => $customFields->count(),
                    'per_page' => $customFields->perPage(),
                    'current_page' => $customFields->currentPage(),
                    'total_pages' => $customFields->lastPage(),
                ],
                'stats' => CustomField::selectRaw("
                    COUNT(*) as total,
                    COUNT(DISTINCT model) as models,
                    COUNT(DISTINCT type) as types,
                    SUM(CASE WHEN JSON_EXTRACT(validation_rules, '$.required') = 1 THEN 1 ELSE 0 END) as required
                ")->first()->toArray(),
            ],
            'links' => [
                'first' => $customFields->url(1),
                'last' => $customFields->url($customFields->lastPage()),
                'prev' => $customFields->previousPageUrl(),
                'next' => $customFields->nextPageUrl(),
            ],
        ]);
    }

    public function modelsAndTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->metaService->forBuilder(),
        ]);
    }

    public function store(StoreCustomFieldRequest $request): JsonResponse
    {
        $customField = CustomField::create($request->validated());

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field created successfully.',
            'data' => new CustomFieldResource($customField),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $customField = CustomField::withTrashed()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new CustomFieldResource($customField),
        ]);
    }

    public function update(UpdateCustomFieldRequest $request, string $id): JsonResponse
    {
        $customField = CustomField::withTrashed()->findOrFail($id);
        $customField->update($request->validated());

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field updated successfully.',
            'data' => new CustomFieldResource($customField),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $customField = CustomField::findOrFail($id);
        $customField->delete();

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field deleted successfully.',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $customField = CustomField::onlyTrashed()->findOrFail($id);
        $customField->restore();

        return response()->json([
            'success' => true,
            'message' => 'Custom field restored successfully.',
            'data' => new CustomFieldResource($customField),
        ]);
    }

    public function forceDestroy(string $id): JsonResponse
    {
        $customField = CustomField::withTrashed()->findOrFail($id);
        $customField->forceDelete();

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field permanently deleted.',
        ]);
    }
}
