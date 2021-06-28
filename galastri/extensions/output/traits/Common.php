<?php

namespace galastri\extensions\output\traits;

use \galastri\core\Parameters;

/**
 * This trait has common methods that are shared within the type classes.
 */
trait Common
{
    /**
     * This method checks if the browserCache parameter exists in the route configuration. If it
     * exists, then a series of headers will be returned to the user's browser to make shure if the
     * content of the route changed.
     *
     * Returns true when there is a valid cache and the data doesn't need to be download or if the
     * cache is unset or null.
     *
     * @param  string $currentIdentifier            A string that identifies the current status of
     *                                              the view or file. It can be the last modified
     *                                              date of the view or the current content of the
     *                                              file. Any kind of content that identifies that
     *                                              the current content is changed from the cached
     *                                              one.
     * @return bool
     */
    private static function browserCache(string $currentIdentifier): bool
    {
        if (null !== $browserCache = Parameters::getBrowserCache()) {

            /**
             * Converts the identifier to MD5. It will be compared with the MD5 tag from the
             * browser's request.
             */
            $etag = md5($currentIdentifier);

            /**
             * Headers that makes the browser identifies that the response needs to be stored in
             * cache.
             */
            header('Last-Modified: '.gmdate('r', time()));
            if (isset($browserCache[1])) {
                header('Cache-Control: ' . $browserCache[1]);
            }
            header('Expires: Tue, 01 Jul 1980 1:00:00 GMT');
            header('Etag: '.$etag);

            /**
             * Validations taht checks if there are changes in the browser.
             */
            $cached = false;

            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
                if(time() <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + $browserCache[0]){
                    $cached = true;
                }
            }

            if(isset($_SERVER['HTTP_IF_NONE_MATCH'])){
                if(str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) != $etag){
                    $cached = false;
                }
            }

            if($cached){
                header('HTTP/1.1 304 Not Modified');
            }

            return $cached;
        }

        return false;
    }
}
