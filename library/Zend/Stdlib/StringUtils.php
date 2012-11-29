<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Stdlib
 */

namespace Zend\Stdlib;

use Zend\Stdlib\StringWrapper\StringWrapperInterface;
use Zend\Stdlib\StringWrapper\MbString as MbStringWrapper;
use Zend\Stdlib\StringWrapper\Iconv as IconvWrapper;
use Zend\Stdlib\StringWrapper\Intl as IntlWrapper;
use Zend\Stdlib\StringWrapper\Native as NativeWrapper;

/**
 * Utility class for handling strings of different character encodings
 * using available PHP extensions.
 *
 * Declared abstract, as we have no need for instantiation.
 *
 * @category   Zend
 * @package    Zend_Stdlib
 */
abstract class StringUtils
{

    /**
     * Ordered list of registered string wrapper instances
     * 
     * @var StringWrapperInterface[]
     */
    protected static $wrapperRegistry;

    /**
     * A list of known single-byte encodings (upper-case)
     * 
     * @var string[]
     */
    protected static $singleByteEncodings = array(
        'ASCII', '7BIT', '8BIT',
        'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
        'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
        'ISO-8859-11', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
        'CP-1251', 'CP-1252'
        // TODO

    );

    /**
     * Get registered wrappers
     *
     * @return StringWrapperInterface[]
     */
    public static function getRegisteredWrappers()
    {
        if (static::$wrapperRegistry === null) {
            static::$wrapperRegistry = array();

            if (extension_loaded('intl')) {
                static::$wrapperRegistry[] = new IntlWrapper();
            }

            if (extension_loaded('mbstring')) {
                static::$wrapperRegistry[] = new MbStringWrapper();
            }

            if (extension_loaded('iconv')) {
                static::$wrapperRegistry[] = new IconvWrapper();
            }

            static::$wrapperRegistry[] = new NativeWrapper();
        }

        return static::$wrapperRegistry;
    }

    /**
     * Register a string wrapper
     *
     * @param StringWrapperInterface
     * @return void
     */
    public static function registerWrapper(StringWrapperInterface $wrapper)
    {
        if (!in_array($wrapper, static::$wrapperRegistry, true)) {
            static::$wrapperRegistry[] = $wrapper;
        }
    }

    /**
     * Unregister a string wrapper
     *
     * @param StringWrapperInterface $wrapper
     * @return void
     */
    public static function unregisterWrapper(StringWrapperInterface $wrapper)
    {
        $index = array_search($wrapper, static::$wrapperRegistry, true);
        if ($index !== false) {
            unset(static::$wrapperRegistry[$index]);
        }
    }

    /**
     * Get the first string wrapper supporting one or more encodings
     *
     * @param string $encoding Encoding supported by he string wrapper
     * @param string $encodingN, ... Unlimited OPTIONAL number of additional encodings
     * @return StringWrapperInterface
     * @throws Exception\RuntimeException If no wrapper supports all given encodings
     */
    public static function getWrapper($encoding = 'UTF-8')
    {
        $encodings = func_get_args();

        foreach (static::getRegisteredWrappers() as $wrapper) {
            foreach ($encodings as $encoding) {
                if (!$wrapper->isEncodingSupported($encoding)) {
                    continue 2;
                }
            }

            return $wrapper;
        }

        throw new Exception\RuntimeException(
            'No wrapper found supporting encoding(s) ' . implode(', ', $encodings)
        );
    }

    /**
     * Get a list of all known single-byte encodings
     *
     * @return string[]
     */
    public static function getSingleByteEncodings()
    {
        return static::$singleByteEncodings;
    }

    /**
     * Check if a given encoding is a known single-byte encoding
     *
     * @param string $encoding
     * @return boolean
     */
    public static function isSingleByteEncoding($encoding)
    {
        return in_array(strtoupper($encoding), static::$singleByteEncodings);
    }

    /**
     * Check if a given string is valid UTF-8 encoded
     *
     * @param string $str
     * @return boolean
     */
    public static function isValidUtf8($str)
    {
        return is_string($str) && ($str === '' || preg_match('/^./su', $str) == 1);
    }
}
