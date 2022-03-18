#!/bin/bash

###
 # Stores the path of the current directory (which is meant to be the framework path).
 # 
 # @var string
 ##
tmpFolder='tmp/build-package';

###
 # Stores the path where the files of the update will be placed.
 # 
 # @var string
 ##
filesFolder="$tmpFolder/files";

###
 # Stores the path of the file that has the list of files.
 # 
 # @var string
 ##
fileList="$tmpFolder/file-list.txt";

###
 # Stores the path of the file that has the list of files.
 # 
 # @var string
 ##
filteredFileList="$tmpFolder/filtered-file-list.txt";

###
 # Stores the path of the "updates" repository, based on the current directory (which is meant to be
 # the framework path).
 # 
 # @var string
 ##
localUpdatesRepository="$PWD/../updates";

###
 # Stores current version of the framework.
 # 
 # @var string
 ##
currentVersion=$(cat 'galastri/VERSION');

###
 # List of files and directories that need to be ignored from the update packages.
 # 
 # @var array
 ##
ignoredUpdatePaths=(
    '^.git'
    '^build-package.sh'
    '^composer.json'
    '^LICENSE'
    '^README.md'
    '^app\/'
    '^logs\/'
    '^public_html\/'
    '^tmp\/'
    '^vendor\/'
);

###
 # List of files and directories that need to be ignored from the release packages.
 # 
 # @var array
 ##
ignoredReleasePaths=(
    '^.\/.git'
    '^.\/build-package.sh'
    '^.\/updater-bk'
    '^.\/tmp\/'
    '^.\/releases\/'
);

###
 # Creates the folders and prepare the files that will be used in the process of building the
 # package.
 # 
 # @return void
 ##
prepare()
{
    # Create a temporary folders, where the files will be stored to the package be built.
    mkdir -p $filesFolder;
}

###
 # Gets the paths of files that were changed in the last commit and place it in a file. Then it
 # filters the results to remove the files or directories that need to be ignored.
 #
 # @param  string $1                                Type of instructions that will be executed.
 #                             
 # @param  ?string $2                               (Optional) Specifies the commit hash or the head
 #                                                  of the commit.
 #
 # @return void
 ##
getGitChanges()
{
    # Creates a new empty file that will store the list of changed files.
    truncate -s 0 $fileList;

    # Executes a 'git show' based on the arguments passed to the function.
    if [ "$1" == 'copy-files' ];
    then
        git show --name-status $2 | grep -oP '(?<=M\s).*' >> $fileList
        git show --name-status $2 | grep -oP '(?<=U\s).*' >> $fileList
        git show --name-status $2 | grep -oP '(?<=A\s).*' >> $fileList
        git show --name-status $2 | grep -oP '(?<=R[0-9]..\s).*' | grep -oP '[^\s]*$' >> $fileList
    fi

    if [ "$1" == 'new-files' ];
    then
        git show --name-status $2 | grep -oP '(?<=U\s).*' >> $fileList
        git show --name-status $2 | grep -oP '(?<=A\s).*' >> $fileList
    fi

    if [ "$1" == 'modified-files' ];
    then
        git show --name-status $2 | grep -oP '(?<=M\s).*' >> $fileList
        git show --name-status $2 | grep -oP '(?<=R[0-9]..\s).*' | grep -oP '[^\s]*$' >> $fileList
    fi

    if [ "$1" == 'deleted-files' ];
    then
        git show --name-status $2 | grep -oP '(?<=D\s).*' >> $fileList
        git show --name-status $2 | grep -oP '(?<=R[0-9]..\s).*' | grep -oP '^[^\\s]+' >> $fileList
    fi

    # Calls the filterGitChanges to remove the ignored files and directories.
    filterGitChanges;
}

###
 # Removes the ignored files and directories from the changes of the commit.
 #
 # @return void
 ##
filterGitChanges()
{
    # Creates a empty file that will store the filtered list.
    truncate -s 0 $filteredFileList;

    # Gets the ignored list and creates a string with grep arguments that makes it filter the
    # unfiltered list.
    for ignoredPath in ${ignoredUpdatePaths[@]};
    do
        grepIgnoredListCommand+=" -e '$ignoredPath'";
    done

    # Executes the grep using the command with the filters and place the results in a filtered list
    # file.
    eval "grep -v $grepIgnoredListCommand $fileList > $filteredFileList";
}

###
 # Copies the files of the commit, based on the filtered list.
 #
 # @return void
 ##
copyUpdateFiles()
{
    # Creates a folder of the current version to place the files there.
    mkdir -p "$filesFolder/$currentVersion";

    # Gets the filtered list
    files=$(cat $filteredFileList);

    # Copies each file of the filtered list to the folder of the current version.
    for file in $files;
    do
        install -D $file "$filesFolder/$currentVersion/$file";
    done
}

###
 # Copies the files and directories to create a release.
 #
 # @return void
 ##
