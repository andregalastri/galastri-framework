<?php
namespace galastri\modules;

use \galastri\core\Parameters;
use \galastri\extensions\Exception;
use \galastri\modules\types\TypeArray;

class Permission implements \Language
{
    private array $allowedList = [];
    private bool $required = false;

    public function allow(...$allowedList): self
    {
        $allowedList = (new TypeArray($allowedList))->flatten()->get();

        foreach ($allowedList as $allowed) {
            $this->allowedList[] = $allowed;
        }

        return $this;
    }

    public function require(...$requiredList): self
    {
        $this->required = true;

        $this->allow(...$requiredList);

        return $this;
    }

    public function remove(...$removeList): void
    {
        $removeList = (new TypeArray($removeList))->flatten()->get();

        foreach ($removeList as $removeValue) {
            $keyList = array_keys($this->allowedList, $removeValue);
            foreach ($keyList as $removeKey) {
                unset($this->allowedList[$removeKey]);
            }
        }
    }

    public function onFail(string $message): self
    {
        Parameters::setPermissionFailMessage($message);

        return $this;
    }

    public function validate(...$permissions): void
    {
        Debug::setBacklog();

        $allowedList = $this->allowedList;
        $permissions = (new TypeArray($permissions))->flatten()->get();

        $validated = false;

        foreach ($allowedList as $allowed) {
            if (array_search($allowed, $permissions) === false) {
                if ($this->required) {
                    $validated = false;
                    break;
                }
            } else {
                $validated = true;
            }
        }

        if (!$validated) {
            throw new Exception(
                Parameters::getPermissionFailMessage(),
                self::DEFAULT_PERMISSION_FAIL_MESSAGE[0]
            );
        }
    }

    public function clear(): self
    {
        $this->allowedList = [];
        $this->required = false;

        return $this;
    }
}
