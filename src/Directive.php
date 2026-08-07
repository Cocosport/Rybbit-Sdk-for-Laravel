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
                $rybbitTag = config('rybbit.script.tag');

                $rybbitReplayMaskTextSelectors = config('rybbit.script.replay.mask_text_selectors', []);
                $rybbitReplayBlockClass = config('rybbit.script.replay.block_class');
                $rybbitReplayBlockSelector = config('rybbit.script.replay.block_selector');
                $rybbitReplayIgnoreClass = config('rybbit.script.replay.ignore_class');
                $rybbitReplayIgnoreSelector = config('rybbit.script.replay.ignore_selector');
                $rybbitReplayMaskTextClass = config('rybbit.script.replay.mask_text_class');
                $rybbitReplayMaskAllInputs = config('rybbit.script.replay.mask_all_inputs');
                $rybbitReplayMaskInputOptions = config('rybbit.script.replay.mask_input_options', []);
                $rybbitReplayCollectFonts = config('rybbit.script.replay.collect_fonts');
                $rybbitReplaySampling = config('rybbit.script.replay.sampling', []);
                $rybbitReplaySlimDomOptions = config('rybbit.script.replay.slim_dom_options');
            ?>
            <?php if (config('rybbit.site_id') && $rybbitSrc): ?>
                <script
                    src="<?php echo e($rybbitSrc.'/script.js'); ?>"
                    data-site-id="<?php echo e(config('rybbit.site_id')); ?>"
                    <?php if (filled($rybbitDebounce)): ?>
                    data-debounce="<?php echo e((string) $rybbitDebounce); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitTag)): ?>
                    data-tag="<?php echo e((string) $rybbitTag); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitSkipPatterns)): ?>
                    data-skip-patterns='<?php echo json_encode($rybbitSkipPatterns, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    <?php if (filled($rybbitMaskPatterns)): ?>
                    data-mask-patterns='<?php echo json_encode($rybbitMaskPatterns, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayMaskTextSelectors)): ?>
                    data-replay-mask-text-selectors='<?php echo json_encode($rybbitReplayMaskTextSelectors, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayBlockClass)): ?>
                    data-replay-block-class="<?php echo e((string) $rybbitReplayBlockClass); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayBlockSelector)): ?>
                    data-replay-block-selector="<?php echo e((string) $rybbitReplayBlockSelector); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayIgnoreClass)): ?>
                    data-replay-ignore-class="<?php echo e((string) $rybbitReplayIgnoreClass); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayIgnoreSelector)): ?>
                    data-replay-ignore-selector="<?php echo e((string) $rybbitReplayIgnoreSelector); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayMaskTextClass)): ?>
                    data-replay-mask-text-class="<?php echo e((string) $rybbitReplayMaskTextClass); ?>"
                    <?php endif; ?>
                    <?php if (! is_null($rybbitReplayMaskAllInputs)): ?>
                    data-replay-mask-all-inputs="<?php echo json_encode(filter_var($rybbitReplayMaskAllInputs, FILTER_VALIDATE_BOOLEAN)); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitReplayMaskInputOptions)): ?>
                    data-replay-mask-input-options='<?php echo json_encode($rybbitReplayMaskInputOptions, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    <?php if (! is_null($rybbitReplayCollectFonts)): ?>
                    data-replay-collect-fonts="<?php echo json_encode(filter_var($rybbitReplayCollectFonts, FILTER_VALIDATE_BOOLEAN)); ?>"
                    <?php endif; ?>
                    <?php if (filled($rybbitReplaySampling)): ?>
                    data-replay-sampling='<?php echo json_encode($rybbitReplaySampling, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    <?php if (is_bool($rybbitReplaySlimDomOptions)): ?>
                    data-replay-slim-dom-options="<?php echo json_encode($rybbitReplaySlimDomOptions); ?>"
                    <?php elseif (filled($rybbitReplaySlimDomOptions)): ?>
                    data-replay-slim-dom-options='<?php echo json_encode($rybbitReplaySlimDomOptions, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS); ?>'
                    <?php endif; ?>
                    defer
                ></script>
            <?php endif; ?>
            BLADE;
    }
}
