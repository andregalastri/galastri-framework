<?php
/**
 * - Database.php -
 * 
 * Classe que realiza a conexão com banco de dados e executa consultas SQL. A conexão se utiliza
 * da extensão PDO.
 * 
 * Esta classe possui métodos que permitem encadeamento de forma a não ser necessário especificar
 * o objeto da instância a cada execução.
 * 
 * ----
 * Exemplo de uso:
 *         
 *     $database = new Database;
 *     $database->connect();
 *
 *     $database->begin();
 *
 *     $database
 *         ->query('SELECT * FROM autor WHERE id=:id', 'lista_autores')
 *             ->bind(':id', 2)
 *            
 *          ->query('INSERT INTO autor(nome,email,formacao,foto) VALUES(:nome,:email,:formacao,:foto)', 'insere_livros')
 *             ->bindArray([
 *                 ':nome'        => 'André Galastri',
 *                 ':email'    => 'contato@andregalastri.com.br',
 *                 ':formacao'    => 'Sistemas para Internet',
 *                 ':foto'        => '../fotos/andre.jpg',
 *             ])
 * 
 *          ->query('SELECT * FROM livro WHERE edicao=:edicao', 'lista_livros')
 *             ->bind(':edicao', 1)
 *             ->pagination(1, 10)
 *             
 *         ->submit();
 *
 *     $database->commit();
 *
 *     vdump($database->getResult('lista_autores'));
 *     vdump($database->getPagination('lista_livros'));
 */
namespace galastri\core;

use galastri\extensions\Exception;

class Database
{
    private $active;
    private $driver;
    private $host;
    private $port;
    private $database;
    private $user;
    private $password;
    private $options;
    private $backupFolder;
    private $status     = true;
    private $customDns  = false;

    private $table      = false;
    private $filename   = false;

    private $pdo;
    private $label      = null;
    private $result     = [];
    private $pagination = [];

    /**
     * Quando a instância da classe é criada, alguns atributos são configurados para terem valores
     * padrão.
     */
    public function __construct()
    {
        $this->setDefaultConfig();
    }

    /**
     * Métodos de configuração. Cada parâmetro define uma configuração.
     */
    public function setActive($active)            { $this->active       = $active;       return $this; }
    public function setDriver($driver)            { $this->driver       = $driver;       return $this; }
    public function setHost($host)                { $this->host         = $host;         return $this; }
    public function setDatabase($database)        { $this->database     = $database;     return $this; }
    public function setUser($user)                { $this->user         = $user;         return $this; }
    public function setPassword($password)        { $this->password     = $password;     return $this; }
    public function setOptions($options)          { $this->options      = $options;      return $this; }
    public function setCustomDns($customDns)      { $this->customDns    = $customDns;    return $this; }
    public function setStatus($status)            { $this->status       = $status;       return $this; }
    public function setTable($table)              { $this->table        = $table;        return $this; }
    public function setFilename($filename)        { $this->filename     = $filename;     return $this; }
    public function setPort($port)                { $this->port         = empty($port) ? '' : "port=$port"; return $this; }
    
    public function setBackupFolder($backupFolder){
        $backupFolder = ltrim($backupFolder, '/');
        $backupFolder = rtrim($backupFolder, '/');
        
        $this->backupFolder = $backupFolder;
        return $this;
    }

    /**
     * Métodos de configuração. Cada parâmetro define uma configuração.
     */
    public function getActive()      { return $this->active; }
    public function getDriver()      { return $this->driver; }
    public function getHost()        { return $this->host; }
    public function getPort()        { return $this->port; }
    public function getDatabase()    { return $this->database; }
    public function getUser()        { return $this->user; }
    public function getPassword()    { return $this->password; }
    public function getOptions()     { return $this->options; }
    public function getBackupFolder(){ return $this->backupFolder; }
    public function getCustomDns()   { return $this->customDns; }
    public function getStatus()      { return $this->status; }
    public function getTable()       { return $this->table; }
    public function getFilename()    { return $this->filename; }
    
