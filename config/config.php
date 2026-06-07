<?php

if (!function_exists('config')) {
    /**
      * Obtiene una variable de entorno con un valor por defecto opcional. 
      *
      * @param string $key
      * @param mixed $default
      * @return mixed
      */
    function config(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false ? $value : $default;
    }
}