copyReleaseFiles()
{
    find -type f > $fileList;
    
    for ignoredPath in ${ignoredReleasePaths[@]};
    do
        grepIgnoredListCommand+=" -e '$ignoredPath'";
    done

    eval "grep -v $grepIgnoredListCommand $fileList > $filteredFileList";

    files=$(cat $filteredFileList);

    for file in $files;
    do
        install -D $file "$filesFolder/$currentVersion/$file";
    done
}

###
 # Creates the PHP file that lists the changes. This file is used by the updater to apply the update
 # and to restore the backups.
 #
 # @param  string $1                                The name of the file. It can be 'new-files',
 #                                                  'deleted-files' or 'modified.files.'
 #
 # @return void
 ##
createPhpFileList()
{
    # Stores the path to the PHP file that will store the list of changes.
    changeList="$filesFolder/$currentVersion/$1.php";
    
    # Gets the filtered list
    files=$(cat $filteredFileList);

    # Place the PHP code inside the file.
    echo "<?php" > $changeList;
    echo "return [" >> $changeList;
    for file in $files;
    do
        echo -e "\t'$file'," >> $changeList;
    done
    echo "];" >> $changeList
}

###
 # A series of functions to create the PHP file that lists the changes.
 #
 # @param  string $1                                The name of the file. It can be 'new-files',
 #                                                  'deleted-files' or 'modified.files.'
 #
 # @return void
 ##
createUpdateList()
{
    getGitChanges $1;
    createPhpFileList $1;
}

###
 # Creates a change log template and calls the 'nano' editor to describe the changes of the update.
 #
 # @return void
 ##
createChangelogFile()
{
    # Stores the path to the TXT file that will store the changelog template.
    changelogFile="$filesFolder/$currentVersion/Changelog.txt";

    # Place the template inside the file.
    echo "CHANGELOG                                 $(date '+%Y-%m-%d')" > $changelogFile
    echo "" >> $changelogFile
    echo "Galastri Framework $currentVersion" >> $changelogFile
    echo "----------------------------------------------------" >> $changelogFile
    echo "" >> $changelogFile
    echo " - Title" >> $changelogFile
    echo "" >> $changelogFile
    echo "Description" >> $changelogFile
    echo "" >> $changelogFile
    echo "###" >> $changelogFile

    # Calls the 'nano' editor to describe the update.
    nano $changelogFile
}

###
 # Packs the files in a .tar file without compression.
 #
 # @param  ?string $1                               (Optional) Adds a string to the file name. Helps
 #                                                  when testing.
 #
 # @return string
 ##
packageFiles()
{
    # Packs the folder in .tar extension, withou compression.
    tar -C $filesFolder -cf "$tmpFolder/$currentVersion$1.tar" "$currentVersion";

    # Returns the file name.
    echo "$currentVersion$1.tar";
}

###
 # Moves the package to the 'updates' repository.
 #
 # @param  string $1                                The package file name.
 #
 # @return void
 ##
movePackageToUpdateRepository()
{
    mv "$tmpFolder/$1" "$localUpdatesRepository/updates/$1"
}

###
 # Moves the package to the 'releases' repository.
 #
 # @param  string $1                                The package file name.
 #
 # @return void
 ##
movePackageToReleaseRepository()
{
    install -D "$tmpFolder/$1" "./releases/$1"
}

###
 # Main function that executes the script.
 #
 # @return void
 ##
main()
{
    case $1 in
    'update')
        # Prepares the environment.
        prepare;

        # Copies the files of the update.
        getGitChanges 'copy-files';
        copyUpdateFiles;

        # Creates the PHP file lists that is used by the updater to keep track about what are the changes.
        createUpdateList 'new-files';
        createUpdateList 'modified-files';
        createUpdateList 'deleted-files';

        # Creates the log file.
        createChangelogFile;

        # Packs the files in a .tar file and moves them to the 'updates' repository
        movePackageToUpdateRepository "$(packageFiles '')";

        # Calls the PHP script to append the new version to the available updates list.
        /usr/bin/php8.1 "$localUpdatesRepository/versionListAppend.php" "$currentVersion";

        rm -R $tmpFolder;
        ;;
    
    'release')
        # Prepares the environment.
        prepare;

        # Copies the files of the release.
        copyReleaseFiles;

        # Packs the files in a .tar file and moves them to the 'release' repository
        movePackageToReleaseRepository "$(packageFiles '')";

        rm -R $tmpFolder;
        ;;
    *)
        echo -e "
--------------------------
Galastri Package Builder
--------------------------

List of options

'update'    Creates a update package, based on the last
            commit, for the galastri-framework-updates
            repository.

'release'   Creates a new release package, including
            all files inside the main folder.

########################################################
WARNING: If you aren't a developer of Galastri Framework
source code, just stop using this script RIGHT NOW!

It is meant to be used only by the core developers of
the framework, not by the users of the framework.
########################################################
";
;;
    esac
}

# Calls the main function to execute the script.
main $1;
