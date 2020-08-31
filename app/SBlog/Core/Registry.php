<?php


namespace App\SBlog\Core;


class Registry
{
    use TSingleton;

    protected static $properties = [];

    public function setProperty($name, $value){
        self::$properties[$name] = $value;
    }

    /**
     * @return array
     */
    public static function getProperty($name)
    {
        if(isset(self::$properties[$name])){
            return self::$properties[$name];
        }
        return null;
    }

    public function getProperties(){
        return self::$properties;
    }
}
