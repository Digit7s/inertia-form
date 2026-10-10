<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('registers the generator and publishes only the documented component tree', function () {
    expect(Artisan::all())->toHaveKey('make:inertia-form');

    Artisan::call('vendor:publish', [
        '--tag' => 'inertia-form-components',
        '--force' => true,
        '--no-interaction' => true,
    ]);

    $publishedRoot = resource_path('js/InertiaForm/components');
    $relativeFiles = fn (string $directory): array => collect(File::allFiles($directory))
        ->map(fn (SplFileInfo $file) => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();

    expect($relativeFiles($publishedRoot))->toBe($relativeFiles(dirname(__DIR__, 2).'/resources/js/components'));

    $customizedComponent = $publishedRoot.'/InertiaForm.vue';
    File::append($customizedComponent, "\n<!-- Consumer customization -->\n");
    $customizedContents = File::get($customizedComponent);

    Artisan::call('vendor:publish', [
        '--tag' => 'inertia-form-components',
        '--no-interaction' => true,
    ]);

    expect(File::get($customizedComponent))->toBe($customizedContents);
});

it('generates parseable forms in app Forms and protects existing files', function () {
    $path = app_path('Forms/GeneratedForm.php');
    File::ensureDirectoryExists(dirname($path));
    File::delete($path);

    expect(Artisan::call('make:inertia-form', ['name' => 'GeneratedForm']))->toBe(Command::SUCCESS)
        ->and(file_exists($path))->toBeTrue();

    $generated = file_get_contents($path);
    expect(fn () => token_get_all($generated, TOKEN_PARSE))->not->toThrow(Throwable::class)
        ->and($generated)->toContain('namespace App\\Forms;')
        ->and($generated)->toContain('use Digit7s\\InertiaForm\\InertiaForm;')
        ->and($generated)->toContain('use Digit7s\\InertiaForm\\Fields\\TextInput;');

    file_put_contents($path, "<?php\n// Consumer-owned form\n");
    Artisan::call('make:inertia-form', ['name' => 'GeneratedForm', '--no-interaction' => true]);
    expect(file_get_contents($path))->toBe("<?php\n// Consumer-owned form\n");

    $command = Artisan::all()['make:inertia-form'];
    expect($command->getDefinition()->hasOption('model'))->toBeFalse();
    File::delete($path);
});
