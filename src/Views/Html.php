<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Views;

use Illuminate\Contracts\Support\Htmlable;
use Twig\Markup;

/**
 * Markup that neither engine escapes again: Twig treats it as safe, and
 * Blade prints an Htmlable as is in `{{ }}`. So `{{ craft.consent.banner() }}`
 * and `{{ $craft->consent()->banner() }}` both output the banner.
 */
final class Html extends Markup implements Htmlable
{
    public static function make(string $html): self
    {
        return new self($html, 'UTF-8');
    }

    public function toHtml(): string
    {
        return (string)$this;
    }
}
