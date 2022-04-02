<?php

// namespace galastri\cli\classes;

// use galastri\cli\interfaces\UpdaterLanguage;
// use galastri\cli\interfaces\TextDesignerConstants;
// use galastri\cli\traits\Common;
// use galastri\cli\traits\FileManager;
// use galastri\cli\traits\Language;
// use galastri\cli\traits\TextDesigner;

// final class Updater implements TextDesignerConstants, UpdaterLanguage
// {
//     use Common;
//     use FileManager;
//     use Language;
//     use TextDesigner;

//     const VERSION_FILE        = GALASTRI_PROJECT_DIR.'/galastri/VERSION';
    
//     const LOCAL_VERSION_LIST  = GALASTRI_PROJECT_DIR.'/tmp/versionList.php';
//     const REMOTE_VERSION_LIST = 'https://raw.githubusercontent.com/andregalastri/galastri-framework-updates/main/versionList.php';
    
//     const TMP_PATH            = GALASTRI_PROJECT_DIR.'/tmp';
//     const TMP_PACKAGE_PATH    = GALASTRI_PROJECT_DIR.'/tmp/packages';
//     const TMP_BACKUP_PATH     = GALASTRI_PROJECT_DIR.'/tmp/backup';
//     const TMP_WORKING_FOLDER  = GALASTRI_PROJECT_DIR.'/tmp/working';

//     const REMOTE_PACKAGE_URL  = 'https://github.com/andregalastri/galastri-framework-updates/raw/main/updates';

//     private static bool $restoreBackupStatus;
    
//     /**
//      * This is a singleton class, the __construct() method is private to avoid users to instanciate
//      * it.
//      *
//      * @return void
//      */
//     private function __construct()
//     {
//     }

//     /**
//      * prepare
//      *
//      * @return void
//      */
//     private static function prepare()
//     {
//         self::isWritable(GALASTRI_PROJECT_DIR);
//         self::isWritable(GALASTRI_PROJECT_DIR.'/galastri');

//         if (!file_exists(self::VERSION_FILE)) {
//             self::drawMessageBox(self::message('NO_VERSION_FILE', 0), self::message('NO_VERSION_FILE', 1));
//             self::pressEnterToContinue();
//             exit;
//         }

//         if (!is_dir(self::TMP_PATH)) {
//             mkdir(self::TMP_PATH);
//         }

//         self::isWritable(self::TMP_PATH);

//         if (!is_dir(self::TMP_BACKUP_PATH)) {
//             mkdir(self::TMP_BACKUP_PATH);
//         }

//         self::isWritable(self::TMP_BACKUP_PATH);

//         if (!is_dir(self::TMP_PACKAGE_PATH)) {
//             mkdir(self::TMP_PACKAGE_PATH);
//         }

//         self::isWritable(self::TMP_PACKAGE_PATH);

//         self::$restoreBackupStatus = false;
//     }
    
//     /**
//      * execute
//      *
//      * @return void
//      */
//     public static function execute(): void
//     {
//         self::prepare();
        
//         do {
//             self::drawMainWindow();
//             $option = readline(self::message('CHOOSE_AN_OPTION', 0));
//             echo "\n\n\n";

//             switch($option) {
//                 case 0:
//                     self::drawMessageBox(self::message('EXIT', 0), self::message('EXIT', 1));
//                     self::wait(1, false);
//                     exit();

//                 case 1:
//                     self::checkUpdates();
//                     break;

//                 case 2:
//                     self::checkBackups();
//                     break;

//                 default:
//                     self::drawMessageBox(self::message('MAIN_INVALID_OPTION', 0).$option.self::message('MAIN_INVALID_OPTION', 1));
//                     self::pressEnterToContinue();
//             }
//         } while($option != 0);
//     }

    
//     /**
//      * checkBackups
//      *
//      * @return void
//      */
//     private static function checkBackups(): void
//     {
//         $backupList = [];

//         foreach(array_reverse(glob(self::TMP_BACKUP_PATH.'/*', GLOB_ONLYDIR)) as $key => $backupPath) {
//             $backupName = str_replace(self::TMP_BACKUP_PATH.'/', '', $backupPath);
//             $backupData = explode('_', $backupName);
//             $date = (\Datetime::createFromFormat('YmdHis', $backupData[0]))->format('Y-m-d, H:i:s');
//             $backupLabel = ($key+1).'. '.self::stringpad($date, 15, ' ').' | v'.$backupData[1];

