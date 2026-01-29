@extends('custom-fields::layout')

@section('content')
<script>
    window.CustomFieldsMeta = @json($meta);
</script>

<div class="min-h-screen bg-gray-50 py-12" x-data="fieldForm()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-10 flex items-center justify-between">
            <div>
                <a href="{{ route('custom-fields.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Fields
                </a>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                    Create New Field
                </h1>
                <p class="mt-2 text-sm text-gray-500 font-medium">Define a new dynamic field to capture custom data.</p>
            </div>
        </div>

        <form action="{{ route('custom-fields.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Main Configuration Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-2 h-6 bg-indigo-600 rounded-full mr-3"></span>
                        Basic Configuration
                    </h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Field Name --}}
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Field Name</label>
                            <input type="text" name="name"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50 focus:bg-white text-gray-900 font-medium placeholder-gray-400 @error('name') border-red-500 bg-red-50 @enderror"
                                value="{{ old('name') }}" required placeholder="e.g., Company Size">
                            @error('name') <p class="text-red-500 text-xs font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Model Selection --}}
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Target Model</label>
                            <div class="relative">
                                <select name="model" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50 focus:bg-white text-gray-900 font-medium appearance-none">
                                    @foreach($meta['models'] as $model)
                                    <option value="{{ $model }}" {{ old('model') === $model ? 'selected' : '' }}>{{ class_basename($model) }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('model') <p class="text-red-500 text-xs font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Field Type --}}
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Field Type</label>
                            <div class="relative">
                                <select name="type" x-model="selectedType" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50 focus:bg-white text-gray-900 font-medium appearance-none">
                                    @foreach($meta['types'] as $type)
                                    <option value="{{ $type['name'] }}">{{ $type['label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Placeholder --}}
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 px-1 text-gray-400">Placeholder (Optional)</label>
                            <input type="text" name="placeholder"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50 focus:bg-white text-gray-900 font-medium placeholder-gray-300"
                                value="{{ old('placeholder') }}" placeholder="What users see inside the field...">
                            @error('placeholder') <p class="text-red-500 text-xs font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Required Toggle --}}
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Field Requirement</label>
                            <div class="flex items-center h-[50px] px-4 bg-gray-50 rounded-xl border border-gray-200">
                                <input type="hidden" name="required" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="required" value="1" class="sr-only peer" {{ old('required') ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-3 text-sm font-bold text-gray-700">Is Required?</span>
                                </label>
                            </div>
                            @error('required') <p class="text-red-500 text-xs font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Options Card -->
            <div x-show="hasOptions" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 bg-amber-50/30">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-2 h-6 bg-amber-500 rounded-full mr-3"></span>
                        Field Options
                    </h2>
                </div>
                <div class="p-8">
                    <p class="text-sm text-gray-500 mb-6 font-medium">Define the list of items available for this field.</p>
                    <div class="space-y-3">
                        <template x-for="(option, index) in options" :key="index">
                            <div class="flex items-center space-x-3 group animate-in slide-in-from-top duration-200">
                                <div class="flex-grow">
                                    <input type="text" name="options[]" x-model="options[index]" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all bg-gray-50 focus:bg-white text-gray-900" placeholder="e.g., Small" :required="hasOptions">
                                </div>
                                <button type="button" @click="removeOption(index)" class="p-3 text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addOption()" class="mt-6 inline-flex items-center px-4 py-2 text-sm font-bold text-amber-700 bg-amber-50 rounded-xl hover:bg-amber-100 transition-all transform active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Another Option
                    </button>
                    @error('options') <p class="text-red-500 text-xs font-bold mt-4">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Validation Rules Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 bg-indigo-50/30">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-2 h-6 bg-indigo-600 rounded-full mr-3"></span>
                        Validation Rules
                    </h2>
                </div>
                <div class="p-8">
                    @error('validation_rules') <p class="text-red-500 text-sm font-bold mb-6 p-4 bg-red-50 rounded-xl border border-red-100">{{ $message }}</p> @enderror
                    <!-- Single Hidden Input for all Validation Rules (JSON) -->
                    <input type="hidden" name="validation_rules" :value="JSON.stringify(rulesValues)">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="rule in allowedRules" :key="rule.name">
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 transition-all hover:bg-white hover:shadow-sm">
                                <label class="block text-xs font-bold text-indigo-600 uppercase tracking-widest mb-1" x-text="rule.label"></label>
                                <p class="text-[10px] text-gray-500 mb-3" x-text="rule.description"></p>

                                <template x-if="rule.ui.tag === 'checkbox'">
                                    <div class="flex items-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" value="1" class="sr-only peer" x-model="rulesValues[rule.name]">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                            <span class="ml-3 text-sm font-bold text-gray-700">Enabled</span>
                                        </label>
                                    </div>
                                </template>

                                <template x-if="rule.ui.tag === 'select'">
                                    <select class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900 font-medium"
                                        x-model="rulesValues[rule.name]">
                                        <option value="">Select Option</option>
                                        <template x-for="option in rule.ui.options" :key="option.value">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                </template>

                                <template x-if="rule.ui.tag === 'input'">
                                    <input :type="rule.ui.type" 
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900 font-medium placeholder:text-[.9em]"
                                        :placeholder="rule.ui.placeholder || 'Enter value...'"
                                        x-model="rulesValues[rule.name]">
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="flex items-center justify-end space-x-4 pt-4">
                <a href="{{ route('custom-fields.index') }}" class="px-8 py-3 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition-all">
                    Cancel
                </a>
                <button type="submit" class="group inline-flex items-center px-10 py-4 border border-transparent rounded-xl shadow-xl text-base font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
                    Save Custom Field
                    <svg class="ml-3 h-5 w-5 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function fieldForm() {
        return {
            meta: window.CustomFieldsMeta,
            selectedType: @json(old('type', 'text')),
            options: @json(old('options', [''])),
            rulesValues: @json(old('validation_rules', (object)[])),

            get currentType() {
                console.log(window.CustomFieldsMeta);
                return this.meta.types.find(t => t.name === this.selectedType);
            },

            get hasOptions() {
                return this.currentType ? this.currentType.has_options : false;
            },

            get allowedRules() {
                return this.currentType ? this.currentType.allowed_rules : [];
            },

            addOption() {
                this.options.push('');
            },

            removeOption(index) {
                this.options.splice(index, 1);
                if (this.options.length === 0) this.options.push('');
            }
        }
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    input:focus,
    select:focus {
        outline: none !important;
    }
</style>
@endsection