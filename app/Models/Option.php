<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Option extends Model
{
    /**
     * Options cache prefix.
     */
    protected $prefix = 'option.';

    /**
     * Get cache prefix.
     */
    protected function getPrefix()
    {
        return request()->getHttpHost().'.option.';
    }

    /**
     * Set Options in Database.
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->persist($name, $value);
            }
        } else {
            $this->persist($key, $value);
        }
    }

    /**
     * Persist option in database.
     */
    protected function persist($key, $value)
    {
        $this->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );

        Cache::forget($this->getPrefix().$key);
    }

    /**
     * Get Options from Database.
     */
    public function get($key, $default = null)
    {
        $value = Cache::rememberForever($this->getPrefix().$key, function () use ($key) {
            $option = optional(DB::table('options')->whereKey($key)->first());

            if (is_array(json_decode($option->value, true))) {
                return json_decode($option->value, true);
            }

            return $option->value;
        });

        if ($value || is_array($value)) {
            return $value;
        }

        return $default;
    }

    /**
     * Set Theme Settings in Database.
     */
    public function setThemeSetting($settings)
    {
        $this->persist(
            'theme_settings',
            array_merge(
                $this->get('theme_settings'),
                $settings
            )
        );
    }

    /**
     * Get Theme Settings from Database.
     */
    public function getThemeSetting($key, $default = null)
    {
        return $this->get('theme_settings')[$key] ?? $default;
    }
}
