<?php

namespace App\Http\Repositories;


use App\Map;
use Illuminate\Http\Request;

class ConfigsRepository extends Repository
{
    public function saveSettings(array $inputs)
    {
        foreach ($inputs as $key => $value) {
            if (!$value) {
                continue;
            }

            $map = $this->model()->firstOrNew([
                'key' => $key,
            ]);

            $map->tag = 'settings';
            $map->value = $value;
            $map->save();
        }
    }

    public function model()
    {
        return app(Map::class);
    }

    public function getArrayByTag($tag)
    {
        $maps = $this->getByTag($tag);
        $arr = [];
        foreach ($maps as $map) {
            $arr[$map->key] = $map->value;
        }

        return $arr;
    }

    public function getByTag($tag)
    {
        return $this->model()->where('tag', $tag)->get();
    }

    public function getBoolValue($key, $default = false)
    {
        $defaultValue = $default ? 'true' : 'false';

        return $this->getValue($key, $defaultValue) == 'true';
    }

    public function getValue($key, $default = null)
    {
        $map = $this->get($key);
        if ($map && !$map->value == null && !$map->value == '')
            return $map->value;

        return $default;
    }

    public function get($key)
    {
        return $this->model()->where('key', $key)->first();
    }

    public function delete($key)
    {
        return $this->model()->where('key', $key)->delete();
    }

    public function create(Request $request)
    {
        $map = $this->model()->create([
            'key'   => $request['key'],
            'value' => $request['value'],
        ]);

        return $map;
    }
}