    /**
     * Método que define as configurações padrão para conexão.
     */
    public function setDefaultConfig()
    {
        $this->setActive      (GALASTRI['database']['active']       ?? false);
        $this->setDriver      (GALASTRI['database']['driver']       ?? false);
        $this->setHost        (GALASTRI['database']['host']         ?? null);
        $this->setPort        (GALASTRI['database']['port']         ?? null);
        $this->setDatabase    (GALASTRI['database']['database']     ?? null);
        $this->setUser        (GALASTRI['database']['user']         ?? null);
        $this->setPassword    (GALASTRI['database']['password']     ?? null);
        $this->setOptions     (GALASTRI['database']['options']      ?? []);
        $this->setBackupFolder(GALASTRI['database']['backupFolder'] ?? []);

        return $this;
    }

    public function setCustomPdo($customDns, $user = null, $password = null, $options = null)
    {
        $this->setCustomDns($customDns);
        $this->setUser($user ?? $this->getUser());
        $this->setPassword($password ?? $this->getPassword());
        $this->setOptions($options ?? $this->getOptions());

        return $this;
    }

    /**
     * Método que faz a conexão com o banco de dados baseado nas configurações especificadas tanto
     * no arquivo config/database.php ou caso novas configurações sejam definidas diretamente pelos
     * métodos de configuração.
     */
    public function connect()
    {
        $active    = $this->getActive();
        $customDns = $this->getCustomDns();
        $driver    = $this->getDriver();
        $host      = $this->getHost();
        $port      = $this->getPort();
        $database  = $this->getDatabase();
        $user      = $this->getUser();
        $password  = $this->getPassword();
        $options   = $this->getOptions();

        if($active){
            try {
                if($customDns)  $this->pdo = new \PDO($customDns, $user, $password, $options);
                else            $this->pdo = new \PDO("$driver:host=$host;$port dbname=$database", $user, $password, $options);

                $this->setStatus(true);
            } catch (\PDOException $e) {
                throw new Exception(GALASTRI['debug'] ? $e->getMessage() : 'Erro durante a conexão com o banco de dados.', 'pdoError');
            }
        }
        return $this;
    }

    /**
     * Métodos de transação. Permite a definição de onde uma transação será iniciada e, caso algum
     * erro ocorra durante as consultas SQL, que todas as transações concluídas sejam desfeitas.
     */
    public function begin()  { if($this->getActive()) $this->pdo->beginTransaction(); }
    public function cancel() { if($this->getActive()) $this->pdo->rollBack(); }
    public function commit() { if($this->getActive()) $this->pdo->commit(); }

    /**
     * Método que verifica se existe um elo da corrente ativo antes de executar um novo teste.
     * Caso positivo, a corrente deverá ser resolvida antes da criação de uma nova.
     */
    private function beforeTest()
    {
        if(!$this->getStatus()){
            Debug::error('DATABASE001');
        } else {
            if($this->getActive()){
                if(Chain::hasLinks()){
                    $this->submit();
                }    
            }
        }
    }

