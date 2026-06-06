<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Strip dangerous markup while preserving a safe editorial tag/attribute set.
     */
    public function sanitize(string $content): string
    {
        $clean = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $content) ?? '';
        $clean = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $clean) ?? '';

        $allowedTags = '<p><br><strong><b><em><i><u><s><del><strike><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><figure><figcaption><img><pre><code><hr><table><thead><tbody><tr><td><th><colgroup><col><span><mark><sup><sub><div><dl><dt><dd><action-text-attachment>';
        $clean = strip_tags($clean, $allowedTags);

        $globalAttrs = ['class', 'style', 'id', 'dir', 'title', 'lang'];

        $allowedAttrs = [
            'a' => array_merge(['href', 'target', 'title', 'rel', 'download', 'hreflang'], $globalAttrs),
            'img' => array_merge(['src', 'alt', 'title', 'width', 'height', 'loading', 'decoding'], $globalAttrs),
            'table' => array_merge(['border', 'cellpadding', 'cellspacing', 'width', 'summary'], $globalAttrs),
            'td' => array_merge(['colspan', 'rowspan', 'scope', 'headers', 'width', 'height', 'align', 'valign'], $globalAttrs),
            'th' => array_merge(['colspan', 'rowspan', 'scope', 'headers', 'width', 'height', 'align', 'valign'], $globalAttrs),
            'colgroup' => array_merge(['span', 'width'], $globalAttrs),
            'col' => array_merge(['span', 'width'], $globalAttrs),
            'hr' => array_merge(['width', 'size', 'align', 'noshade'], $globalAttrs),
            'p' => $globalAttrs,
            'h1' => $globalAttrs, 'h2' => $globalAttrs, 'h3' => $globalAttrs,
            'h4' => $globalAttrs, 'h5' => $globalAttrs, 'h6' => $globalAttrs,
            'div' => $globalAttrs,
            'span' => $globalAttrs,
            'mark' => $globalAttrs,
            'blockquote' => array_merge(['cite'], $globalAttrs),
            'ul' => $globalAttrs,
            'ol' => array_merge(['start', 'type', 'reversed'], $globalAttrs),
            'li' => array_merge(['value'], $globalAttrs),
            'tr' => $globalAttrs,
            'tbody' => $globalAttrs,
            'thead' => $globalAttrs,
            'pre' => $globalAttrs,
            'code' => $globalAttrs,
            'figure' => $globalAttrs,
            'figcaption' => $globalAttrs,
            'dl' => $globalAttrs,
            'dt' => $globalAttrs,
            'dd' => $globalAttrs,
            'sup' => $globalAttrs,
            'sub' => $globalAttrs,
            'strong' => $globalAttrs,
            'b' => $globalAttrs,
            'em' => $globalAttrs,
            'i' => $globalAttrs,
            'u' => $globalAttrs,
            's' => $globalAttrs,
            'del' => array_merge(['cite', 'datetime'], $globalAttrs),
            'strike' => $globalAttrs,
            'br' => $globalAttrs,
        ];

        $allowedCss = [
            'color', 'background-color',
            'font-family', 'font-size', 'font-weight', 'font-style', 'line-height',
            'text-align', 'text-decoration', 'text-transform', 'text-indent',
            'letter-spacing', 'word-spacing', 'white-space', 'vertical-align',
            'width', 'height', 'max-width', 'min-width', 'max-height', 'min-height',
            'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
            'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            'border', 'border-top', 'border-right', 'border-bottom', 'border-left',
            'border-width', 'border-style', 'border-color', 'border-collapse', 'border-spacing', 'border-radius',
            'box-sizing', 'overflow', 'overflow-x', 'overflow-y',
            'display', 'float', 'clear', 'position',
            'top', 'right', 'bottom', 'left',
            'list-style-type', 'list-style-position', 'list-style-image',
            'opacity', 'visibility', 'z-index',
        ];

        return preg_replace_callback(
            '#<([a-z][a-z0-9]*)\b([^>]*)>#i',
            function (array $matches) use ($allowedAttrs, $allowedCss): string {
                $tag = strtolower($matches[1]);
                $attrsRaw = $matches[2];
                $safe = [];

                $tagAllowed = $allowedAttrs[$tag] ?? [];

                foreach ($tagAllowed as $attrName) {
                    $pattern = '#\b'.preg_quote($attrName, '#').'\s*=\s*(["\'])(.*?)\1#i';
                    if (! preg_match($pattern, $attrsRaw, $m)) {
                        continue;
                    }

                    $value = $m[2];

                    if ($attrName === 'style') {
                        $value = $this->sanitizeStyle($value, $allowedCss);
                        if ($value === '') {
                            continue;
                        }
                    } elseif (in_array($attrName, ['href', 'src'], true)) {
                        if (str_starts_with(strtolower(trim($value)), 'javascript:')) {
                            continue;
                        }
                        $value = filter_var($value, FILTER_SANITIZE_URL) ?: $value;
                    }

                    $safe[] = $attrName.'="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"';
                }

                $attrStr = $safe === [] ? '' : ' '.implode(' ', $safe);

                return '<'.$tag.$attrStr.'>';
            },
            $clean
        ) ?? $clean;
    }

    /**
     * @param  array<int, string>  $allowedProperties
     */
    private function sanitizeStyle(string $style, array $allowedProperties): string
    {
        $declarations = array_filter(array_map('trim', explode(';', $style)));
        $clean = [];

        foreach ($declarations as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            if (! in_array($property, $allowedProperties, true)) {
                continue;
            }

            $lowerValue = strtolower($value);
            if (str_contains($lowerValue, 'javascript:')
                || str_contains($lowerValue, 'expression(')
                || str_contains($lowerValue, 'behavior:')
                || str_contains($lowerValue, '-moz-binding')
                || str_contains($lowerValue, 'url(')
            ) {
                continue;
            }

            if ($property === 'position' && in_array($lowerValue, ['fixed', 'absolute', 'sticky'], true)) {
                continue;
            }

            $clean[] = $property.': '.$value;
        }

        return implode('; ', $clean);
    }
}
