<?php
/**
 * - Redirect.php -
 * 
 * Classe que efetua o redirecionamento de uma URL para outra.
 */
namespace galastri\core;

class Redirect
{
    /**
     * Método que faz o redirecionamento para uma URL específica ou para um atalho configurado
     * nas configurações do arquivo config/default.php.
     * 
     * É possível também utilizar-se de placeholder do tipo %s na URL para que sejam substituídos
     * por valores de variável através dos parâmetros $printf.
     * 
     * Por exemplo:
     *      Redirect::to('/url/%s/test', $myVar);
     * 
     * Neste caso, o valor do placeholder %s será substituído pelo valor armazenado na variável
     * $myVar. Supondo que $myVar armazene uma string 'minha-string', o resultado ficaria:
     *      
     *      /url/minha-string/test
     * 
     * 
     * @param string $to               URL para redirecionamento ou o nome da chave com o atalho
     *                                 configurado.
     * 
     * @param array ...$prinft         Valores que substituirão placeholders que estiverem na
     *                                 string.
     * 
     */
    public static function location($to = false, ...$printf)
    {
        Debug::trace(debug_backtrace()[0]);

        if($to === false){
            Debug::error('REDIRECT000')::print();
        } else {
            $root = GALASTRI['routes']['root']/* === '/' ? '' : GALASTRI['routes']['root']*/;
            if($root !== '/'){
                $root = self::cleanLocation($root);
                $root = "/$root/";
            }

            if(array_key_exists($to, GALASTRI['urlAlias'])){
                $to = self::cleanLocation(GALASTRI['urlAlias'][$to]);
            } else {
                $to = self::cleanLocation($to);
            }

            $to = vsprintf($to, $printf);

            exit(header('Location: '.$root.$to));
        }
    }

    /**
     * Método alias, que chama o location. Trata-se apenas de uma outra nomenclatura
     * para o método location().
     */
    public static function to($to = false, ...$printf)
    {
        self::location($to, $printf);
    }

    private static function cleanLocation($to)
    {
        $to = ltrim($to, '/');
        $to = rtrim($to, '/');
        return $to;
    }
}
