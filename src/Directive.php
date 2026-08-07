<?php

namespace Cocosport\Rybbit;

class Directive
{
    public static function injectedScript(): string
    {
        return <<<'BLADE'
            <?php
                $rybbitSrc = config('rybbit.tunnel.enabled') && config('rybbit.tunnel.url')
                    ? config('rybbit.tunnel.url')
                    : config('rybbit.host');

                $rybbitDebounce = config('rybbit.script.debounce');
                $rybbitSkipPatterns = config('rybbit.script.skip_patterns', []);
                $rybbitMaskPatterns = config('rybbit.script.mask_patterns', []);
            ?>
            <?php if (config('rybbit.site_id') && $rybbitSrc): ?>
                <script
                    src="<?php echo e($rybbitSrc.'/script.js'); ?>"
                    data-site-id="<?php echo e(config('rybbit.site_id')); ?>"
                    <?php if (filled($rybbitDebounce)): ?>
                    data-debounce="<?php echo e((string) $rybbitDebounce); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitSkipPatterns)): ?>
                    data-skip-patterns='<?php echo json_encode($rybbitSkipPatterns, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    <?php if (filled($rybbitMaskPatterns)): ?>
                    data-mask-patterns='<?php echo json_encode($rybbitMaskPatterns, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    defer
                ></script>
            <?php endif; ?>
            BLADE;
    }
}
