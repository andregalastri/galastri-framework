<?php

namespace galastri\cli\interfaces\language;

interface Common
{
    const AVAILABLE_LANGUAGES = ['br', 'en'];

    const NO_VERSION_FILE = [
        'en' => [
            'The "VERSION" file wasn\'t found in the "galastri" folder.',
            'Without this file, the updater cannot continue.',
        ],
        'br' => [
            'Não foi encontrado o arquivo "VERSION" dentro da pasta "galastri".',
            'Sem este arquivo, o atualizador não pode continuar.',
        ],
    ];

    const NO_WRITING_PERMISSION = [
        'en' => [
            'The following folder doesn\'t have writing permissions:',
            'Grant write permissions to the folder and run the updater again.',
        ],
        'br' => [
            'A seguinte pasta não possui permissões de escrita:',
            'Conceda permissões de escrita para a pasta e execute o atualizador novamente.',
        ],
    ];

    const PRESS_ENTER_TO_CONTINUE = [
        'en' => [
            'Press ENTER to continue...',
        ],
        'br' => [
            'Pressione ENTER para continuar...',
        ],
    ];

    const CHOOSE_AN_OPTION = [
        'en' => [
            'Choose an option: ',
        ],
        'br' => [
            'Escolha uma das opções: ',
        ],
    ];

    const INVALID_OPTION = [
        'en' => [
            'Option "',
            '" is invalid. Please, choose a valid option.',
        ],
        'br' => [
            'A opção "',
            '" é inválida. Por favor, escolha uma opção válida.',
        ],
    ];
  
    const INFORM_VALUE = [
        'en' => [
            'Inform a value: ',
        ],
        'br' => [
            'Informe um valor: '
        ],
    ];

    const INVALID_VALUE = [
        'en' => [
            'The informed value "',
            '" is invalid. Please, try again.',
        ],
        'br' => [
            'O valor informado "',
            '" é inválido. Por favor, tente novamente.',
        ],
    ];

    const CHOOSE_NO_OR_YES = [
        'en' => [
            'Choose yes or No [y/N]: ',
        ],
        'br' => [
            'Escolha sim ou Não [s/N]: ',
        ],
    ];

    const CHOOSE_YES_OR_NO = [
        'en' => [
            'Choose Yes or no [y/N]: ',
        ],
        'br' => [
            'Escolha Sim ou não [S/n]: ',
        ],
    ];

    const EXIT = [
        'en' => [
            'See ya!',
            'Bye bye! :)',
        ],
        'br' => [
            'Até mais!',
            'Bye bye! :)',
        ],
    ];
}
