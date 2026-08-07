<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('points the tracking script at the tunnel url when the tunnel is enabled', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', true);
    config()->set('rybbit.tunnel.url', '/rybbit');

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->toContain('src="/rybbit/script.js"')
        ->toContain('data-site-id="site-123"');
});

it('falls back to the host when the tunnel is disabled', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.tunnel.url', '/rybbit');

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->toContain('src="https://rybbit.io/script.js"')
        ->toContain('data-site-id="site-123"');
});

it('adds the optional script attributes when configured', function () {
    config()->set('rybbit.site_id', 'bfce983839d1');
    config()->set('rybbit.host', 'https://analytics.cocosport.it');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.script.debounce', 503);
    config()->set('rybbit.script.skip_patterns', ['/admin/**']);
    config()->set('rybbit.script.mask_patterns', ['/users/**/profile']);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->toContain('src="https://analytics.cocosport.it/script.js"')
        ->toContain('data-site-id="bfce983839d1"')
        ->toContain('data-debounce="503"')
        ->toContain('data-skip-patterns=\'["/admin/**"]\'')
        ->toContain('data-mask-patterns=\'["/users/**/profile"]\'');
});

it('omits the optional script attributes when not configured', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.script.debounce', null);
    config()->set('rybbit.script.skip_patterns', []);
    config()->set('rybbit.script.mask_patterns', []);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->not->toContain('data-debounce')
        ->not->toContain('data-skip-patterns')
        ->not->toContain('data-mask-patterns');
});

it('renders nothing when the site_id is missing', function () {
    config()->set('rybbit.site_id', null);
    config()->set('rybbit.host', 'https://rybbit.io');

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect(trim($html))->toBe('');
});

it('renders nothing when neither the tunnel nor the host are available', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', null);
    config()->set('rybbit.tunnel.enabled', false);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect(trim($html))->toBe('');
});

it('reflects config changes without recompiling the view', function () {
    config()->set('rybbit.site_id', 'first-site');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);

    $compiled = Blade::compileString('@rybbit');

    ob_start();
    eval('?>'.$compiled);
    $first = ob_get_clean();

    config()->set('rybbit.site_id', 'second-site');

    ob_start();
    eval('?>'.$compiled);
    $second = ob_get_clean();

    expect($first)->toContain('data-site-id="first-site"');
    expect($second)->toContain('data-site-id="second-site"');
});
