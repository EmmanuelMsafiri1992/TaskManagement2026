<?php

namespace Admin\Static;

use JsonSerializable;

class Color implements JsonSerializable
{
    protected $items = [
        'slate-500' => '#64748B',
        'zinc-500' => '#71717A',
        'red-500' => '#EF4444',
        'orange-500' => '#F97316',
        // 'amber-500' => '#F59E0B',
        'yellow-500' => '#EAB308',
        'lime-500' => '#84CC16',
        'green-500' => '#22C55E',
        // 'emerald-500' => '#10B981',
        'teal-500' => '#14B8A6',
        // 'cyan-500' => '#06B6D4',
        'sky-500' => '#0EA5E9',
        'blue-500' => '#3B82F6',
        'indigo-500' => '#6366F1',
        'violet-500' => '#8B5CF6',
        // 'purple-500' => '#A855F7',
        // 'fuchsia-500' => '#D946EF',
        'pink-500' => '#EC4899',
        'rose-500' => '#F43F5E',
    ];

    /**
     * Get specific item with key.
     *
     * @return string
     */
    public function get($key)
    {
        return $this->items[$key];
    }

    /**
     * Get all items.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get default color.
     *
     * @return string
     */
    public function default()
    {
        return $this->items['zinc-500'];
    }

    /**
     * Get items as options.
     *
     * @return array
     */
    public function options()
    {
        return collect($this->items)
                    ->map(fn ($value, $label) => compact('label', 'value'))
                    ->values();

        return collect($this->items)
                    ->map(fn ($item, $key) => ['label' => $key, 'value' => $item])
                    ->values();

        return collect($this->items)->map(fn ($item, $key) => ['label' => $key, 'value' => $item])->values();

        return collect($this->items)->map(function ($item, $key) {
            return ['label' => $key, 'value' => $item];
        })->values();
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->items;
    }
}