    /**
     * Método que faz as consultas SQL. Neste microframework optou-se pela execução das consultas
     * através da digitação completa das querystrings. Ou seja, não existem atalhos prontos para
     * se realizar as consultas, todas as consultas precisam ser escritas em linguagem SQL.
     * 
     * O motivo disso é flexibilidade. As consultas SQL podem possuir sintaxes diferentes, inclusive
     * caso se utilize SGBDs diferentes do MySQL.
     * 
     * Todas as consultas, se utilizam da extensão PDO do PHP, o que permite o uso de algumas
     * especificações que tornam a consulta mais segura.
     * 
     * A sintaxe para consulta é
     * 
     *     $database = new Database;
     *     $database->query(<query SQL>, <rótulo>);
     * 
     * A query SQL é a consulta em si. O rótulo é um rótulo para armazenamento do resultado desta
     * consulta. Isso permite que os resultados de uma consulta sejam armazenados em uma array e
     * podem ser recuperados a qualquer momento através da chamada do rótulo. Quando um rótulo não
     * é especificado, a consulta fica armazenada em um rótulo padrão chamado galastriDefaultQuery
     * e cada consulta sem rótulo que for efetuada irá sobrescrever o resultado anterior.
     * 
     * A consulta pode se utilizar de que se utilizem de referências, que tornam os comandos SQL
     * mais seguros. Por exemplo:
     * 
     * Ao invés de utilizar algo como SELECT * FROM tabela WHERE id = $id
     * Utiliza-se SELECT * FROM tabela where id = :id
     * 
     * :id não se trata de uma variável ou constante PHP, por isso, precisa precisa ser referenciada
     * através do método bind() ou bindArray(), de forma a permitir que a consulta seja feita
     * corretamente.
     * 
     * O motivo de se usar referências é que isto é muito mais seguro do que usar a variável
     * diretamente na consulta, pois toda a consulta é interpretada como uma string, o que faz
     * com que ameaças como SQL Injection sejam suprimidas.
     * 
     * @param string $queryString      Comandos SQL para realização da consulta.
     * 
     * @param string $label            Rótulo da consulta para ser armazenado individualmente.
     */
    public function query($queryString, $label = 'galastriDefaultQuery')
    {
        $this->beforeTest();

        /** Este método cria um elo em uma corrente, o que permite que sejam concatenados outros
         * métodos junto a ela. */
        Chain::create(
            'query',
            [
                'name'        => 'query',
                'queryString' => $queryString,
                'label'       => $label,
                'attach'      => true,
            ],
            (
                function($chainData, $data){
                    if($this->getActive()){
                        Debug::trace(debug_backtrace()[0]);

                        $this->label = $data['label'];

                        $bind      = [];
                        $bindArray = [];

                        /** Armazena os dados da querystring principal e de paginação. A querystring
                         * é dividida entre duas variáveis pois a paginação realiza uma consulta
                         * um pouco diferente da consulta principal. A consulta de paginação
                         * leva a conta todos os resultados. Já a consulta principal leva em conta
                         * apenas o que se deseja exibir. */
                        $mainQuery  = trim($data['queryString']);
                        $mainQuery  = preg_replace('/[\t\n]+/u', ' ', $mainQuery);
                        $resultLog  = [];

                        $pagQuery   = $data['queryString'];
                        $pagStatus  = false;
                        $pagLog     = [];

                        $queryType  = trim(lower(explode(' ', $mainQuery)[0]));
                        /** Verifica se o termo LIMIT foi usado na querystring. Caso tenha sido,
                         * então a paginação não será executada. Isso ocorre pois a paginação
                         * necessita de uma querystring livre de limitações, já que a paginação
                         * se utiliza, em suma, de um limitador próprio. */
                        preg_match('/limit/', lower($mainQuery), $limitMatch);
                        $limitMatch = empty($limitMatch[0]) ? false : $limitMatch[0];

                        foreach($chainData as $parameter){
                            switch($parameter['name']){
                                    /** Execução da Query. Caso o LIMIT não esteja definido na própria
                                     * querystring e caso o status de paginação seja verdadeiro, então
                                     * é inserido, na consulta principal, um LIMIT baseado nas
                                     * configurações da paginação.
                                     * 
                                     * A consulta é preparada e todos os valores inseridos nos métodos
                                     * bind() ou bindArray() são unificados na variável bindArray().
                                     * 
                                     * Cada um dos valores informados nos binds são verificados, já
                                     * que valores do tipo null precisam ser explicitamente declarados
                                     * com o parâmetro PDO::PARAM_NULL.*/
                                case 'query':
                                    if(!$limitMatch and $pagStatus){
                                        $mainQuery .= $pagPerPage ? ' LIMIT '.(($pagPage-1)*$pagPerPage).", $pagPerPage" : '';
                                    }

                                    $sql       = $this->pdo->prepare($mainQuery);
                                    $bindArray = array_merge($bind, $bindArray);

                                    foreach($bindArray as $key => &$value){
                                        if($value === null or (empty($value) and $value !== 0 and $value !== 0.0 and $value !== "0")){
                                            $sql->bindParam($key, $value, \PDO::PARAM_NULL);
                                            $value = null;
                                        }
                                    } unset($value);

                                    /** A consulta é realizada. É verificado se a consulta é do
                                     * tipo SELECT. Caso seja e caso a quantidade de resultados
                                     * encontrados seja maior do que zero, então isso indica que
                                     * tais resultados precisam ser organizados em uma array
                                     * associativa.
                                     * 
                                     * A variável/array $resultLog['found'] é definida como true,
                                     * para que seja fácil identificar quando a consulta encontra
                                     * ou não resultados.
                                     * 
                                     * A variável/array $resultLog['data'] armazena todos os
                                     * resultados encontrados em uma array associativa em que o
                                     * nome do campo da tabela é a chave o seu valor é o valor da
                                     * chave.*/

                                    if($sql->execute($bindArray)) {
                                        switch($queryType){
                                            case 'select':

                                                if($sql->rowCount() > 0){
                                                    $resultLog['found'] = true;

                                                    while($found = $sql->fetch(\PDO::FETCH_ASSOC)){
                                                        $resultLog['data'][] = $found;
                                                    }

                                                    /** Não existindo LIMIT na consulta e a paginação
                                                     * estando ativa, uma nova consulta é realizada,
                                                     * levando em conta apenas a querystring sem
                                                     * limitações.
                                                     * 
                                                     * Desta forma é possível recuperar a quantidade
                                                     * total de resultados encontrado, calcular a
                                                     * quantidade de páginas que a consulta possui,
                                                     * a página atual e a quantidade de resultados
                                                     * por página.*/
                                                    if(!$limitMatch and $pagStatus){
                                                        $pagLog['status'] = true;

                                                        $sql = $this->pdo->prepare(trim($pagQuery));
                                                        $sql->execute($bindArray);

                                                        $pagLog['entries'] = $sql->rowCount();
                                                        $pagLog['pages']   = (int)ceil($sql->rowCount()/$pagPerPage);
                                                        $pagLog['page']    = $pagPage;
                                                        $pagLog['perPage'] = $pagPerPage;
                                                    }
                                                }
                                                break;

                                                /** Caso a consulta seja do tipo INSERT, então é
                                                 * armazenado o último ID inserido no banco de
                                                 * dados.*/
                                            case 'insert':
                                                $resultLog['found'] = true;
                                                $resultLog['lastId'] = $this->pdo->lastInsertId();
                                                break;

                                            default:
                                                $resultLog['found'] = true;
                                                break;
                                        }
                                        $resultLog['affectedRows'] = $sql->rowCount();
                                        $resultLog['queryType']    = $queryType;
                                        $this->setPagination($pagLog);
                                        $this->setResult($resultLog);
                                    } else {
                                        throw new Exception(GALASTRI['debug'] ? implode(' - ',$sql->errorInfo()) : 'Houve um erro durante a consulta ao banco de dados.', 'pdoError');
                                    }

                                    break;

                                    /** Os casos abaixo fazem o armazenamento dos parâmetros informados
                                     * nos métodos que estiverem encadeados na corrente. Cada elo
                                     * resolvido tem funções específicas.
                                     * 
                                     * Os métodos bind() e bindArray() criam elos na corrente cujos
                                     * dados são armazenados e usados no método principal query()
                                     * com os argumentos de referência na querystring.
                                     * 
                                     * O método pagination() cria um elo na corrente que cujos dados
                                     * são armazenados e usados no método principal query() a respeito
                                     * de paginação. */
                                case 'bind':
                                    $field        = $parameter['field'];
                                    $value        = $parameter['value'];

                                    $bind[$field] = $value;
                                    break;

                                case 'bindArray':
                                    $bindArray    = $parameter['fields'];
                                    break;

                                case 'pagination':
                                    $pagStatus    = true;
                                    $pagPage      = $parameter['page'];
                                    $pagPerPage   = $parameter['perPage'];
                                    break;
                            }
                        }
                        return Chain::resolve($chainData, $data);
                    }
                }
            )
        );
        return $this;
    }

