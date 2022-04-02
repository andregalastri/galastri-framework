<?php

namespace galastri\cli\classes;

use galastri\cli\interfaces\constants\TextDesigner as TextDesignerConstants;
use galastri\cli\interfaces\language\Common as CommonLanguage;
use galastri\cli\interfaces\language\PackageBuilder as PackageBuilderLanguage;
use galastri\cli\traits\Common;
use galastri\cli\traits\FileManager;
use galastri\cli\traits\Language;
use galastri\cli\traits\TextDesigner;

final class PackageBuilder implements CommonLanguage, PackageBuilderLanguage, TextDesignerConstants
{
    use Common;
    use FileManager;
    use Language;
    use TextDesigner;

    const VERSION_FILE             = GALASTRI_PROJECT_DIR.'/galastri/VERSION';

    const RELEASE_PACK_PATH        = GALASTRI_PROJECT_DIR.'/releases';
    const RELEASE_PACK_DEV_PATH    = GALASTRI_PROJECT_DIR.'/releases/dev';

    const UPDATE_PACK_IGNORE = [
        '^.git',
        '^package-builder',
        'PackageBuilder.php',
        '^build-package',
        '^composer',
        '^LICENSE',
        '^nicolette',
        '^README.md',
        '^app\/',
        '^logs\/',
        '^public_html\/',
        '^public\/',
        '^releases\/',
        '^tmp\/',
        '^vendor\/',
    ];
    
    const RELEASE_PACK_IGNORE = [
        '^.git',
        '^package-builder',
        'PackageBuilder.php',
        '^build-package',
        '^nicolette',
        '^tmp\/',
        '^releases\/',
    ];

    private static array $storedChanges = [];
    private static bool $isDevPack = false;
    private static bool $breakingChange = false;
    private static ?string $copyFilesTo = null;

    /**
     * This is a singleton class, the __construct() method is private to avoid users to instanciate
     * it.
     *
     * @return void
     */
    private function __construct()
    {
    }





    /**
     * prepare
     *
     * @return void
     */
    private static function prepare()
    {
        if (!file_exists(self::VERSION_FILE)) {
            self::drawMessageBox(self::message('NO_VERSION_FILE', 0), self::message('NO_VERSION_FILE', 1));
            self::pressEnterToContinue();
            exit;
        }

        self::prepareFolders();

        self::$breakingChange = false;
    }

    /**
     * prepare
     *
     * @return void
     */
    private static function prepareFolders()
    {
        self::createDirectory(self::RELEASE_PACK_PATH);
        self::createDirectory(self::RELEASE_PACK_DEV_PATH);
    }




    
    /**
     * execute
     *
     * @return void
     */
    public static function mainWindow(): void
    {
        self::prepare();

        switch($option = self::chooseAnOption('drawMainWindow')) {
            case 0:
                self::exit();

            case 1:
                break;

            case 2:
                self::updatePackageWindow();
                break;

            default:
                self::invalidOption($option);
        }
        
        self::mainWindow();
    }

    /**
     * windowPackAnUpdate
     *
     * @return void
     */
    public static function updatePackageWindow(): void
    {
        switch($option = self::chooseAnOption('drawUpdatePackageWindow')) {
            case 0:
                return;
            case 1:
                self::buildUpdatePackage();
                return;

            case 2:
                self::updatePackPreviousHeadsWindow();
                break;

            default:
                self::invalidOption($option);
        }

        self::updatePackageWindow();
    }

    /**
     * windowInformHeads
     *
     * @return void
     */
    public static function updatePackPreviousHeadsWindow(): void
    {
        if (($value = (int)self::chooseValue('drawChooseHeadNumberWindow')) > 0) {
            self::buildUpdatePackage($value);
        }
    }





    /**
     * buildUpdatePackage
     *
     * @param  mixed $heads
     * @return void
     */
    public static function buildUpdatePackage($value = 0): void
    {
        self::$breakingChange = self::chooseNoOrYes('drawChooseBreakingChangeWindow');
        
        self::setChanges($value);
        self::checkIfDevPack();
        self::packTheUpdateFiles();
    }







