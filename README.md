# Laravel Custom Fields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/salaheldeen911/laravel-custom-fields.svg?style=for-the-badge&color=blue)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/salaheldeen911/laravel-custom-fields.svg?style=for-the-badge&color=green)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)
[![PHP Version](https://img.shields.io/packagist/php-v/salaheldeen911/laravel-custom-fields.svg?style=for-the-badge&color=777bb4)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)
[![License](https://img.shields.io/packagist/l/salaheldeen911/laravel-custom-fields.svg?style=for-the-badge&color=orange)](https://packagist.org/packages/salaheldeen911/laravel-custom-fields)

**The Professional, Sealed-Lifecycle EAV Solution for Modern Laravel Applications.**

Tired of messy "extra_attributes" JSON columns that are impossible to validate? Treat user-defined fields as first-class citizens. This package provides high-performance, strictly validated, and extensible custom fields with native support for both **Blade (Full-Stack)** and **Headless (API)** architectures.

---

## 🔥 Why This Package?

-   **🛡 Strict Lifecycle**: We validate the _rules_ themselves. You can't save a `min > max` or an invalid `regex`.
-   **🚫 Intelligent Conflict Prevention**: Automatically prevents assigning conflicting rules (e.g., you can't use `Letters Only` and `Alpha-Numeric` together).
-   **⚡️ Built for Speed**: Uses database `upserts` and batch operations. Reduces database overhead from N queries to just **one** per request.
-   **🏗 Refactor-Safe Polymorphism**: Uses a `config` map for models. High stability even if you change model namespaces.
-   **🧩 Dual-Nature Architecture**:
    -   **Blade**: Ready-to-use Tailwind components with error handling and old-input support.
    -   **Headless**: Rich metadata API (`models-and-types`) explaining rules, labels, and tags to your Frontend.

---

## 📦 Installation

```bash
composer require salaheldeen911/laravel-custom-fields
```

Install the package (publishes config, migrations, and assets):

```bash
php artisan custom-fields:install
```

---

## ⚙️ Configuration

1.  **Map Your Models**: In `config/custom-fields.php`, define simple aliases for your models. This decouples your database from your class names.

    ```php
    'models' => [
        'user'    => \App\Models\User::class,
        'product' => \App\Models\Product::class,
    ],
    ```

2.  **Prepare Your Model**: Add the `HasCustomFields` trait.

    ```php
    use Salah\LaravelCustomFields\Traits\HasCustomFields;

    class User extends Model {
        use HasCustomFields;
    }
    ```

---

## 🏛 Usage: The Laravel Way

### 1. Rendering the UI (Blade)

Automatically render all custom fields for a specific model using a single tag. It handles `errors`, `old()`, and specific input types.

```blade
<form action="{{ route('users.store') }}" method="POST">
    @csrf

    <!-- Standard Fields -->
    <input type="text" name="name" />

    <!-- Dynamic Custom Fields Magic -->
    <x-custom-fields::render :model="$user ?? null" :customFields="\App\Models\User::customFields()" />

    <button type="submit">Save</button>
</form>
```

### 2. Validation (Option A: Form Request - Recommended)

The cleanest way to validate custom fields is by using the `ValidatesCustomFields` trait in your Form Request.

> **CRITICAL:** If `strict_validation` is enabled in config (default: true), you **MUST** use this trait. It not only merges rules but also "marks" the data as safely validated. Failure to use it will result in a `ValidationIntegrityException`.

```php
use Salah\LaravelCustomFields\Traits\ValidatesCustomFields;

class StoreUserRequest extends FormRequest
{
    use ValidatesCustomFields;

    public function rules(): array
    {
        // MERGE custom fields rules into your existing rules
        return $this->withCustomFieldsRules(User::class, [
            'name' => 'required|string|max:255',
        ]);
    }
}
```

### 3. Validation (Option B: Controller)

If you prefer validating in the controller, use the helper method on the model:

```php
$validated = $request->validate(array_merge([
    'name' => 'required',
], User::getCustomFieldRules()));
```

### 4. Storage & Updates
Use optimized batch methods to save or update custom values.

> **Recommendation:** It is highly recommended to wrap the creation/update of your main model and the custom fields in a **Database Transaction**. This ensures that if the custom field validation fails (or any other error occurs), the main model is not created/updated partially.

```php
use Illuminate\Support\Facades\DB;

// Storing
DB::transaction(function () use ($request) {
    $user = User::create($request->validated());
    $user->saveCustomFields($request->validated());
});

// Updating (Uses high-performance UPSERT logic)
DB::transaction(function () use ($request, $user) {
    $user->update($request->validated());
    $user->updateCustomFields($request->validated());
});
```

---

## 🔍 Retrieval & Powerful Querying

### Get Single Value

```php
$bio = $user->custom('biography');
```

### Get All Values (Flat Array)

Perfect for API responses or data exports.

```php
return response()->json([
    'user' => $user,
    'custom_data' => $user->customFieldsResponse()
]);
// Response: {"biography": "...", "age": 30, "city": "Cairo"}
```

### Querying like a Pro

The package provides a powerful scope to filter your models by custom fields values.

```php
// Find users where custom field 'city' is 'Cairo'
$users = User::whereCustomField('city', 'Cairo')->get();
```

---

## ⚡️ Performance & Eager Loading

To avoid the **N+1 query problem** when displaying multiple models, always use the `withCustomFields` scope. This eager loads all values and their field configurations in just two queries.

```php
// Optimized for lists/tables
$users = User::withCustomFields()->paginate(20);

foreach ($users as $user) {
    echo $user->custom('biography'); // No extra queries!
}
```

---

## 🧩 Built-in Field Types

| Type | Icon | HTML Control | Supported Rules |
| :--- | :---: | :--- | :--- |
| `text` | 📝 | `<input type="text">` | `min`, `max`, `regex`, `alpha`, `alpha_dash`, `alpha_num` |
| `textarea` | 📄 | `<textarea>` | `min`, `max`, `regex`, `not_regex` |
| `number` | 🔢 | `<input type="number">` | `min`, `max` |
| `decimal` | 💹 | `<input type="number" step="any">` | `min`, `max` |
| `date` | 📅 | `<input type="date">` | `after`, `before`, `after_or_equal`, `date_format` |
| `time` | 🕒 | `<input type="time">` | `required` (Standard string validation) |
| `select` | 🔽 | `<select>` | `required` (Strictly validated against options) |
| `radio` | 🔘 | `radio buttons` | `required` (Strictly validated against options) |
| `checkbox` | ✅ | `<input type="checkbox">` | `required` |
| `phone` | 📞 | `<input type="tel">` | `phone` (Supports formats or `AUTO` detection) |
| `email` | ✉️ | `<input type="email">` | `min`, `max`, `regex` (Native email validation) |
| `url` | 🔗 | `<input type="url">` | `min`, `max`, `regex` (Native URL validation) |
| `color` | 🎨 | `<input type="color">` | `required` (Validates hex color format) |

---

## 🛡 Validation Rule Conflicts

The system is smart enough to prevent logical errors in your field configurations. If you try to apply conflicting rules, the system will throw a validation error during the field creation/update.

**Common Conflicts Prevented:**
- `alpha` vs `alpha_num` vs `alpha_dash`
- `after` vs `after_or_equal`
- `before` vs `before_or_equal`

---

## 🛠 Advanced Customization

### Registering New Types

Create a class extending `FieldType` and register it in your `AppServiceProvider`.

```php
public function boot() {
    $this->app->make(FieldTypeRegistry::class)->register(new MyCustomType());
}
```

### Extending Validation Rules

You can add your own validation rules. If your rule conflicts with another, simply override the `conflictsWith()` method:

```php
class MyPremiumRule extends ValidationRule {
    public function conflictsWith(): array {
        return ['basic_rule_name'];
    }
}
```

---

## 🏛 Headless & API Reference

This package is a first-class citizen for Headless architectures. It provides a built-in API to manage custom fields and provides the necessary metadata for frontends to render them.

### 1. The Blueprint (Metadata)

Before rendering any UI, your frontend (React/Vue/Mobile) should fetch the types and rules.

**Endpoint:** `GET /api/custom-fields/models-and-types`

**Response:**
```json
{
  "success": true,
  "data": {
    "models": ["user", "product"],
    "types": [
      {
        "name": "text",
        "label": "Text Field",
        "tag": "input",
        "type": "text",
        "has_options": false,
        "allowed_rules": [
          { "name": "min", "label": "Min Length", "tag": "input", "type": "number" }
        ]
      }
    ]
  }
}
```

### 2. Managing Fields (CRUD API)

If you are building your own Admin Dashboard in a JS framework, use these endpoints:

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| **GET** | `/api/custom-fields` | List all fields (Paginated) |
| **POST** | `/api/custom-fields` | Create a new field |
| **PUT** | `/api/custom-fields/{id}` | Update field configuration |
| **DELETE** | `/api/custom-fields/{id}` | Soft delete a field |

#### Example: Creating a Field
**Payload (`POST /api/custom-fields`):**
```json
{
  "name": "Technical Bio",
  "model": "user",
  "type": "text",
  "required": true,
  "validation_rules": {
    "min": 10,
    "max": 500
  }
}
```

### 3. Storing Values (Entity Integration)

When your frontend sends data to update a model (like a User profile), send the custom fields as a flat object where the key is the **slug**.

**Payload (`PUT /api/users/12`):**
```json
{
  "name": "Salah Eldeen",
  "email": "salah@example.com",
  "technical-bio": "Full-stack developer with 10 years of experience."
}
```

**Controller Implementation:**
```php
public function update(Request $request, User $user) {
    $user->update($request->all());
    $user->updateCustomFields($request->all()); // Scans for slugs and updates values automatically
    
    return response()->json(['success' => true]);
}
```

---

## 🎨 Management UI

The package comes with a built-in, secure management interface to create and manage fields.
- **Route**: `/custom-fields` (Configurable in `custom-fields.php`)
- **Features**: List, Search, Create, Edit, and Trash management.

---

---

## 👨‍🍳 Cookbook: Advanced Scenarios

### Creating a Dependent Dropdown Field Type

Scenario: You want a `City` field that updates its options based on a `Country` field.

**1. Create the Field Type Class**

Create `app/CustomFields/Types/DependentSelectField.php`. We will use the `options` array to store the "parent field" slug.

```php
namespace App\CustomFields\Types;

use Salah\LaravelCustomFields\FieldTypes\FieldType;

class DependentSelectField extends FieldType
{
    public function name(): string { return 'dependent_select'; }
    public function label(): string { return 'Dependent Select'; }
    public function htmlTag(): string { return 'select'; }
    
    // We expect 'options' to contain the slug of the parent field
    // e.g., options: ["country"]
    public function hasOptions(): bool { return true; } 

    public function description(): string {
        return 'A select menu that depends on another field.';
    }

    public function baseRule(): array {
        return ['string']; // Basic validation
    }
    
    public function view(): string {
        return 'components.custom-fields.dependent-select';
    }
}
```

**2. Register the Type**

In `AppServiceProvider::boot()`:

```php
use Salah\LaravelCustomFields\FieldTypeRegistry;
use App\CustomFields\Types\DependentSelectField;

public function boot() {
    app(FieldTypeRegistry::class)->register(new DependentSelectField());
}
```

**3. Frontend Implementation**

Since the dependency logic is frontend-heavy, your component (`resources/views/components/custom-fields/dependent-select.blade.php`) should listen to the parent field.

```blade
@props(['field', 'value', 'allFields'])

@php
    $parentSlug = $field->options[0] ?? null;
@endphp

<div x-data="{ 
    parentVal: '', 
    options: [],
    init() {
        // Pseudo-code: Listen to the parent field change
        document.addEventListener('custom-field-changed:{{ $parentSlug }}', (e) => {
            this.fetchOptions(e.detail.value);
        });
    }
}">
    <select name="{{ $field->slug }}" x-model="value">
        <option value="">Select Option</option>
        <template x-for="opt in options">
            <option :value="opt" x-text="opt"></option>
        </template>
    </select>
</div>
```

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