    /**
     * Método que cria um elo na corrente que armazena argumentos de referência e seu respectivo
     * valor real. Os argumentos de referência podem ser nomeados iniciando-se com dois pontos :
     * ou sem nomes, usando apenas pontos de interrogação ?. Neste caso, o bind irá requerer o
     * uso de números na ordem em que aparecem na querystring.
     * 
     * Exemplos de uso:
     * 
     *     $database->query(SELECT * FROM tabela WHERE autor = :autor AND editora = :editora)
     *              ->bind(':autor', $_POST['autor'])
     *              ->bind(':editora', $_POST['editora'])
     *              ->submit();
     * 
     *     $database->query(SELECT * FROM tabela WHERE autor = ? AND editora = ?)
     *              ->bind(1, $_POST['autor'])
     *              ->bind(2, $_POST['editora'])
     *              ->submit();
     * 
     * @param int|string $field        Armazena o nome da referência ou o número da ocorrência da
     *                                 querystring.
     * 
     * @param mixed $value             Valor real a qual o argumento de referência se refere e que
     *                                 será o dado real usado na consulta.
     */
    public function bind($field, $value)
    {
        if($this->getActive()){
            Chain::create(
                'bind',
                [
                    'name'         => 'bind',
                    'field'        => $field,
                    'value'        => $value,
                    'attach'    => true,
                ],
                (function($chainData, $data){ return Chain::resolve($chainData, $data); })
            );
        }
        return $this;
    }


