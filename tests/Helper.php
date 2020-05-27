<?php

    function base_path($string = '') {
        $path = str_replace(['\src', '/src'], '', dirname(__DIR__));

        if ($string == 'config/adminlte.php') {
            $string = 'config_test/adminlte.php';
        } else if ($string == 'resources/views/vendor/adminlte/') {
            $string = 'resources_test/views/vendor/adminlte/';
        }

        return $path.'/'.$string;
    }


    function public_path($string = '') {
        $path = str_replace(['\src', '/src'], '', dirname(__DIR__));
        return $path.'/public/'.$string;
    }

    function resource_path($string = '') {
        $path = str_replace(['\src', '/src'], '', dirname(__DIR__));
        return $path.'/resources_test/'.$string;
    }

    function config_path($string = '') {
        $path = str_replace(['\src', '/src'], '', dirname(__DIR__));
        return $path.'/config_test/'.$string;
    }

    function create_empty_file($path) {
        $file = fopen($path, "w");
        fwrite($file, "");
        fclose($file);
    }
