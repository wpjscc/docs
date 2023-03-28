<?php

namespace Wpjscc\Docs\Classes;

/**
 * Class that splits markdown by headers
 * Regex found in Michelf markdown class
 * See: https://github.com/michelf/php-markdown
 */
class MarkdownSplit {


    function splitMarkdown($markdown) { 
        $result = array(); 
        $lines = explode("\n", $markdown); 
        $tmpContent = ""; 
        $tmpTitle = ""; 
        $inBlock = false; //是否在代码块中 
        foreach ($lines as $line) { 
            if (strpos($line, "```") !== false) { //代码块开关判断 
                $inBlock = !$inBlock; 
            } 
            if (strpos($line, "#") === 0 && !$inBlock) { //标题判断 
                if ($tmpTitle != "") { //如果已经有缓存的标题，则处理之前的内容 
                    $content = trim($tmpContent); 
                    if ($content != "") { 
                        $result[] = array("title" => $tmpTitle, "content" => $content); 
                    } 
                    $tmpContent = ""; 
                } 
                $tmpTitle = trim($line); //获取新的标题 
            } else { 
                $tmpContent .= $line . "\n"; //缓存内容 
            } 
        } //处理最后一个标题及其内容 
        if ($tmpTitle != "") { 
            $content = trim($tmpContent); 
            if ($content != "") { 
                $result[] = array("title" => $tmpTitle, "content" => $content); 
            } 
        } 
        return $result; 
    }
}
