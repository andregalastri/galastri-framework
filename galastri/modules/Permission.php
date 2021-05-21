<?php
namespace galastri\modules;

use \galastri\core\Parameters;
use \galastri\extensions\Exception;
use \galastri\modules\types\TypeArray;

/**
 * This class stores a list of permissions that will be used to define if the execution of the code
 * will continue or will fail, based on a given permission that will be searched from the list.
 *
 * Example:
 *
 *  1. $userPermissions = ['edit-customer'];
 *  2.
 *  3. $permission = new Permission();
 *  4.
 *  5. $permission->allow('add-customer', 'remove-customer')
 *  6.            ->onFail("You don't have permission to execute this action")
 *  7.            ->validate($userPermissions);
 *  8.
 *  9. // Code that adds or remove users
 *
 * Based on the example above, suppose that there is a code that adds or removes customers from your
 * webapp (line 9 of the example above), but you, as user, want to restrict which user can execute
 * these actions. Therefor, each registered user will have permissions of what they can execute.
 * Suppose that a user with the permission 'edit-customer' is trying to execute the script (line 1
 * of the example above).
 *
 * Before the execution of adding or removing a user (line 9), you can set a permission instance
 * (line 3). With it you can set that the allowed permissions to keep the execution is
 * 'add-customer' and 'remove-customer' (line 5).
 *
 * The user permissions is validated (line 7). If the user doesn't have one of these permissions, it
 * will fail and an exception will be thrown with a custom message (line 6). In the example above,
 * it will fail, because the user doesn't have one of the allowed permissions to keep the execution.
 */
final class Permission implements \Language
{
    /**
     * Stores the allowed list.
     *
     * @var array
     */
    private array $allowedList = [];

    /**
     * Defines if all the allowed list is required to keep the execution.
     *
     * @var bool
     */
    private bool $required = false;

    /**
     * This method gets each value declared as a allowed permission and store it in the $allowedList
     * property.
     *
     * @param  bool|int|string $allowedList         Values that allows the script execution.
     *
     * @return self
     */
    public function allow(/*bool|int|string*/ ...$allowedList): self
    {
        $allowedList = (new TypeArray($allowedList))->flatten()->get();

        foreach ($allowedList as $allowed) {
            $this->allowedList[] = $allowed;
        }

        return $this;
    }

    /**
     * This method defines the $required property as true, which makes that all values in the list
     * be required in the validation list. This method can have a list that will be used as shortcut
     * to the allow method.
     *
     * @param  bool|int|string $requiredList        Values that will be passed to the allow method.
     *
     * @return self
     */
    public function require(/*bool|int|string*/ ...$requiredList): self
    {
        $this->required = true;

        $this->allow(...$requiredList);

        return $this;
    }

    /**
     * This method removes values from the allowed list.
     *
     * @param  bool|int|string $removeList          Values that will be removed from the allowed
     *                                              list.
     *
     * @return void
     */
    public function remove(...$removeList): self
    {
        $removeList = (new TypeArray($removeList))->flatten()->get();

        foreach ($removeList as $removeValue) {
            $keyList = array_keys($this->allowedList, $removeValue);
            foreach ($keyList as $removeKey) {
                unset($this->allowedList[$removeKey]);
            }
        }

        return $this;
    }

    /**
     * This method sets a custom fail message that will be returned as an exception when the
     * permission to be validated doesn't match the allowed permissions.
     *
     * @param  mixed $message                       Message that will be returned if there is no
     *                                              permission.
     *
     * @param  mixed $printf                        PrintF values to replace %s flags that can be
     *                                              put in the message.
     *
     * @return self
     */
    public function onFail(string $message, string ...$printf): self
    {
        Parameters::setPermissionFailMessage(vsprintf($message, $printf));

        return $this;
    }

    /**
     * This method executes the validation, comparing the permission listed with the allowed list.
     *
     * @param  bool|int|string $permissions         List of permissions that will be compared with
     *                                              the allowed list.
     *
     * @return void
     */
    public function validate(...$permissions): void
    {
        Debug::setBacklog();

        /**
         * Prepares the variables that will be compared.
         */
        $allowedList = $this->allowedList;
        $permissions = (new TypeArray($permissions))->flatten()->get();

        /**
         * The validation will always be presumed as false. Only if there is a match that the
         * validation will be set as true, which means that the script can keep its execution.
         */
        $validated = false;

        /**
         * Each allowed value will be test.
         */
        foreach ($allowedList as $allowed) {
            /**
             * If an allowed value isn't found in the permission list it can have 2 behaviors:
             *
             * 1. If the $required property is true, the validation will fail and an exception will
             *    be thrown.
             *
             * 2. If the $required property is false, the validation will test the next value until
             *    the end.
             */
            if (array_search($allowed, $permissions) === false) {
                if ($this->required) {
                    $validated = false;
                    break;
                }

            /**
             * If the value is allowed, the validation will be true, but the next validation will be
             * checked anyway, keeping the validation as true.
             */
            } else {
                $validated = true;
            }
        }

        /**
         * Throws an exception when the validation fail, returning an message with the error.
         */
        if (!$validated) {
            throw new Exception(
                Parameters::getPermissionFailMessage(),
                self::DEFAULT_PERMISSION_FAIL_MESSAGE[0]
            );
        }
    }

    /**
     * Resets the $allowedList and the $required properties.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->allowedList = [];
        $this->required = false;

        return $this;
    }
}
