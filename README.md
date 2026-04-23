# Laravel Custom Fields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/salah/laravel-custom-fields.svg?style=for-the-badge&color=blue)](https://packagist.org/packages/salah/laravel-custom-fields)
[![Total Downloads](https://img.shields.io/packagist/dt/salah/laravel-custom-fields.svg?style=for-the-badge&color=green)](https://packagist.org/packages/salah/laravel-custom-fields)
[![PHP Version](https://img.shields.io/packagist/php-v/salah/laravel-custom-fields.svg?style=for-the-badge&color=777bb4)](https://packagist.org/packages/salah/laravel-custom-fields)
[![License](https://img.shields.io/packagist/l/salah/laravel-custom-fields.svg?style=for-the-badge&color=orange)](https://packagist.org/packages/salah/laravel-custom-fields)

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
-   **🚀 Laravel Octane Ready**: The singleton service state is automatically reset after every request via a `terminating` hook — safe for persistent Octane workers with zero configuration.

---

## 📦 Installation

```bash
composer require salah/laravel-custom-fields
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

    }
    ```

3.  **Advanced Configuration**: Tune the package in `config/custom-fields.php`.
    *   **Cache Strategy**: Control `ttl` and `prefix` to balance performance and freshness.
    *   **Security**: Enable `sanitize_html` to automatically strip tags from text inputs.
    *   **Maintenance**: Configure `pruning` retention periods for soft-deleted fields.

> [!IMPORTANT]
> **API Security**: If you enable API or Web routes in config, the package will automatically check for authentication middleware. If missing, it will log a warning. Ensure your routes are protected by adding `auth` middleware in the config.

---

## 🧹 Maintenance & Pruning

To keep your database clean, you can permanently remove soft-deleted custom fields that are older than a configured threshold.

1.  **Configure**: Set `'prune_deleted_after_days' => 30` in your config file.
2.  **Run Command**:

    ```bash
    php artisan custom-fields:prune
    ```

    *Tip: Schedule this command in your `App\Console\Kernel` to run weekly.*

---

## 🧠 Architecture & Validation Concepts

This package separates the world into two distinct logical flows to prevent confusion:

### 1. The Admin Flow (Defining Fields)
*   **Goal:** Define *what* a field is (e.g., "Age").
*   **Handled by:** The package's internal `CustomFieldBaseRequest` (used by `StoreCustomFieldRequest` and `UpdateCustomFieldRequest`).
*   **Usage:** Automatically applied when creating/editing field definitions via the package routes. It validates that your rules don't conflict (e.g., preventing `alpha` logic on a `number` field).

### 2. The User Flow (Entering Data)
*   **Goal:** Fill in the field (e.g., "25").
*   **Trait:** `ValidatesFieldData`
*   **Usage:** Used in your Application's forms. It applies the rules defined in Step 1 to the user's input.

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

The cleanest way to validate custom fields is by using the `ValidatesFieldData` trait in your Form Request.

> **CRITICAL:** If `strict_validation` is enabled in config (default: true), you **MUST** use this trait. It not only merges rules but also "marks" the data as safely validated. Failure to use it will result in a `ValidationIntegrityException`.

```php
use Salah\LaravelCustomFields\Traits\ValidatesFieldData;

class StoreUserRequest extends FormRequest
{
    use ValidatesFieldData;

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

// Note: getCustomFieldRules() is a helper from the HasCustomFields trait
// getCustomFieldModelAlias() is also available for programmatic model resolution
```

### 3. Validation (Option C: Manual Service)

For complex scenarios where you need granular control or are validating data outside of a request:

```php
// Validate only custom fields (Throws ValidationException on failure)
app(CustomFieldsService::class)->validate(User::class, $data);
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

### Optimize Show/Edit Pages

When displaying a single model (e.g., in `show` or `edit` methods), use the `loadCustomFields()` helper to ensure all data is loaded efficiently before rendering the view.

```php
public function edit(User $user)
{
    // Eager loads values relationship
    return view('users.edit')->with('user', $user->loadCustomFields());
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
| `checkbox` | ✅ | `<input type="checkbox">` | `required` |
| `phone` | 📞 | `<input type="tel">` | `phone`, `mobile`, `landline` (Supports formats or `AUTO` detection) |
| `email` | ✉️ | `<input type="email">` | `min`, `max`, `regex` (Native email validation) |
| `url` | 🔗 | `<input type="url">` | `min`, `max`, `regex` (Native URL validation) |
| `color` | 🎨 | `<input type="color">` | `required` (Validates hex color format) |
| `file` | 📂 | `<input type="file">` | `mimes`, `max_file_size` (Secure storage & URL generation) |

> [!IMPORTANT]
> **Immutability Notice**: To maintain data integrity, the **Field Type** (`type`) and **Target Model** (`model`) are immutable once a field is created. These cannot be changed during an update to prevent database schema mismatch and validation errors.

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

### Registering Custom Filters

The `FilterEngine` is registered as a singleton. You can add your own query filters — for example, filtering by a custom attribute — from your `AppServiceProvider`:

```php
use Salah\LaravelCustomFields\Filters\FilterEngine;

public function boot() {
    $this->app->make(FilterEngine::class)->registerFilter('active', MyActiveFilter::class);
}
```

Your filter class must implement a static `apply(Builder $query, mixed $value): Builder` method:

```php
class MyActiveFilter {
    public static function apply(Builder $query, mixed $value): Builder {
        return $query->where('is_active', (bool) $value);
    }
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
| **GET** | `/api/custom-fields/{id}` | Get a single field |
| **POST** | `/api/custom-fields` | Create a new field |
| **PUT** | `/api/custom-fields/{id}` | Update field configuration |
| **DELETE** | `/api/custom-fields/{id}` | Soft delete a field |
| **POST** | `/api/custom-fields/{id}/restore` | Restore a soft-deleted field |
| **DELETE** | `/api/custom-fields/{id}/force` | Permanently delete a field |

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

## ⚡️ Laravel Octane Support

This package is fully safe for use with Laravel Octane. The `CustomFieldsService` is registered as a singleton but its internal `$validated` state is automatically reset at the end of every request lifecycle via a `terminating` hook in the service provider. No configuration is needed.

If you run Octane and cache model aliases via `config('custom-fields.cache.octane_compatibility', true)`, the static alias cache in the `HasCustomFields` trait is disabled by default to avoid cross-request leakage.

---

## 🔐 Authorization

By default, the package does not enforce authorization on its management routes (it relies on your middleware configuration).
To protect them with a Gate ability, add it in `config/custom-fields.php`:

```php
'authorization' => [
    'ability' => 'manage-custom-fields',
],
```

Then define the ability in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('manage-custom-fields', function ($user) {
    return $user->isAdmin();
});
```

When set, all custom field management requests (list, create, update, delete) will check this ability. If the user does not have the ability, a `403 Forbidden` response is returned.

---

## 🌍 Country Service

The package ships with a `CountryService` that provides a full list of country names using `libphonenumber`. It is registered as a singleton in the container:

```php
use Salah\LaravelCustomFields\Services\CountryService;

class MyController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function countries() {
        return response()->json($this->countryService->getAll());
    }
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