//             $backupList[] = [
//                 'path' => $backupPath,
//                 'label' => $backupLabel,
//             ];
//         }

//         if (count($backupList) <= 0) {
//             self::drawMessageBox(self::message('NO_BACKUP_FOUND', 0), ':(');
//         } else {
//             do {
//                 self::drawBackupWindow($backupList);

//                 echo self::message('CHOOSE_BACKUP_RESTORATION', 0);
//                 $key = readline(self::message('CHOOSE_BACKUP_RESTORATION', 1));

//                 if ($key == 0) {
//                     self::drawMessageBox(self::message('CANCEL_BACKUP_RESTORATION', 0), self::message('CANCEL_BACKUP_RESTORATION', 1));
//                 } else {
//                     if (array_key_exists($key-1, $backupList)) {
//                         self::drawMessageBox(self::message('CONFIRM_BACKUP_RESTORAION', 0), '', $backupList[$key-1]['label']);
//                         $confirm = readline(self::message('CONFIRM_BACKUP_RESTORAION', 1));

//                         switch($confirm){
//                             case 'y':
//                             case 'Y':
//                             case 's':
//                             case 'S':
//                                 self::executeRestore($backupList, $key-1);
//                                 break;
                            
//                             default:
//                                 self::drawMessageBox(self::message('CANCEL_BACKUP_RESTORATION', 0), self::message('CANCEL_BACKUP_RESTORATION', 1));
//                         }
//                         self::$restoreBackupStatus = true;
//                     } else {
//                         self::drawMessageBox(self::message('INVALID_BACKUP_OPTION', 0));
//                         self::pressEnterToContinue();
//                     }
//                 }
//             } while($key != 0 and self::$restoreBackupStatus != true);
//         }
//         self::pressEnterToContinue();
//     }
    
//     /**
//      * checkUpdates
//      *
//      * @return void
//      */
//     private static function checkUpdates(): void
//     {
//         $currentVersion = self::getVersion('galastri');
        
//         self::drawMessageBox(self::message('CHECKING_UPDATES', 0));
        
//         file_put_contents(self::LOCAL_VERSION_LIST, file_get_contents(self::REMOTE_VERSION_LIST));

//         $versionList = require(self::LOCAL_VERSION_LIST);
//         $versionPosition = array_search($currentVersion, $versionList);

//         if ($versionPosition === false) {
//             self::drawMessageBox(
//                 self::message('INVALID_VERSION', 0).$currentVersion.self::message('INVALID_VERSION', 1),
//                 ':(',
//                 '',
//                 self::message('INVALID_VERSION', 2)
//             );
//         } else {
//             if ($versionPosition == array_key_last($versionList)) {
//                 self::drawMessageBox(
//                     self::message('UP_TO_DATE_VERSION', 0),
//                     '',
//                     self::message('UP_TO_DATE_VERSION', 1).$currentVersion.self::message('UP_TO_DATE_VERSION', 2),
//                     ':D'
//                 );
//             } else {
//                 self::wait(2, false);
//                 $packageQty = 0;

//                 foreach ($versionList as $i => $version) {
//                     if ($i <= $versionPosition) continue;
//                     $packageQty++;
//                     $lastVersion = $version;
//                 }

//                 self::drawUpdateWindow($currentVersion, $packageQty, $lastVersion);
//                 $option = readline(self::message('CONFIRM_UPDATE', 0));

//                 switch($option){
//                     case 'y':
//                     case 'Y':
//                     case 's':
//                     case 'S':
//                         self::executeUpdate($versionList, $versionPosition, $packageQty);
//                         break;
                    
//                     default:
//                         self::drawMessageBox(self::message('CANCEL_UPDATE', 0), self::message('CANCEL_UPDATE', 1));
//                 }
//             }
//         }
//         self::pressEnterToContinue();
//     }
    
//     /**
//      * executeRestore
//      *
//      * @param  mixed $backupPath
//      * @return void
//      */
//     private static function executeRestore(array $backupList, int $restorePoint)
//     {
//         self::drawMessageBox(self::message('RESTORING_BACKUP', 0));
//         self::wait(5, true, false);
//         echo "\n";

//         self::draw('doubled', 'top');

//         foreach($backupList as $backupPoint => $backupData) {
//             if ($backupPoint <= $restorePoint) {
//                 $backupFolder = $backupData['path'];

//                 self::text('doubled', self::message('RESTORING_BACKUP_PROCESS', 0).$backupData['label']);

//                 $currentVersion = self::getVersion('galastri');

