<?php

namespace LeanLanguages
{
    require_once(dirname(__FILE__) . '/Lexer.php');
    require_once(dirname(__FILE__) . '/Parser.php');

    class Language
    {
        private static $cache_dir = NULL;
        private static $languages_dir = NULL;


        protected static $current_language = NULL;
        protected $strings;

        public static function start($languages_dir, $language_name, $cache_dir = NULL)
        {
            self::$languages_dir = $languages_dir;
            self::$cache_dir = $cache_dir;
            self::set_language($language_name);
        }

        public static function set_language($language_name)
        {
            self::$current_language = new self($language_name);
        }

        private function __construct($language_name)
        {
            $cached_language_name = 'leanlanguage_cached_language_' . md5($language_name);

            if (self::$cache_dir !== NULL && file_exists(self::$cache_dir . '/' . $language_name . '.php')) {
                require_once(self::$cache_dir . '/' . $language_name . '.php');
                $this->strings = $cached_language_name::$strings;
            } else {
                $subdir = str_replace(array('_', '-', ' '), '/', $language_name);
                $lex = new \LeanLanguages\Lexer(static::$languages_dir . '/' . $subdir . '/');
                $this->strings = \LeanLanguages\Parser::parse($lex);

                if (self::$cache_dir !== NULL) {
                    $enc = var_export($this->strings, TRUE);
                    $contents = "<?php class $cached_language_name{public static \$strings = $enc;}";
                    file_put_contents(self::$cache_dir . '/' . $language_name . '.php', $contents);
                }
            }
        }

        public static function get_translation($string, $args = array())
        {
            if (self::$current_language === NULL) {
                throw new \Exception("Must call Language::start before using the language.");
            }

            $return = self::$current_language->strings[$string];

            if (!$return) {
                $return = $string;
            }

            // Allow for argument passing with function overloading
            if(count($args) > 0){
                $return = vsprintf($return, $args);
            }

            // Allow for references
            $return = preg_replace_callback('/\{\{(.*?)\}\}/', function($matches){
                                                $matches = explode("|", $matches[1], 2); // For each match, explode out any arguments
                                                $name = $matches[0];

                                                if(count($matches) > 1){
                                                    $args = explode("|", $matches[1]);
                                                } else {
                                                    $args = array();
                                                }

                                                return Language::get_translation($name, $args);
                                            }, $return);

            while(is_array($return)){
                $return = $return[rand(0, count($return) - 1)];
            }

            return $return;
        }
    }
}

namespace
{
    function __g($string)
    {
        $args = func_get_args();
        array_shift($args);
        if(count($args) == 2 && is_array($args[1])){
            $args = $args[1];
        }
        return \LeanLanguages\Language::get_translation($string, $args);
    }

    function __e($string)
    {
        $args = func_get_args();
        array_shift($args);
        if(count($args) == 2 && is_array($args[1])){
            $args = $args[1];
        }
        echo __g($string, $args);
    }
}

