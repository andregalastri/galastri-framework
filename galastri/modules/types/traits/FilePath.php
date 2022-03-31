<?php

namespace galastri\modules\types\traits;

use galastri\extensions\Exception;

/**
 * This trait has the methods related to manipulate substrings.
 */
trait FilePath
{
    /**
     * Based on
     * https://www.php.net/manual/pt_BR/function.realpath.php#84012
     * Author: Sven Arduwie
     *
     * This method formats strings that stores directory paths to convert it to an absolute path. It
     * takes into account the project directory as root and will format the given path using it as
     * base folder.
     *
     * @return self
     */
    public function realPath(): self
    {
        $path = str_replace(GALASTRI_PROJECT_DIR, '', $this->get());
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, GALASTRI_PROJECT_DIR . DIRECTORY_SEPARATOR . $path);

        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];

        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        $finalPath = implode(DIRECTORY_SEPARATOR, $absolutes);

        $this->execHandleValue(strncasecmp(PHP_OS, 'WIN', 3) == 0 ? $finalPath : DIRECTORY_SEPARATOR . $finalPath);

        return $this;
    }

    /**
     * This method checks if the stored string leads to a file and return if it exists.
     *
     * @return bool
     */
    public function fileExists(): bool
    {
        if ($this->isEmpty()) {
            throw new Exception(self::EMPTY_FILE_PATH, [__METHOD__]);
        }

        return is_file($this->realPath()->get());
    }

    /**
     * This method checks if the stored string leads to a file and return if it doesn't exist.
     *
     * @return bool
     */
    public function fileNotExists(): bool
    {
        return !$this->fileExists();
    }

    /**
     * This method checks if the stored string leads to a directory and return if it exists.
     *
     * @return bool
     */
    public function directoryExists(): bool
    {
        if ($this->isEmpty()) {
            throw new Exception(self::EMPTY_FILE_PATH, [__METHOD__]);
        }

        return is_dir($this->realPath()->get());
    }

    /**
     * This method checks if the stored string leads to a directory and return if it doesn't exist.
     *
     * @return bool
     */
    public function directoryNotExists(): bool
    {
        return !$this->directoryExists();
    }

    /**
     * This method gets the stored string as a file path and creates a file inside this path if
     * it doesn't exist. If the directory doesn't exist, it will be created.
     *
     * @param  int $permission                      Defines the permission mode of the file that
     *                                              will be created.
     *
     * @return self
     */
    public function createFile(int $permission = 0755): self
    {
        $filePath = $this->realPath()->get();
        $parentDir = $this->concat('/..')->realPath()->get();

        if (!file_exists($parentDir)) {
            $permissionResolve = umask(0);
            mkdir($parentDir, $permission, true);
            umask($permissionResolve);
        }

        if (!file_exists($filePath)) {
            $fileOpen = fopen($filePath, 'a');
            fclose($fileOpen);
        }

        return $this;
    }

    /**
     * This method gets the stored string as a directory path and creates a folder in this path if
     * it doesn't exist.
     *
     * @param  int $permission                      Defines the permission mode of the folder that
     *                                              will be created.
     *
     * @return self
     */
    public function createDirectory(int $permission = 0755): self
    {
        $dirPath = $this->realPath()->get();

        if (!is_dir($dirPath)) {
            $permissionResolve = umask(0);
            mkdir($dirPath, $permission, true);
            umask($permissionResolve);
        }

        return $this;
    }

    /**
     * This method gets the stored string as a file path and insert data inside it.
     *
     * @param  string $string                       Content that will be inserted in the file.
     *
     * @param  string $method                       The fopen function operation mode.
     *
     * @return self
     */
    public function fileInsertContents(string $string, string $mode = 'a'): self
    {
        $filePath = $this->realPath()->get();

        $fileOpen = fopen($filePath, $mode);
        fwrite($fileOpen, $string);
        fclose($fileOpen);

        return $this;
    }

    /**
     * This method gets the stored string as a file path and store its contents.
     *
     * @return self
     */
    public function fileGetContents(): self
    {
        $this->execHandleValue(file_get_contents($this->realPath()->get()));

        return $this;
    }

    /**
     * This method gets the stored string as a file path and return its MIME type.
     *
     * @return self
     */
    public function mimeType(): self
    {
        $realPath = $this->realPath()->get();

        $this->execHandleValue(is_file($realPath) ? mime_content_type($realPath) : false);

        return $this;
    }

    /**
     * This method gets the stored string as a file path and return its last modified date or false
     * on failure.
     *
     * @param  null|string $format                  When null, the method will return the Unix
     *                                              Timestamp (when successful), otherwise, it will
     *                                              format the Unix Timestamp into the given format.
     *
     * @return self
     */
    public function fileLastModified(?string $format = null): self
    {
        $filemtime = filemtime($this->realPath()->get());
        $lastModified = $format ? date($format, $filemtime) : $filemtime;

        $this->execHandleValue($lastModified);

        return $this;
    }

    public function directoryName(): self
    {
        if ($this->isNotEmpty()) {
            $this->execHandleValue(pathinfo($this->realPath()->get())['dirname']);
        }

        return $this;
    }

    public function fileBaseName(): self
    {
        if ($this->isNotEmpty()) {
            $this->execHandleValue(pathinfo($this->realPath()->get())['basename']);
        }

        return $this;
    }

    public function fileExtension(): self
    {
        if ($this->isNotEmpty()) {
            $this->execHandleValue(pathinfo($this->realPath()->get())['extension']);
        }

        return $this;
    }

    public function fileName(): self
    {
        if ($this->isNotEmpty()) {
            $this->execHandleValue(pathinfo($this->realPath()->get())['filename']);
        }

        return $this;
    }
}
