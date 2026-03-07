<?php

namespace Salah\LaravelCustomFields\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Salah\LaravelCustomFields\Http\Requests\FilterCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\StoreCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\UpdateCustomFieldRequest;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;
use Salah\LaravelCustomFields\Services\CustomFieldsMetaService;

class CustomFieldController extends Controller
{
    public function __construct(
        protected CustomFieldsMetaService $metaService,
        protected CustomFieldRepositoryInterface $repository
    ) {}

    public function index(FilterCustomFieldRequest $request): View
    {
        $customFields = $this->repository->paginate($request->all(), 10);
        $stats = $this->repository->getStats();
        $meta = $this->metaService->forIndex();
        $models = $meta['models'];
        $types = $meta['types'];

        return view('custom-fields::index', compact('customFields', 'models', 'types', 'stats'));
    }

    public function create(): View
    {
        $fieldBuilderMeta = $this->metaService->forBuilder();

        return view('custom-fields::create', ['meta' => $fieldBuilderMeta]);
    }

    public function store(StoreCustomFieldRequest $request): RedirectResponse
    {
        $this->repository->store($request->validated());

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field created successfully.');
    }

    public function show(string $id): View
    {
        $customField = $this->repository->findById($id, true);

        return view('custom-fields::show', compact('customField'));
    }

    public function edit(string $id): View
    {
        $customField = $this->repository->findById($id, true);
        $fieldBuilderMeta = $this->metaService->forBuilder();

        return view('custom-fields::edit', ['customField' => $customField, 'meta' => $fieldBuilderMeta]);
    }

    public function update(UpdateCustomFieldRequest $request, string $id): RedirectResponse
    {
        $this->repository->update($id, $request->validated());

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->repository->delete($id);

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field deleted successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $this->repository->restore($id);

        return redirect()->route('custom-fields.index')->with('success', 'Custom field restored successfully.');
    }

    public function forceDestroy(string $id): RedirectResponse
    {
        $this->repository->forceDelete($id);

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field permanently deleted.');
    }
}
