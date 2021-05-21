<?php

/**
 * Global function that is a superset of the var_dump PHP function. This function return a more
 * readable HTML output for var_dump.
 *
 * @param  mixed $values                            Data that will be dumped.
 *
 * @return void
 */
function varDump(...$values): void
{
    $debug = debug_backtrace()[0];

    $varDump = _getVarDump(true, ...$values);

    $varDump = preg_replace(
        '/\]=>\n[\s]+/',
        '] => ',
    $varDump);

    $varDump = preg_replace(
        '/(\[)(".*?")(?:\:(?:"(.*?)"))?:(private|protected|public)(\])/',
        '<objectKey title="$3">$1$2:<small>$4</small>$5</objectKey>',
    $varDump);

    $varDump = preg_replace(
        '/(\[)((?:".*?")|(?:.*?))(\])(\s=>\s&?(string|NULL|bool|object|array|float|int))/',
        '<arrayKey>$1$2$3</arrayKey>$4',
    $varDump);

    $varDump = preg_replace(
        '/(object\(.*?\)#.*?\s\(.*?\))/',
        '<objectTitle>$1</objectTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(array\(.*?\))/',
        '<arrayTitle>$1</arrayTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(string\(.*?\))\s(".*")/',
        '<stringValue>$2</stringValue> <stringTitle>$1</stringTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(int)(?:\((.*?)\))/',
        '<numericValue>$2</numericValue> <numericTitle>$1</numericTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(float)(?:\((.*?)\))/',
        '<numericValue>$2</numericValue> <numericTitle>$1</numericTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(bool)(\(true\))/',
        '<trueValue>$2</trueValue> <trueTitle>$1</trueTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(bool)(\(false\))/',
        '<falseValue>$2</falseValue> <falseTitle>$1</falseTitle>',
    $varDump);

    $varDump = preg_replace(
        '/(NULL)/',
        '<nullValue>$1</nullValue>',
    $varDump);

    echo "
    <style>" . file_get_contents(GALASTRI_PROJECT_DIR . '/galastri/misc/vardump.css') . "</style>
    <div id='galastriVardump'>
        <big><b>GALASTRI VARDUMP</b></big>
        <section>
            <b>ORIGIN: </b>$debug[file]<br>
            <b>LINE  : </b>$debug[line]<br><br>
            <b>VALUES: </b><br>
            <pre>$varDump</pre>
        <section>
    </div>";
}

/**
 * Global function that is a superset of the var_dump PHP function. This function return the
 * var_dump output in JSON format.
 *
 * @param  mixed $values                            Data that will be dumped.
 *
 * @return void
 */
function jsonDump(...$values): void
{
    $debug = debug_backtrace()[0];

    $varDump = _getVarDump(false, ...$values);

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

/**
 * Global function that store the var_dump output and return its content. It is meant to be used by
 * the varDump and jsonDump function, but can be used by its own.
 *
 * @param  string $html                             When true, a HTML div will be printed between
 *                                                  the values.
 *
 * @param  mixed $values                            Data that will be dumped.
 *
 * @return string
 */
function _getVarDump(bool $html, ...$values): string
{
    ob_start();
    foreach ($values as $key => $value) {
        $key++;
        echo $html === true ? '<span class="division">' . $key . '</span>' : '';
        var_dump($value);
    };
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