    /**
     * setChanges
     *
     * @param  mixed $head
     * @return void
     */
    private static function setChanges($value = 0)
    {
        $storedChanges = [
            'commit' => '',
            'version' => self::getVersion(),
            'breakingChange' => self::$breakingChange,
            'files' => [
                'new' => [],
                'modified' => [],
                'deleted' => [],
            ],
        ];

        for ($heads = $value; $heads >= 0; $heads--) {
            self::executionMessage(self::message('GETTING_THE_COMMIT_DATA', 0) .$heads);

            exec('git show --name-status HEAD~'.$heads, $gitChanges);
            
            foreach ($gitChanges as $data) {
                preg_match('/commit\s.*?([\w].+)/', $data, $matches);
                if (!empty($matches[0])) {
                    $storedChanges['commit'] = $matches[1];
                }

                preg_match('/[R]\d.*?\s.*?([.\w].+?)\s+?([.\w].+)/', $data, $matches);
                if (!empty($matches[0])) {
                    $storedChanges['files']['deleted'][] = $matches[1];
                    $storedChanges['files']['new'][] = $matches[2];
                }

                preg_match('/[UA]\s.*?([.\w].+)/', $data, $matches);
                if (!empty($matches[0])) {
                    $storedChanges['files']['new'][] = $matches[1];
                }

                preg_match('/[M]\s.*?([.\w].+)/', $data, $matches);
                if (!empty($matches[0])) {
                    $storedChanges['files']['modified'][] = $matches[1];
                }

                preg_match('/[D]\s.*?([.\w].+)/', $data, $matches);
                if (!empty($matches[0])) {
                    $storedChanges['files']['deleted'][] = $matches[1];
                }
            }

            $storedChanges = self::filterDuplicated($storedChanges);
            
        }
        self::$storedChanges = $storedChanges;
    }

    /**
     * filterDuplicated
     *
     * @param  mixed $storedChanges
     * @return array
     */
    private static function filterDuplicated(array $storedChanges): array
    {
        $storedChanges['files']['new'] = array_unique($storedChanges['files']['new']);
        $storedChanges['files']['deleted'] = array_unique($storedChanges['files']['deleted']);
        $storedChanges['files']['modified'] = array_unique($storedChanges['files']['modified']);

        foreach ($storedChanges['files']['deleted'] as $deletedKey => $file) {
            $modifiedKey = array_search($file, $storedChanges['files']['modified']);
            if($modifiedKey !== false) {
                unset($storedChanges['files']['modified'][$modifiedKey]);
                unset($storedChanges['files']['deleted'][$deletedKey]);
            }

        }

        foreach ($storedChanges['files']['deleted'] as $deletedKey => $file) {
            $newKey = array_search($file, $storedChanges['files']['new']);
            if($newKey !== false) {
                unset($storedChanges['files']['new'][$newKey]);
                unset($storedChanges['files']['deleted'][$deletedKey]);
            }
        }
        
        foreach ($storedChanges['files']['modified'] as $modifiedKey => $file) {
            $newKey = array_search($file, $storedChanges['files']['new']);
            if($newKey !== false) {
                unset($storedChanges['files']['modified'][$modifiedKey]);
            }
        }

        return $storedChanges;
    }

    /**
     * checkIfDevPack
     *
     * @return void
     */
    private static function checkIfDevPack()
    {
        self::prepareFolders();

        preg_match('/^(\d\.\d\.\d\.?\d?)-(dev)/', self::getVersion('galastri'), $match);
        if (($match[2] ?? null) === 'dev') {

            $devPackPath = self::RELEASE_PACK_DEV_PATH.'/'.$match[1];

            self::createDirectory($devPackPath);
            self::createDirectory($devPackPath.'/files');

            self::$copyFilesTo = $devPackPath;
            self::$isDevPack = true;
        } else {
            preg_match('/^(\d\.\d\.\d\.?\d?)/', self::getVersion('galastri'), $match);
            
            $devPackPath = self::RELEASE_PACK_DEV_PATH.'/'.$match[1];

            if (is_dir($devPackPath)) {
                self::$copyFilesTo = $devPackPath;
            } else {
                $packPath = self::RELEASE_PACK_PATH.'/'.self::getVersion('galastri');

                self::createDirectory($packPath);

                self::$copyFilesTo = $packPath;
            }
            self::$isDevPack = false;
        }
    }

