<?php

use Illuminate\Support\Facades\{Config, File};

beforeEach(function () {
    $this->view          = getLaravelDir() . 'resources/views/view.blade.php';
    $this->controller    = getLaravelDir() . 'app/Http/Controllers/LaraDumpsController.php';
    $this->bladeHtml     =  '<div>@ds(\'Hello\') </div>';
});

it('display "No ds() found" when not ds found', function () {
    if (File::exists($this->view)) {
        File::delete($this->view);
    }

    if (File::exists($this->controller)) {
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

it('displays error when found on controller', function ($dsFunction) {
    createControllerClass($this, $dsFunction);

    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain($dsFunction)
        ->expectsOutputToContain('Found 1 error / 1 file')
        ->assertFailed();
})->with([
    ["ds('Hello from Controller')->label('label');"],
    ["@ds('supress error!');"],
    ["dsq('silence!');"],
    ["dsd('stop me!');"],
    ["ds2('screen 2');"],
    ["ds3('screen 3');"],
    ["ds4('screen 4');"],
    ["ds5('screen 5');"],
    ["//ds('commented');"],
])
->requiresLaravel9();

it('displays error when ds macro has line breaks', function () {
    $dsFunction = <<<PHP
        User::query()->where('id', 20)
        ->ds()
        ->get();
    PHP;

    createControllerClass($this, $dsFunction);

    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('->ds()')
        ->expectsOutputToContain('Found 1 error / 1 file')
        ->assertFailed();
})
->requiresLaravel9();

it('does displays error when found on controller when not specified in config', function () {
    if (File::exists($this->view)) {
        File::delete($this->view);
    }

    createControllerClass($this, '//Nothing here');

    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        resource_path(),
    ]);

    $this->artisan('ds:check')
        ->doesntExpectOutputToContain('error')
        ->expectsOutputToContain('No ds() found.')
        ->assertSuccessful();
})->requiresLaravel9();

it('will not match a partial funcion', function () {
    if (File::exists($this->view)) {
        File::delete($this->view);
    }

    createControllerClass($this, 'blablads(\'test\');');

    $this->assertFileExists($this->controller);

    Config::set('laradumps.ci_check.directories', [
        base_path('app'),
    ]);

    $this->artisan('ds:check')
        ->doesntExpectOutputToContain('error')
        ->expectsOutputToContain('No ds() found.')
        ->assertSuccessful();
})->requiresLaravel9();

it('displays errors when found on controller and resources path', function () {
    createBlade($this);
    createControllerClass($this, "ds('Hello from Controller')->label('label');");

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

it('displays errors when for dsAutoClearOnPageReload directive', function () {
    $html = <<<HTML
        <!-- Scripts -->
        @livewireScripts
        @if(app()->environment('local'))
            @dsAutoClearOnPageReload
        @endif
        </body>
    HTML;

    createBlade($this, $html);

    $this->assertFileExists($this->view);

    Config::set('laradumps.ci_check.directories', [
        resource_path(),
    ]);

    $this->artisan('ds:check')
        ->expectsOutputToContain('@dsAutoClearOnPageReload')
        ->expectsOutputToContain('Found 1 error / 1 file')
        ->assertFailed();
})->requiresLaravel9();

it('ignore an error when encountering specific text on the line', function () {
    createBlade($this);
    createControllerClass($this, '//Hello from');

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

//Helpers

function createBlade($self, string $bladeHtml = ''): void
{
    if (File::exists($self->view)) {
        File::delete($self->view);
    }

    if (empty($bladeHtml)) {
        $bladeHtml = $self->bladeHtml;
    }

    File::put($self->view, $bladeHtml);
}

function createControllerClass($self, $dsFunction = ''): void
{
    if (File::exists($self->controller)) {
        File::delete($self->controller);
    }

    $phpCode = <<<PHP
    <?php

    namespace App\Http\Controllers;

    use Illuminate\Routing\Controller as BaseController;

    class LaraDumpsController extends BaseController
    {
        public function index()
        {
            $dsFunction
        }
    }
    PHP;

    File::put($self->controller, $phpCode);
}
