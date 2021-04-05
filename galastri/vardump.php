<?php

function jsonDump(...$values)
{
    $debug = debug_backtrace()[0];

    $varDump = _getVarDump(VARDUMP_JSON_TYPE, $values);

    header('Content-Type: application/json');
    echo json_encode([
        'code' => 'GALASTRI_JSON_DUMP',
        'origin' => $debug['file'],
        'line' => $debug['line'],
        'values' => $varDump,
        'message' => 'Returned a JSON var_dump',
        'error' => false,
        'warning' => true,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function varDump(...$values)
{
    $debug = debug_backtrace()[0];

    $varDump = _getVarDump(VARDUMP_HTML_TYPE, $values);

    // Remove all breaklines and spaces between ["name"]=>"value"
    $varDump = preg_replace('/(\[".+)\n[\s]*/', '$1', $varDump);
    $varDump = preg_replace('/(\[[0-9].+)\n[\s]*/', '$1', $varDump);

    // Format the head of the var_dump
    $varDump = preg_replace('/(string\(.*\)) (".*")/', '<span class="string text">$2</span> <span class="string solid"><b>$1</b></span>', $varDump);
    $varDump = preg_replace('/(array\(.*\))/', '<span class="array solid"><b>$1</b></span>', $varDump);
    $varDump = preg_replace('/(object\(.*\)#.* \([0-9]\))/', '<span class="object solid"><b>$1</b></span>', $varDump);
    $varDump = preg_replace('/(float)\((.*)\)/', '<span class="numeric text">$2</span> <span class="numeric solid"><b>$1</b></span>', $varDump);
    $varDump = preg_replace('/(bool)\((true)\)/', '<span class="bool text true">$2</span> <span class="bool solid true"><b>$1</b></span>', $varDump);
    $varDump = preg_replace('/(bool)\((false)\)/', '<span class="bool text false">$2</span> <span class="bool solid false"><b>$1</b></span>', $varDump);
    $varDump = preg_replace('/(int)\((.*)\)/', '<span class="numeric text">$2</span> <span class="numeric solid"><b>$1</b></span>', $varDump);
    
    // Division between values
    $varDump = preg_replace('/(Result\s[0-9]\.)/', '<span class="division">$1</span>', $varDump);

    // // Array/Object key
    $varDump = preg_replace('/(\[[0-9]\])(=>)/', '<span class="array-key">$1</span> $2 ', $varDump);
    $varDump = preg_replace('/(\[".*"\])(=>)/', '<span class="array-key">$1</span> $2 ', $varDump);

    // // Array Brackets
    $varDump = preg_replace('/[\s]({)/', '<span class="brackets open">$1</span>', $varDump);
    $varDump = preg_replace('/([\s].*)(})/', '$1<span class="brackets">$2</span>', $varDump);

    // // Remove linebreak after =>
    $varDump = preg_replace('/(=>\n[\s]*)/', ' => ', $varDump);

    echo "
    <style>".file_get_contents(GALASTRI_PROJECT_DIR.'/galastri/misc/styles.css')."</style>
    <div id='galastriDump'>
        <big><b>GALASTRI VARDUMP</b></big>
        <section>
            <b>ORIGIN: </b>$debug[file]<br>
            <b>LINE  : </b>$debug[line]<br><br>
            <b>VALUES: </b><br>
            <pre>$varDump</pre>
        <section>
    </div>";
}

function _getVarDump(int $type, ...$values)
{
    ob_start();
    foreach ($values[0] as $key => $value) {
        echo $type !== VARDUMP_JSON_TYPE ? "Result $key." : '';
        var_dump($value);
    };
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
