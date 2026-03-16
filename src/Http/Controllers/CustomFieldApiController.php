<?php

namespace Salah\LaravelCustomFields\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Salah\LaravelCustomFields\Http\Requests\FilterCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\StoreCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\UpdateCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Resources\CustomFieldResource;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;
use Salah\LaravelCustomFields\Services\CustomFieldsMetaService;
use Salah\LaravelCustomFields\DTOs\CustomFieldDTO;

class CustomFieldApiController extends Controller
{
    public function __construct(
        protected CustomFieldsMetaService $metaService,
        protected CustomFieldRepositoryInterface $repository
    ) {}

    public function index(FilterCustomFieldRequest $request): JsonResponse
    {
        $customFields = $this->repository->paginate($request->all(), $request->get('limit', 15));

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
                'stats' => $this->repository->getStats(),
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
        $customField = $this->repository->store(CustomFieldDTO::fromArray($request->validated()));

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field created successfully.',
            'data' => new CustomFieldResource($customField),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $customField = $this->repository->findById($id, true);

        return response()->json([
            'success' => true,
            'data' => new CustomFieldResource($customField),
        ]);
    }

    public function update(UpdateCustomFieldRequest $request, string $id): JsonResponse
    {
        $customField = $this->repository->update($id, CustomFieldDTO::fromArray($request->validated()));

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field updated successfully.',
            'data' => new CustomFieldResource($customField),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repository->delete($id);

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field deleted successfully.',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $this->repository->restore($id);
        $customField = $this->repository->findById($id, true);

        return response()->json([
            'success' => true,
            'message' => 'Custom field restored successfully.',
            'data' => new CustomFieldResource($customField),
        ]);
    }

    public function forceDestroy(string $id): JsonResponse
    {
        $this->repository->forceDelete($id);

        $this->metaService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom field permanently deleted.',
        ]);
    }
}
