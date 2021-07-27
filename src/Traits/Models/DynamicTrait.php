<?php

namespace Cmdobueno\Mod\Traits\Models;

use Closure;

trait DynamicTrait
{
    /**
     * Store the relations
     *
     * @var array
     */
    private static $dynamic_relations = [];
    protected static $macros = [];

    /**
     * Add a new relation
     *
     * @param $name
     * @param $closure
     */
    public static function addDynamicRelation($name, $closure)
    {
        static::$dynamic_relations[$name] = $closure;
    }

    /**
     * Register a custom macro.
     *
     * @param string $name
     * @param object|callable $macro
     */
    public static function macro(string $name, $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Determine if a relation exists in dynamic relationships list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicRelation($name)
    {
        return array_key_exists($name, static::$dynamic_relations);
    }

    /**
     * Determine if a relation exists in dynamic function list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicFunction($name)
    {
        return array_key_exists($name, static::$macros);
    }

    /**
     * If the key exists in relations then
     * return call to relation or else
     * return the call to the parent
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (static::hasDynamicRelation($name)) {
            // check the cache first
            if ($this->relationLoaded($name)) {
                return $this->relations[$name];
            }

            // load the relationship
            return $this->getRelationshipFromMethod($name);
        }

        return parent::__get($name);
    }

    /**
     * If the method exists in relations then
     * return the relation or else
     * return the call to the parent
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasDynamicRelation($method)) {
            $m = static::$dynamic_relations[$method];
            return call_user_func_array($m->bindTo($this, static::class), $parameters);
        }


        if (static::hasDynamicFunction($method)) {
            $macro = static::$macros[$method];
            if ($macro instanceof Closure) {
                return call_user_func_array($macro->bindTo($this, static::class), $parameters);
            }
            return call_user_func_array($macro, $parameters);
        }


        return parent::__call($method, $parameters);
    }
}
