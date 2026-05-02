<?php

use Digit7s\InertiaForm\Fields\TextInput;

it('can be instantiated using make', function () {
    $field = TextInput::make('first_name');

    expect($field)->toBeInstanceOf(TextInput::class);
});

it('sets the name and generates a default headline label', function () {
    $field = TextInput::make('first_name');

    expect($field->toArray())
        ->name->toBe('first_name')
        ->label->toBe('First Name');
});

it('can set a custom label', function () {
    $field = TextInput::make('first_name')->label('Given Name');

    expect($field->toArray())->label->toBe('Given Name');
});

it('can set a default value', function () {
    $field = TextInput::make('email')->default('test@example.com');

    expect($field->toArray())->default->toBe('test@example.com');
});

it('can be marked as required', function () {
    $field = TextInput::make('email')->required();

    expect($field->toArray())->required->toBeTrue();

    $field->required(false);
    expect($field->toArray())->required->toBeFalse();
});

it('can set a placeholder', function () {
    $field = TextInput::make('email')->placeholder('Enter your email');

    expect($field->toArray())->placeholder->toBe('Enter your email');
});

it('can set a suffix icon', function () {
    $field = TextInput::make('email')->suffixIcon('heroicon-o-mail');

    expect($field->toArray())->suffix_icon->toBe('heroicon-o-mail');
});

it('can set a column span', function () {
    $field = TextInput::make('email')->columnSpan(2);

    expect($field->toArray())->column_span->toBe(2);

    $field->columnSpan('full');
    expect($field->toArray())->column_span->toBe('full');
});

it('can add custom metadata', function () {
    $field = TextInput::make('email')->meta('custom_key', 'custom_value');

    expect($field->toArray()['meta'])
        ->toHaveKey('custom_key', 'custom_value');
});

it('can set various input types', function (string $method, string $expectedType) {
    $field = TextInput::make('field')->$method();

    expect($field->toArray()['meta']['input_type'])->toBe($expectedType);
})->with([
    ['email', 'email'],
    ['password', 'password'],
    ['numeric', 'number'],
    ['tel', 'tel'],
    ['url', 'url'],
    ['color', 'color'],
]);

it('can set a manual input type', function () {
    $field = TextInput::make('field')->type('search');

    expect($field->toArray()['meta']['input_type'])->toBe('search');
});

it('can set character length constraints', function () {
    $field = TextInput::make('username')
        ->minLength(3)
        ->maxLength(20);

    $data = $field->toArray();

    expect($data['meta'])
        ->min_length->toBe(3)
        ->max_length->toBe(20);
});

it('can set numeric value constraints', function () {
    $field = TextInput::make('age')
        ->numeric()
        ->minValue(18)
        ->maxValue(100)
        ->step(0.5);

    $data = $field->toArray();

    expect($data['meta'])
        ->min_value->toBe(18)
        ->max_value->toBe(100)
        ->step->toBe(0.5);
});

it('can set prefix and suffix text', function () {
    $field = TextInput::make('price')
        ->prefix('$')
        ->suffix('.00');

    $data = $field->toArray();

    expect($data['meta'])
        ->prefix->toBe('$')
        ->suffix->toBe('.00');
});

it('can be disabled, read-only, or auto-focused', function () {
    $field = TextInput::make('field')
        ->disabled()
        ->readOnly()
        ->autofocus();

    $data = $field->toArray();

    expect($data['meta'])
        ->disabled->toBeTrue()
        ->read_only->toBeTrue()
        ->autofocus->toBeTrue();
});

it('can set autocomplete attribute', function () {
    $field = TextInput::make('email')->autocomplete('email');

    expect($field->toArray()['meta']['autocomplete'])->toBe('email');
});

it('serializes to the correct array structure', function () {
    $field = TextInput::make('first_name')
        ->label('Your Name')
        ->placeholder('John Doe')
        ->default('Jane')
        ->required()
        ->columnSpan(1)
        ->meta('foo', 'bar');

    expect($field->toArray())->toBe([
        'name' => 'first_name',
        'label' => 'Your Name',
        'placeholder' => 'John Doe',
        'type' => 'text',
        'default' => 'Jane',
        'required' => true,
        'suffix_icon' => null,
        'column_span' => 1,
        'meta' => [
            'foo' => 'bar',
        ],
    ]);
});

it('handles null values for optional attributes', function () {
    $field = TextInput::make('test');
    $data = $field->toArray();

    expect($data['placeholder'])->toBeNull();
    expect($data['suffix_icon'])->toBeNull();
    expect($data['column_span'])->toBeNull();
    expect($data['default'])->toBeNull();
    expect($data['meta'])->toBeEmpty();
});
