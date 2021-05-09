<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to manipulate substrings.
 */
trait FilePath
{
    /**
     * Importing traits to the class.
     */
    use Trim;

    /**
     * Author: Sven Arduwie
     * https://www.php.net/manual/pt_BR/function.realpath.php#84012

     * Receives a path and converts it to the real path, based on project's
     * root.
     *
     * @return self
     */
    public function realPath(): self
    {
        $path = str_replace(GALASTRI_PROJECT_DIR, '', $this->get());
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, GALASTRI_PROJECT_DIR . DIRECTORY_SEPARATOR . $path);

        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
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
     * fileExists
     *
     * @param null|string $path
     *
     * @return bool
     */
    public function fileExists(): bool
    {
        if ($this->isEmpty()) {
            throw new Exception(self::EMPTY_FILE_PATH[1], self::EMPTY_FILE_PATH[0], __METHOD__);
        }

        return is_file($this->realPath()->get());
    }

    /**
     * fileNotExists
     *
     * @param null|string $path
     *
     * @return bool
     */
    public function fileNotExists(): bool
    {
        return !$this->fileExists();
    }

    /**
     * directoryExists
     *
     * @param null|string $path
     *
     * @return bool
     */
    public function directoryExists(): bool
    {
        if ($this->isEmpty()) {
            throw new Exception(self::EMPTY_FILE_PATH[1], self::EMPTY_FILE_PATH[0], __METHOD__);
        }

        return is_dir($this->realPath()->get());
    }

    /**
     * directoryNotExists
     *
     * @param null|string $path
     *
     * @return bool
     */
    public function directoryNotExists(): bool
    {
        return !$this->directoryExists();
    }

    /**
     * Creates a file and all the directory path, if the file will be stored inside a path that
     * doesn't exist.
     *
     * @return self
     */
    public function createFile($permission = 0755): self
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

    public function createDirectory($permission = 0755): self
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
     * Insert a string inside a file.
     *
     * @param  string $string                       The text that will be included inside the file.
     *
     * @param  string $method                       Read/Write method used by fopen.
     *
     * @return self
     */
    public function fileInsertContents(string $string, string $method = 'a'): self
    {
        $filePath = $this->realPath()->get();

        $fileOpen = fopen($filePath, $method);
        fwrite($fileOpen, $string);
        fclose($fileOpen);

        return $this;
    }

    /**
     * Gets the content from a file.
     *
     * @return mixed
     */
    public function fileGetContents()
    {
        return file_get_contents($this->realPath()->get());
    }
}
