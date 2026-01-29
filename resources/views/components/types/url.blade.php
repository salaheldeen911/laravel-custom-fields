@props(['field', 'value' => null, 'inputName'])

<input type="url"
    name="{{ $inputName }}"
    id="{{ $field->name }}"
    value="{{ $value }}"
    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-gray-50 focus:bg-white text-gray-900 font-medium placeholder-gray-400"
    placeholder="{{ $field->placeholder }}">