    /**
     * Método que faz a mesma coisa que o método bind(), com a diferença de que ao invés de receber
     * uma array com vários argumentos de referência e seus respectivos valores. O bind() aceita
     * apenas um argumento de referência e um valor. O bindArray() recebe uma array com vários.
     * 
     * Importante: este método tem efeito uma única vez, ou seja, usar vários bindArray() fará
     * apenas com que seus valores se sobreponham, não adiantando utilizá-lo várias vezes.
     * 
     * Exemplo de uso:
     * 
     *     $dados = array(
     *          ':autor' => $_POST['autor'],
     *          ':editora' => $_POST['editora'],
     *     );
     * 
     *     $database->query(SELECT * FROM tabela WHERE autor = :autor AND editora = :editora)
     *              ->bindArray($dados)
     *              ->submit();
     * 
     * @param array $fields            Armazena os nomes de referência ou números de ocorrência
     *                                 da querystring e seus respectivos valores reais.
     */
    public function bindArray(array $fields)
    {
        if($this->getActive()){
            Chain::create(
                'bindArray',
                [
                    'name'   => 'bindArray',
                    'fields' => $fields,
                    'attach' => true,
                ],
                (function($chainData, $data){ return Chain::resolve($chainData, $data); })
            );
        }
        return $this;
    }

    /**
     * Método que cria um elo na corrente contendo dados para criar paginação.
     * 
     * Importante: este método tem efeito uma única vez, ou seja, usar vários pagination() fará
     * apenas com que seus valores se sobreponham, não adiantando utilizá-lo várias vezes.
     * 
     * Exemplo de uso:
     * 
     *     $database->query(SELECT * FROM tabela)
     *              ->pagination(1, 10)
     *              ->submit();
     * 
     * @param int $page                Armazena a página atual. Nada mais é do que um offset, ou
     *                                 seja, se a paginação exibe 10 resultados por página, então
     *                                 a página 1 irá exibir os resultados de 1 a 10, a página 2
     *                                 irá exibir os resultados de 11 a 20, e assim por diante.
     * 
     * @param int $perPage             Armazena quantos resultados serão mostrados por página.
     *                                 Ou seja, trata-se da quantidade máxima de resultados que
     *                                 serão retornados na consulta.
     * 
     */
    public function pagination($page, $perPage)
    {
        if($this->getActive()){
            Chain::create(
                'pagination',
                [
                    'name'    => 'pagination',
                    'page'    => $page,
                    'perPage' => $perPage,
                    'attach'  => true,
                ],
                (function($chainData, $data){ return Chain::resolve($chainData, $data); })
            );
        }
        return $this;
    }

