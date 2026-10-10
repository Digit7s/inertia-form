<?php

use Digit7s\InertiaForm\Fields\Select;
use Digit7s\InertiaForm\Fields\TextInput;
use Digit7s\InertiaForm\InertiaForm;
use Digit7s\InertiaForm\Layouts\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormSecurityOption extends Model
{
    protected $table = 'form_security_options';

    protected $guarded = [];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope('active', fn (Builder $query) => $query->where('active', true));
    }
}

class FormSecurityRecord extends Model
{
    protected $table = 'form_security_records';

    protected $guarded = [];

    public function options(): HasMany
    {
        return $this->hasMany(FormSecurityOption::class, 'tenant_id', 'tenant_id');
    }

    public function limitedOptions(): HasMany
    {
        return $this->options()->limit(1);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(FormSecurityOption::class, 'option_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            FormSecurityOption::class,
            'form_security_record_option',
            'record_id',
            'option_id',
        )->where('form_security_options.tenant_id', $this->tenant_id);
    }

    public function invalidRelationMethod(): string
    {
        return 'not a relationship';
    }

    public function getDisplayNameAttribute(): string
    {
        return strtoupper((string) $this->getAttribute('name'));
    }
}

class SecurityBoundaryForm extends InertiaForm
{
    public function __construct(mixed $resource = null, protected array $fields = [])
    {
        parent::__construct($resource);
    }

    public function schema(): array
    {
        return $this->fields;
    }
}

beforeEach(function () {
    Schema::dropIfExists('form_security_record_option');
    Schema::dropIfExists('form_security_options');
    Schema::dropIfExists('form_security_records');

    Schema::create('form_security_records', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('tenant_id');
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('name')->nullable();
        $table->string('password')->nullable();
        $table->string('custom_password')->nullable();
        $table->string('api_token')->nullable();
        $table->string('secret_key')->nullable();
        $table->string('integration_value')->nullable();
        $table->string('nullable_value')->nullable();
        $table->timestamps();
    });

    Schema::create('form_security_options', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('tenant_id');
        $table->string('name');
        $table->boolean('active')->default(true);
    });

    Schema::create('form_security_record_option', function (Blueprint $table) {
        $table->unsignedBigInteger('record_id');
        $table->unsignedBigInteger('option_id');
    });
});

function securityOptions(array $attributes = []): array
{
    return array_merge(['tenant_id' => 1, 'name' => 'Option', 'active' => true], $attributes);
}

it('preserves local relationship constraints and global model scopes', function () {
    FormSecurityOption::create(securityOptions(['name' => 'Tenant one']));
    FormSecurityOption::create(securityOptions(['tenant_id' => 2, 'name' => 'Tenant two']));
    FormSecurityOption::create(securityOptions(['name' => 'Inactive', 'active' => false]));
    $record = FormSecurityRecord::create(['tenant_id' => 1, 'name' => 'Owner']);

    $field = Select::make('option_id')
        ->relationship('options', 'name')
        ->relationshipOptions(fn (Builder $query) => $query->where('tenant_id', 1));

    $serialized = (new SecurityBoundaryForm($record, [$field]))->toArray()['schema'][0]['meta']['options'];

    expect($serialized)->toBe([1 => 'Tenant one']);
});

it('does not widen a limit already applied by the relationship', function () {
    FormSecurityOption::create(securityOptions(['name' => 'First']));
    FormSecurityOption::create(securityOptions(['name' => 'Second']));
    $record = FormSecurityRecord::create(['tenant_id' => 1]);
    $field = Select::make('limitedOptions')
        ->relationship('limitedOptions', 'name')
        ->relationshipOptions(fn (Builder $query) => $query);

    expect((new SecurityBoundaryForm($record, [$field]))->toArray()['schema'][0]['meta']['options'])
        ->toHaveCount(1);
});

it('preserves belongs-to-many pivot and relation constraints', function () {
    $allowed = FormSecurityOption::create(securityOptions(['name' => 'Allowed']));
    $otherTenant = FormSecurityOption::create(securityOptions(['tenant_id' => 2, 'name' => 'Other tenant']));
    $record = FormSecurityRecord::create(['tenant_id' => 1]);
    DB::table('form_security_record_option')->insert([
        ['record_id' => $record->id, 'option_id' => $allowed->id],
        ['record_id' => $record->id, 'option_id' => $otherTenant->id],
    ]);

    $field = Select::make('tags')
        ->relationship('tags', 'name')
        ->relationshipOptions(fn (Builder $query) => $query);

    expect((new SecurityBoundaryForm($record, [$field]))->toArray()['schema'][0]['meta']['options'])
        ->toBe([$allowed->id => 'Allowed']);
});

it('resolves belongs-to options from the constrained relation query', function () {
    $option = FormSecurityOption::create(securityOptions(['name' => 'Assigned option']));
    $record = FormSecurityRecord::create(['tenant_id' => 1, 'option_id' => $option->id]);
    $field = Select::make('option_id')
        ->relationship('option', 'name')
        ->relationshipOptions(fn (Builder $query) => $query);

    expect((new SecurityBoundaryForm($record, [$field]))->toArray()['schema'][0]['meta']['options'])
        ->toBe([$option->id => 'Assigned option']);
});