//                 $workingFiles = require($backupFolder.'/changes/deleted-files.php');
//                 foreach($workingFiles as $file) {
//                     self::copyFile($backupFolder.'/'.$file, GALASTRI_PROJECT_DIR.'/'.$file);
//                 }

//                 $workingFiles = require($backupFolder.'/changes/new-files.php');
//                 foreach($workingFiles as $file) {
//                     unlink(GALASTRI_PROJECT_DIR.'/'.$file);
//                 }

//                 $workingFiles = require($backupFolder.'/changes/modified-files.php');
//                 foreach($workingFiles as $file) {
//                     self::copyFile($backupFolder.'/'.$file, GALASTRI_PROJECT_DIR.'/'.$file);
//                 }

//                 self::deleteAll($backupFolder);
//             }
//         }
//         self::draw('doubled', 'bottom');
//         self::wait(2, false, false);
//         echo "\n";


//         self::drawMessageBox(self::message('BACKUP_RESTORED', 0));
//     }
    
//     /**
//      * executeUpdate
//      *
//      * @param  mixed $versionList
//      * @param  mixed $versionPosition
//      * @param  mixed $packageQty
//      * @return void
//      */
//     private static function executeUpdate(array $versionList, int $versionPosition, int $packageQty): void
//     {
//         self::drawMessageBox(self::message('STARTING_UPDATE', 0));
//         self::wait(5, true, false);
//         echo "\n";

//         $packageNumber = 1;
//         $lastVersion = '';

//         self::draw('doubled', 'top');
//         foreach ($versionList as $i => $version) {
//             $currentVersion = self::getVersion('galastri');

//             if ($i <= $versionPosition) continue;

//             if ($packageNumber > 1) {
//                 self::draw('doubled', 'line');
//             }

//             if (!file_exists(self::TMP_WORKING_FOLDER)) {
//                 mkdir(self::TMP_WORKING_FOLDER);
//             }

//             $packageTarFile = $version.'.tar';
//             $packageTarPath = self::TMP_WORKING_FOLDER.'/'.$packageTarFile;

//             self::text('doubled', self::message('UPDATE_PROCESS', 0).$version.'" ('.$packageNumber.'/'.$packageQty.')');
//             file_put_contents($packageTarPath, file_get_contents(self::REMOTE_PACKAGE_URL.'/'.$packageTarFile));

//             self::text('doubled', self::message('UPDATE_PROCESS', 1));
//             (new \PharData($packageTarPath))->extractTo(self::TMP_WORKING_FOLDER);
//             unlink($packageTarPath);

//             $packageFolder = self::TMP_PACKAGE_PATH.'/'.$version;

//             if (file_exists($packageFolder)) {
//                 self::deleteAll($packageFolder);
//             }
//             self::copyDirectory(self::TMP_WORKING_FOLDER.'/'.$version, self::TMP_PACKAGE_PATH.'/'.$version);
//             self::deleteAll(self::TMP_WORKING_FOLDER);

//             $backupFolder = self::TMP_BACKUP_PATH.'/'.date('YmdHis_').$currentVersion;

//             if (!file_exists($backupFolder)) {
//                 mkdir($backupFolder);
//             }

//             // self::copyFile($packageFolder.'/deleted-files.php', $backupFolder.'/changes/deleted-files.php');
//             // self::copyFile($packageFolder.'/new-files.php', $backupFolder.'/changes/new-files.php');
//             self::copyFile($packageFolder.'/changes.php', $backupFolder.'/changes.php');

//             self::text('doubled', self::message('UPDATE_PROCESS', 2));

//             $workingFiles = require($packageFolder.'/changes.php');
//             foreach($workingFiles['deleted'] as $file) {
//                 if (file_exists(GALASTRI_PROJECT_DIR.'/'.$file)) {
//                     self::copyFile(GALASTRI_PROJECT_DIR.'/'.$file, $backupFolder.'/'.$file);
//                 }
//             }

//             foreach($workingFiles['new'] as $file) {
//                 self::copyFile($packageFolder.'/'.$file, GALASTRI_PROJECT_DIR.'/'.$file);
//             }

//             foreach($workingFiles['modified'] as $file) {
//                 self::copyFile(GALASTRI_PROJECT_DIR.'/'.$file, $backupFolder.'/'.$file);
//                 self::copyFile($packageFolder.'/'.$file, GALASTRI_PROJECT_DIR.'/'.$file);
//             }

//             foreach($workingFiles['deleted'] as $file) {
//                 unlink(GALASTRI_PROJECT_DIR.'/'.$file);
//             }


