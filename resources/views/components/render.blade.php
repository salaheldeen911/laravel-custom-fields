@props(['model' => null, 'customFields' => []])

@if(count($customFields) > 0)
<div class="space-y-8 mt-12 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/50">
        <h2 class="text-lg font-bold text-gray-900 flex items-center">
            <span class="w-2 h-6 bg-indigo-600 rounded-full mr-3"></span>
            Additional Information
        </h2>
    </div>

    <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            @foreach($customFields as $field)
            <div class="col-span-1">
                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 px-1 flex justify-between items-center">
                    <span>
                        {{ $field->label ?? str_replace('_', ' ', $field->name) }}
                        @if(isset($field->validation_rules['required']) && $field->validation_rules['required'])
                        <span class="text-red-500 ml-0.5">*</span>
                        @endif
                    </span>
                </label>

                <div class="relative">
                    {{-- Dynamic Component Loading --}}
                    <x-dynamic-component
                        :component="'custom-fields::types.'.$field->type"
                        :field="$field"
                        :value="$field->currentValue($model)"
                        :input-name="$field->slug" />
                </div>

                @error($field->slug)
                <p class="text-red-500 text-xs font-bold mt-2 ml-1">
                    {{ $message }}
                </p>
                @enderror
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    .custom-fields-container {
        font-family: 'Inter', sans-serif;
    }
</style>