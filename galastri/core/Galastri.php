<?php
/**
 * - Galastri.php -
 * 
 * Classe que contém a inicialização do microframework, os renderizadores e os controladores. A
 * execução de tudo ocorre dentro da classe Galastri, através do padrão Singleton, o que permite
 * maior flexibilidade na chamada de componentes.
 */
namespace galastri\core;

class Galastri
{
    private static $controller = false;
    private static $requireController = false;

    /**
     * Importação dos renderizadores.
     * Rendizador, no contexto deste microframework, é qualquer componente que retorne algo para
     * impressão, sejam dados ou sejam arquivos. Cada renderizador tem sua função. Todos eles foram
     * escritos como sendo traits ao invés de classes.
     * 
     * Os renderizadores padrão são:
     * 
     * view             Organiza os elementos para imprimir um HTML. Importante: este microframe
     *                  faz preferencia do uso do próprio PHP para imprimir os dados que são
     *                  processados pelo controller, não optando por nenhuma engine de template.
     *                  De qualquer forma, é provável que a instalação de uma engine de templates
     *                  não cause incompatibilidades. Este renderizador exige que haja um controller
     *                  configurado.
     * 
     * json             Todos os dados organizados pelo controller são impressos na tela em formato
     *                  json. É ideal para se criar uma API rest ou para retornos de dados para
     *                  consultas assíncronas de Javascript. Este renderizador exige que haja um
     *                  controller configurado.
     * 
     * file             Responsável por renderizar arquivos, como imagens, documentos PDF, audio,
     *                  vídeo, etc. É possível utilizar este renderizador para arquivos de download.
     *                  Este renderizador não exige que haja um controller configurado,
     * 
     * text             O resultado é impresso em formato de texto plano. Caso o retorno contenha uma
     *                  array com vários dados, todos são convertidos para serem exibidos como sendo
     *                  uma única string. É ideal para se utilizar em execuções CLI (via linha de
     *                  comando) para se retornar mensagens de sucesso ou falha. Este renderizador
     *                  exige que haja um controller configurado.
     */
    use \galastri\extensions\renderers\File;
    use \galastri\extensions\renderers\Json;
    use \galastri\extensions\renderers\View;
    use \galastri\extensions\renderers\Text;
    
    /** Classe que trabalha sob o padrão Singleton, por isso, não poderá ser instanciada. */
    private function __construct(){}
    
    /**
     * Execução do microframework.
     * Uma série de testes são executados em série para verificar se as configurações foram feitas
     * corretamente. Se tudo estiver certo, uma instância do controller é criada
     * 
     * O controller irá retornar dados que são armaeznados no atributo $controller, sendo este
     * acessível pelo renderizador.
     * 
     * O renderizador é chamado para realizar a exibição dos dados.
     */
    public static function execute()
    {
        date_default_timezone_set(GALASTRI['timezone']);
        Route::resolve();

        self::checkRendererExists()
            ::checkOffline('global')
            ::checkOffline(Route::renderer())
            ::checkRequiredController()
            ::checkClass()
            ::checkMethod()
            ::callController();

        $renderer = Route::renderer();
        self::$renderer();
    }
    
    /**
     * Verifica se a configuração offline está ativa ou não. Este método executa outro método cujo
     * nome é formado pelo nome do renderizador e seguido pelo termo 'CheckOffline'. Por exemplo,
     * o renderizador view possui um método chamado viewCheckOffline().
     * 
     * Por padrão, ao menos 2 métodos são executados usando o método abaixo: um verificando se a
     * configuração offline está ativa globalmente e outra se a configuração offline está ativa
     * na rota (arquivo config/routes.php).
     * 
     * @param string $scope            Nome do renderizador ou do escopo que irá se unir ao termo
     *                                 'CheckOffline' a fim de compor o nome completo do método
     *                                 que verifica se há configurações offline ativas.
     */
    private static function checkOffline($scope)
    {
        return self::{$scope.'CheckOffline'}();
    }
    
    /**
     * Verifica se a opção global offline está ativa. Caso esteja, verifica se a configuração
     * redirectTo está prenchida. Caso sim, o usuário é redirecionado para aquela página configurada
     * no redirectTo. Caso não, apenas a mensagem será exibida na tela.
     */
    private static function globalCheckOffline()
    {
        if(GALASTRI['offline']['status']){
            $redirectTo = GALASTRI['offline']['redirectTo'];
            
            if($redirectTo){
                $url = GALASTRI['url_alias'][$redirectTo];
                if(Route::urlString() !== $url) Redirect::location($url);
            }
            
            if(($redirectTo and GALASTRI['offline']['forceMessage']) or !$redirectTo) self::printContent(GALASTRI['offline']['message']);
        }
        return __CLASS__;
    }
    
