<?php

namespace galastri\cli\interfaces\language;

interface PackageBuilder
{
    const WELCOME_WINDOW = [
        'en' => [
            'Welcome to the package builder of the Galastri Framework',
            'WARNING: Use this program only if you are a developer of the core of the Galastri Framework and if you understand what you are doing.',
            'Choose an option below:',
            '1. Build a release package',
            '2. Build a update package',
            '0. Exit the package builder',
        ],
        'br' => [
            'Seja bem-vindo ao construtor de pacotes do Galastri Framework',
            'AVISO: Use este programa apenas se você for desenvolvedor do core do Galastri Framework e souber o que está fazendo!',
            'Escolha uma das opções abaixo:',
            '1. Gerar pacote de lançamento',
            '2. Gerar pacote de atualização',
            '0. Sair do construtor de pacotes',
        ],
    ];

    const PACK_AN_UPDATE_WINDOW = [
        'en' => [
            'Build an update package',
            'The update package are built based on the commit of Git.',
            'Please, inform which commit you want to start from:',
            '1. Build based on the most recent commit',
            '2. Build getting from a previous commit until the most recent one ',
            '0. Cancel and go back',
        ],
        'br' => [
            'Gerar um pacote de atualização',
            'Os pacotes de atualização são gerados baseados nos commits do Git.',
            'Por favor, informe a partir de qual commit você deseja gerar o pacote.',
            '1. Gerar o pacote com base apenas no commit mais recente',
            '2. Gerar o pacote pegando de um commit anterior até chegar ao commit mais recente',
            '0. Cancelar e voltar',
        ],
    ];

    const PACK_AN_UPDATE_SELECT_HEAD_WINDOW = [
        'en' => [
            'Inform how many previous commits you want to include in your package (counting from the most recent).',
            'Or inform 0 zero to cancel and go back.',
        ],
        'br' => [
            'Informe quantos commits anteriores você deseja incluir no pacote (contando a partir do mais recente).',
            'Ou informe 0 zero para cancelar e voltar.',
        ],
    ];

    const IS_IT_BREAKING_CHANGE = [
        'en' => [
            'Is this update pack part of a breaking change update?',
        ],
        'br' => [
            'Esta é uma atualização com mudanças que quebram versões anteriores (breaking change)?',
        ],
    ];

    const VERSION_IS_IN_DEVELOPMENT_STATUS = [
        'en' => [
            'The Galastri Framework version has the term "dev" in it.',
            'Because of this, the files of the package were separated, but not packed.',
            'They will be packed after a commit that removes the term "dev" from the version.',
        ],
        'br' => [
            'A versão do Galastri Framework está com o termo "dev".',
            'Por conta disso, os arquivos do pacote foram separados, mas não empacotados.',
            'Eles serão empacotados assim que for realizado um commit sem o termo "dev" na versão.',
        ],
    ];

    const PACKAGE_CREATED_AT = [
        'en' => [
            'Update package created at:',
        ],
        'br' => [
            'Pacote de atualização criado em:',
        ],
    ];

    const COPYING_UPDATE_FILES = [
        'en' => [
            'Getting the files of the update.',
        ],
        'br' => [
            'Separando os arquivos do pacote de atualização.',
        ],
    ];

    const GETTING_THE_COMMIT_DATA = [
        'en' => [
            'Grouping data from the commit HEAD~',
        ],
        'br' => [
            'Reunindo dados do commit HEAD~',
        ],
    ];
}
