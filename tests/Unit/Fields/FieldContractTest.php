<?php

use Digit7s\InertiaForm\Fields\CheckboxList;
use Digit7s\InertiaForm\Fields\DatePicker;
use Digit7s\InertiaForm\Fields\DateTimePicker;
use Digit7s\InertiaForm\Fields\File;
use Digit7s\InertiaForm\Fields\MarkdownEditor;
use Digit7s\InertiaForm\Fields\Radio;
use Digit7s\InertiaForm\Fields\RichTextEditor;
use Digit7s\InertiaForm\Fields\Select;
use Digit7s\InertiaForm\Fields\Textarea;
use Digit7s\InertiaForm\Fields\TextInput;
use Digit7s\InertiaForm\Fields\TimePicker;
use Digit7s\InertiaForm\InertiaForm;
use Digit7s\InertiaForm\Layouts\Grid;

it('serializes every supported field with a stable renderer type', function () {
    $fields = [
        TextInput::make('text'),
        Textarea::make('textarea'),
        Select::make('select'),
        CheckboxList::make('checkboxes'),
        DatePicker::make('date'),
        TimePicker::make('time'),
        DateTimePicker::make('datetime'),
        File::make('file'),
        Radio::make('radio'),
        RichTextEditor::make('rich'),
        MarkdownEditor::make('markdown'),
    ];

    expect(array_column(array_map(fn ($field) => $field->toArray(), $fields), 'type'))
        ->toBe(['text', 'textarea', 'select', 'checkbox-list', 'date', 'time', 'datetime', 'file', 'radio', 'rich-text-editor', 'markdown-editor']);
});

it('preserves core field defaults and metadata in the serialized contract', function () {
    $fields = [
        TextInput::make('name')->default('Ada')->required()->email()->minLength(3),
        Textarea::make('bio')->default('')->rows(5)->disabled(),
        Select::make('role')->default(2)->options(['2' => 'Editor'])->multiple()->searchable(),
        CheckboxList::make('tags')->options(['a' => 'A'])->columns(['default' => 1, 'md' => 2]),
        DatePicker::make('birthday')->default(null),
        TimePicker::make('start')->default(''),
        DateTimePicker::make('appointment')->seconds()->minutesStep(5),
        File::make('avatar')->required(),
    ];

    $data = array_map(fn ($field) => $field->toArray(), $fields);

    expect($data[0])->toMatchArray([
        'name' => 'name', 'type' => 'text', 'default' => 'Ada', 'required' => true,
    ])->and($data[0]['meta'])->toMatchArray(['input_type' => 'email', 'min_length' => 3]);
    expect($data[1]['meta'])->toMatchArray(['rows' => 5, 'disabled' => true]);
    expect($data[2]['meta'])->toMatchArray(['multiple' => true, 'searchable' => true, 'options' => ['2' => 'Editor']]);
    expect($data[3]['default'])->toBe([])->and($data[3]['meta']['columns'])->toBe(['default' => 1, 'md' => 2]);
    expect($data[4]['default'])->toBeNull()->and($data[5]['default'])->toBe('');
    expect($data[6]['meta'])->toMatchArray(['has_seconds' => true, 'minutes_step' => 5]);
    expect($data[7])->toMatchArray(['type' => 'file', 'required' => true]);
});

it('serializes nested grids and keeps all child defaults available', function () {
    $form = new class extends InertiaForm
    {
        public function schema(): array
        {
            return [
                Grid::make(2)->schema([
                    TextInput::make('first')->default('one')->columnSpan(1),
                    Grid::make(3)->columnSpan(2)->schema([
                        Textarea::make('nested')->default('two'),
                    ]),
                ]),
            ];
        }
    };

    $payload = $form->toArray();

    expect($payload['schema'][0]['type'])->toBe('grid')
        ->and($payload['schema'][0]['columns'])->toBe(2)
        ->and($payload['schema'][0]['schema'][1]['type'])->toBe('grid')
        ->and($payload['schema'][0]['schema'][1]['column_span'])->toBe(2)
        ->and($payload['schema'][0]['schema'][1]['schema'][0]['name'])->toBe('nested')
        ->and($payload['defaults'])->toBe(['first' => 'one', 'nested' => 'two']);
});

it('evaluates the schema once when creating a payload', function () {
    $form = new class extends InertiaForm
    {
        public int $calls = 0;

        public function schema(): array
        {
            $this->calls++;

            return [TextInput::make('name')->default('stable')];
        }
    };

    $payload = $form->toArray();

    expect($form->calls)->toBe(1)
        ->and($payload['schema'][0]['name'])->toBe('name')
        ->and($payload['defaults']['name'])->toBe('stable');
});
