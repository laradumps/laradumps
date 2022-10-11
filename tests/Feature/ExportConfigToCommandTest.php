<?php

use LaraDumps\LaraDumps\Actions\ExportConfigToCommand;

it('can generate the config command', function () {
    config()->set('laradumps.host', '127.0.0.1');
    config()->set('laradumps.port', '8181');
    config()->set('laradumps.send_queries', false);
    config()->set('laradumps.send_log_applications', false);
    config()->set('laradumps.send_livewire_components', false);
    config()->set('laradumps.send_livewire_events', false);
    config()->set('laradumps.send_livewire_dispatch', false);
    config()->set('laradumps.send_livewire_failed_validation.enabled', false);
    config()->set('laradumps.auto_clear_on_page_reload', false);
    config()->set('laradumps.auto_invoke_app', true);
    config()->set('laradumps.preferred_ide', 'phpstorm');

    expect(ExportConfigToCommand::handle())->toBe('php artisan ds:init --no-interaction  --host=127.0.0.1  --auto_invoke=true  --send_queries=false  --send_logs=false  --send_livewire=false  --livewire_events=false  --livewire_dispatch=false  --livewire_validation=false  --livewire_autoclear=false  --ide=phpstorm');
});