it('rejects relationship resolution without an explicit constrained query', function () {
    $record = FormSecurityRecord::create(['tenant_id' => 1]);
    $field = Select::make('option_id')->relationship('options', 'name');

    expect(fn () => (new SecurityBoundaryForm($record, [$field]))->toArray())
        ->toThrow(LogicException::class, 'explicit constrained relationshipOptions() query');
});

it('requires valid relationship methods and reports failures without database details', function () {
    $record = FormSecurityRecord::create(['tenant_id' => 1]);
    $invalid = Select::make('option_id')->relationship('missing', 'name')->relationshipOptions(fn ($query) => $query);
    $unsupported = Select::make('option_id')->relationship('invalidRelationMethod', 'name')->relationshipOptions(fn ($query) => $query);
    $badResolver = Select::make('option_id')->relationship('options', 'name')->relationshipOptions(fn () => throw new RuntimeException('query bindings contain a secret'));
    $replacementBuilder = Select::make('option_id')->relationship('options', 'name')->relationshipOptions(fn () => FormSecurityOption::query());

    expect(fn () => (new SecurityBoundaryForm($record, [$invalid]))->toArray())
        ->toThrow(RuntimeException::class, "relationship 'missing'");
    expect(fn () => (new SecurityBoundaryForm($record, [$unsupported]))->toArray())
        ->toThrow(RuntimeException::class, 'Unable to resolve options');
    expect(fn () => (new SecurityBoundaryForm($record, [$badResolver]))->toArray())
        ->toThrow(RuntimeException::class, 'Unable to resolve options');
    expect(fn () => (new SecurityBoundaryForm($record, [$replacementBuilder]))->toArray())
        ->toThrow(RuntimeException::class, 'Unable to resolve options');
});

it('keeps explicit options and empty scoped relationships predictable', function () {
    $record = FormSecurityRecord::create(['tenant_id' => 1]);
    $explicit = Select::make('option_id')
        ->relationship('options', 'name')
        ->options(['manual' => 'Manual option']);
    $empty = Select::make('empty_option')
        ->relationship('options', 'name')
        ->relationshipOptions(fn (Builder $query) => $query->where('tenant_id', 99));

    $schema = (new SecurityBoundaryForm($record, [$explicit, $empty]))->toArray()['schema'];

    expect($schema[0]['meta']['options'])->toBe(['manual' => 'Manual option'])
        ->and($schema[1]['meta']['options'])->toBe([]);
});

it('rejects oversized relationship datasets and invalid limits', function () {
    foreach (range(1, 3) as $index) {
        FormSecurityOption::create(securityOptions(['name' => "Option {$index}"]));
    }
    $record = FormSecurityRecord::create(['tenant_id' => 1]);
    $field = Select::make('option_id')
        ->relationship('options', 'name')
        ->relationshipOptions(fn (Builder $query) => $query, 2);

    expect(fn () => (new SecurityBoundaryForm($record, [$field]))->toArray())
        ->toThrow(LogicException::class, 'exceed the configured limit of 2');
    expect(fn () => Select::make('option_id')->relationshipOptions(fn ($query) => $query, 501))
        ->toThrow(InvalidArgumentException::class, 'between 1 and 500');
});

it('does not hydrate password or common secret fields unless explicitly opted in', function () {
    $record = FormSecurityRecord::create([
        'tenant_id' => 1,
        'name' => 'Normal value',
        'password' => 'hashed-password',
        'custom_password' => 'custom-secret',
        'api_token' => 'api-secret',
        'secret_key' => 'private-secret',
        'integration_value' => 'integration-secret',
    ]);
    $fields = [
        TextInput::make('password')->password(),
        TextInput::make('custom_password')->type('password'),
        TextInput::make('api_token'),
        TextInput::make('secret_key'),
        TextInput::make('integration_value')->sensitive(),
        TextInput::make('name'),
    ];

    $defaults = (new SecurityBoundaryForm($record, $fields))->toArray()['defaults'];

    expect($defaults)->toBe([
        'password' => null,
        'custom_password' => null,
        'api_token' => null,
        'secret_key' => null,
        'integration_value' => null,
        'name' => 'Normal value',
    ]);

    expect((new SecurityBoundaryForm($record, [TextInput::make('secret_key')->hydrateSensitiveDefault()]))->toArray()['defaults'])
        ->toBe(['secret_key' => 'private-secret']);
});

it('applies sensitive hydration policy inside nested grids', function () {
    $record = FormSecurityRecord::create(['tenant_id' => 1, 'password' => 'hashed-password', 'name' => 'Ada']);
    $grid = Grid::make(2)->schema([
        TextInput::make('password')->default(''),
        Grid::make(1)->schema([TextInput::make('name')]),
    ]);

    expect((new SecurityBoundaryForm($record, [$grid]))->toArray()['defaults'])
        ->toBe(['password' => '', 'name' => 'Ada']);
});

it('hydrates nullable values and declared accessors without probing model relationships', function () {
    $record = FormSecurityRecord::create(['tenant_id' => 1, 'name' => 'Ada', 'nullable_value' => null]);
    $form = new SecurityBoundaryForm($record, [
        TextInput::make('nullable_value')->default('stale'),
        TextInput::make('display_name'),
        TextInput::make('options')->default('not loaded'),
    ]);

    expect($form->toArray()['defaults'])->toBe([
        'nullable_value' => null,
        'display_name' => 'ADA',
        'options' => 'not loaded',
    ]);
});
