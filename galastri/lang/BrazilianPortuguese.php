<?php

namespace galastri\lang;

interface BrazilianPortuguese
{
    /**
     * Constants used in \galastri\core\Debug.
     */
    const GENERIC_MESSAGE = "Ocorreu um erro. Por favor, contate o administrador.";
    
    /**
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
     * Constants used in \galastri\extensions\types\TraitCommon.
     */
    const VALIDATION_DEFAULT_INVALID_MESSAGE = [
        'G0013', "O valor '%s' é inválido."
    ];
}