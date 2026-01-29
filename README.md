# Laravel Custom Fields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/salaheldeen911/laravel-custom-fields.svg?style=flat-square)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/salaheldeen911/laravel-custom-fields.svg?style=flat-square)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)
[![License](https://img.shields.io/packagist/l/salaheldeen911/laravel-custom-fields.svg?style=flat-square)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)

**The Ultimate "Sealed Lifecycle" EAV Solution for Enterprise Laravel Applications.**

Treat user-defined fields as first-class citizens. This package provides high-performance, strictly validated, and highly extensible custom fields with native support for both **Blade (Full-Stack)** and **Headless (API)** architectures.

---

## 🔥 Key Differentiators

- **🛡 Meta-Validation**: We don't just validate field values; we validate the _rules_ themselves. Ensure `min < max` and `regex` patterns are valid _before_ they are saved.
- **⚡️ High-Performance Engine**: Optimized with database `upserts` and batch operations. Reduces database overhead from N queries to just **one** per request.
- **🏗 Robust Polymorphism**: Uses a `config` map for models. High stability even if you refactor model class names/namespaces.
- **🧩 Architecture Agnostic**:
    - **Blade Version**: Ready-to-use Tailwind components for rapid development.
    - **Headless Version**: Rich metadata API (`models-and-types`) for React/Vue/Mobile applications.

---

## 📦 Installation

```bash
composer require salaheldeen911/laravel-custom-fields
```

**Installer**: Publishes config, migrations, and assets.

```bash
php artisan custom-fields:install
```

---

## ⚙️ Configuration

1. **Map Your Models**: In `config/custom-fields.php`, define aliases for your models to maintain database stability.

    ```php
    'models' => [
        'user'    => \App\Models\User::class,
        'product' => \App\Models\Product::class,
    ],
    ```

2. **Prepare Models**: Add the `HasCustomFields` trait to any model that should support custom fields.

    ```php
    use Salah\LaravelCustomFields\Traits\HasCustomFields;

    class User extends Model {
        use HasCustomFields;
    }
    ```

---

## 🏛 Usage: Blade Version (Full-Stack)

### 1. Rendering Inputs

Use the provided `<x-custom-fields::render>` component to automatically generate the correct input type (text, number, select, etc.) with its specific validation rules and options.

```blade
<form action="{{ route('users.store') }}" method="POST">
    @csrf

    <!-- Standard Fields -->
    <input type="text" name="name" value="{{ old('name') }}" />

    <!-- Dynamic Custom Fields -->
    <x-custom-fields::render :model="$user ?? null" :customFields="\App\Models\User::customFields()" />

    <button type="submit">Save</button>
</form>
```

### 2. Validation & Storing

In your controller, you can use the trait's methods to handle validation and storage seamlessly.

```php
public function store(Request $request)
{
    // 1. Standard Validation
    $request->validate(['name' => 'required']);

    // 2. Custom Fields Validation
    $validator = User::customFieldsValidation($request);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // 3. Create Model
    $user = User::create($request->only('name'));

    // 4. Save Custom Fields (Optimized Batch Insert)
    $user->saveCustomFields($request->all());

    return redirect()->route('users.index');
}
```

### 3. Updating Values

Updating is just as easy and uses high-performance `upsert` logic.

```php
public function update(Request $request, User $user)
{
    // Validate
    $validator = User::customFieldsValidation($request);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    // Update Main Model
    $user->update($request->only('name'));

    // Update Custom Fields
    $user->updateCustomFields($request->all());

    return back();
}
```

---

## 🏛 Usage: Headless Version (API)

### 1. Fetching Specifications

Call `GET /api/custom-fields/models-and-types` to get the list of supported models and the metadata for each field type (including allowed validation rules).

The package expects custom field data in a simple key-value format where the key is the field's `slug`.

```json
{
    "name": "John Doe",
    "biography": "Author and developer.",
    "age": 30
}
```

---

## 🔍 Retrieval & Querying

### Get Single Value

```php
$bio = $user->custom('biography');
```

### Get All Values (Flat Array)

Useful for API responses via `JsonResource`.

```php
return response()->json([
    'data' => $user,
    'custom_fields' => $user->customFieldsResponse()
]);
// Output: {"biography": "...", "age": 30}
```

### Querying by Custom Field

Find users where a custom field matches a value.

```php
$users = User::whereCustomField('city', 'New York')->get();
```

---

## 🛠 Advanced Features

### Supported Field Types

- `text`: Standard text input.
- `number`: Numeric input with `min`/`max` support.
- `phone`: Specialized phone validation.
- `select`: Dropdown with configurable options.
- `checkbox`: Boolean or multiple choice.

### Meta-Validation

The package ensures that when you _create_ a custom field, its configuration is valid:

- **Regex Safety**: Validates that the regex pattern is a valid PHP regex.
- **Range Safety**: Automatically ensures `min` cannot be greater than `max`.
- **Constraint Safety**: Only allows validation rules that make sense for the field type (e.g., `max` length for text, `min` value for numbers).

---

## 🔌 Extending the Core

### New Field Type

Register a new field type by extending `FieldType`:

```php
namespace App\CustomFields;

use Salah\LaravelCustomFields\FieldTypes\FieldType;

class ColorPickerType extends FieldType {
    public function name(): string { return 'color'; }
    public function baseRule(): string { return 'string|size:7'; }
    public function allowedRules(): array { return ['required' => 'boolean']; }
    public function view(): string { return 'components.color-picker'; }
}
```

### New Validation Rule

Extend `ValidationRule` to add logic like `UniqueVatNumber` or specialized formatting.

---

## 🎨 Management UI

The package comes with a built-in UI to manage your custom fields.

- **Route**: `/custom-fields` (configurable in `config/custom-fields.php`)
- **Features**: List, Create, Edit, Soft Delete, and Restore custom fields.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
