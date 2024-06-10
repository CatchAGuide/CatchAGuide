<?php
namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ModelService{
    public const STATUS_DRAFT = 0;
    public const STATUS_PUBLISHED = 1;
    protected const CLASSES = [
        Product::class => 'Products',
        Brand::class => 'Brands',
        Category::class => 'Categories',
    ];

    public static function getModel($className, $id){

        if(!isset(self::CLASSES[$className])){
            return;
        }

        return $className::find($id);
    }

    public static function getStatuses(){
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published'
        ];
    }

    public static function getStatusText($status){
        $statuses = self::getStatuses();
        if(!isset($statuses[$status])){
            return 'Unknown';
        }

        return $statuses[$status];
    }

    public static function getClassNameByTable($tableName){
        $classes = [
            'categories' => '\App\Models\Category',
            'products' => '\App\Models\Product',
        ];

        if(isset($classes[$tableName])){
            return $classes[$tableName];
        }
    }

    public static function getModelTypes(){
        return self::CLASSES;
    }
}