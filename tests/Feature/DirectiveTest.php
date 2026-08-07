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
    config()->set('rybbit.script.tag', 'v2-launch');

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->toContain('src="https://analytics.cocosport.it/script.js"')
        ->toContain('data-site-id="bfce983839d1"')
        ->toContain('data-debounce="503"')
        ->toContain('data-skip-patterns=\'["/admin/**"]\'')
        ->toContain('data-mask-patterns=\'["/users/**/profile"]\'')
        ->toContain('data-tag="v2-launch"');
});

it('omits the optional script attributes when not configured', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.script.debounce', null);
    config()->set('rybbit.script.skip_patterns', []);
    config()->set('rybbit.script.mask_patterns', []);
    config()->set('rybbit.script.tag', null);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->not->toContain('data-debounce')
        ->not->toContain('data-skip-patterns')
        ->not->toContain('data-mask-patterns')
        ->not->toContain('data-tag');
});

it('adds the session replay attributes when configured', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.script.replay.mask_text_selectors', ['.user-name', '#email']);
    config()->set('rybbit.script.replay.block_class', 'rr-block');
    config()->set('rybbit.script.replay.block_selector', '.admin-panel');
    config()->set('rybbit.script.replay.ignore_class', 'rr-ignore');
    config()->set('rybbit.script.replay.ignore_selector', "input[name='ssn']");
    config()->set('rybbit.script.replay.mask_text_class', 'rr-mask');
    config()->set('rybbit.script.replay.mask_all_inputs', false);
    config()->set('rybbit.script.replay.mask_input_options', ['password' => true, 'email' => true]);
    config()->set('rybbit.script.replay.collect_fonts', false);
    config()->set('rybbit.script.replay.sampling', ['mousemove' => 100, 'scroll' => 150]);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->toContain('data-replay-mask-text-selectors=\'[".user-name","#email"]\'')
        ->toContain('data-replay-block-class="rr-block"')
        ->toContain('data-replay-block-selector=".admin-panel"')
        ->toContain('data-replay-ignore-class="rr-ignore"')
        ->toContain('data-replay-ignore-selector="input[name=&#039;ssn&#039;]"')
        ->toContain('data-replay-mask-text-class="rr-mask"')
        ->toContain('data-replay-mask-all-inputs="false"')
        ->toContain('data-replay-mask-input-options=\'{"password":true,"email":true}\'')
        ->toContain('data-replay-collect-fonts="false"')
        ->toContain('data-replay-sampling=\'{"mousemove":100,"scroll":150}\'');
});

it('omits the session replay attributes when not configured', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)
        ->not->toContain('data-replay-mask-text-selectors')
        ->not->toContain('data-replay-block-class')
        ->not->toContain('data-replay-block-selector')
        ->not->toContain('data-replay-ignore-class')
        ->not->toContain('data-replay-ignore-selector')
        ->not->toContain('data-replay-mask-text-class')
        ->not->toContain('data-replay-mask-all-inputs')
        ->not->toContain('data-replay-mask-input-options')
        ->not->toContain('data-replay-collect-fonts')
        ->not->toContain('data-replay-sampling')
        ->not->toContain('data-replay-slim-dom-options');
});

it('renders the slim dom options as a boolean shorthand', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.script.replay.slim_dom_options', true);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)->toContain('data-replay-slim-dom-options="true"');
});

it('renders the slim dom options as a json object', function () {
    config()->set('rybbit.site_id', 'site-123');
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.tunnel.enabled', false);
    config()->set('rybbit.script.replay.slim_dom_options', ['script' => false, 'comment' => true]);

    $html = Blade::render('@rybbit', deleteCachedView: true);

    expect($html)->toContain('data-replay-slim-dom-options=\'{"script":false,"comment":true}\'');
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
