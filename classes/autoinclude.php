<?php

namespace PsumsAggregator\Classes;

/**
 * Class Autoinclude
 * @package PsumsAggregator\Classes
 *
 * Class for including other classes.
 * Works with spl_autoload_register and needs to be called in format spl_autoload_register([Autoinclude::class, "autoload"]);
 *
 */
abstract class Autoinclude
{
    public static $alreadyIncluded = array();

    /**
     *
     * Contains all paths for classes that needs to be included.
     * All new paths has to be added here
     *
     * @return array
     */
    private static function getPaths() {
        $root = $_SERVER["DOCUMENT_ROOT"];
        return array(
            $root . "/classes/logger",
            $root . "/classes/controllers",
            $root . "/classes",
            $root . "/interface",
            $root . "/config",
            $root . "/extenders",
            $root . "/models",
        );
    }

    /**
     * Inits auto loading. Uses defined paths
     */
    public static function autoload() {
        foreach(self::getPaths() as $path) {
            $files = scandir($path);
            foreach($files as $file) {
                if($file !== "." && $file !== "..") {
                    $fullPath = $path . "/" . $file;
                    if(file_exists($fullPath) && !is_dir($fullPath)) {
                        $rawName = self::getRawName($file);
                        if(!in_array($rawName, self::$alreadyIncluded)) {
                            list($parents, $interfaces, $traits) = self::extractParentsInterfacesOrTraits($rawName);
                            if(!empty($interfaces)) {
                                self::include($interfaces);
                            }
                            if(!empty($traits)) {
                                self::include($traits);
                            }
                            if(!empty($parents)) {
                                self::include($parents);
                            }
                        }
                        include_once($fullPath);
                        self::$alreadyIncluded[] = $rawName;
                    }
                }
            }
        }
    }

    /**
     *
     * Handle inclusion if not already included
     *
     * @param array $subjects
     */
    private static function include(array $subjects) {
        $subjects = array_reverse($subjects);
        foreach($subjects as $rawName) {
            if(!in_array($rawName, self::$alreadyIncluded)) {
                $fullPath = self::findPath($rawName);
                if($fullPath !== "") {
                    include_once($fullPath);
                    self::$alreadyIncluded[] = $rawName;
                }
            }
        }
    }

    /**
     *
     * Parses php file. looks for any parent classes, interfaces or traits and includes them as well
     *
     * @param string $rawName
     * @return array
     */
    private static function extractParentsInterfacesOrTraits(string $rawName) {
        $parents = array();
        $interfaces = array();
        $traits = array();

        $parent = "";
        do {
            $fullPath = self::findPath($rawName);
            if($fullPath !== "") {
                $phpContent = file_get_contents($fullPath);
                $phpContent = str_replace("<?php", "", $phpContent);
                if(strpos($phpContent, "class")) {
                    $classLine = self::getClassLine($phpContent);
                    $parent = self::extractParent($classLine);
                    $interfacesRet = self::extractInterfaces($classLine);
                    $traitsRet = self::extractTraits($phpContent);

                    if($parent !== "") {
                        $parent = strtolower($parent);
                        $parents[] = $parent;
                        $rawName = $parent;
                    }
                    if(!empty($interfacesRet)) {
                        $interfaces = array_merge($interfaces, $interfacesRet);
                    }
                    if(!empty($traitsRet)) {
                        $traits = array_merge($traits, $traitsRet);
                    }
                }
            } else {
                $parent = "";
            }
        } while($parent != "");

        return array($parents, $interfaces, $traits);
    }

    /**
     *
     * Find a path for extracted class
     *
     * @param string $rawName
     * @return string
     */
    private static function findPath(string $rawName) {
        $validExtensions = array(
            ".php" ,".inc", ".module", ".php4", ".php5", ".hphp", ".phtml", ".ctp",
        );
        foreach($validExtensions as $ext) {
            foreach(self::getPaths() as $path) {
                if(file_exists($path . "/" . $rawName . $ext)) {
                    return $path . "/" . $rawName . $ext;
                }
            }
        }

        return "";
    }

    /**
     *
     * Finds this line class SomeClass extend ParentClass Implements SomeInterface
     *
     * @param string $phpContent
     * @return false|string
     */
    private static function getClassLine(string $phpContent) {
        $endLine = strpos($phpContent, "{");
        $allClassLine = array();
        $pos = 0;
        while(($pos = strpos($phpContent, "class", $pos)) !== false) {
            if(count($allClassLine) > 2) {
                array_shift($allClassLine);
            }
            if($pos > $endLine) {
                break;
            }
            $allClassLine[] = $pos;

            $pos++;
        }

        if(count($allClassLine) === 1) {
            $startLine = $allClassLine[0];
        } else {
            $startLine = $allClassLine[count($allClassLine) - 1];
        }

        return substr($phpContent, $startLine, ($endLine + 1) - $startLine);
    }

    /**
     *
     * Extracts ParentClass from class line
     *
     * @param string $classLine
     * @return string
     */
    private static function extractParent(string $classLine) {
        if(!strpos($classLine, "extends")) {
            return "";
        }
        $parts = explode("extends", $classLine);
        if(strpos($parts[1], "implements")) {
            $parts[1] = substr($parts[1], 0, strpos($parts[1], "implements"));
        } else {
            $parts[1] = substr($parts[1], 0, strlen($parts[1]) - 2);
        }

        if(strpos($parts[1], '\\')) {
            return "";
        }

        return trim($parts[1]);
    }

    /**
     *
     * Extracts SomeUInterface from class line
     *
     * @param string $classLine
     * @return array
     */
    private static function extractInterfaces(string $classLine) {
        if(!strpos($classLine, "implements")) {
            return array();
        }
        $parts = explode("implements", $classLine);
        $interfacesStr = trim(substr($parts[1], 0, strlen($parts[1]) - 2));
        $interfaces = array_map(function($interface) {
            return strtolower(stripslashes($interface));
        }, explode(",", $interfacesStr));

        return $interfaces;
    }

    /**
     *
     * Extracts traits block and returns all Traits names
     *
     * @param string $phpContent
     * @return array
     */
    private static function extractTraits(string $phpContent) {
        $endClassLine = strpos($phpContent, "{");

        $after = array();
        if(strpos($phpContent,"const")) {
            $after[] = strpos($phpContent,"const");
        }
        if(strpos($phpContent, "private")) {
            $after[] = strpos($phpContent, "private");
        }
        if(strpos($phpContent,"public")) {
            $after[] = strpos($phpContent,"public");
        }
        if(strpos($phpContent,"protected")) {
            $after[] = strpos($phpContent,"protected");
        }
        if(strpos($phpContent,"function")) {
            $after[] = strpos($phpContent,"function");
        }
        sort($after);

        $traitsApplicable = trim(substr($phpContent, $endClassLine + 1, ((int)$after[0] -1) - $endClassLine));
        if($traitsApplicable === "") {
            return array();
        }

        $parts = explode(" ", $traitsApplicable);
        $traitsStr = substr($parts[1], 0, strlen($parts[1]) - 1);
        $traits = array_map(function($trait) {
           return strtolower(stripslashes($trait));
        }, explode(",", $traitsStr));

        return $traits;
    }

    /**
     * @param string $filename
     * @return mixed
     */
    private static function getRawName(string $filename) {
        return explode(".", $filename)[0];
    }
}