<?php

use Digit7s\InertiaForm\Fields\DatePicker;
use Digit7s\InertiaForm\Fields\TextInput;
use Digit7s\InertiaForm\Fields\TimePicker;
use Digit7s\InertiaForm\InertiaForm;
use Digit7s\InertiaForm\Layouts\Grid;
use Illuminate\Database\Eloquent\Model;

// Concrete implementation for testing
class TestForm extends InertiaForm
{
    public function schema(): array
    {
        return [
            TextInput::make('name'),
            Grid::make(2)->schema([
                TextInput::make('email'),
            ]),
        ];
    }
}

it('can be instantiated', function () {
    $form = TestForm::make();
    expect($form)->toBeInstanceOf(InertiaForm::class);
});

it('serializes to array with correct structure', function () {
    $form = TestForm::make()
        ->title('Test Form')
        ->description('This is a test');

    $data = $form->toArray();

    expect($data)
        ->toHaveKeys(['schema', 'defaults', 'action', 'method', 'style', 'title', 'description'])
        ->title->toBe('Test Form')
        ->description->toBe('This is a test')
        ->method->toBe('post') // Default
        ->style->toBe('default');

    expect($data['schema'])->toHaveCount(2);
    expect($data['schema'][1]['type'])->toBe('grid');
    expect($data['schema'][1]['schema'])->toHaveCount(1);
});

it('detects method from model existence', function () {
    $model = Mockery::mock(Model::class);
    $model->shouldReceive('getAttribute')->andReturn(null);

    // New model -> post
    $model->exists = false;
    $form = TestForm::make($model);
    expect($form->toArray()['method'])->toBe('post');

    // Existing model -> put
    $model->exists = true;
    $form = TestForm::make($model);
    expect($form->toArray()['method'])->toBe('put');
});

it('can explicitly set action and method', function () {
    $form = TestForm::make()
        ->action('/custom-action')
        ->method('patch');

    $data = $form->toArray();
    expect($data['action'])->toBe('/custom-action');
    expect($data['method'])->toBe('patch');
});

it('resolves defaults from resource', function () {
    $resource = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $form = TestForm::make((object) $resource);
    $data = $form->toArray();

    expect($data['defaults'])
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com');
});

it('resolves redirect url from string or closure', function () {
    $form = TestForm::make(['id' => 123]);

    $form->redirect('/dashboard');
    expect($form->getRedirectUrl())->toBe('/dashboard');

    $form->redirect(fn ($resource) => "/users/{$resource['id']}");
    expect($form->getRedirectUrl())->toBe('/users/123');
});

it('handles model collections for multi-select defaults', function () {
    $role1 = (object) ['name' => 'admin'];
    $role2 = (object) ['name' => 'editor'];

    $resource = new class
    {
        public $roles;

        public function __construct()
        {
            $this->roles = collect([(object) ['name' => 'admin'], (object) ['name' => 'editor']]);
        }
    };

    $form = new class($resource) extends InertiaForm
    {
        public function schema(): array
        {
            return [TextInput::make('roles')];
        }
    };

    $data = $form->toArray();
    expect($data['defaults']['roles'])->toBe(['admin', 'editor']);
});

it('resolves date objects to strings in defaults', function () {
    $resource = (object) [
        'published_at' => new DateTime('2023-01-01 12:30:00'),
    ];

    $form = new class($resource) extends InertiaForm
    {
        public function schema(): array
        {
            return [
                DatePicker::make('published_at'),
            ];
        }
    };

    $data = $form->toArray();
    expect($data['defaults']['published_at'])->toBe('2023-01-01');
});

it('resolves time objects to strings in defaults', function () {
    $resource = (object) [
        'start_time' => new DateTime('2023-01-01 14:45:00'),
    ];

    $form = new class($resource) extends InertiaForm
    {
        public function schema(): array
        {
            return [
                TimePicker::make('start_time'),
            ];
        }
    };

    $data = $form->toArray();
    expect($data['defaults']['start_time'])->toBe('14:45');
});
