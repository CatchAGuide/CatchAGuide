<?php
namespace App\Services;
use App\Models\Target;


class ModelService{
    protected const CLASSES = [
        Target::class => 'Target Fish',
    ];

    public static function getModel($className, $id){

        if(!isset(self::CLASSES[$className])){
            return;
        }

        return $className::find($id);
    }



    public static function getClassNameByTable($tableName){
        $classes = [
            'target' => '\App\Models\Target',
        ];

        if(isset($classes[$tableName])){
            return $classes[$tableName];
        }
    }

    public static function getModelTypes(){
        return self::CLASSES;
    }
}