    /**
     * Método que faz a resolução dos elos da corrente. Cada um dos elos criados não é executado,
     * apenas armazenado. Por isso, o método submit() é necessário, pois é ele que inicia a
     * resolução da corrente executando cada um dos elos armazenados.
     */
    public function submit()
    {
        if($this->getActive()){
            return Chain::resolve();
        }
    }

    /**
     * Método que armazena os resultados da consulta em um objeto StdClass que será acessível
     * através do método getResult().
     * 
     * @param array $result            Armazena a array que retorna todos os dados encontrados
     *                                 numa consulta SQL, caso a consulta seja um SELECT, ou
     *                                 dados como o último id inserido na tabela, caso a consulta
     *                                 seja um INSERT, ou ainda a quantidade de resultados
     *                                 afetados, em qualquer tipo de consulta.
     */
    private function setResult($result)
    {
        $label                = $this->label;
        $this->result[$label] = new \StdClass;

        $this->result[$label]->label        = $label;
        $this->result[$label]->queryType    = $result['queryType'] ?? false;
        $this->result[$label]->affectedRows = $result['affectedRows'] ?? false;
        $this->result[$label]->found        = $result['found'] ?? false;
        $this->result[$label]->data         = $result['data'] ?? [];
        $this->result[$label]->lastId       = $result['lastId'] ?? null;
    }

    /**
     * Método que armazena os resultados de paginação em um objeto StdClass que será acessível
     * através do método getPagination().
     * 
     * @param array $result            Armazena a array que retorna os dados de paginação
     *                                 retornados pela consulta de paginação.
     */
    private function setPagination($result)
    {
        $label = $this->label;
        $this->pagination[$label] = new \StdClass;

        $this->pagination[$label]->status  = $result['status'] ?? false;
        $this->pagination[$label]->entries = $result['entries'] ?? null;
        $this->pagination[$label]->pages   = $result['pages'] ?? null;
        $this->pagination[$label]->page    = $result['page'] ?? null;
        $this->pagination[$label]->perPage = $result['perPage'] ?? null;
    }

    /**
     * Recupera os resultados de consulta.
     * 
     * @param string|null $label       Informa o rótulo de consulta utilizado no método query()
     *                                 que armazena o resultado que se quer recuperar. Quando
     *                                 não informado, utiliza o rótulo padrão.
     */
    public function getResult($label = 'galastriDefaultQuery')
    {
        return $this->propertyResults($label, 'result');
    }

    /**
     * Recupera os resultados de paginação.
     * 
     * @param string|null $label       Informa o rótulo de consulta utilizado no método query()
     *                                 que armazena o resultado que se quer recuperar. Quando
     *                                 não informado, utiliza o rótulo padrão.
     */
    public function getPagination($label = 'galastriDefaultQuery')
    {
        return $this->propertyResults($label, 'pagination');
    }

