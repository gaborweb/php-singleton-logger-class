<?php

/**
 * Description of Logger
 *
 * @author Kószó Gábor
 */
class Logger {

    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ALERT = 4;
    const DIR = "_log_";                // alapértelmezett mappa, amelybe a logfile.log dokumentum létrejön
    const FILENAME = "logfile.log";     // dokumentum neve

    private static $instance = null;
    private static $enabled_level = 0;
    private static $log_file_path;

    private function __construct() {

    }

    public static function inst() {
        if (!self::$instance) {
            self::$instance = new Logger();
            self::setFilePath();
        }

        return self::$instance;
    }

    public static function setLogLevel($level) {

        $min_level = 0;
        $max_level = self::LEVEL_INFO | self::LEVEL_WARNING | self::LEVEL_ALERT;

        if ($level > $min_level && $level <= $max_level) {
            self::$enabled_level = $level;
        }
    }

    public static function need($level) {

        if ($level & self::$enabled_level) {        // bitwise(bitművelet) operátor     1&1=>0001&0001->0001=1 , 2&2=>0010&0010->0010=2 , stb...
            return true;
        }

        return false;
    }

    public static function timeStamp() {            

        $date = new DateTime();
        return $date->format('Y-m-d H:i:s');
    }

    private static function setFilePath() {

        self::$log_file_path = self::DIR . DIRECTORY_SEPARATOR . self::FILENAME;
    }

    private static function getVarName($var) {

        foreach ($GLOBALS as $var_name => $value) {
            if ($value === $var) {
                return $var_name;
            }
        }
    }

    private static function varTest($var) {

        return var_export($var, true);
    }

    private static function arrayTest($arr) {

        return implode(", ", $arr);
    }

    private static function objTest($obj) {

        return serialize($obj);
    }

    private static function writerCheck($file_path) {

        if (!is_writable($file_path)) {
            die("Ooops, hiba történt! Változtasd meg a " . $file_path . " állomány hozzáférési jogait.");
        }
    }

    private static function fileComposer($log_type, $result) {

        return self::timeStamp() . " - " . $log_type . " - " . $result . PHP_EOL;
    }

    private static function putContent($content) {

        file_put_contents(self::$log_file_path, "\xEF\xBB\xBF".$content, FILE_APPEND| LOCK_EX);
    }

    private static function logWriterAll($result, $log_type) {

        $result = "Változó neve: $" . self::getVarName($result) . " - értéke: " . self::varTest($result);
        self::putContent(self::fileComposer($log_type, $result));
        self::writerCheck(self::$log_file_path);
    }

    private static function logWriterFail($result, $log_type) {

        self::putContent(self::fileComposer($log_type, $result));
        self::writerCheck(self::$log_file_path);
    }

    private static function logWriterArray($result, $log_type) {

        $result = "Tömb tartalma: " . self::arrayTest($result);
        self::putContent(self::fileComposer($log_type, $result));
        self::writerCheck(self::$log_file_path);
    }

    private static function logWriterObj($result, $log_type) {

        $result = "Objektum tartalma: " . self::objTest($result);
        self::putContent(self::fileComposer($log_type, $result));
        self::writerCheck(self::$log_file_path);
    }

    private static function logWriterVar($result, $log_type) {

        $result = "Változó tartalma: $" . self::varTest($result);
        self::putContent(self::fileComposer($log_type, $result));
        self::writerCheck(self::$log_file_path);
    }

    private static function logWriterText($result, $log_type) {

        self::putContent(self::fileComposer($log_type, $result));
        self::writerCheck(self::$log_file_path);
    }

    public function infoText($txt) {

        if (!is_string($txt)) {
            $txt = "a paraméter nem szöveg típus...";
            self::logWriterFail($txt, "err_text MESSAGE");
        } else {
            self::logWriterText($txt, "TEXT-INFO");
        }
    }

    public function infoArray($arr) {

        if (!is_array($arr)) {
            $arr = "a paraméter nem tömb típus...";
            self::logWriterFail($arr, "err_array MESSAGE");
        } else {
            self::logWriterArray($arr, "ARRAY-INFO");
        }
    }

    public function infoObject($obj) {

        if (!is_object($obj)) {
            $obj = "a paraméter nem objektum típus...";
            self::logWriterFail($obj, "err_object MESSAGE");
        } else {
            self::logWriterObj($obj, "OBJECT-INFO");
        }
    }

    public function infoVar($var) {

        self::logWriterVar($var, "VAR-INFO");
    }

    public function warning($log) {

        self::logWriterAll($log, "WARNING");
    }

    public function alert($log) {

        self::logWriterAll($log, "ALERT");
    }

}


/*

A Logger class függvényeinek meghívása példányosítás nélkül:

Példa: 

if (Logger::need(Logger::LEVEL_INFO)) Logger::inst()->infoObject($object);      // objektum tartalma info

if (Logger::need(Logger::LEVEL_INFO)) Logger::inst()->infoText("sql lekérdezés: " . $query);    // tetszőleges szöveg + változó érték info

if (Logger::need(Logger::LEVEL_INFO)) Logger::inst()->infoText("szöveg");   // tetszőleges szöveg

if (Logger::need(Logger::LEVEL_INFO)) Logger::inst()->infoVar($variable);   // változó tartalma info

if (Logger::need(Logger::LEVEL_INFO)) Logger::inst()->infoArray($array);    // tömb tartalma info

if (Logger::need(Logger::LEVEL_WARNING)) Logger::inst()->warning($text);    // warning  

if (Logger::need(Logger::LEVEL_ALERT)) Logger::inst()->alert($text);        // alert 

*/

?>