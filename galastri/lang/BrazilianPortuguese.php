<?php

namespace galastri\lang;

/**
 * This interface stores the various messages in Brazilian Portuguese language. It is dynamically
 * implemented in many classes based on the debug configuration 'language' parameter.
 */
interface BrazilianPortuguese
{
    /**
     * Constants used in \galastri\core\Debug.
     */
    const GENERIC_MESSAGE = "Ocorreu um erro. Por favor, contate o administrador.";
    
    /************************************************
     * Constants used in \galastri\core\Galastri.
     */
    const OFFLINE = [
        'G0000', ""
    ];

    const UNDEFINED_OUTPUT = [
        'G0001', "Não foi definido um parâmetro 'output' para esta rota. Configure-a em '\app\config\routes.php'."
    ];

    const INVALID_OUTPUT = [
        'G0002', "Output '%s' é inválido. Apenas estes outputs são permitidos: view, json, file or text."
    ];

    const ERROR_404 = [
        'G0003', "Erro 404: A rota requisitada não foi encontrada."
    ];

    const CONTROLLER_NOT_FOUND = [
        'G0004', "O controller '%s' requisitado não existe. Verifique se o arquivo '%s.php' existe na pasta '%s' ou se seu namespace foi definido corretamente."
    ];

    const CONTROLLER_DOESNT_EXTENDS_CORE = [
        'G0005', "O controller '%s' não está herdando a classe principal \galastri\core\Controller. Adicione a classe principal em seu controller."
    ];

    const CONTROLLER_METHOD_NOT_FOUND = [
        'G0006', "O controller '%s' não contém o método requisitado '@%s'."
    ];

    const VALIDATION_ERROR = [
        'G0007', "A validação '%s' retornou como inválida. A execução não pode prosseguir."
    ];

    /**
     * Constants used in \galastri\core\Route.
     */
    const INVALID_PARAM_TYPE = [
        'G0008', "Parâmetro de configuração inválido. O parâmetro '%s' precisa ser um(a) '%s'. Foi informado um(a) '%s'."
    ];

    const REQUEST_METHOD_STARTS_WITH_AT = [
        'G0009', "Método de requisição '%s' precisa ter seu primeiro caractere com @"
    ];
        
    const INVALID_REQUEST_METHOD_NAME = [
        'G0010', "Método de requisição '%s' possui um nome inválido."
    ];

    /**
     * Constants used in \galastri\extensions\ViewOutputData.
     */
    const VIEW_INVALID_DATA_KEY = [
        'G0011', "Chave '%s' não existe nos dados processados pelo controller."
    ];

    /**
     * Constants used in \galastri\modules\Toolbox.
     */
    const EMPTY_FILE_PATH = [
        'G0012', "O caminho do método '%s' está vazio."
    ];

    /**
     * Constants used in \galastri\types\ Type* files.
     */
    const TYPE_DEFAULT_INVALID_MESSAGE = [
        'G0014', "Tipo de dado inválido. Esperando que seja '%s', mas '%s' foi atribuído."
    ];

    /**
     * Constants used in \galastri\extensions\output\View
     */
    const UNDEFINED_TEMPLATE_FILE = [
        'G0015', "Não foi definido um arquivo de template para este rota. Defina um template padrão nas configurações de projeto ou de rota."
    ];

    const TEMPLATE_FILE_NOT_FOUND = [
        'G0016', "Arquivo de template '%s' não encontrado."
    ];

    const VIEW_FILE_NOT_FOUND = [
        'G0017', "Arquivo de view '%s' não encontrado."
    ];

    /**
     * Constants used in \galastri\extensions\typeValidation\StringValidation
     */
    const UNDEFINED_VALIDATION_ALLOWED_CHARSET = [
        'G0018', "O método 'allowedCharset()' requer um ou mais charsets definidos. Nenhum foi informado."
    ];

    const UNDEFINED_VALIDATION_REQUIRED_CHARSET = [
        'G0019', "O método 'requiredChars()' requer um ou mais charsets definidos. Nenhum foi informado."
    ];

    const VALIDATION_STRING_LOWER_CASE_ONLY = [
        'G0023', "O valor deve conter apenas characteres minúsculos."
    ];

    const VALIDATION_STRING_UPPER_CASE_ONLY = [
        'G0023', "O valor deve conter apenas characteres maiúsculos."
    ];

    const VALIDATION_STRING_MIN_LENGTH = [
        'G0023', "O valor deve conter no mínimo '%s' caracteres, mas o valor atual contém '%s'."
    ];

    const VALIDATION_STRING_MAX_LENGTH = [
        'G0023', "O valor deve conter no máximo '%s' caracteres, mas o valor atual contém '%s'."
    ];

    const VALIDATION_STRING_INVALID_CHARS = [
        'G0023', "O valor não pode conter os caracteres '%s'."
    ];

    const VALIDATION_STRING_REQUIRED_CHARS = [
        'G0023', "É obrigatório o valor conter '%s' de um destes caracteres '%s'. Foi(ram) informado(s) '%s'."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\Common
     */
    const TYPE_HISTORY_KEY_NOT_FOUND = [
        'G0020', "Não há nenhuma chave '%s' no histórico do objeto de tipo."
    ];

    const TYPE_HISTORY_DISABLED = [
        'G0020', "Salvamento de histório está desabilitado, não existem dados para se reverter. Se você quiser habilitá-lo, atribua 'true' para o segundo parâmetro do construtor na definição deste objeto de tipos."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\RandomStringValue
     */
    const SECURE_RANDOM_GENERATOR_NOT_FOUND = [
        'G0022', "Nenhuma função de geração de string aleatória criptograficamente segura disponível. Você precisa verificar sua configuração do PHP para disponibilizar as funções 'random_bytes()' ou 'openssl_random_pseudo_bytes()'."
    ];

    /**
     * Constants used in \galastri\extensions\typeValidation\NumericValidation
     */
    const VALIDATION_NUMERIC_MIN_VALUE = [
        'G0023', "O valor mínimo é '%s'. Foi informado '%s'."
    ];

    const VALIDATION_NUMERIC_MAX_VALUE = [
        'G0023', "O valor máximo é '%s'. Foi informado '%s'."
    ];

    /**
     * Constants used in \galastri\extensions\typeValidation\traits\AllowedValueList
     */
    const VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST = [
        'G0023', "É necessário definir ao menos um valor no método 'allowedValueList'."
    ];

    const VALIDATION_INVALID_TYPE_ALLOWED_VALUE_LIST = [
        'G0023', "O método de validação 'allowedValueList' possui um valor inválido '%s'. Era esperado um tipo '%s', mas um tipo '%s' foi atribuído."
    ];

    const VALIDATION_NO_VALUE_IN_ALLOWED_LIST = [
        'G0023', "O valor %s não é permitido. Os valores possíveis são '%s'."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\Math
     */
    const MATH_ROOT_CANNOT_BE_ZERO = [
        'G0024', "O método root() não pode ter um grau igual a zero."
    ];
}
