<?php

use Illuminate\Support\Facades\{Config, File};

beforeEach(function () {
    $this->view          = getLaravelDir() . 'resources/views/view.blade.php';
    $this->controller    = getLaravelDir() . 'app/Http/Controllers/LaraDumpsController.php';
});

it('display "No ds() found" when not ds found', function () {
    if (file_exists($this->view)) {
        File::delete($this->view);
    }

    if (file_exists($this->controller)) {
        File::delete($this->controller);
    }

    Config::set('laradumps.ci_check.directories', [
        resource_path(),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('No ds() found.');
})->requiresLaravel9();

it('displays error when found on resources path', function () {
    createBlade($this);

    $this->assertFileExists($this->view);

    Config::set('laradumps.ci_check.directories', [
        resource_path(),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('@ds(\'Hello\')')
        ->expectsOutputToContain('Found 1 error / 1 file')
        ->assertFailed();
})->requiresLaravel9();

it('does not display error when found on resources path when not specified in config', function () {
    createBlade($this);

    $this->assertFileExists($this->view);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('No ds() found.')
        ->assertSuccessful();
})->requiresLaravel9();

it('displays error when found on controller', function () {
    createControllerClass($this);

    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('ds(\'Hello from Controller\')->label(\'label\')')
        ->expectsOutputToContain('Found 1 error / 1 file')
        ->assertFailed();
})->requiresLaravel9();

it('does displays error when found on controller when not specified in config', function () {
    if (file_exists($this->view)) {
        File::delete($this->view);
    }

    createControllerClass($this);

    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        resource_path(),
    ]);

    $this->artisan('ds:check')
        ->doesntExpectOutputToContain('error')
        ->expectsOutputToContain('No ds() found.')
        ->assertSuccessful();
})->requiresLaravel9();

it('displays errors when found on controller and resources path', function () {
    createBlade($this);
    createControllerClass($this);

    $this->assertFileExists($this->view);
    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
        resource_path(),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('@ds(\'Hello\')')
        ->expectsOutputToContain('ds(\'Hello from Controller\')->label(\'label\')')
        ->expectsOutputToContain('Found 2 errors / 2 files')
        ->assertFailed();
})->requiresLaravel9();

it('ignore an error when encountering specific text on the line', function () {
    createBlade($this);
    createControllerClass($this);

    $this->assertFileExists($this->view);
    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
        resource_path(),
    ]);

    Config::set('laradumps.ci_check.ignore_line_when_contains_text', [
        'Hello from',
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('@ds(\'Hello\')')
        ->doesntExpectOutputToContain('ds(\'Hello from Controller\')->label(\'label\')')
        ->expectsOutputToContain('Found 1 error / 1 file')
        ->assertFailed();
})->requiresLaravel9();

function createBlade($self): void
{
    $blade = '<div>@ds(\'Hello\') </div>';

    if (!file_exists($self->view)) {
        file_put_contents($self->view, $blade);
    }
}

function createControllerClass($self): void
{
    $html = <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class LaraDumpsController extends BaseController
{
    public function index()
    {
        ds('Hello from Controller')->label('label');
    }
}
PHP;

    if (!file_exists($self->controller)) {
        file_put_contents($self->controller, $html);
    }
}
