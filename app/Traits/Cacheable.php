<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    protected static function bootCacheable()
    {
        static::created(function ($model) {
            $model->clearModelCache();
        });

        static::updated(function ($model) {
            $model->clearModelCache();
        });

        static::deleted(function ($model) {
            $model->clearModelCache();
        });
    }

    public function clearModelCache()
    {
        $modelName = strtolower(class_basename($this));
        $keys = Cache::get('cache_keys_' . $modelName, []);
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Cache::forget('cache_keys_' . $modelName);
    }

    protected static function cacheKey($key)
    {
        return strtolower(class_basename(static::class)) . '_' . $key;
    }

    protected static function remember($key, $ttl, $callback)
    {
        $cacheKey = static::cacheKey($key);
        $modelName = strtolower(class_basename(static::class));
        
        // Store the cache key for later invalidation
        $keys = Cache::get('cache_keys_' . $modelName, []);
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::forever('cache_keys_' . $modelName, $keys);
        }
        
        return Cache::remember($cacheKey, $ttl, $callback);
    }
} 