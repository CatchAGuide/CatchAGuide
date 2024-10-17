<?php

namespace App\Models;

use \Carbon\Carbon;
use App\Models\CacheList;
use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    protected $fillable = [
        'key',
        'value',
        'expire_at'
    ];

    public static function process($table, $table_id, $blade, $data)
    {
        $cache_list = CacheList::whereTable($table)->whereTableId($table_id)->first();

        if (!is_null($cache_list)) {

            $is_https = request()->secure();
            $cache_key = $table . ':' . $table_id . '=' . url('');
            
            $cache = self::where('key', $cache_key)->first();
            

            if (is_null($cache)) {

                $page = view($blade, $data)->render();
                $cache_expire_at = Carbon::now();
                
                self::create([
                    'key' => $cache_key,
                    'value' => $page,
                    'expire_at' => $cache_expire_at->addDays(7),
                ]);
            } else {
                
                $page = $cache->value;
                $cache_expire_at = Carbon::parse($cache->expire_at);
                $now = Carbon::now();

                if ($cache_expire_at->lte($now)) {

                    $page = view($blade, $data)->render();

                    $cache->value = $page;
                    $cache->expire_at = $cache_expire_at->addDays(7);
                    $cache->save();
                }
            }

        } else {

            $page = view($blade, $data);
        }

        return $page;
    }
}