//             $newUpdater = $packageFolder.'/updater';

//             if (file_exists($newUpdater)) {
//                 self::draw('doubled', 'bottom');
                
//                 self::drawMessageBox(self::message('UPDATE_DONE', 1).$version);

//                 self::drawMessageBox(
//                     self::message('UPDATER_UPDATED', 0),
//                     self::message('UPDATER_UPDATED', 1),
//                     '',
//                     self::message('UPDATER_UPDATED', 2)
//                 );

//                 self::pressEnterToContinue();
//                 exit;
//             } else {
//                 $packageNumber++;
//                 $lastVersion = $version;
//             }
//             self::wait(2, false, false);
//         }
//         self::draw('doubled', 'bottom');
//         self::drawMessageBox(self::message('UPDATE_DONE', 0), self::message('UPDATE_DONE', 1).$lastVersion);
//     }
    
//     /**
//      * drawMainWindow
//      *
//      * @return void
//      */
//     private static function drawMainWindow(): void
//     {
//         self::draw('doubled', 'top');
//         self::text('doubled', 'GALASTRI FRAMEWORK UPDATER', 'center');
//         self::text('doubled', 'v'.self::getVersion('updater'), 'center');
//         self::draw('doubled', 'bottom');
//         self::text('thin', self::message('WELCOME_WINDOW', 0), 'center');
//         self::draw('thin', 'line');
//         self::text('thin', self::message('WELCOME_WINDOW', 1));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('WELCOME_WINDOW', 2));
//         self::text('thin', self::message('WELCOME_WINDOW', 3));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('WELCOME_WINDOW', 4));
//         self::draw('thin', 'bottom');
//     }

//     /**
//      * drawUpdateWindow
//      *
//      * @param  mixed $currentVersion
//      * @param  mixed $packageQty
//      * @param  mixed $lastVersion
//      * @return void
//      */
//     private static function drawUpdateWindow(string $currentVersion, int $packageQty, string $lastVersion): void
//     {
//         $headerText = $packageQty > 1 ? self::message('UPDATE_WINDOW_HEADER', 0).$packageQty.self::message('UPDATE_WINDOW_HEADER', 1) : self::message('UPDATE_WINDOW_HEADER', 2);

//         self::draw('doubled', 'top');
//         self::text('doubled', $headerText, 'center');
//         self::draw('doubled', 'bottom');
//         self::text('thin', self::message('UPDATE_WINDOW_VERSIONS', 0).$currentVersion);
//         self::text('thin', self::message('UPDATE_WINDOW_VERSIONS', 1).$lastVersion);
//         self::draw('thin', 'line');
//         self::text('thin', self::message('UPDATE_WINDOW_ABOUT', 0));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('UPDATE_WINDOW_ABOUT', 1));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('UPDATE_WINDOW_ABOUT', 2));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('UPDATE_WINDOW_ABOUT', 3));
//         self::draw('thin', 'line');
//         self::text('thin', self::message('UPDATE_WINDOW_ABOUT', 4), 'center');
//         self::text('thin', self::message('UPDATE_WINDOW_ABOUT', 5), 'center');
//         self::draw('thin', 'bottom');
//     }
    
//     /**
//      * drawBackupWindow
//      *
//      * @param  mixed $backupList
//      * @return void
//      */
//     private static function drawBackupWindow(array $backupList): void
//     {
//         $backupQty = count($backupList);
//         $headerText = $backupQty > 1 ? self::message('BACKUP_WINDOW_HEADER', 0).$backupQty.self::message('BACKUP_WINDOW_HEADER', 1) : self::message('BACKUP_WINDOW_HEADER', 2);

//         self::draw('doubled', 'top');
//         self::text('doubled', $headerText, 'center');
//         self::draw('doubled', 'bottom');

//         foreach($backupList as $backupData) {
//             self::text('thin', $backupData['label']);
//         }
        
//         self::draw('thin', 'line');
//         self::text('thin', self::message('BACKUP_WINDOW_ABOUT', 0));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('BACKUP_WINDOW_ABOUT', 1));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('BACKUP_WINDOW_ABOUT', 2));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('BACKUP_WINDOW_ABOUT', 3));
//         self::draw('thin', 'empty');
//         self::text('thin', self::message('BACKUP_WINDOW_ABOUT', 4));
//         self::draw('thin', 'line');
//         self::text('thin', self::message('BACKUP_WINDOW_ABOUT', 5), 'center');
//         self::draw('thin', 'bottom');
//     }
// }
