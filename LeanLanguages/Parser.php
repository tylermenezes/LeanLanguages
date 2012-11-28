<?php

namespace LeanLanguages;

class Parser
{
    public static function parse(Lexer $lex)
    {
        $parsed_files = array();
        foreach ($lex->lexed as $path=>$file) {
            foreach (self::_parse($file) as $k=>$v) {
                $shortpath = $path;
                $shortpath = substr($shortpath, 0, strrpos($shortpath, '.'));
                $shortpath = str_replace('/', ' ', $shortpath);
                $shortpath .= ' ';
                if ($shortpath === 'main ') {
                    $shortpath = '';
                }
                $parsed_files[$shortpath . $k] = $v;
            }
        }

        return $parsed_files;
    }

    private static function _parse($arr) {
        $parsed_arr = array();
        foreach ($arr as $node) {
            if (isset($node['value'])) {
                $parsed_arr[$node['key']] = $node['value'];
            }

            if (isset($node['__subtree'])) {
                $subtree = self::_parse($node['__subtree']);
                foreach ($subtree as $k => $v) {
                    $parsed_arr[$node['key'] . ' ' . $k] = $v;
                }
            }
        }

        return $parsed_arr;
    }
}
