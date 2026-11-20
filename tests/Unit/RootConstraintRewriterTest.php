<?php

/**
 * Unit coverage for {@see \InEngine\ModulusComposer\Composer\RootConstraintRewriter}.
 */

use InEngine\Modulus\Upgrades\UpgradeConstraintPolicy;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Exceptions\InvalidComposerJsonException;

it('rewrites require constraints using patch policy ~x.y.z', function () {
    $path = sys_get_temp_dir().'/modulus-composer-json-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode([
        'name' => 'host/app',
        'require' => [
            'php' => '^8.5',
            'inengine/alert' => '^1.0',
        ],
    ], JSON_PRETTY_PRINT));

    $rewriter = new RootConstraintRewriter($path, new UpgradeConstraintPolicy(UpgradeConstraintPolicy::PATCH));

    expect($rewriter->get('inengine/alert'))->toBe('^1.0');

    $constraint = $rewriter->setSuggested('inengine/alert', '2.3.1');

    expect($constraint)->toBe('~2.3.1')
        ->and($rewriter->get('inengine/alert'))->toBe('~2.3.1');

    $decoded = json_decode((string) file_get_contents($path), true);
    expect($decoded['require']['inengine/alert'])->toBe('~2.3.1')
        ->and($decoded['require']['php'])->toBe('^8.5');

    unlink($path);
});

it('throws when composer.json is missing', function () {
    $rewriter = new RootConstraintRewriter('/tmp/does-not-exist-'.uniqid('', true).'.json');

    $rewriter->get('inengine/alert');
})->throws(InvalidComposerJsonException::class);

it('sets an explicit constraint and sorts require keys', function () {
    $path = sys_get_temp_dir().'/modulus-composer-json-set-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode([
        'name' => 'host/app',
        'require' => [
            'php' => '^8.5',
            'zebra/pkg' => '^1.0',
        ],
    ], JSON_PRETTY_PRINT));

    $rewriter = new RootConstraintRewriter($path);
    $rewriter->set('inengine/alert', '^2.0');

    $require = json_decode((string) file_get_contents($path), true)['require'];
    expect(array_keys($require))->toBe(['inengine/alert', 'php', 'zebra/pkg'])
        ->and($require['inengine/alert'])->toBe('^2.0');

    unlink($path);
});

it('suggests caret constraints when policy is minor', function () {
    $path = sys_get_temp_dir().'/modulus-composer-json-minor-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode([
        'name' => 'host/app',
        'require' => ['inengine/alert' => '~1.0.0'],
    ], JSON_PRETTY_PRINT));

    $rewriter = new RootConstraintRewriter(
        $path,
        new UpgradeConstraintPolicy(UpgradeConstraintPolicy::MINOR)
    );

    expect($rewriter->setSuggested('inengine/alert', '2.1.0'))->toBe('^2.1.0');

    unlink($path);
});
