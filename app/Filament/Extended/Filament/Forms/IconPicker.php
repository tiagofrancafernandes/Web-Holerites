<?php

namespace App\Filament\Extended\Filament\Forms;

use BladeUI\Icons\Factory as IconFactory;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Support\Htmlable;

class IconPicker extends \Guava\FilamentIconPicker\Forms\IconPicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->placeholder(
                fn () => $this->view(
                    'filament-icon-picker::placeholder',
                    [
                        'placeholderText' => $this->getPlaceholderText(),
                    ]
                )->render()
            );
    }

    protected Closure|string|Htmlable|null $placeholderText = null;

    private function loadIcons(): Collection
    {
        $iconsHash = md5(serialize($this->getSets()));
        $key = "icon-picker.fields.{$iconsHash}.{$this->getStatePath()}";

        [$sets, $allowedIcons, $disallowedIcons] = $this->tryCache(
            $key,
            function () {
                $allowedIcons = $this->getAllowedIcons();
                $disallowedIcons = $this->getDisallowedIcons();

                $iconsFactory = App::make(IconFactory::class);
                $allowedSets = $this->getSets();
                $sets = collect($iconsFactory->all());

                if ($allowedSets) {
                    $sets = $sets->filter(fn ($value, $key) => in_array($key, $allowedSets));
                }

                return [$sets, $allowedIcons, $disallowedIcons];
            }
        );

        $icons = [];

        foreach ($sets as $set) {
            $prefix = $set['prefix'];

            foreach ($set['paths'] as $path) {
                foreach (File::files($path) as $file) {
                    $filename = $prefix . '-' . $file->getFilenameWithoutExtension();

                    if ($allowedIcons && !in_array($filename, $allowedIcons)) {
                        continue;
                    }

                    if ($disallowedIcons && in_array($filename, $disallowedIcons)) {
                        continue;
                    }

                    $icons[] = $filename;
                }
            }
        }

        return collect($icons);
    }
    public function placeholderText(Closure|string|Htmlable|null $placeholderText): static
    {
        $this->placeholderText = $placeholderText;

        return $this;
    }

    public function getPlaceholderText(): string
    {
        if ($this->placeholderText) {
            return $this->evaluate($this->placeholderText);
        }

        return __('No icon selected');
    }
}
