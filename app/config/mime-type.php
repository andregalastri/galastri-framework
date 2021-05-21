<?php

/**
 * This is the MIME type configuration file. This file stores an array with the file extensions and
 * its MIME types that will be recognized by the File output.
 *
 * The data here needs to follow this format:
 *
 *      <extension1> => [<MIME type A>, <MIME type B>, ...],
 *      <extension2> => [<MIME type C>, <MIME type D>, ...],
 *      ...
 *
 * <extension>          string                      The extension of the file. It needs to be in
 *                                                  lower case, even if the file to be accessed has
 *                                                  upper cased extensions.
 *
 * [<MIME type>]        array                       An array of string, with the possible MIME types
 *                                                  of the extension. There are extensions that
 *                                                  have multiple MIME types. Inform then here and
 *                                                  it will compare them with the MIME type of the
 *                                                  file to make shure the file will be returned
 *                                                  without being corrupted
 */
return [
    /**
     * These are common extensions and MIME types. You can include or remove any extension here.
     */
    'webmanifest'    => ['application/manifest+json'],
    'xml'            => ['application/xml'],
    'jpg'            => ['image/jpg', 'image/jpeg'],
    'jpeg'           => ['image/jpg', 'image/jpeg'],
    'txt'            => ['text/plain'],
    'png'            => ['image/png'],
    'gif'            => ['image/gif'],
    'ico'            => ['image/ico'],
    'svg'            => ['image/svg+xml'],
    'svgf'           => ['font/svg+xml'],
    'pdf'            => ['application/pdf'],
    'css'            => ['text/css'],
    'js'             => ['application/javascript'],
    'woff2'          => ['font/woff2'],
    'woff'           => ['font/woff'],
    'eot'            => ['font/eot'],
    'ttf'            => ['font/ttf'],
];
