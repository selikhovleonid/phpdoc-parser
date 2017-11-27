<?php

/**
 * This namespace contains tools for PHPDoc blocks parsing.
 * @author Leonid Selikhov
 */
namespace nadir2\PhpDocParser;

/**
 * This function parses the passed PHPDoc line into an array structure which
 * contains tag assosiated fields: name, type, varName, and desription.
 * @param string $docLine
 * @return mixed[]
 */
function parseDocLine($docLine)
{
    $res     = array(
        'name'        => null,
        'type'        => null,
        'varName'     => null,
        'description' => null,
    );
    $matches = array();
    if (preg_match(
        '#^@(\w+)\s+([^\s]+)(?:\s+(\$\S+))?(?:\s+(.*))?#s',
        $docLine,
        $matches
    )) {
        $res['name'] = $matches[1];
        if (isset($matches[2]) && !empty($matches[2])) {
            $res['type'] = $matches[2];
        }
        if (isset($matches[3]) && !empty($matches[3])) {
            $res['varName'] = $matches[3];
        }
        if (isset($matches[4]) && !empty($matches[4])) {
            $res['description'] = preg_replace('#\s+#', ' ', $matches[4]);
        }
    } elseif (preg_match('#^@(\w+)(?:\s+([^\s].*)|$)?#', $docLine, $matches)) {
        $res['name'] = $matches[1];
        if (isset($matches[2]) && !empty($matches[2])) {
            $res['description'] = $matches[2];
        }
    }
    return $res;
}

/**
 * This function parses the passed PHPDoc comment block into an array structure.
 * The array contains a field with the block description and a list of sructured
 * tags.
 * @param string $docComment
 * @return mixed[]
 */
function parseDocComment($docComment)
{
    $res        = array(
        'description' => null,
        'tags'        => array(),
    );
    $docComment = preg_replace(
        '#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#',
        '$1',
        $docComment
    );
    $docComment = ltrim($docComment, "\r\n");
    while (($newlinePos = strpos($docComment, "\n")) !== false) {
        $line    = substr($docComment, 0, $newlinePos);
        $matches = array();
        if ((strpos($line, '@') === 0) && (preg_match(
            '#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s',
            $docComment,
            $matches
        ))) {
            $res['tags'][] = parseDocLine($matches[1]);
            $docComment    = str_replace(
                $matches[1].$matches[2],
                '',
                $docComment
            );
        } else {
            $res['description'] .= $line."\n";
            $docComment         = substr($docComment, $newlinePos + 1);
        }
    }
    $res['description'] = rtrim($res['description'], "\n");
    return $res;
}
