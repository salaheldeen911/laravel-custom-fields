<?php

namespace Salah\LaravelCustomFields\Http\Controllers;

use Salah\LaravelCustomFields\Filters\FilterEngine;
use Salah\LaravelCustomFields\Http\Requests\FilterCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\StoreCustomFieldRequest;
use Salah\LaravelCustomFields\Http\Requests\UpdateCustomFieldRequest;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Services\CustomFieldsMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    public function __construct(
        protected CustomFieldsMetaService $metaService
    ) {}

    public function index(FilterCustomFieldRequest $request): View
    {
        $customFields = (new FilterEngine)
            ->apply($request->all())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = CustomField::selectRaw('
            COUNT(*) as total,
            COUNT(DISTINCT model) as models,
            COUNT(DISTINCT type) as types,
            SUM(CASE WHEN required = 1 THEN 1 ELSE 0 END) as required
        ')->first()->toArray();

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
        CustomField::create($request->validated());

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field created successfully.');
    }

    public function show(string $id): View
    {
        $customField = CustomField::withTrashed()->findOrFail($id);

        return view('custom-fields::show', compact('customField'));
    }

    public function edit(string $id): View
    {
        $customField = $this->findWithTrashed($id);
        $fieldBuilderMeta = $this->metaService->forBuilder();

        return view('custom-fields::edit', ['customField' => $customField, 'meta' => $fieldBuilderMeta]);
    }

    public function update(UpdateCustomFieldRequest $request, string $id): RedirectResponse
    {
        $customField = $this->findWithTrashed($id);

        $customField->update($request->validated());

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $customField = CustomField::findOrFail($id);
        $customField->delete();

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field deleted successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $customField = CustomField::onlyTrashed()->findOrFail($id);
        $customField->restore();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field restored successfully.');
    }

    public function forceDelete(string $id): RedirectResponse
    {
        $customField = CustomField::onlyTrashed()->findOrFail($id);
        $customField->forceDelete();

        $this->metaService->clearCache();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field permanently deleted.');
    }

    protected function findWithTrashed(string $id): CustomField
    {
        return CustomField::withTrashed()->findOrFail($id);
    }
}
