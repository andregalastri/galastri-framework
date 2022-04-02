<?php

namespace galastri\cli\interfaces;

interface UpdaterLanguage
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

    const CHOOSE_AN_OPTION = [
        'en' => [
            'Choose an option: ',
        ],
        'br' => [
            'Escolha uma das opções: ',
        ],
    ];

    const EXIT = [
        'en' => [
            'Closing the updater!',
            'Bye bye! :)',
        ],
        'br' => [
            'Encerrando o atualizador!',
            'Bye bye! :)',
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

    const NO_BACKUP_FOUND = [
        'en' => [
            'No backup found.',
        ],
        'br' => [
            'Nenhum backup encontrado.',
        ],
    ];

    const CHOOSE_BACKUP_RESTORATION = [
        'en' => [
            "Choose a number of the backup to be restored,\n",
            'or choose 0 (zero) to cancel: ',
        ],
        'br' => [
            "Informe o número do backup a ser restaurado,\n",
            'ou informe 0 (zero) para cancelar: ',
        ],
    ];

    const CANCEL_BACKUP_RESTORATION = [
        'en' => [
            'No problem! :)',
            'Process cancelled. No backup was restored!',
        ],
        'br' => [
            'Sem problemas! :)',
            'Processo cancelado. Nenhum backup foi restaurado!',
        ],
    ];

    const CONFIRM_BACKUP_RESTORAION = [
        'en' => [
            'Confirm the backup restoration? ',
            'Confirma the restoration? [y/N]: ',
        ],
        'br' => [
            'Confirma a restauração do backup? ',
            'Confirma a restauração? [s/N]: ',
        ],
    ];

    const INVALID_BACKUP_OPTION = [
        'en' => [
            'The number you choose is invalid. Try again!',
        ],
        'br' => [
            'O número informado é inválido. Tente novamente!',
        ],
    ];

    const CHECKING_UPDATES = [
        'en' => [
            'Checking for updates. Make sure that you have internet access!',
        ],
        'br' => [
            'Verificando atualizações. Certifique-se de que você tem acesso à internet!',
        ],
    ];

    const RESTORING_BACKUP = [
        'en' => [
            'Restoring the backup files.',
        ],
        'br' => [
            'Restaurando os arquivos do backup.',
        ],
    ];

    const RESTORING_BACKUP_PROCESS = [
        'en' => [
            '- Restoring files from ',
        ],
        'br' => [
            '- Restaurando os arquivos de ',
        ],
    ];

    const BACKUP_RESTORED = [
        'en' => [
            'Restoration process done! :D',
        ],
        'br' => [
            'Processo de restauração concluído! :D',
        ],
    ];

    const INVALID_VERSION = [
        'en' => [
            'Strange... Your current version is "',
            '", but it isn\'t in the list of available versions of Galastri Framework.',
            'For security reasons, it is better you update the framework manually.',
        ],
        'br' => [
            'Estranho... A sua versão é a "',
            '", mas ela não consta na lista de versões existentes do Galastri Framework.',
            'Por questões de segurança, é melhor você atualizar o framework manualmente.',
        ],
    ];

    const UP_TO_DATE_VERSION = [
        'en' => [
            'There is no new updates available',
            'Your version "',
            '" is already the most up to date!',
        ],
        'br' => [
            'Nenhuma nova atualização disponível.',
            'Sua versão "',
            '" já é a mais recente!',
        ],
    ];

    const STARTING_UPDATE = [
        'en' => [
            'Starting the update process!',
        ],
        'br' => [
            'Iniciando o processo de atualização!',
        ],
    ];

    const UPDATE_PROCESS = [
        'en' => [
            ' - Downloading the package',
            ' - Package downloaded. Extracting the files.',
            ' - Applying the update package.',
        ],
        'br' => [
            ' - Baixando o pacote "',
            ' - Pacote baixado. Extraindo os arquivos.',
            ' - Aplicando o pacote de atualização.',
        ],
    ];

    const UPDATER_UPDATED = [
        'en' => [
            'The updater updated itself!',
            'We need to restart the updater before continue.',
            'Run the updater again and restart the updating process to continue with the installation.',
        ],
        'br' => [
            'O atualizador foi atualizado!',
            'Precisamos reiniciar o atualizar antes de continuar.',
            'Reabra-o e reinicie o processo de atualização para prosseguir com a instalação de novos pacotes.',
        ],
    ];

    const UPDATE_DONE = [
        'en' => [
            'Updating process done! :D',
            'Your Galastri Framework is now in the version ',
        ],
        'br' => [
            'Processo de atualização concluído! :D',
            'Seu Galastri Framework agora está na versão ',
        ],
    ];

    const CONFIRM_UPDATE = [
        'en' => [
            'Start update? [y/N]: ',
        ],
        'br' => [
            'Deseja atualizar? [s/N]: ',
        ],
    ];

    const CANCEL_UPDATE = [
        'en' => [
            'No problem! :)',
            'Update cancelled. No update package was installed!',
        ],
        'br' => [
            'Sem problemas! :)',
            'Processo cancelado. Nenhum pacote de atualização foi instalado!',
        ],
    ];

    const BACKUP_PROCESS = [
        'en' => [
            '- Creating a backup of the current files.',
            'Backup created at:',
        ],
        'br' => [
            '- Fazendo backup dos arquivos atuais.',
            'Backup criado em:',
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

    const WELCOME_WINDOW = [
        'en' => [
            'Welcome to the Galastri Framework updater!',
            'Choose an option below:',
            '1. Check for new updates',
            '2. Restore backups',
            '0. Exit the updater',
        ],
        'br' => [
            'Seja bem-vindo ao atualizador do Galastri Framework!',
            'Escolha uma das opções abaixo:',
            '1. Verificar novas atualizações',
            '2. Restaurar backups',
            '0. Sair do atualizador',
        ],
    ];

    const UPDATE_WINDOW_ABOUT = [
        'en' => [
            'About the updating process:',
            '- A backup will be created before any changes.',
            '- It WON\'T CHANGE the files inside "app", "logs", "public_html" and "vendor", nor the files .gitattributes", ".gitignore", "composer.json", "LICENSE" and "README.md".',
            '- Any other file can be updated.',
            'Do you want to proceed with the update?',
            '(y) YES | (N) No',
        ],
        'br' => [
            'Sobre o processo de atualização:',
            '- É realizado um backup antes de qualquer alteração.',
            '- NÃO SERÃO ALTERADOS arquivos contidos em "app", "logs", "public_html" e "vendor", nem os arquivos ".gitattributes", ".gitignore", "composer.json", "LICENSE" e "README.md".',
            '- Quaisquer outros arquivos podem sofrer alterações.',
            'Deseja aplicar a atualização?',
            '(s) SIM | (N) NÃO',
        ],
    ];

    const BACKUP_WINDOW_ABOUT = [
        'en' => [
            'About the restoring process:',
            '- The restoration process restore the updates from the most recent to the selected point you choose.',
            '- The restored backup data is removed from the backup folder.',
            '- It WON\'T CHANGE the files inside "app", "logs", "public_html" and "vendor", nor the files .gitattributes", ".gitignore", "composer.json", "LICENSE" and "README.md".',
            '- Any other file can be restored.',
            'Choose the number of the backup you want to restore, or type 0 (zero) to cancel.',
        ],
        'br' => [
            'Sobre o processo de restauração:',
            '- A restauração reverte as alterações do ponto mais recente até o ponto que você escolher.',
            '- Os dados do backup restaurado são removidos da pasta de backup.',
            '- NÃO SERÃO ALTERADOS arquivos contidos em "app", "logs", "public_html" e "vendor", nem os arquivos ".gitattributes", ".gitignore", "composer.json", "LICENSE" e "README.md".',
            '- Quaisquer outros arquivos podem sofrer alterações.',
            'Informe agora o número do backup que deseja restaurar, ou digite 0 (zero) para cancelar.',
        ],
    ];

    const UPDATE_WINDOW_HEADER = [
        'en' => [
            'There are ',
            ' update packages found!',
            'There is 1 update package found!',
        ],

        'br' => [
            'Foram encontrados ',
            ' pacotes de atualização!',
            'Foi encontrado 1 pacote de atualização!',
        ],
    ];

    const UPDATE_WINDOW_VERSIONS = [
        'en' => [
            '- Your version: ',
            '- Most recent version: ',
        ],

        'br' => [
            '- Sua versão: ',
            '- Versão mais recente: ',
        ],
    ];

    const BACKUP_WINDOW_HEADER = [
        'en' => [
            'There are ',
            ' backups found!',
            'There is 1 backup found!',
        ],

        'br' => [
            'Foram encontrados ',
           ' backups!',
            'Foi encontrado 1 backup!',
        ],
    ];
}