    /**
     * Este método é chamado quando algum dos métodos getResult() ou getPagination() é executado.
     * Como ambos executam comandos idênticos, optou-se por definir um bloco de comandos único que
     * pode ser reaproveitado por qualquer um dos métodos.
     * 
     * @param string $label            Informa o rótulo de consulta utilizado no método query().
     * 
     * @param string $property         Informa o nome da propriedade que representa qual o
     *                                 método está sendo executado.
     */
    private function propertyResults($label, $property)
    {
        Debug::trace(debug_backtrace()[0]);

        $keys = array_keys($this->$property);

        if(isset($this->$property[$label])){
            return $this->$property[$label];
        } else {
            if($label === 'galastriDefaultQuery'){
                Debug::error('DATABASE003', $label)::print();

            } else {
                Debug::error('DATABASE004', $label)::print();
            }
        }
    }

    /**
     * Método que limpa os dados armazenados em um rótulo.
     * 
     * @param string $label            Informa o rótulo de consulta utilizado no método query().
     */
    public function clearResult($label)
    {
        $this->result[$label]     = null;
        $this->pagination[$label] = null;
    }

    /**
     * Método que elimina um rótulo e, consequentemente, todos seus dados.
     * 
     * @param string $label            Informa o rótulo de consulta utilizado no método query().
     */
    public function removeResult($label)
    {
        $this->clearResult($label);

        if($label != 'galastriDefaultQuery'){
            unset($this->result[$label]);
            unset($this->pagination[$label]);
        }
    }

    /**
     * Método que retorna o AUTO_INCREMENT de uma tabela do banco de dados informado.
     * 
     * @param string $table            Tabela de onde se quer verificar o próximo AUTO_INCREMENT.
     */
    public function getTableNextId($table)
    {
        $query = (function($driver){
            switch($driver){
                case 'mysql':
                    return '
                        SELECT
                            AUTO_INCREMENT as autoIncrement
                        FROM
                            INFORMATION_SCHEMA.TABLES
                        WHERE
                            TABLE_SCHEMA = :database AND
                            TABLE_NAME = :table';
                    break;
            }
        })($this->driver);

        $this->query($query, 'galastriLastId')
            ->bind(':database', $this->database)
            ->bind(':table', $table)
            ->submit();

        return $this->getResult('galastriLastId')->data[0]['autoIncrement'];
    }

    /**
     * Método para simplificar a criação de arquivos de backup. Este método permite 3 modos para
     * a criação de um arquivo de backup:
     * 
     *    1. Backup do banco de dados inteiro;
     *    2. Backup de uma tabela inteira;
     *    3. Backup de um trecho de uma tabela.
     * 
     * Quando o driver é o mysql, este método se utiliza de comandos mysqldump que exigem que o
     * servidor permita execuções de comandos através da função exec() do PHP. É importante
     * ressaltar que nem todo servidor tem isso habilitado por padrão.
     * 
     * @param bool|string $condition       Condição se será usada caso se deseje fazer um backup
     *                                     de um trecho de uma tabela.
     */
    public function backup($condition = false)
    {
        if(!$this->getStatus())
            Debug::error('DATABASE001')::print();

        if(!$this->getFilename())
            Debug::error('DATABASE005')::print();

        if($this->getActive()){
            $file = path(rtrim($this->getBackupFolder().'/'.$this->getFilename(), '.sql').'.sql');

            $query = (function($driver, $condition, $file){
                $host      = $this->getHost();
                $database  = $this->getDatabase();
                $user      = $this->getUser();
                $password  = $this->getPassword();
                $table     = $this->getTable();

                $folder = dirname($file);

                if(!is_dir($folder))
                    mkdir($folder, 0755, true);

                switch($driver){
                    case 'mysql':
                        $baseQuery = "mysqldump --user=$user --password=$password --host=$host $database";
                        if(!$table and !$condition)
                            return "$baseQuery --result-file='$file' 2>&1";

                        if($table and !$condition)
                            return "$baseQuery $table --result-file='$file' 2>&1";

                        if($table and $condition)
                            return "$baseQuery $table --where='$condition' --result-file='$file' 2>&1";
                }
            })($this->getDriver(), $condition, $file);

            exec($query, $output);

            return [
                'file' => $file,
                'path' => formatAbsolutePath($file),
                'output' => $output,
            ];
        }
    }
}
