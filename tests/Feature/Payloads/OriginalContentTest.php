<?php

use LaraDumps\LaraDumps\Payloads\{BladePayload, BrainPayload, ContextPayload, LogPayload, MailablePayload, MarkdownPayload, ModelPayload, QueriesPayload, RoutesPayload};
use LaraDumps\LaraDumps\Tests\Support\Models\Dish;

describe('original_content in src/Payloads', function () {
    it('BladePayload sets and retrieves original_content', function () {
        $dump = '<div>Test</div>';
        $payload = new BladePayload($dump);

        $payload->setOriginalContent($dump);
        expect($payload->getOriginalContent())->toBe($dump);
    });

    it('BrainPayload sets and retrieves original_content', function () {
        $className = 'TestClass';
        $payload = new BrainPayload($className);

        $payload->setOriginalContent($className);
        expect($payload->getOriginalContent())->toBe($className);
    });

    it('ContextPayload sets and retrieves original_content', function () {
        $keys = ['user_id', 'request_id'];
        $payload = new ContextPayload($keys);

        $payload->setOriginalContent($keys);
        expect($payload->getOriginalContent())->toBe($keys);
    });

    it('LogPayload sets and retrieves original_content', function () {
        $log = [
            'message' => 'Test log',
            'level' => 'info',
        ];
        $payload = new LogPayload($log);

        $payload->setOriginalContent($log);
        expect($payload->getOriginalContent())->toBe($log);
    });

    it('MarkdownPayload sets and retrieves original_content', function () {
        $markdown = '# Test Markdown';
        $payload = new MarkdownPayload($markdown);

        $payload->setOriginalContent($markdown);
        expect($payload->getOriginalContent())->toBe($markdown);
    });

    it('QueriesPayload sets and retrieves original_content', function () {
        $queries = [
            ['sql' => 'SELECT * FROM users'],
            ['sql' => 'SELECT * FROM posts'],
        ];
        $payload = new QueriesPayload($queries);

        $payload->setOriginalContent($queries);
        expect($payload->getOriginalContent())->toBe($queries);
    });

    it('RoutesPayload sets and retrieves original_content', function () {
        $except = ['api/*'];
        $payload = new RoutesPayload($except);

        $payload->setOriginalContent($except);
        expect($payload->getOriginalContent())->toBe($except);
    });

    it('ModelPayload sets and retrieves original_content', function () {
        $dish = Dish::query()->first();
        if ($dish) {
            $payload = new ModelPayload($dish);

            $payload->setOriginalContent($dish);
            expect($payload->getOriginalContent())->toBe($dish);
        }
    });

    it('MailablePayload sets and retrieves original_content', function () {
        $mailable = new class()
        {
            public function subject() {}
        };

        $payload = new MailablePayload($mailable);

        $payload->setOriginalContent($mailable);
        expect($payload->getOriginalContent())->toBe($mailable);
    })->skip('Requires proper Mailable instance');
});

describe('src/Payloads edge cases', function () {
    it('BladePayload handles null original_content', function () {
        $payload = new BladePayload('<div>Test</div>');

        $payload->setOriginalContent(null);
        expect($payload->getOriginalContent())->toBeNull();
    });

    it('BrainPayload handles various original_content types', function () {
        $payload = new BrainPayload('TestClass');

        $payload->setOriginalContent('string');
        expect($payload->getOriginalContent())->toBe('string');

        $payload->setOriginalContent(['array' => 'value']);
        expect($payload->getOriginalContent())->toBe(['array' => 'value']);

        $payload->setOriginalContent(123);
        expect($payload->getOriginalContent())->toBe(123);
    });

    it('LogPayload handles nested array original_content', function () {
        $log = [
            'message' => 'Test',
            'context' => [
                'user_id' => 1,
                'trace' => ['line 1', 'line 2'],
            ],
        ];
        $payload = new LogPayload($log);

        $payload->setOriginalContent($log);
        expect($payload->getOriginalContent())->toBe($log);
    });

    it('ContextPayload handles string keys as original_content', function () {
        $keys = 'single_key';
        $payload = new ContextPayload($keys);

        $payload->setOriginalContent($keys);
        expect($payload->getOriginalContent())->toBe($keys);
    });

    it('MarkdownPayload handles multiline markdown original_content', function () {
        $markdown = "# Title\n\n## Subtitle\n\nParagraph with **bold** and *italic*";
        $payload = new MarkdownPayload($markdown);

        $payload->setOriginalContent($markdown);
        expect($payload->getOriginalContent())->toBe($markdown);
    });

    it('QueriesPayload handles empty queries original_content', function () {
        $queries = [];
        $payload = new QueriesPayload($queries);

        $payload->setOriginalContent($queries);
        expect($payload->getOriginalContent())->toBe($queries);
    });

    it('RoutesPayload handles multiple except patterns original_content', function () {
        $except = ['api/*', 'admin/*', 'test/*'];
        $payload = new RoutesPayload($except);

        $payload->setOriginalContent($except);
        expect($payload->getOriginalContent())->toBe($except);
    });
});

describe('setOriginalContent updates for src/Payloads', function () {
    it('BladePayload allows updating original_content', function () {
        $payload = new BladePayload('<div>Initial</div>');

        $payload->setOriginalContent('<div>Initial</div>');
        expect($payload->getOriginalContent())->toContain('Initial');

        $payload->setOriginalContent('<div>Updated</div>');
        expect($payload->getOriginalContent())->toContain('Updated');
    });

    it('LogPayload allows updating original_content after instantiation', function () {
        $logInitial = ['message' => 'Initial', 'level' => 'info'];
        $payload = new LogPayload($logInitial);

        $payload->setOriginalContent($logInitial);
        expect($payload->getOriginalContent())->toBe($logInitial);

        $logUpdated = ['message' => 'Updated', 'level' => 'error'];
        $payload->setOriginalContent($logUpdated);
        expect($payload->getOriginalContent())->toBe($logUpdated);
    });

    it('ContextPayload allows changing original_content type', function () {
        $payload = new ContextPayload('key1');

        $payload->setOriginalContent('string_value');
        expect($payload->getOriginalContent())->toBe('string_value');

        $payload->setOriginalContent(['key1', 'key2']);
        expect($payload->getOriginalContent())->toBe(['key1', 'key2']);

        $payload->setOriginalContent(null);
        expect($payload->getOriginalContent())->toBeNull();
    });

    it('QueriesPayload allows extending original_content', function () {
        $initialQueries = [['sql' => 'SELECT 1']];
        $payload = new QueriesPayload($initialQueries);

        $payload->setOriginalContent($initialQueries);
        expect($payload->getOriginalContent())->toHaveLength(1);

        $extendedQueries = [
            ['sql' => 'SELECT 1'],
            ['sql' => 'SELECT 2'],
            ['sql' => 'SELECT 3'],
        ];
        $payload->setOriginalContent($extendedQueries);
        expect($payload->getOriginalContent())->toHaveLength(3);
    });

    it('BrainPayload original_content works with all property types', function () {
        $payload = new BrainPayload('TestClass', 'process-123');

        $payload->setOriginalContent(['className' => 'TestClass', 'id' => 'process-123']);
        expect($payload->getOriginalContent())->toBe(['className' => 'TestClass', 'id' => 'process-123']);

        $payload->setOriginalContent('SimpleString');
        expect($payload->getOriginalContent())->toBe('SimpleString');
    });
});