    /**
     * packTheUpdateFiles
     *
     * @return void
     */
    private static function packTheUpdateFiles()
    {
        self::prepareFolders();

        self::executionMessage(self::message('COPYING_UPDATE_FILES', 0));

        $changesFile = self::$copyFilesTo.'/changes.php';
        
        self::createFile($changesFile, "<?php\nreturn [];");
    
        $currentChanges = require($changesFile);

        self::arrayOverwrite($currentChanges, self::$storedChanges);

        $currentChanges = self::filterDuplicated($currentChanges);

        foreach ($currentChanges['files'] as $changeType => $changes) {
            foreach ($changes as $key => $file) {
                foreach (self::UPDATE_PACK_IGNORE as $ignorePattern) {
                    preg_match('/'.$ignorePattern.'/', $file, $matches);
                    if (!empty($matches)) {
                        unset($currentChanges['files'][$changeType][$key]);
                    }
                }
            }
        }

        foreach (['new', 'modified'] as $type) {
            foreach ($currentChanges['files'][$type] as $key => $file) {
                if (!file_exists($file)) {
                    unset($currentChanges['files'][$type][$key]);
                    continue;
                }
                self::copyFile($file, self::$copyFilesTo.'/files/'.$file);
            }
        }

        self::writeFile($changesFile, "<?php\nreturn ".var_export($currentChanges, true).';');

        if (self::$isDevPack) {
            self::done('drawDevPackageDoneWindow');
        } else {
            $phar = new \PharData(self::RELEASE_PACK_PATH.'/'.self::getVersion('galastri').'.tar');
            $phar->buildFromDirectory(self::$copyFilesTo);

            self::done('drawUpdatePackageDoneWindow');
        }
    }



    /**
     * drawMainWindow
     *
     * @return void
     */
    private static function drawMainWindow(): void
    {
        echo "\n\n\n";
        self::draw('doubled', 'top');
        self::text('doubled', 'GALASTRI FRAMEWORK - PACKAGE BUILDER', 'center');
        self::text('doubled', 'v'.self::getVersion('package-builder'), 'center');
        self::draw('doubled', 'bottom');
        self::text('thin', self::message('WELCOME_WINDOW', 0), 'center');
        self::draw('thin', 'line');
        self::text('thin', self::message('WELCOME_WINDOW', 1));
        self::draw('thin', 'line');
        self::text('thin', self::message('WELCOME_WINDOW', 2));
        self::draw('thin', 'empty');
        self::text('thin', self::message('WELCOME_WINDOW', 3));
        self::text('thin', self::message('WELCOME_WINDOW', 4));
        self::draw('thin', 'empty');
        self::text('thin', self::message('WELCOME_WINDOW', 5));
        self::draw('thin', 'bottom');
    }

    /**
     * drawPackAnUpdateWindow
     *
     * @return void
     */
    private static function drawUpdatePackageWindow(): void
    {
        echo "\n\n\n";
        self::draw('doubled', 'top');
        self::text('doubled', self::message('PACK_AN_UPDATE_WINDOW', 0), 'center');
        self::draw('doubled', 'bottom');
        self::draw('thin', 'line');
        self::text('thin', self::message('PACK_AN_UPDATE_WINDOW', 1));
        self::draw('thin', 'line');
        self::text('thin', self::message('PACK_AN_UPDATE_WINDOW', 2));
        self::draw('thin', 'empty');
        self::text('thin', self::message('PACK_AN_UPDATE_WINDOW', 3));
        self::text('thin', self::message('PACK_AN_UPDATE_WINDOW', 4));
        self::draw('thin', 'empty');
        self::text('thin', self::message('PACK_AN_UPDATE_WINDOW', 5));
        self::draw('thin', 'bottom');
    }

    /**
     * drawPackAnUpdateWindow
     *
     * @return void
     */
    private static function drawChooseHeadNumberWindow(): void
    {
        echo "\n\n\n";
        self::drawMessageBox(self::message('PACK_AN_UPDATE_SELECT_HEAD_WINDOW', 0), self::message('PACK_AN_UPDATE_SELECT_HEAD_WINDOW', 1));
    }

    /**
     * drawPackAnUpdateWindow
     *
     * @return void
     */
    private static function drawChooseBreakingChangeWindow(): void
    {
        echo "\n\n\n";
        self::drawMessageBox(self::message('IS_IT_BREAKING_CHANGE', 0));
    }

    /**
     * drawPackAnUpdateWindow
     *
     * @return void
     */
    private static function drawDevPackageDoneWindow(): void
    {
        echo "\n\n\n";
        self::drawMessageBox(
            self::message('VERSION_IS_IN_DEVELOPMENT_STATUS', 0),
            '', 
            self::message('VERSION_IS_IN_DEVELOPMENT_STATUS', 1),
            self::message('VERSION_IS_IN_DEVELOPMENT_STATUS', 2)
        );
    }

    /**
     * drawPackAnUpdateWindow
     *
     * @return void
     */
    private static function drawUpdatePackageDoneWindow(): void
    {
        echo "\n\n\n";
        self::drawMessageBox(
            self::message('PACKAGE_CREATED_AT', 0),
            '',
            self::RELEASE_PACK_PATH.'/'.self::getVersion('galastri').'.tar'
        );
    }
    

    private static function dummy($option)
    {
    }
}