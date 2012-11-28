<?php

namespace LeanLanguages;

class Lexer
{
    private $language_path;
    public $lexed;

    public function __construct($language_path)
    {
        $it = new \RecursiveDirectoryIterator($language_path);

        $files = array();

        foreach(new \RecursiveIteratorIterator($it) as $file) {
            $path = $file->getPath() . '/' . $file->getFilename();
            $shortpath = substr($path, strlen($language_path));

            if (substr($shortpath, 0, 1) === '/') {
                $shortpath = substr($shortpath, 1);
            }

            $files[$shortpath] = $this->process($path);
        }

        $this->lexed = $files;
    }

    private function process($file_name)
    {
        $content = file_get_contents($file_name);
        $lines = explode("\n", $content);

        $tree = $this->process_line_level($lines);
        return $tree;
    }

    private function process_line_level($lines)
    {
        if (count($lines) === 0) {
            return NULL;
        }

        $current_spaces = $this->get_leading_spaces($lines[0]);
        $tree = array();

        $last_line = NULL;
        $next_lines_to_process = array();
        foreach ($lines as $line) {
            $line_spaces = $this->get_leading_spaces($line);

            if ($line_spaces === $current_spaces) {
                if (count($next_lines_to_process) > 0 && $last_line !== NULL) {
                    $last_line['__subtree'] = $this->process_line_level($next_lines_to_process);
                    $last_lines_to_process = array();
                }

                if ($last_line !== NULL) {
                    $tree[$last_line['key']] = $last_line;
                }
                $last_line = $this->parse_line($line);
            } else if ($line_spaces > $current_spaces) {
                $next_lines_to_process[] = $line;
            } else {
                throw new \Exception("File was ill-formed.");
            }
        }

        if (count($next_lines_to_process) > 0 && $last_line !== NULL) {
            $last_line['__subtree'] = $this->process_line_level($next_lines_to_process);
            $last_lines_to_process = array();
        }

        if (count($last_line) > 0) {
            $tree[$last_line['key']] = $last_line;
        }

        return $tree;
    }

    private function parse_line($line)
    {
        if (strlen(trim($line)) === 0) {
            return NULL;
        }

        $line = ltrim($line);
        if (strstr($line, ':') !== FALSE) {
            list($key, $value) = explode(':', $line);
            $key = rtrim($key);
            if (substr($value, 0, 1) == ' ') {
                $value = substr($value, 1);
            }
            return array('key' => $key, 'value' => $value);
        } else {
            return array('key' => rtrim($line), 'value' => NULL);
        }
    }

    private function get_leading_spaces($line)
    {
        $leading_spaces = 0;
        foreach (str_split($line) as $char) {
            if ($char === ' ' || $char === "\t") {
                $leading_spaces++;
            } else {
                return $leading_spaces;
            }
        }
    }
}