    /**
     * Verifica se o renderizador especificado na configuração existe. É importante ressaltar que
     * os renderizadores são traits importadas logo após a definição desta classe.
     */
    private static function checkRendererExists()
    {
        Debug::trace(debug_backtrace()[0]);
        
        $renderer = Route::renderer();
        $path     = Route::path();

        if(isset($renderer) and !empty($renderer)){
            if(!method_exists(__CLASS__, $renderer)){
                Debug::error('RENDERER003', $renderer)::print();
            }
        } else {
            Debug::error('CONFIG001', $path)::print();
        }
        
        return __CLASS__;
    }
    
    /**
     * Verifica se o renderizador obriga que um controller esteja configurado e que ele exista
     * na pasta controller. Os renderizadores view e json, por padrão, exigem que existam controllers
     * ativos para cada página requisitada. Caso não exista nenhum método ou classe configurada
     * nestes casos, uma mensagem de erro é exibida.
     */
    private static function checkRequiredController()
    {
        Debug::trace(debug_backtrace()[0]);
        
        $renderer   = Route::renderer();
        $controller = Route::controller();
        $method     = Route::method();
        $path       = Route::path();
        
        self::$requireController = self::{$renderer.'Controller'}();

        if($renderer === false and self::$requireController){
            Debug::error('RENDERER004', $renderer, $path)::print();
        // } elseif($controller === null and self::$requireController){
        //     Debug::error('RENDERER002', $renderer, $path)::print();
        }
        return __CLASS__;
    }
    
    /**
     * Verifica se o arquivo com a classe controladora existe.
     */
    private static function checkClass()
    {
        Debug::trace(debug_backtrace()[0]);
        $controller = Route::controller();
        
        if($controller){
            if(!class_exists($controller)){
                if(self::$requireController){
                    Debug::error('CONTROLLER001', $controller)::print();
                }
            } else {
                self::$controller = $controller;
            }
        }
        return __CLASS__;
    }

    /**
     * Verifica se o arquivo com a classe controladora possui um método que representa a página
     * requisitada.
     */
    private static function checkMethod()
    {
        Debug::trace(debug_backtrace()[0]);
        
        $controller = self::$controller;
        $method     = Route::method();
        
        if($controller){
            if(!method_exists($controller, $method)){
                Debug::error('CONTROLLER002', $controller, $method)::print();
            }
        }
        return __CLASS__;
    }
    
    /**
     * Estando tudo correto e sem erros, uma instância desta classe controladora é criada dentro
     * do atributo $controller, seguido da execução do método que representa a página requisita.
     * 
     * A execução deste método irá retornar dados ou não. Caso retorne, estes dados poderão ser
     * impressos pelo renderizador.
     */
    private static function callController()
    {
        $controller = self::$controller;

        if($controller){
            self::$controller = new $controller();
            self::$controller->startController();
        }
    }
    
    /**
     * Método usado apenas pelos renderizadores. Verifica se a página requer autenticação. Neste
     * caso, é verificado se a sessão está ativa. Caso não esteja, será necessário ou redirecionar
     * o usuário para uma outra página ou retornar dados de erros.
     * 
     * O redirecionamento só ocorrerá quando o parâmetro authFailUrl estiver ativo na configuração
     * das rotas (routes.php). Do contrário, uma array contendo erro código e uma mensagem (ambos
     * definidos nos parâmetros de configuração 'authentication' é retornada para a view.
     * 
     * É importante alertar que este teste depende de outro teste realizado na classe Controller.
     * Lá é verificado se a sessão está ativa ou não antes da controller ser processada.
     * 
     * @param object $data             Armazena o objeto obtido do método getRenderData() da
     *                                 classe Controller.
     */
    private static function checkAuth($data)
    {
        if($data !== null){
            
            $authBlock = property_exists($data, 'authBlock') ? $data->authBlock : true;
            $authFailUrl = property_exists($data, 'authFailUrl') ? $data->authFailUrl : null;
            
            if(empty($authFailUrl) and $authFailUrl !== false and gettype(Route::authTag()) === 'string')
                Debug::error('ROUTE002', Route::authTag())::print();

            if($authBlock === true and gettype(Route::authTag()) === 'string'){

                if($authFailUrl){
                    Redirect::location($authFailUrl);
                } else {
                    $data->data = ['error' => true, 'message' => GALASTRI['authentication']['failMessage'], 'requestStatus' => GALASTRI['authentication']['exceptionTag']];
                }
            }
        }
        return $data;
    }
    
    /**
     * Métodos para impressão ou requerimento de conteúdo.
     */
    private static function requireContent($render, $file){ exit(require_once($file)); }
    private static function printContent($content){ exit(print($content)); }
}
