<?php
if (!function_exists('__log')) { 

$__phplogConfig = array(
	'handler' => 'file',
	'storeDir' => dirname(__FILE__) . '/Log',
	'fileLogPath' => dirname(__FILE__) . '/Log/logdata.php' ,
	'enabled' => true,
	'minTraceLevel' => 2,
	'maxTransportDataSize' => 0.8 * 1024 * 1024,
	'timezone' => '',
	'logFilePrefix' => 'log_',
	'editor' => array(
		'enabled' => true,
		'opener' => 'C:\Program Files\Notepad++\notepad++.exe',
		'arguments' => '-n%d'
	),
	// jslog ajax client access method, it can be 'get', 'post', 'crossdomain'
	// crossdomain is used in the situation when your web site domain differ to the jslog client domain
	'jslogAjaxMethod' => 'crossdomain',
	// this flag is set by this script, can be php, javascript, page
	'context' => 'php',
	'filters' => array(
		'*' => array('ezpublish'),
		'__log_variables' => array('backtrace')
	)
);

// init configuration


class phplogConfig
{
	/*
	 * @var Array
	 */
	public static $config = array();
	
	/**
	 * @var boolean
	 */
	public static $isInitialized = false;
	
	public static function init($config)
	{
		self::$config = $config;
		self::$isInitialized = true;
	}
	
	/**
	 * Get Original configuration Array
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		return self::$config;
	}
	
	public static function isInitialized()
	{
		return self::$isInitialized;
	}
	
	/**
	 * Get configuration based on $key
	 *
	 * @param string $key
	 * @return string|array|null|mixed
	 */
	public static function get($key)
	{
		if (self::$isInitialized || isset(self::$config)) {
			return self::$config[$key];
		}
		return null;
	}
	
	/**
	 * Set configuration value, maybe used for future, not for now.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public static function set($key, $value)
	{
	    self::$config[$key] = $value;
	}
	
	/**
	 * is Php Context
	 *
	 */
	public static function isPhpContext()
	{
	    $context = self::getContext();
	    return strtolower($context) == 'php';
	}
	
	/**
	 * Request from Javascript context
	 *
	 */
	public static function isJavascriptContext()
	{
	    $context = self::getContext();
	    return strtolower($context) == 'javascript';
	}
	
	/**
	 * Request from browser
	 *
	 */
	public static function isBrowserContext()
	{
	    $context = self::getContext();
	    return strtolower($context) == 'browser';
	}
	
	public static function getContext()
	{
	    return self::get('context');
	}
	
}



phplogConfig::init($__phplogConfig);
//unset($__phplogConfig);

if ((array_search(__FILE__, get_included_files()) !== false)
    && (array_search('phplog.php', pathinfo($_SERVER['SCRIPT_FILENAME'])) === false) 
) {
    phplogConfig::set('context', 'php');
} else {
    phplogConfig::set('context', 'browser');
}



class phplogException extends Exception 
{
	const ABORT = 1;
	const WARN = 2;
	const NOTICE = 4;
	const INFO = 8;
	
}




/**
 * Though it is not used at present source code,
 * @todo make sure myself understand LOCK_SH, LOCK_EX, include function
 *       check Ez and ZF source code, to see how MUTEX and LOCK implemented
 *
 */
class phplogLockManager
{
	const WRITE_LOCK_FILE = 'write.lock.file';
	const READ_LOCK_FILE = 'read.lock.file';
	const METADATA_LOCK_FILE = 'metadata.lock.file';
	
	public static function obtainMatadataLock()
	{
		
		return true;
	}
	
	public static function releaseMetadataLock()
	{
		return true;
	}
	
}




/**
 * Handler metadata
 * 
 * The metadata is used across between request 
 */
class phplogMetadata
{
	const METADATA_FILE = 'metadata.cache';
	
	protected static $_instance = null;
	// default metadata
	protected $_metadata = array(
		// version from sourceforge
		'version' => phplogVersion::VERSION,
	    'version-check-date' => '2010-11-10',
		// tick, auto-increment
		'refresh-tick' => 1,
		// whether we should refresh per request
		'refresh-per-request' => 1,
		// which types of log entity should be considered to render to page.
		'render-types' => array(
			'php', 'javascript'
		)
	);
	
	// store place of metadataFile
	protected $_metadataFile;
	
	protected static $_hasChanged = false;
	
	protected function __construct()
	{
		$config = phplogConfig::getConfig();
		
		$storeDir = $config['storeDir'];
		
		$this->_metadataFile = phplogHelper::path(array($storeDir, self::METADATA_FILE));
		
		// create file failed something happened
		if (!phplogHelper::createFile($this->_metadataFile, $this->_generateFileContent())) {
			throw new phplogException("File {$this->_metadataFile} cann't be created!", phplogException::ABORT);
		}
		
		$metadata = $this->_readMetadataFromFile();
		
		if (is_array($metadata)) {
			$this->_metadata = $metadata;
			$this->_metadata['version-check-date'] = date('Y-m-d');	
		}
	}
	
	/**
	 * Get Metadata as a singleton pattern
	 *
	 * @return phplogMetadata
	 */
	public static function instance()
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Increase Refresh Tick, this method is major used in __log_refresh
	 *
	 */
	public static function increaseRefreshTick()
	{
		$instance = self::instance();
		$tick = $instance->get('refresh-tick');
		//@todo, should compare with max integer, Or will overflow
		$instance->set('refresh-tick', $tick + 1);
	}
	
	/**
	 * Set metadata, this will automatically update file, since metadata is emergency	
	 *
	 * @param string $name
	 * @param mixed|string|array|boolean $value
	 */
	public function set($name, $value)
	{
		$this->_metadata[$name] = $value;
		
		$this->refreshMetadata();
	}
	
	/**
	 * Get Metadata from..
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function get($name)
	{
		if (isset($this->_metadata[$name])) {
			return $this->_metadata[$name];
		}
		return null;
	}
	
	/**
	 * Refresh all metadata information to files
	 * @return boolean
	 */
	public function refreshMetadata()
	{
		$fp = fopen($this->_metadataFile, 'w+');
		if (flock($fp, LOCK_EX)) {
			$content = $this->_generateFileContent();
			fwrite($fp, $content);
			flock($fp, LOCK_UN);
			fclose($fp);
		} else {
			fclose($fp);
			throw new phplogException("Cann't lock {$this->_metadataFile}!");
		}
		return true;
	}
	
	/**
	 * Get metadata from file
	 *
	 * @return Array|boolean
	 */
	private function _readMetadataFromFile()
	{
		// Read Mode
		// @todo, maybe we need use LOCK_SH
		$content = file_get_contents($this->_metadataFile);
		if ($content) {
			return $this->_parseFileContent($content);
		} else {
			return false;
		}
	}
	
	/**
	 * Unserialize content from file
	 *
	 * @param string $content
	 * @return Array
	 */
	private function _parseFileContent($content)
	{
		return unserialize($content);
	}
	
	/**
	 * Generate content which represent current settings
	 *
	 * @return string
	 */
	private function _generateFileContent()
	{
		return serialize($this->_metadata);
	}
}




class phplogHelper
{

    /**
     * Make directory if not present, if $recursive is true, then it will created recursively
     *
     * @param string $dir
     * @param integer $mode
     * @param boolean $recursive
     */
    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (is_null($dir) || $dir === '') {
            return false;
        }
        if (is_dir($dir) || $dir === '/') {
            return true;
        }
        if (self::mkdirs(dirname($dir), $mode, $recursive)) {
            return mkdir($dir, $mode);
        }
        return false;
    }
    
    /**
     * Join all dirs (array) into a news path
     *
     * @param array $parts
     */
    public static function path($parts)
    {
        $path = implode(DIRECTORY_SEPARATOR, $parts);
        
        $path = preg_replace('#\\{2,}#', '/', $path);
        $path = preg_replace('#/{2,}#', '/', $path);
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
    
    /**
     * Create a file a specified place with content, 
     * if file exist, then return without writting the content
     *
     * @param string $filename filename to created
     * @param string $content file content
     * @return boolean
     */
    public static function createFile($filename, $content)
    {
    	if (file_exists($filename)) {
    		return true;		
    	}
    	$dir = dirname($filename);
    	if (!file_exists($dir)) {
    		self::mkdirs($dir);
    	}
    	file_put_contents($filename, $content);
    	if (file_exists($filename)) {
    		return true;
    	}
    	return false;
    }
    
    /**
     * Clean all file content 
     *
     * @param string $filename
     */
    public static function cleanFileContent($filename)
    {
    	$fp = fopen($filename, 'w+');
    	if(flock($fp, LOCK_EX)) {
    		ftruncate($fp, 0);
    		flock($fp, LOCK_UN);
    	} else {
    		ftruncate($fp,0);
    	}
    	fclose($fp);
    }
    
    /**
     * Output expire header to browser
     */
    public static function expireHeaders()
    {
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
		// HTTP/1.1
		header('Cache-Control: private, no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0, max-age=0', false);
		// HTTP/1.0
		header('Pragma: no-cache');
    }
    
    /**
     * htmlspecialchars must be more efficient, not change for this version.
     *
     * @param string $string
     */
    public static function escapeJavascriptString($string)
    {
        $from = array("\r", "\n", "'");
        $to = array("", "", "\'");
        return str_replace($from, $to, $string);
    }
    
    /**
     * Count rows in xml content...
     *
     * @param string $data
     * @return integer
     */
    public static function countXmlRows($data)
    {
        $data = preg_replace("/\s/", "", $data);
		$data = str_replace("</", "data.xml.", $data);
		$data = str_replace("<?", "data.xml.", $data);

		$count = (int) substr_count($data, "<");
		return 2 * $count;
    }
    
    /**
     * Count Rows in string content
     *
     * @param string
     * @return integer
     */
    public static function countRows($data)
    {
        $data = preg_replace("/\r\n?/", "\n", $data);
        return substr_count($data, "\n");
    }
    
	/**
	 * Print all variables in page
	 *
	 * @return string Html content about vars
	 */
	public static function printPre($var)
	{
		$trace = debug_backtrace();
		echo '<p>-----File: ' . $trace[0]['file'] . ' line: '. $trace[0]['line'];
		echo '</p>';
	    echo '<pre>';print_r($var);echo '</pre>';
	}
	
}





final class phplogVersion
{
    const VERSION = '2.2.1';
    const VERSION_UPGRADE_URL = 'http://phplog.sourceforge.net/version.php';
    
    /**
     * Compare the specified phplog version string $version
     * with the current phplogVersion::VERSION of phplog.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return boolean           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }
    
    /**
     * Upgrade version information from source forge
     *
     * @return boolean true on success
     */
    public static function updateVersionInfo()
    {
        $iniUrlOpen = strtolower(ini_get('allow_url_fopen'));
        
        if ($iniUrlOpen == 'on' || $iniUrlOpen == '1') {
            
            $versionInfo = json_decode(@file_get_contents(self::VERSION_UPGRADE_URL));
            
            $ver = $versionInfo->version;
            
            $date = date('Y-m-d');
            
            $metadata = phplogMetadata::instance();
            
            $metadata->set('version', $ver);
            $metadata->set('version-check-date', $date);
            
            return true;
        } else {
            return false;
        }
        
    }
    
}






interface phplogHandlerInterface
{
    
    /**
     * Parse log 
     */
    public function parse();
    
    /**
     * Parse a data row
     *
     * @param string $data
     */
    public function parseLine($data);
    
    public function getLogsFromLine($currentLine);
    
    public function getTraceByLine($line);
    
    public function getLogByLine($line);
    
    public function deleteLines($from, $to);
    
    public function deleteSelectedLines($selectedLines);
    
    public function clean();
    
    public function gc();
    
    public function guide();
    
    public function welcome();
    
    
}




class phplogHandlerAbstract implements phplogHandlerInterface
{
    protected $_renderTypes = array();
    
    // you should override
    public function __construct()
    {
        $metadata = phplogMetadata::instance();
        $this->_renderTypes = $metadata->get('render-types');
        
        $this->init();
    }
    
    // please override this function when you implement you handler engine
    public function init()
    {
        
    }
    
    public function log($args, $backtrace = array(), $type = 'php'){}
    
    /**
     * Parse log
     */
    public function parse(){}
    
    // ------begin copy from int
    /**
     * Parse a data row
     *
     * @param string $data
     */
    public function parseLine($data){}
    
    public function getLogsFromLine($currentLine){}
    
    public function getLogByLine($line){}
    
    public function getTraceByLine($line){}
    
    public function deleteLines($from, $to){}
    
    public function deleteSelectedLines($selectedLines){}
    
    public function clean(){}
    
    public function gc(){}
    
    public function guide(){}
    
    /**
     * Check whether current entity log type is allowed 
     *
     * @param string $type
     * @return boolean
     */
    public function isTypeRenderAllowed($type)
    {
        if (in_array($type, $this->_renderTypes)) {
            return true;
        }
        return false;
    }
    
    /**
     * Log a welcome message about this projects 
     *
     * @return void
     */
    public function welcome()
    {
        // something you do
        
        $this->_logWelcome();
    }
    
    /**
     * Log a welcome message about this projects 
     * 
     * @return void
     */
    protected function _logWelcome()
    {
        $version = phplogVersion::VERSION;
        
	    $tips = <<<EOT
Congratulation, your configuration is correct!

1. You can include (phplog.php) in you program.

2. Then you can use __log() function to spy object.

3. Example: __log(), __log('Hello'), __log(\$_SERVER, \$_COOKIE), __log(apache_request_headers()), it is all okay.

4. then return to this page, you will know how to use it, it is simple but useful!

Best Regards!
EOT;
	   $copyright = <<<EOT
       PHPLog v{$version}
				
Author    : DengZhiYi
Contact   : 
            Email: altsee@gmail.com
            Skype: altsee
            Msn  : altsee@hotmail.com
App Name  : PHPLog
Version   : {$version}
License   : GNU GENERAL PUBLIC LICENSE Version 3
Website   : http://sourceforge.net/projects/phplog/
Copyright : All rights reserved.
EOT;
		
		__log($copyright);
		__log($tips);
		return true;
    }
    
}





class phplogFileHandler extends phplogHandlerAbstract 
{
    protected $_file = '';
    protected $_dir = '';   
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function init()
    {
        $config = phplogConfig::getConfig();
        
        $storeDir = $config['storeDir'];
        
        if (!is_dir($storeDir)) {
            phplogHelper::mkdirs($storeDir);
        }
        
        if (!is_dir($storeDir)) {
            // some configuration must be wrong, tell the user will this guide
            if (phplogConfig::isBrowserContext()) {
                $this->guide();
                exit;
            }
            // throw the error
            throw new phplogException("The storage directory '$storeDir' is not exists!");
        }
        // @todo
        // @todo, how to fixed date-time problem, since in reality time-zone can be changed! 
        // @todo, time-zone, problem, maybe we should make log file name fixed. 
        // then anything logged come to the same space, log resource is not a very useful resource. 
        // It can be re-occure by re-run the code.
        // but refresh-timestamp is time-fixed problem, all thing, we cann't believe timestamp is always correct!
        // So we need remove all timestamp related dependence, let project himself to determine what to do!
        // then refresh-timestamp should be replaced by ticks. auto-increment from 1 if refresh page is needed!
        
        // $date is only for test case
        $this->_dir = $storeDir;
        $this->_file = phplogHelper::path(array($this->_dir, $config['logFilePrefix'] .'data.file' . '.log'));
        
        if (phplogConfig::isBrowserContext() && !file_exists($this->_file)) {
        	$this->welcome();
        }
    }
    
    /**
     * Log all information about these log to file
     *
     * @param array $args
     * @param array $backtrace
     * @param string $type
     */
    public function log( $args, $backtrace = array(), $type = 'php')
    {
        $ts = date('H:i:s');
        
        $fd = fopen($this->_file, "a+");
        flock($fd, LOCK_EX);
        
        // @todo, some object is unserializeable, for example PDO
        // in that case we should find a way for this case.
        $traces = urlencode(serialize($backtrace));
        
        $info = urlencode(print_r($args, true));
        
        $line = "$type> <$ts> <$traces> <$info\n";
        
        fwrite($fd, $line);
        flock($fd, LOCK_UN);
        fclose($fd);
        
        unset($backtrace, $args, $line, $info, $traces);
        return true;
    }
    
    public function clean()
    {
    	phplogHelper::cleanFileContent($this->_file);
    }
    
    /**
     * Parse Log into entity, actually means recover the log from file.
     */
    public function parse()
    {
        $logs = file($this->_file);
        
        $ret = array();
        for ($i = 0; $i < count($logs); $i++) {
            $line = $this->parseLine($logs[$i]);
            $type = $line[0];
            if ($this->isTypeRenderAllowed($type)) {
                $ret[] = $line;
            }
        }
        return $ret;
    }
    
    /**
     * Parse Line Data to a entity array
     *
     * @param string $lineData
     * @return array
     */
    public function parseLine($lineData)
    {
        $arr = explode('> <', $lineData);
        
        $program = $arr[0];
		$ts = $arr[1];
		$traces = unserialize(urldecode($arr[2]));
		$data = urldecode($arr[3]);
	
		return array($program, $ts, $traces, $data);
    }
    
    /**
     * Get log > line
     * for speed up, we return total file lines if current_line >= total line;
     *
     * @param integer $currentLine
     */
    function getLogsFromLine($currentLine)
    {
        
        $logs = $this->parse();
        
        if (count($logs) <= $currentLine) {
            return count($logs);
        }
        
        $lines = array_splice($logs, $currentLine);
        
        return $lines;
        
    }
    
    /**
     * Get log entity by line
     *
     * @param integer $line
     * @return array line entity
     */
    function getLogByLine($line)
    {
        $logs = $this->parse();
        return $logs[$line - 1];
    }
    
    /**
     * Get Trace for a line entity
     *
     * @param integer $line
     * @return array
     */
    function getTraceByLine($line)
    {
        $log = $this->getLogByLine($line);
        
        return $log[2];
    }
    
    /**
     * Delete all entities by $from  -> $to
     *
     * @param integer $from
     * @param integer $to
     */
    function deleteLines($from, $to)
    {
        $lines = file($this->_file);
        
        $currentLine = 0;
        $recored = array();
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $this->parseLine($lines[$i]);
            if (!$this->isTypeRenderAllowed($line[0])) {
                $recored[] = $lines[$i];
            } else {
                $currentLine++;
                if ($currentLine >= $from && $currentLine <= $to) {
                    // removed line, not recored to array
                } else {
                    $recored[] = $lines[$i];
                }
            }
        }
        
        // line seperator? \n ?
        file_put_contents($this->_file, join("", $recored));
        
    }
    
    /**
     * Delete selected lines from file
     *
     * @param array|string $selectedLines
     */
    function deleteSelectedLines($selectedLines)
    {
        $lines = file($this->_file);
        
        if (is_string($selectedLines)) {
            $selectedLines = explode(',', $selectedLines);
        }
        
        $selectedLines = array_unique($selectedLines);
        
        $recored = array();
        $currentLine = 0;
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $this->parseLine($lines[$i]);
            
            if (!$this->isTypeRenderAllowed($line[0])) {
                $recored[] = $lines[$i];
            } else {
                $currentLine++;
                if (in_array($currentLine, $selectedLines)) {
                    // removed line
                } else {
                    $recored[] = $lines[$i];
                }
            }
        }
        
        file_put_contents($this->_file, join("", $recored));
    }
    
    
    
    /***********Other Helper Function***********/
    
    function guide()
    {
        $dir = $this->dir;
		$html = <<<EOT
<html><title>PHPLog</title>
	<body>
		<div style="margin-top:50px;font-size:16px;text-align:center;">
			Please ensure: The directory <b>($dir)</b> exists, and you have write permission on it, Then <b>retry</b>.
		</div>
	</body>
</html>
EOT;
		echo $html;
		exit;
    }
    
    public function welcome()
    {
    	touch($this->_file);
    	$this->_logWelcome();	
    }
    
    // deprecated, should be removed in future
    function gc()
    {
        
    }
    
    
}






/**
 * Singleton
 * 
 * Proxy execute all registered plugins if preLog, postLog event happened
 */
class phplogPlugin
{
	
	protected static $_instance = null;
	
	protected static $_plugins = array();
	
	/**
	 * Initialize plugin Broker
	 *
	 */
	protected function __construct()
	{
		// maybe we need find a way to configuration plugin
		self::registerPlugin(new phplogPluginIgnore());
		self::registerPlugin(new phplogPluginFilter());
		self::registerPlugin(new phplogPluginSmarty());
	}
	
	/**
	 * Get plugin for phplog
	 *
	 * @return phplogPlugin
	 */
	public static function instance()
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Notify all plugins that pre log event happened
	 *
	 * @param Array $args
	 * @param Array $backtrace
	 * @param string $type
	 * @param string $func
	 * @return void
	 */
	public function preLog(&$args, &$backtrace, $type, $func)
	{
		foreach (self::$_plugins as $plugin){
			$plugin->preLog($args, $backtrace, $type, $func);
			if (phplog::isCancelStatus()) {
				return false;
			}
		}
		return true;
	}
	
	public function postLog()
	{
		
	}
	
	
	/**
	 * Register a plugin into current plugin 
	 * 
	 * @param phplogPluginAbstract $plugin
	 */
	public static function registerPlugin($plugin)
	{
		$id = $plugin->getIdentifier();
		
		self::$_plugins[$id] = $plugin;
	}
	
	/**
	 * Remove plugin from plugin pool...
	 *
	 * @param string|phplogPluginAbstract $id
	 */
	public static function removePlugin($plugin)
	{
		if ($plugin instanceof phplogPluginAbstract) {
			$id = $plugin->getIdentifier();
		} else {
			$id = $plugin;
		}
		if (isset(self::$_plugins[$id])) {
			self::$_plugins[$id] = null;
		}
	}
	
}




abstract class phplogPluginAbstract
{
	protected $_identifier = '';
	
	public function __construct()
	{
		if (!$this->getIdentifier()) {
			// find 
			$className = get_class($this);
			$className = strtolower($className);
			$identifier = str_replace('phplogplugin', '', $className);
			
			$this->setIdentifier($identifier);
		}
		$this->init();
	}
	
	public function init()
	{
		
	}
	
	/**
	 * Get Plugin Identifier
	 *
	 * @return string
	 */
	public function getIdentifier(){
		return $this->_identifier;	
	}
	
	/**
	 * Set plugin identifier
	 *
	 * @param string $identifier
	 */
	public function setIdentifier($identifier)
	{
		$this->_identifier = $identifier;
	}
	
	/**
	 * Cancel current log request
	 */
	public function cancelLog()
	{
		phplog::setStatus(phplog::STATUS_CANCEL);	
	}
	
	/**
	 * Modify arguments, backtrace something like that
	 *
	 * @param mixed $args
	 * @param array $backtrace
	 * @param mixed $type
	 */
	public function preLog(&$args, &$backtrace, $type, $func){}
	
	public function postLog(){}
	
}



class phplogPluginIgnore extends phplogPluginAbstract 
{
	protected static $_comingLogIgnored = false;
	
	/**
	 * Notify Event
	 *
	 * @param array $args
	 * @param array $backtrace
	 * @param array $type
	 */
	public function preLog(&$args, &$backtrace, $type, $func)
	{
		if (self::$_comingLogIgnored || !phplogConfig::get('enabled')) {
			$this->cancelLog();
		}
	}
	
	public static function ignoreComingLog()
	{
		self::$_comingLogIgnored = true;
	}
	
	public static function restoreComingLog()
	{
		self::$_comingLogIgnored = false;
	}
	
}



 

/**
 * @todo  i little hard to implement a general and easy use filters
 *
 */
class phplogPluginFilter extends phplogPluginAbstract
{
    /**
     * Function filters, organized by function name
     *
     * @var array
     */
    static $filters = array();
    
    public function init()
    {
    	
    }
    
    public function preLog(&$args, &$backtrace, $type, $func) {
    	
    	$filters = self::getFiltersByFunc($func);
    	
    	foreach ($filters as $f) {
    		$f->filter($args, $backtrace, $type, $func);
    	}
    }
    
    public function postLog()
    {
    	
    }
    
    
    /**
     * Fetch filters by function name, such as __log, __log_variables
     *
     * @param string $funcName
     * @return Array
     */
    public static function getFiltersByFunc($funcName)
    {
        
        if (isset(self::$filters[$funcName])) {
            return self::$filters[$funcName];
        }
        $filters = phplogConfig::get('filters');
        
        $defaultFiltersConfig = self::_normalizeFilterConfigArr($filters['*']);
        
        if (isset($filters[$funcName])) {
            $funcFiltersConfig = self::_normalizeFilterConfigArr($filters[$funcName]);
        } else {
            $funcFiltersConfig = array(); 
        }
        
        $filtersInfoArr = array_merge($defaultFiltersConfig, $funcFiltersConfig);
        
        // filters by func
        $fs = array();
        
        foreach ($filtersInfoArr as $key => $options) {
            $className = 'phplogFilter' . ucfirst($key);
            if (class_exists($className)) {
            	$fs[$key] = new $className($options);
            }
        }
        
        self::$filters[$funcName] = $fs;
        return $fs;
    }
    
    
    /**
     * Normalized Config options from global config data
     *
     * @param Array $configArr
     */
    private static function _normalizeFilterConfigArr($configArr)
    {
        if (!is_array($configArr) || empty($configArr)) { 
            return array();
        }
        $return = array();
        foreach ($configArr as $k => $f) {
            if (is_string($k) && is_array($f)) {
                $return[$k] = $f;
            } else {
                // check default the fault has exists or not exists
                $return[$f] = array();
            }
        }
        return $return;
    }
     
    
    
    
    /**
     * Init Filters from config array
     *
     * @param array $filtersConfig
     */
    private static function initFilters($filtersConfig)
    {
        
    }
    
    /**
     * 
     * @param  $filtersConfig
     */
    public static function factory($filtersConfig)
    {
        
    }
    
    /**
     *
     * @param set $funcName
     * @param set $type
     */
    public static function registerFilterForFunc($funcName, $type)
    {
        
    }
    
}




class phplogPluginSmarty extends phplogPluginAbstract 
{
    static $originalArgumentCount = 0;
    static $currentIterator = 0;
    
	public function preLog(&$args, &$backtrace, $type, $func)
	{
		
	    if ($type == 'php' && count($backtrace) > 3) {
            
            $smartyInternalPluginHandler = $backtrace[2];
            
            // please refer to smarty Smarty_Internal_Plugin_Handler::executeModifier
            if (isset($smartyInternalPluginHandler['class'])
                && $smartyInternalPluginHandler['class'] == 'Smarty_Internal_Plugin_Handler'
                && isset($smartyInternalPluginHandler['function'])
                && $smartyInternalPluginHandler['function'] == 'executeModifier' ) {
                    
                // original arguments
                $originalArguments = $backtrace[2]['args'][1][0];
               
                $checkArray = $backtrace[2]['args'][2];
                
                // please refer to the smarty engine for this condition
                // only once call
                if (!$checkArray || !is_array($originalArguments)) {
                    
                // multiple call, thread-safe
                } else {
                    self::$originalArgumentCount = count($originalArguments);
                    self::$currentIterator++;
                    
                    if (self::$currentIterator != self::$originalArgumentCount) {
                        return self::cancelLog();
                    } else {
                        self::$originalArgumentCount = 0;
                        self::$currentIterator = 0;
                        $args = $originalArguments;
                    }
                }
                // how to get file infomation
                
            	$fileInfoBackTrace = $backtrace[3];
            	
            	$smartyTemplate = $backtrace[4]['object'];
            	
                if ($smartyTemplate instanceof Smarty_Internal_Template) {
                    $filePath = $smartyTemplate->getTemplateFilePath();
                } else {
                    $filePath = $fileInfoBackTrace['file']['args'][0];    
                }
            	
                // when original is array then, we always output Array, 
                // see Smarty_Internal_Plugin_Handler::executeModifier
                // for more information 
                // @todo, no way?!
                if (is_array($originalArguments)) {
                    
                }
                
                $backtrace = array(
            		array(
            				'file' => $filePath,
            				'line' => '',
            				'function' => '__log_request_info',
            				'args' => 'Called from Smarty, no backtrace!'
            		)
            	);
            	
            	#phplogHelper::printPre($smartyInternalPluginHandler['object']->smarty);
                
            }
        }
	}
	
	
}





abstract class phplogFilterAbstract
{
    protected $_options = array();
    
    public function __construct($options = array())
    {
        $this->setOptions($options);
        // all child class are supposed to use init method to initialized resource
        $this->init();
    }
    
    public function init()
    {
        
    }
    
    /**
     * Set option 
     *
     * @param string $name
     * @param mixed $value
     */
    public function setOption($name, $value = null)
    {
        if (empty($name)) {
            return false;
        }
        $this->_options[$name] = $value;
        return true;
    }
    
    /**
     * Get option by option name
     *
     * @param string $name
     * @return mixed|null
     */
    public function getOption($name)
    {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        }
        return null;
    }
    
    /**
     * Get Options for this object
     *
     * @return Array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Set Options for object
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            foreach ($options as $key => $val) {
                $this->setOption($key, $val);   
            }
        }
    }
    
    /**
     * Filter backtrace and modify somethig
     *
     * @param array $args Input arguments
     * @param array $backtrace backtrace
     * @param string $type log type like php,javascript
     * @param string $func log function like __log
     * 
     * @return void
     */
    abstract function filter(&$args, &$backtrace, $type, $func);
    
}




class phplogFilterEzpublish extends phplogFilterAbstract 
{

	public function filter(&$args, &$backtrace, $type, $func){
		
    	// please referer to the ezpublish process operator flow!
    	if( count($backtrace) > 2 ){
    		$eZTemplatePHPOperator = & $backtrace[1];
    		$ezTemplate = & $backtrace[2];
    		
    		// @todo, we don't check the cached-version, when excuted complied-template
    		
    		// Please Refer to ezpublish eztemplate file for this condition.!!
    		// If you does not understand  
    		if( $eZTemplatePHPOperator['function'] == 'modify'
    				&& $ezTemplate['function'] == 'processOperator' 
    				&& is_a($eZTemplatePHPOperator['object'], 'eZTemplatePHPOperator') 
    				&& is_a($ezTemplate['object'], 'ezTemplate') ){
    			
    			// then we should modify last 2 ezTemplate when ezTemplate call.
    			// Please referer to ezTemplate::processOperator, and it's sixth parameter: $placement
    			$placement = $ezTemplate['args'][5];
    			
    			$newBacktrace = array();
    			
    			// find the .tpl filepath and line where called the __log
    			if( is_array($placement) ){
    				$file = $placement[2];
    				$line = $placement[0][0];
    				
    				// change the file and line in $debug
    				// we should guess the ezpublish site root directory;
    				// maybe we should use ez api?
    				$oldFilepath = $backtrace[0]['file'];
    				$oldFilepath = str_replace("\\", '/', realpath($oldFilepath) );
    				
    				// stop string.
    				$searchPath = '/lib/eztemplate';
    				$rootPath = substr($oldFilepath, 0, strpos($oldFilepath, $searchPath));
    				
    				$firstTraceRow = array();
    				$firstTraceRow = $backtrace[0];
    				
    				// because of this debug_backtrace is too large, so we cut down it!
    				$file = phplogHelper::path(array($rootPath, $file));

    				$firstTraceRow['file'] = $file;
    				$firstTraceRow['line'] = $line;
    				$newBacktrace[] = $firstTraceRow;
    				$backtrace = $newBacktrace;
    			}
    		}
    	}
	}
	
	
}




/**
 * Reponse for filters all kind backtraces based on options
 *
 */
class phplogFilterBacktrace extends phplogFilterAbstract 
{
    protected $_options = array(
    	// @todo, maybe we don't need offset, or offset should always be zero.
    	'offset' => 0,
    	'length' => -1
    );
    
    public function init()
    {
    	$length = $this->getOption('length');
    	
    	$minTraceLevel = phplogConfig::get('minTraceLevel');
    	
    	if ($length < $minTraceLevel) {
    		$this->setOption('length',$minTraceLevel);
    	}
    }
    
	public function filter(&$args, &$backtrace, $type, $func){
		
		$offset = $this->getOption('offset');
		$length = $this->getOption('length');
		
		if ($offset == 0 && ($length + 1) >= count($backtrace)) {
			return true;
		} else {
			// already cut downsomething
			$newBacktrace = array();
			for ($i = $offset; $i < $length; $i++) {
				$newBacktrace[] = $backtrace[$i];			
			}
			// append we marker that we have changed backtrace
			$backtrace = $newBacktrace;
			self::appendMarkerWhenCutDownTrace($backtrace);
		}
	}
	
	/**
	 * append a marker trace entity to backtrace when backtrace been cut down
	 * 
	 * @param array $backtrace
	 * @return array
	 */
	public static function appendMarkerWhenCutDownTrace(&$backtrace)
	{
		$entity = array(
			'file' => 'we_cut_down_trace.php',
			'line' => 0,
			'function' => 'we_cut_down_trace',
			'args' => array(
			)
		);
		
		$backtrace[] = $entity;
	}
	
	
}




class phplogFilterExclude extends phplogFilterAbstract 
{
	protected $_options = array(
		'withoutBacktrace' => true,
		'callerIsUserFunction' => false
	);
	
	public function init()
	{
		
	}
	
	function filter(&$args, &$backtrace, $type, $func)
	{
		$callerIsUserFunction = $this->getOption('callerIsUserFunction');
		
		if ($callerIsUserFunction && count($backtrace) > 1) {
			array_shift($backtrace); 
		}
		
		$withoutBacktrace = $this->getOption('withoutBacktrace');
		if ($withoutBacktrace){
			$phplogFilterBacktrace = new phplogFilterBacktrace();
			$phplogFilterBacktrace->filter($args, $backtrace, $type, $func);
		}
	}
}





/**
 * Log any variables in your php program, and view these logs in web UI.
 * Log any javascript variable in javascript, and view these in web UI
 * Copyright (C) 2009-2010 DengZhiYi 
 * 
 * Licensed under the terms of the GNU General Public License:
 *      http://www.opensource.org/licenses/gpl-license.php
 * @license  GPL
 * 
 * @link     https://sourceforge.net/project/phplog/
 * @version  2.1
 * @author   DengZhiYi<altsee@gmail.com>
 *           Special thanks stone<stone.pei@gmail.com> suggest adding stack trace.
 * @since    2009/09/10, @GuangZhou of China
 * @modify   2009/12/03, @GuangZhou of China
 *
 *
 * Changes
 *     Revision 1.0
 *         - Initial release
 *         - implement __log() function
 *         - implement view log from web browser
 *         - implement debug_backtrace to trace call stack and call arguments
 *     Revision 1.1
 *         - implement database store function
 *         - fix bug with popup window
 *     Revision 1.2
 *         - implement binary file store function
 *         - define ajax max transport data size, to solve large data process in web browser
 *         - make this program more portable and restruct the code 
 *         - implement timer for performance trace for a block code
 *     Revision 1.3
 *         - implement software upgrade notice function
 *         - restruct the code
 *         - add a new field 'program' in store
 *         - implement API
 *     Revision 2.0
 *         - implement jslog function
 *         - implement jslog embed into phplog.php
 *         - implement image embed into phplog.php
 *         - modify the template
 *         - test the program
 *     Revision 2.1
 *         - implement hook, to give user a change to filter data and backtrace
 *         - implement __log_variable to only log variable when been called, to solve the problem with too many function call.
 *         - implement __log_clean,__log_exit
 *         - implment active X for opening editor in web browser
 *         - store global config to function's static variable, protect global config been clean
 *     Revision 2.1.1
 *         - implement decode function, can decode json/xml/session/serialize/ini so far
 *         - write testcase based on simpletest for this project
 *         - remove 'remove' action in web UI, add gc() function, delete old files mannually.
 *         - fixed some bug.
 *         - make it can included in function,suggest use phplog with auto_prepend_file in php.ini
 *         - improve project tool (project.pl)
 *     Revision 2.2.1
 *         - rebuild php code using php 5, but js code not touched for now
 *         - implement __log_exclude, __log_file,
 *         - implement a universe API for logging, implement a log pre event, and plugins
 *         - implement metadata manipulate class
 *
 * @todo, rebuild client html/javascript, also js.php
 * 
 */
 

 

/**
 * Singleton Pattern
 * 
 * Write and Read log entity to the handler
 * 
 * For most case this class is proxy to handler method
 *
 */
class phplog
{
	/**
	 * static function to hold all object self
	 *
	 * @var phplog
	 */
    protected static $_instance = null;
    
    protected static $_status = 0;
    const STATUS_CANCEL = 0;
    const STATUS_CONTINUE = 1;
    
    /**
     * Plugin, event pre, post 
     *
     * @var phplogPlugin
     */
    protected $_plugin = null;
        
    /**
     * Hanlder, handler realy action upon log event
     *
     * @var phplogHandlerAbstract
     */
    private $_handler = null;
    
    /**
     * Private construct, can only been initialized by itself
     *
     */
    private function __construct()
    {
        $config = phplogConfig::getConfig();
        
        $handler = $config['handler'];
        
        $handler = ucfirst($handler);
        
        $className = 'phplog' . $handler . 'Handler';
        
        if (class_exists($className)) {
            $this->_handler = new $className();
        } else {
            // error, how to handler error in phplog
            throw new phplogException("The handler '$className' does not exists");
        }
        
        // init plugin
        $this->_plugin = phplogPlugin::instance();
        
    }
    
    /**
     * Get instance of phplog, then you can read write information upon log file...
     *
     * @return phplog
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /****************All log request should call this function.*******************/
    
    /**
     * General function for log data
     *
     * @param array $args
     * @param string $type type identifier
     * @param array|null caller function 
     * @param array|null backtrace
     * @return boolean|false
     */
    function log(Array $args, $backtrace = null, $type = 'php', $func = '__log')
    {
    	self::resetStatus();
        if ($backtrace === null) {
            $backtrace = debug_backtrace();
            array_shift($backtrace);
        }
        // centralized filter and analyse
        
    	$this->preLog($args, $backtrace, $type, $func);
    	
    	if (self::isCancelStatus()) {
    		return false;
    	}
    	for($i = 0; $i < count($backtrace); $i++){
			if( isset($backtrace[$i]['args']) ){
				$backtrace[$i]['args'] = print_r( $backtrace[$i]['args'], true );
			}
			if( !empty($backtrace[$i]['class']) AND !empty($backtrace[$i]['type']) ){
				$backtrace[$i]['function'] = $backtrace[$i]['class'] . $backtrace[$i]['type'] . $backtrace[$i]['function'];
			}
		}
		
		if (count($args) == 1) {
			$args = $args[0];
		}
		
		// everything is okay now, call the function, do real
    	$this->_handler->log($args, $backtrace, $type);
    	
    	$this->postLog();
    }
    
    
    /*************Event pre, post for LOG***********/
    
    protected function preLog(&$args, &$backtrace, $type, $func)
    {
    	$this->_plugin->preLog($args, $backtrace, $type, $func);
    }
    
    /**
     * Not need for current, maybe in future.
     * But We let him in here.
     */
    protected function postLog()
    {
//    	$this->_plugin->postLog();
    }
    
    /******************Other Log Engine Api*****************/
    
    /**
     * Parse log 
     *
     * @return array
     */
    public function parse()
    {
        return $this->_handler->parse();    
    }
    
    /**
     * Parse this line data
     *
     * @param string $lineData
     * @return array
     */
    public function parseLine($lineData)
    {
        return $this->_handler->parseLine($lineData);
    }
    
    /**
     * Get logs from line
     *
     * @param integer $currenLine
     * @return Array
     */
    public function getLogsFromLine($currenLine)
    {
        return $this->_handler->getLogsFromLine($currenLine);
    }
    
    /**
     * Delete logs from $from -> $to
     *
     * @param integer $from
     * @param integer $to
     * @return boolean
     */
    public function deleteLines($from, $to)
    {
        return $this->_handler->deleteLines($from, $to);
    }
    
    /**
     * Delete selected lines
     *
     * @param string|array $selectedLines
     * @return boolean
     */
    public function deleteSelectedLines($selectedLines)
    {
        return $this->_handler->deleteSelectedLines($selectedLines);   
    }
    
    /**
     * Get log by line
     *
     * @param integer $line
     * @return integer
     */
    public function getLogByLine($line)
    {
        return $this->_handler->getLogByLine($line);
    }
    
    /**
     * Get trace infor for a line
     *
     * @param integer $line
     * @return string
     */
    public function getTraceByLine($line)
    {
        return $this->_handler->getTraceByLine($line);    
    }
    
    
    /**
     * Clean log data
     */
    public function clean()
    {
    	$this->_handler->clean();
    }
    
    /**************status function ************************/
    
    public static function resetStatus()
    {
    	self::$_status = null;	
    }
    
    /**
     * @param integer $status
     */
    public static function setStatus($status)
    {
    	self::$_status = $status;
    }
    
    /**
     * Get status from static status
     */
    public static function getStatus()
    {
    	return self::$_status;
    }
    
    /**
     * return true, current log status is continue
     */
    public static function isContinueStatus()
    {
    	return self::$_status === self::STATUS_CONTINUE;
    }
    
    /**
     * return true, current log status is canceled
     *
     * @return Boolean
     */
    public static function isCancelStatus()
    {
    	return self::$_status === self::STATUS_CANCEL;
    }

}




/**
 * most used function __log, log arguments and debug_backtrace together
 *
 * @access public
 */
function __log()
{
    $args = func_get_args();
    
    $phplog = phplog::instance();
    
   	$phplog->log($args);
}

/**
 * log_variables 
 * if debug_backtrace is too deep, 
 * we cut down backtrace level to given global option minTraceLevel
 * 
 * @access public
 */
function __log_variables()
{
	$args = func_get_args();
	
	$phplog = phplog::instance();
	
	$phplog->log($args, null, 'php', '__log_variables');
}

/**
 * only log once at special place.
 * 
 * @access public
 */
function __log_once()
{
	static $placements = array();
	
	$debug = debug_backtrace();
	
	$place = $debug[0];
	
	// actually, in some situation, we cann't get 'file' and 'line', eg. php-gtk
	if( isset($place['file']) && isset($place['line'])
			&& $place['file'] && $place['line']){
		
		$md5 = md5($place['file'] . $place['line']);
		if( ! isset($placements[$md5]) ){
			$args = func_get_args();
			$phplog = phplog::instance();
			$phplog->log($args, null, 'php', '__log_once');
		}
		$placements[$md5] = 1;
	}
	unset($debug);
}

/**
 * add log only if the last element is true
 * 
 * @access public
 */
function __log_if()
{
	$args = func_get_args();
	
	if( count($args) && $args[count($args) - 1]){
		array_pop($args);
		$phplog = phplog::instance();
		$phplog->log($args, null, 'php', '__log_if');
	}
}

/**
 * Clean the log file and add a log.T
 * his is useful when you want to log from the particular place at your app.
 *
 * @access public
 */
function __log_clean()
{
	$args = func_get_args();
	$phplog = phplog::instance();
	$phplog->clean();
	$phplog->log($args, null, 'php', '__log_clean');
	phplogMetadata::increaseRefreshTick();
}

/**
 * refresh the web client page for this request
 *
 * @access public
 */
function __log_refresh(){
	
	$args = func_get_args();
	
	$phplog = phplog::instance();
	
	$phplog->clean();
	
	$phplog->log($args, null, 'php', '__log_refresh');
	
	phplogMetadata::increaseRefreshTick();
}

/**
 * End a __log_clean, all __log after __log_end will not __log anything
 *
 * @todo, maybe need implement __log_reopen() function
 * @access public
 */
function __log_end()
{
	$args = func_get_args();
	
	$phplog = phplog::instance();
	
	$phplog->log($args, null, 'php', '__log_end');
	phplogPluginIgnore::ignoreComingLog();
}

/**
 * Add a log and exit the program
 *
 * @access public
 */
function __log_exit(){
	$args = func_get_args();
	
	$phplog = phplog::instance();
	
	$phplog->log($args, null, 'php', '__log_exit');
	exit;	
}

/**
 * Directly log to specific file
 *
 * @access public
 */
function __log_file(){
	
	if (!phplogConfig::get('enabled')) {
		return false;
	}
	
	$fileLogPath = phplogConfig::get('fileLogPath');
	
	$header = "<?php\n";
	$format = "#\n## [%s] %s [%s]\n\$data['%s'] = %s;\n";
	$var = var_export(func_get_args(), true);
	
	$backtrace = debug_backtrace();
	$line = $backtrace[0]['line'];
	$file = $backtrace[0]['file'];
	$time = date('H:i:s');
	
	$entity = sprintf($format, $line, $file, $line, $time, $var);
	
	$filesize = @filesize($fileLogPath);
	
	$metadata = phplogMetadata::instance();
	
	$refreshPerPage = $metadata->get('refresh-per-page');
	
	if ($filesize < 10 || $refreshPerPage) {
		file_put_contents($fileLogPath, $header);
	}
	file_put_contents($fileLogPath, $entity, FILE_APPEND);
	return true;
}

/**
 * log a object, exclude all keys in except array 
 *
 * @param mixed $object
 * @param array $except
 * @param boolean $withouBacktrace
 * @param boolean $callerIsUserFunction
 * 
 * @access public
 */
function __log_exclude($object, $except = array(), $withouBacktrace = true, $callerIsUserFunction = false)
{
	// deal with object in here
	$func = '__log_exclude_' . ((int)$withouBacktrace) . '_' . ((int) $callerIsUserFunction);
	
	// update configure
	$options = array(
		'exclude' => array(
			'withoutBacktrace' => $withouBacktrace,
			'callerIsUserFunction' => $callerIsUserFunction
		)
	);
	$filters = phplogConfig::get('filters');
	
	
	if (!isset($filters[$func])) {
		$filters[$func] = $options;
		phplogConfig::set('filters', $filters);
	}
	
	if (!is_array($except)) {
		$except = array();	
	}
	
	
	if (is_array($object)) {
		$args = array();
		foreach ($object as $key => $val) {
			if (!in_array($key, $except)) {
				$args[$key] = $val;
			}
		}
	} else if (is_object($object)) {
		// reflection here
		$className = get_class($object);
		$args = array();
		foreach (get_object_vars($object) as $key => $val) {
			if (!in_array($key, $except)) {
				$args[$className][$key] = $val;
			}
		}
	} else {
		$args = $object;
	}
	
	$phplog = phplog::instance();
	
	$phplog->log(array($args), null, 'php', $func);
}

/**
 * Start a time stamp dump section
 *
 * @access public
 */
function __log_start()
{
	__phplog_timer('start');
}

/**
 * End a time and memory spent section
 * 
 * @access public
 */
function __log_stop()
{
	__phplog_timer('stop');
}


/**
 * The internal __phplog_timer Function
 *
 * @access private
 */
function __phplog_timer($action = null)
{
	static $__t = null;
	static $__preview_function_used_memory;
	
	$memory = memory_get_usage();
	$microtime = explode(' ', microtime());
	$msec = $microtime[0];
	$sec = $microtime[1];
	
	// private, your application will not access this condition, because this program has access it!
	if( $__t === null ){
		$__t = array('sec' => $sec, 'msec' => $msec, 'memory' => $memory);
		$__preview_function_used_memory = memory_get_usage() - $memory;
		return true;
	}
	
	// preview call record;
	$pc = $__t;
	$__t = array('sec' => $sec, 'msec' => $msec, 'memory' => $memory);
	
	// action != stop, then just record current state for future used.
	if( $action != 'stop'){
		$__preview_function_used_memory = memory_get_usage() - $memory;
		return true;
	}
	// Grab runtime information for user's block code
	
	$spend_time_sec = ($sec - $pc['sec']) * 1000;
	$spend_time_msec = ($msec - $pc['msec']) * 1000;
	// adjust 0.3 ms in my local pc, maybe some gay need another adjust, just let them been now
	$spend_time = round($spend_time_sec + $spend_time_msec - 0.3, 3);
	if( $spend_time < 0 ) $spend_time = 0;
	$used_memory = $memory - $pc['memory'] - $__preview_function_used_memory;
	if( $used_memory < 0 ) $used_memory = 0;
	
	$args = '';
	$args .= "This Block Code Spend Time :  $spend_time (ms)\n";
	$args .= "This Block Code Use Memory :  $used_memory\n";
	$args .= "Current Total Used  Memory :  $memory";
	
	$__preview_function_used_memory = memory_get_usage() - $memory;
	
	// actually log here
	$backtrace = debug_backtrace();
	array_shift($backtrace);
	
	$phplog = phplog::instance();
	
	$phplog->log(array($args), $backtrace, 'php', '__phplog_timer');
	return true;
}

/**
 * Private function
 * 
 * @access private
 */
function __log_refresh_if_necessary(){
	
	$metadata = phplogMetadata::instance();
	
	$phplog = phplog::instance();
	
	if ($metadata->get('refresh-per-page')) {
		$phplog->clean();
		phplogMetadata::increaseRefreshTick();
	}
}

/**
 * __log included files at script shutdown 
 * 
 * @access private
 */
function __log_included_files(){
	if (phplogConfig::isPhpContext()) {
		
		$args = array(get_included_files());
		
		$backtrace = array(
			array(
				'file' => 'php.included.files',
				'line' => '',
				'function' => '__log_included_files',
				'args' => 'Called from shutdown, no backtrace!'
			)
		);
		
		$phplog = phplog::instance();
		// restore Log function, since we are closed to exit the code
		phplogPluginIgnore::restoreComingLog();
		$phplog->log($args, $backtrace, 'php.included.files', '__log_included_files');
	}
}

/**
 * when this file is been executed.
 * log some like $_POST, $_GET, $_COOKIE, $_SERVER.
 * 
 * @access private
 */
function __log_request_info(){
	
	$backtrace = array(
		array(
				'file' => 'php.request.info',
				'line' => '',
				'function' => '__log_request_info',
				'args' => 'Called from bootstrap, no backtrace!'
		)
	);
	
	$args = array(array(
		'GET' => $_GET,
		'POST' => $_POST,
		'FILES' => $_FILES,
		'COOKIE' => $_COOKIE,
		'SERVER' => $_SERVER,
        'HTTP' => __log_get_http_raw()
	));
	
	$phplog = phplog::instance();
	phplogPluginIgnore::restoreComingLog();
	$phplog->log($args, $backtrace, 'php.request.info', '__log_request_info');
}

/**
 * 获取HTTP请求原文
 * @return string
 */
function __log_get_http_raw() {
    $raw = '';

    // (1) 请求行
    $raw .= $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.$_SERVER['SERVER_PROTOCOL']."\r\n";

    // (2) 请求Headers
    foreach($_SERVER as $key => $value) {
        if(substr($key, 0, 5) === 'HTTP_') {
            $key = substr($key, 5);
            $key = str_replace('_', '-', $key);

            $raw .= $key.': '.$value."\r\n";
        }
    }

    // (3) 空行
    $raw .= "\r\n";

    // (4) 请求Body
    $raw .= file_get_contents('php://input');

    return $raw;
}

/******************Below is logical code***********************/
// logic code here
if (phplogConfig::isPhpContext()) {
	__log_refresh_if_necessary();
	__phplog_timer();
	__log_request_info();
	register_shutdown_function('__log_included_files');
	return true;
}

// CLI interface, do not support
if (php_sapi_name() == 'cli') {
	echo 'Current Implementation doesnot support CLI! Please use browser instead.';
	return false;
}
// check whether phplog is been disabled.
// @todo, more meanful notice should provide to user
if (!phplogConfig::get('enabled')) { 
	die('PHPLog has been disabled in your project!');
}



class phplogClient
{

    private $_action = null;
    
    private $_get = null;
    
    private $_post = null;
    
    private $_request = null;
    
    /**
     * phplog object
     *
     * @var phplog
     */
    private $_phplog = null;
    
    public function __construct()
    {
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_request = $_REQUEST;
        $this->_phplog = phplog::instance();
    }
    
    public function get($name)
    {
        if (isset($this->_get[$name])) {
            return $this->_get[$name];
        }
        return null;
    }
    
    public function post($name)
    {
        if (isset($this->_post[$name])) {
            return $this->_post[$name];
        }
        return null;
    }
    
    public function request($name)
    {
        if (isset($this->_request[$name])) {
            return $this->_request[$name];
        }
        return null;
    }
    
    /**
     * Output json for javascript client
     *
     * @param array $info
     */
    public function outputJsonAndExit($info)
    {
        echo json_encode($info);
        exit;
    }
    
    /**
     * Handle client request from browser or somewhere
     *
     */
    public function handle()
    {
        $action = $this->request('action');
        
        if (!empty($action)) {
            
            if ($action != 'image') {
                phplogHelper::expireHeaders();
            }
            
            $method = 'clientAction' . ucfirst($action);
            
            if (!method_exists($this, $method)) {
                // output json error message!
                $this->clientActionDefault();
            } else {
                $this->$method();
            }
        }
    }
    
    public function clientActionDelete()
    {
        $from = $this->request('from');
        $to = $this->request('to');
        $selectedLines = $this->request('selected_lines');
        
        if ($from && $to) {
            $from = (int) $from;
            $to = (int) $to;
            $this->_phplog->deleteLines($from, $to);
        }
        
        if ($selectedLines) {
            $selectedLines = explode(',', $selectedLines);
            $this->_phplog->deleteSelectedLines($selectedLines);
        }
        $info = array(
            'msg' => 'Success',
            'status' => 1
        );
        
        $this->outputJsonAndExit($info);
    }
    
    public function clientActionClean()
    {
        $this->_phplog->clean();
        
        $info = array(
            'msg' => 'Success',
            'status' => 1
        );
        $this->outputJsonAndExit($info);
    }
    
    public function clientActionUpdate()
    {
        $metadata = phplogMetadata::instance();
        
        $boTick = (int) $metadata->get('refresh-tick');
        $foTick = (int) $this->request('frontpage_refresh_timestamp');
        
        if ( $foTick != $boTick) {
            $info = array(
            	'response_log_end_line' => 'page_should_update', 
            	'data' => array()
            );
	        $this->outputJsonAndExit($info);
        }
        
        // Ajax way get log from $current_last_line
        $currentLastLine = $this->request('current_last_line');
        if ($currentLastLine !== null) {
            
            $currentLastLine = (int) $currentLastLine;
            $lines = $this->_phplog->getLogsFromLine($currentLastLine);
            
            if (is_numeric($lines)) {
                $info = array(
                	'response_log_end_line' => $lines, 
                	'data' => array()
                );
                $this->outputJsonAndExit($info);
            } else {
                
                $logs = array();
                for( $i = 0; $i < count($lines); $i++ ){
		
					$line = $lines[$i];
					
					$program = $line[0];
					$date = $line[1];
					$callerFile = $line[2][0]['file'];
					$callerLine = $line[2][0]['line'];
					$data = $line[3];
					if( $program == 'data.xml' ){
						$rows = phplogHelper::countXmlRows($data);
					}else{
						$rows = phplogHelper::countRows($data);
					}
					
					$logs[] = array($program, $date, $callerFile, $callerLine, $data, $rows);
				}
				
				$info = array(
					'response_log_end_line' => $currentLastLine + count($logs), 
					'data' => $logs,
                    'date'=>date('H:i',time())
				);
				$this->outputJsonAndExit($info);
            }
        }
    }
    
    public function clientActionBacktrace()
    {
        $line  = (int) $this->request('line');
        
        $traces = $this->_phplog->getTraceByLine($line);
        for($i = 0; $i < count($traces); $i++){
			$traces[$i]['args'] = htmlspecialchars($traces[$i]['args']);
			$traces[$i]['rows'] = phplogHelper::countRows($traces[$i]['args']);
			if( empty($traces[$i]['file']) ) $traces[$i]['file'] = '';
			if( empty($traces[$i]['line']) ) $traces[$i]['line'] = '';
        }
        
        $info = array(
        	'line'=>$line, 
        	'traces' => $traces
        );
        
        $this->outputJsonAndExit($info);
    }
    
    // updated version information from sourceforge
    public function clientActionVersion()
    {
        if (phplogVersion::updateVersionInfo()) {
            $info = array(
                'msg' => 'Version has been record!',
                'status' => 1
            );
        } else {
            $info = array(
                'msg' => 'Php ini configuration do not support url open!',
                'status' => 0
            );
        }
        $this->outputJsonAndExit($info);
    }
    
    // log javascript content to log file
    public function clientActionLog()
    {
        $jsonArray = $this->request('json_array');
        $type = trim($this->request('program'));
        
        if (empty($type)) {
            $type = 'javascript';
        }
        
        $map = array(
			'\\\\' => "\\",
			'\\r' => "\r",
			'\\n' => "\n",
			'\\v' => "\v",
			'\\t' => "\t",
			'\\"' => "\"",
			'\\f' => "\f"
		);
		$token = array_keys($map);
		$replace = array_values($map);
		$jsonObjects = array();
		for($i = 0; $i < count($jsonArray); ++$i){
			$obj = json_decode($jsonArray[$i]);
			
			if( is_array($obj) ){
				$obj = array( $obj );
			}else if(is_object($obj) ){
			
			// simple value
			}else{
				$obj = $jsonArray[$i];
				$obj = str_replace( $token, $replace, $obj); 
			}
			$jsonObjects[] = $obj;
		}
		
		// should compose more useful info for javascript call
		$backtrace = array(
			array(
				'file' => 'javascript.source.code.js',
				'line' => '',
				'function' => '__log',
				'args' => 'Called from javascript, no backtrace!'
			)
		);
		
		foreach($jsonObjects as $value){
			// show the user in firefox console panel
			print_r($value);
			echo "\r\n\r\n";
			if (!is_array($value)) {
			    $value = array($value);
			}
			$this->_phplog->log($value, $backtrace, $type);
		}
		
		$info = array(
			'msg' => 'Log success!',
		    'status' => 1
		);
		$this->outputJsonAndExit($info);
		
    }
    
    // @todo remove this function
    public function clientActionImage()
    {
		$program = trim($this->request('program'));
		if( strpos($program, 'data.') !== false ){
			$program = 'data';
		}
		if( strpos($program, 'php.') !== false ){
			$program = 'php';
		}
			
		$image = new phplogImage($program);
		$image->outputImage();
		exit;
    }
    
    public function clientActionDecode()
    {
        
        $data = trim($_REQUEST['data']);
        if (empty($data)) {
            $info = array(
    		    'msg' => 'Empty content provided!',
    		    'status' => 0
		    );
		    $this->outputJsonAndExit($info);
        }
		$decode = new phplogDecode($data);
		$decode->decode();
		$decode->log();
		
		$info = array(
		    'msg' => 'Success',
		    'status' => 1
		);
		$this->outputJsonAndExit($info);
    }
    
    public function clientActionGetxml()
    {
    	$line = (int) $this->request('line');
		$log = $this->_phplog->getLogByLine($line);
		header('content-type: text/xml');
		echo $log[3];
		exit;
    }
    
    public function clientActionSetting()
    {
        $data = $this->request('data');
        
        $refreshPerPage = $this->request('refresh_per_page');
        
        $metadata = phplogMetadata::instance();
        
        $metadata->set('render-types', $data);
        
        $metadata->set('refresh-per-page', $refreshPerPage);
        $info = array(
		    'msg' => 'Success',
		    'status' => 1
		);
		$this->outputJsonAndExit($info);
    }
    
    public function clientActionDefault()
    {
        $info = array('msg' => 'action is support for now!', 'status' => 0);
        $this->outputJsonAndExit($info);
    }
    
}




/**
 * Decode xml/json/ini/session/serialize data and log it
 * 
 * @todo re-build source code
 *
 */
class phplogDecode
{
    public $data = null;
	public $dataType = null;
	public $originalData = null;
	
	function __construct($data){
		$this->originalData = trim($data);
	}
	
	function decode(){
		
		$firstchar = $this->originalData[0];
		
		switch( $firstchar ){
			case '{':
			case '[':
				$data = $this->json_decode($this->originalData);
				if( $this->is_complex( $data ) ){
					$this->data = $data;
					$this->dataType = 'json';
					return true;
				}
				break;
			case '<':
				$data = $this->xml_decode($this->originalData);
				
				if( $data ){
					$this->data = $data;
					$this->dataType = 'xml';
					return true;
				}
				break;
		}
		
		if( $this->is_complex($data) ){
			return true;
		}
		$data = $this->serialize_decode($this->originalData);
		if( $this->is_complex($data) ){
			$this->data = $data;
			$this->dataType = 'serialize';
			return true;
		}
		$data = $this->session_decode($this->originalData);
		if( $this->is_complex($data) ){
			$this->data = $data;
			$this->dataType = 'session';
			return true;
		}
        if (strpos($this->originalData,'&') !== false && strpos($this->originalData,'=') !== false  && phplogHelper::countRows($this->originalData) == 0 ) {
            $data = $this->parse_str_decode($this->originalData);
            if ($this->is_complex($data)) {
                $this->data = $data;
                $this->dataType = 'url';
                return true;
            }
        }


		$data = $this->ini_decode($this->originalData);
		if( $this->is_complex($data) ){
			$this->data = $data;
			$this->dataType = 'ini';
			return true;
		}else{
			print "parse Ini error at line: ".$data;
		}
		return false;
	}
	
	function log(){
		
		if( $this->data && $this->dataType ){
			$decode_debug_backtrace = array(
				array(
					'file' => 'data.' . $this->dataType,
					'line' => '',
					'function' => '__log',
					'args' => 'Called from decode\'s functionality, no backtrace!'
				)
			);
			
			$phplog = phplog::instance();
			
			if($this->data && $this->dataType ){
				$phplog->log(array($this->data), $decode_debug_backtrace , 'data.'.$this->dataType);
			}
		}
	}
	
	function xml_decode($data){
		// @todo, xml validate here.
		if (simplexml_load_string($data)) {
			return $data;
		}
		return false;
	}
	
	function json_decode($data){
		return json_decode($data);
	}
	
	function session_decode($data){
		
		// session data must be a single line.
		// @todo, make sure about these situation.
		if( strpos($data, "\r") !== false || strpos($data, "\n") !== false ){
			return false;
		}
		
		session_start();
		session_decode($data);
		$data = $_SESSION;
		session_destroy();
		$keys = array_keys($data);
		$valid = false;
		foreach($keys as $key){
			if( ! is_numeric($key) ){
				$valid = true;
			}
		}
		
		if( $valid ){
			return $data;
		}
		return false;
	}
	
	function serialize_decode($data){
		return unserialize($data);
	}
	
	//
	// this must be the last pattern for data decoding, because many data can follow this style.
	//
	// compatibility with zend framework(php.ini) and ezini style.
	//
	// Notice: if there some error happens, we will not support to thing this data is ini.
	//
	function ini_decode($data){
		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);
		// explode each line
		$lines = explode("\n", $data);
		$count = count($lines);
		
		$ini = array();
		$current_section = null;
		for($i = 0; $i < $count; $i++){
			if (!isset($current_section)) {
				$current_section = '';
			}
			$line = trim($lines[$i]);
			$firstchar = $line[0];
			// follow international varirable name mechanism
			if(('a' <= $firstchar && $firstchar < 'z') || ( 'A' <= $firstchar && $firstchar <= 'Z') ) {
				// a variable
				$equalSignPos = strpos($line, "=");
				if( $equalSignPos !== false){
					$vName = trim( substr($line, 0, $equalSignPos) );
					$vValue = trim( substr($line, $equalSignPos + 1) );
					$dotPos = strpos($vName, ".");
					$leftBracketPos = strpos($vName, "[");
					
					// ezpublish style
					if(  $leftBracketPos !== false ){
						$rightBracketPos = strpos($vName, ']');
						
						if( $rightBracketPos !== false && $leftBracketPos < $rightBracketPos ){
							$firstKey = substr($vName, 0, $leftBracketPos);
							$secondKey = substr($vName, $leftBracketPos + 1, $rightBracketPos - $leftBracketPos - 1);
							if( ! $this->is_legal_name($firstKey) ){
								// name error
								return $i;
							}
							if( $secondKey != '' && ! preg_match("/^[\w\-\/\*]+$/", $secondKey) ){
								return $i;
							}
							// @FIXME
							// maybe that will be more [] matches in name, but for now only for ezpublish
							$vNameArr = array($current_section, $firstKey, $secondKey);
							$this->sequence_name_value($ini, $vNameArr, $vValue);
						}else{
							// name error
							return $i;
						}
					}
					// zend frame work, and php.ini style.
					else if( $dotPos !== false){
						if( preg_match("/^[\w\.\-]+$/", $vName) ){
							$vNameArr = explode(".", $vName);
							foreach ($vNameArr as $key){
								if( ! $this->is_legal_name($key) ){
									return $i;
								}
							}
							array_unshift($vNameArr, $current_section);
							$this->sequence_name_value($ini, $vNameArr, $vValue);
						}else{
							return $i;
						}
					// normal variable
					}else{
						if( $this->is_legal_name($vName) ){
							$ini[$current_section][$vName] = $vValue;
						}else{
							// name error
							return $i;
						}
					}
					
				// not '=', then must be array declare
				}else{
					$vName = $line;
					$vValue = '';
					$vName = preg_replace("/\s/", "", $line);
					// a array declare.
					if( strpos($vName, "[]") !== false ){
						$vName = substr($vName, 0, -2);
						if( $this->is_legal_name($vName) ){
							$ini[$current_section][$vName] = array();
						}else{
							return $i;
						}
					}else{
						// parse error
						return $i;
					}
				}
				
			}else if($firstchar == '['){
				$section_name = trim(substr($line, 1, -1));
				if( $this->is_legal_name($section_name) ){
					// because in a ini, override maybe occur
					// $ini[$section_name] = array();
					// a new section.
					$current_section = $section_name;
				}else{
					// error occur
					return $i;
				}
			}else{
				continue;
			}
		}
		return $ini;
	}
	
	//
	// all keys is in the sequence
	// <code>$arr = array('db', 'mysql', 'host');  $value = 'localhost';
	// should be array('db'=>array('mysql'=> array('host' => 'localhost'));
	//
	// not like array_merge_recursive, should consider override
	function sequence_name_value(&$target, $keys, $value){
		#$reversedKeys = array_reverse($keys);
		$lastKey = array_pop($keys);
		
		$arr = array($lastKey => $value);
		$ref = & $target;
		
		foreach ($keys as $key){
			if( !isset($ref[$key]) ){
				$ref[$key] = array();
			}
			$ref = & $ref[$key];
		}
		if( trim($lastKey) == '' ){
			$ref[] = $value;
		}else{
			$ref[$lastKey] = $value;
		}
	}
	
	function strip_whitespace($str){
		return preg_replace("/\s/", $str);
	}
	
	// private
	function is_legal_name($name){
		return preg_match("/^[\w\- ]+$/", $name);
	}
	
	// private
	function is_complex($data = null){
		if( empty($data) ){
			return false;
		}
		if( is_array($data) || is_object($data) ){
			return true;
		}
		return false;
	}
}




class phplogTemplate
{

	/**
     * Helper Function, Template engine
     * repleace a array key => value
     * replace a object properties => value
     *
     * @param string $template
     * @param array|object $params
     */
    public static function templateEval($template, $params)
    {
        $replace = array();
		
        if (is_object($params)) {
            $params = get_object_vars($params);
        }
        
		if( is_array($params) ){
			foreach( $params as $key => $value){
				$replace += array( '{' . $key . '}' => $value);
			};
		}
		return strtr($template, $replace);
    }
    
	/**
	 * Group template
	 *
	 * @return string
	 */
	public static function groupTemplate()
	{
		return <<<EOT
<div class="group" id="group_{end_line}" >
	<div class="group_info" onclick="toggle('group', {end_line});">
		<span>
			<input type="checkbox" name="delete_group[]" value="{end_line}" id="group_checkbox_{end_line}" 
			onclick="click_checkbox('group', {from_line}, {end_line});stop_bubble(event);" />
		</span>
		<span class="from_to_line">
			<b>[Group@{date}:{from_line}-{end_line}]</b>
		</span>
		
		<!-- reverse checkbox in this group -->
		<!--<span><a onclick="reverse_checkbox({from_line}, {end_line});stop_bubble(event);" />reverse</a></span>-->
		<span style="float: right;">
			[<a onclick="delete_lines({from_line}, {end_line});stop_bubble(event);">Delete</a>]
		</span>
		<span style="float: right;">
			[<a onclick="toggle_a_group({from_line}, {end_line});stop_bubble(event);" />Toggle</a>]
		</span>
	</div>
	<div id="group_data_{end_line}" class="group_data" style="display:none;">
		{log_data_html}
	</div>
</div>
EOT;
	}
	
	/**
	 * Editor active x link 
	 *
	 * @return string
	 */
	private static function _editorLinkTemplate()
	{
		$config = phplogConfig::getConfig();
		
		if (!$config['editor']['enabled']) {
			return "";
		}
		return <<<EOT
	<span>[<a onclick="open_this_file({line});">Edit</a>]</span>
	<input type="hidden" id="editor_line_{line}" value="{caller_line}" />
EOT;
	}
	
	/**
	 * A line entity template
	 *
	 * @return string
	 */
	public static function lineTemplate()
	{
		$editorLinkTemplate = self::_editorLinkTemplate();
		
		return <<<EOT
	<div class="line {parity}" id="line_{line}">
		<div class="line_info {parity}" onclick="toggle('line', {line});">
			<span>
				<input type="checkbox" name="delete_line[]" 
				id="line_checkbox_{line}" value="{line}" 
				onclick="click_checkbox('line', {line});stop_bubble(event);" />
			</span>
			<span><b style="color:#d07adf">[{line}@{date}]</b></span>
			<span>[<input class="line_file_info" id="editor_file_{line}" readonly="true" value="{caller_file}" onclick="this.select();" size="{size}" /><b style="color:#d07adf">@{caller_line}</b>]</span>
			$editorLinkTemplate
			{program}
			<span style="float:right;">
				[<a class="delete_a" onclick="delete_lines({line}, {line});stop_bubble(event);">Delete</a>]
			</span>
			<span style="float:right;">
				[<a onclick="toggle_backtrace({line});stop_bubble(event);">BackTrace</a>]
			</span>
		</div>
		<div class="line_data" id="line_data_{line}">
			<div class="trace off" id="trace_{line}"></div>
			<div><textarea readonly rows="{rows}">{data} </textarea></div>
		</div>
	</div>
EOT;
	}
	
	/**
	 * Line template, data xml type
	 *
	 * @return string
	 */
	public static function lineDataXmlTemplate()
	{
		$phpSelf = $_SERVER['PHP_SELF'];
		
		$editorLinkTemplate = self::_editorLinkTemplate();
		
		return <<<EOT
	<div class="line {parity}" id="line_{line}">
		<div class="line_info {parity}" onclick="toggle('line', {line});">
			<span>
				<input type="checkbox" name="delete_line[]" 
				id="line_checkbox_{line}" value="{line}" 
				onclick="click_checkbox('line', {line});stop_bubble(event);" />
			</span>
			<span><b>[Line:{line}]</b></span>
			<span>
				[<a class="delete_a" onclick="delete_lines({line}, {line});stop_bubble(event);">Delete</a>]
			</span>
			<span>
				[<a onclick="toggle_backtrace({line});stop_bubble(event);">BackTrace</a>]
			</span>
			<span><small>[{date}]</small></span>
			<span>[ <input class="line_file_info" id="editor_file_{line}" readonly="true" value="{caller_file}" onclick="this.select();" size="{size}" />]</span>
			<span><small>[{caller_line}]</small></span>
			$editorLinkTemplate
			{program}
		</div>
		<div class="line_data" id="line_data_{line}">
			<div class="trace off" id="trace_{line}"></div>
			<div>
				<iframe class="xml_iframe" style="height:{rows}em" src="{$phpSelf}?action=getxml&line={line}">
					<html><body bgcolor="transparent"></body></html>
				</iframe>
			</div>
		</div>
	</div>
EOT;
	}
	
	/**
	 * log trace template
	 *
	 * @return string
	 */
	public static function traceTemplate()
	{
		return <<<EOT
	<span class="trace_block">
		<span class="function">{caller_function}(<a onclick="show_window(this, event);" args="{args}" rows="{rows}">...</a>)</span>
		<span class="fileline">[{caller_file}] [{caller_line}]</span>
	</span>
EOT;
	}
	
	/**
	 * Trace Arrow Template
	 *
	 * @return unknown
	 */
	public static function traceArrowTemplate()
	{
		return <<<EOT
		<span class="trace_arrow">>></span>
EOT;
	}
	
	/**
	 * Window template
	 *
	 * @return string
	 */
	public static function windowTemplate()
	{
		return <<<EOT
	<div id="args_window" style="display:none">
		<textarea id="args_textarea" rows="{rows}"></textarea>
	</div>
EOT;
	}
	
	/**
	 * Help content
	 *
	 * @return string
	 */
	public static function helpContent()
	{
		return <<<EOT
Click any underline text will trigger a action:

From       : From line number input
To         : To line number input
Delete     : 
             1.Delete From->To Lines
             2.Delete selected Lines
             3.Delete a Group
             4.Delete a Line
Clean      : Clean current log file.
Refresh    : Refresh Page
Decode     : Decode data(like json,xml,ini,etc)
Toggle     : 
             1.Toggle all lines
             2.Toggle a group lines
...        : Arguments in a function call of BackStack
BackTrace  : generate the debug_backtrace()

All pointer cursor area is clickable,
Their behavior need you to digg, (^_^).

EOT;
	}
	
	/**
	 * About content
	 *
	 * @return string
	 */
	public static function aboutContent()
	{
		$version = phplogVersion::VERSION;
		return <<<EOT
                  PHPLog v{$version}
				
    Author    : DengZhiYi
    Contact   : 
                Email: altsee@gmail.com
                Skype: altsee
                Msn  : altsee@hotmail.com
    App Name  : PHPLog
    Version   : {$version}
    License   : GNU GENERAL PUBLIC LICENSE Version 3
    Website   : http://sourceforge.net/projects/phplog/
    Copyright : All rights reserved.
	
EOT;
	}
	
}




# Deal With image in PHP, (These images are not file, just base64 code)
class phplogImage
{
	public $language = 'php';
	public $mime;
	public $encodedContent = '';
	
	public $map;
	
	function __construct($lang){
		
		$this->language = $lang;
		
		
$map = array(
'javascript' => array( 'mime' => 'image/gif', 'encodedContent' => '71,73,70,56,55,97,50,0,10,0,247,0,0,20,134,124,116,182,196,28,186,172,60,142,140,4,162,140,124,222,220,76,190,172,36,138,140,36,162,148,172,238,236,124,246,228,76,214,196,4,174,156,4,150,140,60,166,164,20,162,140,20,150,140,76,202,196,92,166,172,140,222,212,52,186,172,148,242,236,36,150,140,36,178,172,60,178,172,100,214,204,52,198,188,4,170,152,148,250,244,116,238,220,20,174,162,4,158,145,52,150,148,20,142,124,92,186,180,76,166,172,20,162,164,20,154,164,100,202,204,100,226,220,172,198,220,52,166,148,68,186,180,4,162,156,140,234,236,116,214,212,20,158,137,36,158,140,28,142,136,76,198,204,52,138,140,204,234,236,132,250,236,60,174,172,4,150,148,20,170,156,52,194,188,164,242,248,52,174,172,132,234,232,44,186,175,60,154,156,36,170,160,12,182,164,92,202,204,92,178,172,52,190,196,44,150,148,164,254,252,28,174,164,100,234,220,156,238,228,132,226,220,44,146,144,172,254,244,140,246,232,12,174,162,68,166,168,28,162,156,28,150,148,84,210,204,60,186,172,44,178,168,76,178,180,100,222,220,156,250,248,12,158,145,12,142,140,100,210,204,108,226,226,12,162,156,148,206,212,12,162,148,116,230,216,84,194,184,44,162,148,92,226,212,84,202,192,156,242,244,60,178,180,60,206,188,12,170,158,20,174,172,20,170,164,52,170,159,148,234,228,148,182,188,36,194,180,44,138,132,92,214,196,12,150,132,20,162,156,20,154,142,92,174,176,140,230,236,52,190,175,148,242,252,36,154,144,108,214,212,60,202,180,116,238,236,52,158,156,92,198,196,76,174,172,68,194,196,140,234,244,132,210,212,28,158,148,28,146,136,188,218,228,132,194,188,220,246,252,188,246,244,76,146,148,108,178,180,76,154,156,188,254,252,52,146,156,52,162,172,140,242,246,84,214,204,84,166,156,212,238,252,60,150,140,52,182,172,156,238,252,148,222,228,44,158,140,124,230,228,28,170,164,36,190,172,124,234,228,28,158,164,124,218,212,84,194,200,140,250,244,12,154,152,20,142,140,68,178,188,124,234,244,20,134,140,76,194,188,36,166,164,28,166,164,108,206,204,132,238,244,68,158,156,44,150,156,12,174,172,44,138,148,132,198,204,132,246,236,68,174,169,180,254,252,108,222,212,108,210,208,172,238,252,4,170,164,4,158,156,20,158,148,36,158,156,28,170,156,12,158,156,44,162,164,92,174,188,60,202,196,100,198,196,28,170,180,4,166,148,36,166,156,60,170,156,148,246,244,60,182,164,52,202,196,148,254,249,20,178,164,52,154,153,20,146,132,92,190,188,20,158,156,100,230,224,68,190,182,4,166,159,140,238,232,116,218,220,52,142,148,164,246,252,132,238,236,44,190,180,44,154,140,12,178,164,68,170,167,60,190,180,44,182,169,156,254,252,108,230,228,12,166,159,84,206,196,44,142,140,12,154,140,108,218,220,52,166,164,84,218,212,84,170,164,84,198,204,20,146,148,124,226,220,4,154,140,76,206,196,36,182,172,100,206,204,4,154,148,60,158,156,28,178,164,28,166,156,28,154,148,12,146,140,148,210,212,12,166,148,20,178,172,20,166,156,108,182,180,140,254,244,68,182,188,20,138,140,28,186,180,4,162,148,76,190,180,4,174,164,20,162,148,20,150,148,140,222,220,52,186,180,148,242,244,36,150,148,100,214,212,44,0,0,0,0,50,0,10,0,0,8,255,0,83,180,201,33,1,149,50,101,177,98,173,32,161,229,96,194,13,188,54,108,120,18,65,200,49,19,64,108,144,192,198,5,161,178,21,188,148,149,216,245,73,209,10,143,188,24,172,88,1,239,152,139,57,28,146,140,115,199,132,23,182,50,202,180,48,96,224,142,203,6,124,101,236,177,90,181,205,202,6,75,177,240,125,64,53,236,195,202,136,216,62,240,226,66,192,222,10,38,30,184,112,225,225,234,86,167,100,71,100,120,49,96,76,156,34,59,89,68,233,139,64,170,137,38,79,5,246,60,82,210,101,210,135,15,202,176,1,153,167,43,147,63,115,97,92,253,235,102,68,221,180,2,94,204,45,35,117,128,148,146,120,225,248,68,26,161,224,5,27,49,192,188,84,144,241,39,66,13,74,243,64,33,105,193,110,206,140,65,39,68,105,19,199,231,10,130,5,61,250,33,49,197,106,10,17,98,134,16,121,26,18,109,201,43,7,125,18,65,224,183,136,24,60,46,53,132,85,115,65,48,31,25,108,30,182,193,123,246,202,198,7,43,119,102,160,24,113,228,231,65,122,89,176,0,255,224,84,169,79,15,16,191,156,204,114,1,109,209,148,103,254,158,24,168,210,76,84,4,7,139,230,184,217,210,174,26,5,89,216,132,129,134,56,230,104,225,193,6,4,52,81,129,26,129,124,16,11,78,225,92,3,6,4,202,72,211,15,49,14,8,115,6,39,127,72,17,73,28,165,156,2,72,50,59,176,194,130,54,32,88,67,1,4,232,88,195,8,4,180,224,1,76,8,59,232,82,3,16,31,216,243,69,23,135,228,64,77,8,31,48,1,67,22,11,52,80,6,19,197,229,177,132,58,111,192,144,129,29,14,172,82,7,12,247,44,193,10,33,130,196,145,6,21,180,172,131,204,60,235,12,144,195,4,223,248,33,73,28,19,8,113,65,54,192,216,160,65,2,170,132,162,66,15,114,116,48,201,31,212,56,19,71,33,136,240,129,134,8,153,248,80,75,2,1,68,134,76,49,150,224,128,13,9,239,96,131,205,6,56,41,131,74,48,103,240,34,0,23,102,64,195,3,19,43,68,85,201,29,107,64,87,78,61,101,148,145,78,57,23,248,194,65,53,82,88,130,205,10,178,232,52,48,6,59,109,84,195,9,23,86,124,180,146,50,246,40,179,193,73,227,128,116,16,47,231,124,128,15,47,165,218,115,23,47,178,56,5,210,56,31,156,131,198,38,78,112,114,23,54,188,218,19,16,0,59'),

'php' => array( 'mime' => 'image/gif', 'encodedContent' => '71,73,70,56,55,97,32,0,20,0,247,0,0,0,0,0,236,235,239,140,140,154,185,185,200,119,123,187,133,135,175,145,147,185,113,118,179,118,122,182,120,124,183,117,121,179,123,127,187,119,123,180,115,119,172,107,110,155,133,137,192,117,119,145,69,70,81,92,93,106,123,126,157,106,108,127,53,54,57,255,255,255,2,2,0,9,9,6,4,4,3,19,19,15,14,13,10,30,30,30,6,6,6,255,255,255,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,44,0,0,0,0,32,0,20,0,0,8,255,0,45,8,28,72,176,160,193,131,8,19,42,92,104,208,131,135,0,1,6,12,48,96,160,128,197,2,6,6,64,100,104,225,97,196,137,11,24,48,120,240,64,164,72,5,13,28,76,200,24,0,225,67,144,10,14,40,152,185,128,130,132,7,10,76,50,72,144,64,129,3,8,3,60,20,140,136,243,0,129,4,71,17,32,48,128,129,195,131,163,34,145,18,152,138,192,1,133,150,2,3,24,232,153,160,0,132,175,19,10,44,152,112,161,194,87,177,5,38,124,133,80,224,193,130,6,17,6,88,208,106,242,129,4,12,26,52,52,45,64,1,0,94,188,20,38,108,216,160,97,240,205,158,17,34,54,16,137,224,65,133,12,2,10,72,0,80,97,114,224,201,17,250,74,240,202,225,194,132,4,11,172,22,48,185,84,131,83,2,76,57,84,192,96,96,1,4,0,18,38,79,32,240,32,2,0,8,4,24,44,136,224,192,100,130,9,0,34,144,124,29,193,52,73,203,21,52,24,120,96,128,195,134,2,8,116,71,152,80,183,111,4,8,18,10,11,200,32,188,54,0,1,27,56,64,156,160,80,1,118,73,233,3,26,36,24,105,27,195,6,12,21,4,64,216,112,211,49,7,1,29,58,12,230,64,225,65,244,208,87,81,215,88,121,97,181,181,192,69,8,28,104,128,102,21,45,39,210,2,9,84,208,82,0,19,40,96,128,105,6,40,133,192,76,9,68,167,64,99,17,92,0,193,2,74,61,168,64,92,3,5,96,81,96,33,233,228,226,88,20,20,144,147,110,53,161,72,144,7,3,200,168,0,79,46,234,180,0,132,160,49,224,192,117,88,25,20,81,1,14,52,48,35,79,76,174,135,146,3,18,80,32,64,145,9,189,52,81,90,107,173,37,128,0,26,9,197,209,151,11,5,4,0,59'),

'data' => array( 'mime' => 'image/jpg', 'encodedContent' => '255,216,255,224,0,16,74,70,73,70,0,1,1,1,0,96,0,96,0,0,255,219,0,67,0,8,6,6,7,6,5,8,7,7,7,9,9,8,10,12,20,13,12,11,11,12,25,18,19,15,20,29,26,31,30,29,26,28,28,32,36,46,39,32,34,44,35,28,28,40,55,41,44,48,49,52,52,52,31,39,57,61,56,50,60,46,51,52,50,255,219,0,67,1,9,9,9,12,11,12,24,13,13,24,50,33,28,33,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,50,255,192,0,17,8,0,14,0,51,3,1,34,0,2,17,1,3,17,1,255,196,0,31,0,0,1,5,1,1,1,1,1,1,0,0,0,0,0,0,0,0,1,2,3,4,5,6,7,8,9,10,11,255,196,0,181,16,0,2,1,3,3,2,4,3,5,5,4,4,0,0,1,125,1,2,3,0,4,17,5,18,33,49,65,6,19,81,97,7,34,113,20,50,129,145,161,8,35,66,177,193,21,82,209,240,36,51,98,114,130,9,10,22,23,24,25,26,37,38,39,40,41,42,52,53,54,55,56,57,58,67,68,69,70,71,72,73,74,83,84,85,86,87,88,89,90,99,100,101,102,103,104,105,106,115,116,117,118,119,120,121,122,131,132,133,134,135,136,137,138,146,147,148,149,150,151,152,153,154,162,163,164,165,166,167,168,169,170,178,179,180,181,182,183,184,185,186,194,195,196,197,198,199,200,201,202,210,211,212,213,214,215,216,217,218,225,226,227,228,229,230,231,232,233,234,241,242,243,244,245,246,247,248,249,250,255,196,0,31,1,0,3,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,1,2,3,4,5,6,7,8,9,10,11,255,196,0,181,17,0,2,1,2,4,4,3,4,7,5,4,4,0,1,2,119,0,1,2,3,17,4,5,33,49,6,18,65,81,7,97,113,19,34,50,129,8,20,66,145,161,177,193,9,35,51,82,240,21,98,114,209,10,22,36,52,225,37,241,23,24,25,26,38,39,40,41,42,53,54,55,56,57,58,67,68,69,70,71,72,73,74,83,84,85,86,87,88,89,90,99,100,101,102,103,104,105,106,115,116,117,118,119,120,121,122,130,131,132,133,134,135,136,137,138,146,147,148,149,150,151,152,153,154,162,163,164,165,166,167,168,169,170,178,179,180,181,182,183,184,185,186,194,195,196,197,198,199,200,201,202,210,211,212,213,214,215,216,217,218,226,227,228,229,230,231,232,233,234,242,243,244,245,246,247,248,249,250,255,218,0,12,3,1,0,2,17,3,17,0,63,0,245,91,75,118,213,89,230,154,87,75,68,145,227,72,162,98,165,202,146,164,179,14,113,144,112,7,165,55,88,77,15,67,211,141,229,198,146,183,43,230,36,97,18,53,118,102,118,10,62,249,3,169,29,77,49,146,239,79,146,89,236,94,38,137,201,146,75,121,114,6,238,229,88,103,25,238,48,70,121,174,79,196,158,52,211,181,59,6,211,157,46,237,174,35,154,57,55,172,43,42,130,140,27,24,222,164,142,61,171,169,42,146,179,166,244,242,211,250,103,35,116,163,117,85,123,222,122,255,0,72,233,224,191,240,155,195,59,222,216,105,250,107,219,205,228,77,21,244,80,198,81,241,144,51,146,167,35,145,130,120,161,245,31,8,71,174,91,233,47,105,166,137,174,45,197,196,82,24,162,242,220,19,128,1,234,88,245,0,14,69,121,237,207,136,108,174,18,121,63,180,117,24,238,238,167,243,110,158,59,109,145,202,161,10,42,97,39,86,10,7,63,124,228,245,205,62,199,196,90,102,153,46,153,45,165,205,234,203,103,98,108,93,158,205,27,204,77,193,183,47,239,126,86,200,239,184,123,83,229,196,119,127,127,151,175,113,115,225,187,47,187,207,211,177,233,182,81,248,95,83,15,253,159,30,147,115,229,145,188,218,136,219,110,122,103,111,74,150,109,22,221,87,117,139,189,164,163,149,218,196,161,246,41,156,17,244,193,247,175,49,240,231,138,52,237,19,81,184,190,186,187,212,175,166,158,8,225,102,104,126,99,180,147,184,151,153,249,57,232,48,7,96,43,184,179,215,110,245,235,127,51,79,84,181,128,245,150,95,154,79,193,122,3,238,73,250,80,227,93,106,222,158,110,224,165,135,110,209,90,244,178,179,254,190,101,251,57,218,230,216,59,168,89,21,154,55,80,114,3,43,21,56,246,200,52,83,173,173,227,181,183,72,35,206,213,29,73,201,39,185,39,185,39,154,43,150,86,114,124,187,29,144,77,69,115,110,127,255,217'),

);

		
		$this->map = $map;

		$this->mime = $this->map[$this->language]['mime'];
		$this->encodedContent = $this->map[$this->language]['encodedContent'];
		
	}
	
	// public
	public function outputImage(){
		
		$this->_outputHeaders();
		
		$this->_outputImageContent();
	}

	// private
	private function _outputHeaders(){
	
		header("content-type: " . $this->mime, true);
		header("Expires: " . date('r',time() + 86400000), true);
		header("Pragram: public", true);
		header("Cache-Control: public", true);
	}
	
	// private
	private function _outputImageContent(){
		// output image content
		
		$output = '';
		$encodedContent = explode(',', $this->encodedContent);
		// output image content
		for($i = 0, $length = count($encodedContent); $i < $length; $i++){
			$output .= chr((int)$encodedContent[$i]);
		}
		print $output;
	}
	
	
}



// for javascript __log client script
if( strtolower(str_replace("\\", "/", __FILE__)) == strtolower($_SERVER['SCRIPT_FILENAME']) ){
	
	$scriptName = trim($_SERVER['SCRIPT_NAME']);
	// maybe, http_referer is not reliable
	$httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	
	if (isset($_REQUEST['action'])) {
	    $action = $_REQUEST['action'];
	} else {
	    $action = '';
	}
	
	// need find a way to distinguish that this program is called by <script> tag, not by link referer!
	if(empty($action) AND $httpReferer != ''){
		
		$shouldRetJs = false;
		
		$scriptNameLength = strlen($scriptName);
		$httpRefererLength = strlen($httpReferer);
		
		for($i = 0; $i < $scriptNameLength; $i++){
			$s = $scriptName[$scriptNameLength - $i - 1];
			$h = $httpReferer[$httpRefererLength - $i - 1];
			if( $s != $h ){
				$shouldRetJs = true;
				break;
			}
		}
		if( $shouldRetJs ){
			
// output __log javascript!
header('Content-type: text/javascript');

$php_self = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$jslogAjaxMethod = phplogConfig::get('jslogAjaxMethod');
echo "var phplog_php_self = '$php_self';\n";
echo "var phplog_jslog_ajax_method = '$jslogAjaxMethod';";

?>


/**
 * javascript __log client
 *
 * @author altsee@gmail.com
 * @since 2009/09/16
 */
(function(){
	
	// configure for server who will receive the ajax's request
	//--------------------------------------------------------------------
	//
	var php_self = phplog_php_self;
	var ajax_method = phplog_jslog_ajax_method;
	//
	//--------------------------------------------------------------------
	
	//********************************************************************
	//
	// Core Code,Do Not Change
	//
	//********************************************************************
	
	var isOpera = navigator.userAgent.indexOf('Opera') > -1;
	var isIE = navigator.userAgent.indexOf('MSIE') > 1 && !isOpera;
	var isMoz = navigator.userAgent.indexOf('Mozilla/5.') == 0 && !isOpera;

	// Browser detection mostly taken from prototype.js 1.5.1.1.
	var is_ie     = !!(window.attachEvent && !window.opera);
	var is_khtml  = !!(navigator.appName.match("Konqueror") || navigator.appVersion.match("KHTML"));
	var is_gecko  = navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1;
	var is_ie7    = navigator.userAgent.indexOf('MSIE 7') > 0;
	var is_opera  = !!window.opera;
	var is_webkit = navigator.userAgent.indexOf('AppleWebKit/') > -1;

	var cache = [];
	var enable = true;
	var enable_dom_ready_ajax = true;
	var dom_load_completed = false;
	var debug = function(){};
	var block_code_time = new Date().getTime();


	// for now, only support ie and firefox!
	if ( !isIE && !isMoz && !is_gecko && !is_ie ){
		enable = false;
	}
	
		//
	// author <altsee@gmail.com>
	//
	function ajax(options){
		
		var isOpera = navigator.userAgent.indexOf('Opera') > -1;
		var isIE = navigator.userAgent.indexOf('MSIE') > 1 && !isOpera;
		var isMoz = navigator.userAgent.indexOf('Mozilla/5.') == 0 && !isOpera;
		
		var request = window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest();
		
		// default value for each ajax options;
		var method = 'post';
		var callback = null;
		var asyn = true;
		var values = null;
		var url  = null;
		
		if( options != null ){
			for( var k in options ){
				var v = options[k];
				switch( k ){
					case 'method':
						var method = (v + '' ).toLowerCase();
						break;
					case 'callback':
						callback = v;
						break;
					case 'asyn':
						if( v ){
							asyn = true;
						}else{
							asyn = false;
						}
						break;
					case 'data':
						values = v;
						break;
					case 'url':
						url = v;
						break;
					default:
						// error options
						break;
				}
			}
		}
		
		var pairs = [];
		
		// compose the request data;
		for(var p in values){
			var obj = values[p];	
			if( typeof(obj) == 'object' ){
				var c = obj.constructor;
				// Array or hash Array(object)
				if( c != null && (c == Array || c == Object) ){
					for( var z in obj ){
						pair = encodeURIComponent( p + '[' + z + ']' ) + '=' + encodeURIComponent( obj[z] );
						pairs.push( pair );
					}
				}else{
					pair = encodeURIComponent(p) + encodeURIComponent( obj );
					pairs.push( pair );
				}
			}else{
				pair = encodeURIComponent(p) + '=' + encodeURIComponent(obj);
				pairs.push(pair);
			}
		}
		var data = pairs.join('&');
        if (method == 'crossdomain') {
           url = url.replace('?', '');
           url = url + '?' +  data + '&ajaxaccessfrom=crossdomain';
           var objectHead = document.getElementsByTagName('head');

           var objectScript = document.createElement('script');
           objectScript.src = url;
           objectScript.type = 'text/javascript';
           objectHead[0].appendChild(objectScript);
           return true;
        }
	
		var result;
		
		if( asyn || (! asyn && ! isMoz) ){
			request.onreadystatechange = function(){
				if( request.readyState == 4 ){  // If the request is finished
					if(request.status == 200){  // If it was successful
						result = request.responseText;
						if( callback ){
							result = callback(result);  // Display the server's response
						}
					}
				}
			};
		}
		
		if( method == 'get' ){
			url = url.replace('?', '');
			url = url + '?' + data;
		}
		
		request.open( method.toUpperCase(), url, asyn);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		
		if( method == 'post' ){
			request.send( data );
		}else{
			request.send( null );
		}
		
		// @see, http://hi.baidu.com/snowleung/blog/item/2bbad188cbdd7d9da5c2728f.html
		if(  ! asyn && isMoz  ){
			result = request.responseText;
			if( callback ){
				result = callback( result );
			}
		}
		return result;
	}


	debug.prototype = {
		object_layer_stack:[],
		object_layer: 0,
		obj_first_flag: 1,
		
		initCache: function(){
			if( ! enable ){
				return false;
			}
			for( var i = 0; i < arguments.length; i++ ){
				cache.push( this.wrap_object(arguments[i]) );
			}
			
			if( enable_dom_ready_ajax ){
				if( dom_load_completed )
					this.log();
			}else{
				this.log();
			}
		},
		
		log: function(){
			if( ! enable ){
				return false;
			}
			
			dom_load_completed = true;
			if( cache.length == 0 ) return;
			var json_array = [];
			// because ajax method has already used encodeURIComponent to ensure the data sent to serve correct,
			// we need revert at first, leg ajax() function encode then.
			for(var i = 0; i < cache.length; i++){
				json_array.push( decodeURIComponent( cache[i] ) );
			}
			
			var result = ajax({
				url: php_self, 
				method: ajax_method, 
				data:{
					action: 'log', 
					program: 'javascript', 
					json_array: json_array
				},
				asyn: false
			});
			delete cache;
			cache = [];
			
			return result;
		},
		
		// wrap this object using json format!
		wrap_object: function(obj){
			// init some global status and flag
			this.object_layer_stack=[];
			this.object_layer = 0;
			this.obj_first_flag = 1;
			this.object_stack = [];
			
			var cache_function = this.initCache.caller;
			var c = cache_function.caller;
			
			
			// c();
			//alert( c.arguments.callee );
			
			// how to get object context!??? who execute this function
			/*
			for( var k in top_obj ){
				
				if( top_obj[k] == c){
					//alert(k);
				}
				
			}
			*/
			/*
			var sc = c.toString();
			var m = sc.match(/\s*function (.*)\(/);
			if( m != null ){
				return 'object';
			}
			*/
			
			//while(backtrace.caller != null){
				
			//}
			var c = obj.constructor;
			
			
			return this.encode(obj);
		},
		
		/**
		 * @param obj, current encoded obj
		 * @param string property, need display prop name
		 * @param boolean use_array_style, need display like arr, not object style
		 */
		encode: function(obj, property, use_array_style){
			// track if the object is recursive for now!
			for( var z = 0; z < this.object_stack.length; z++ ){
				// simple object like string, number, undefined, boolean, null, must not been recursion.
				if( obj === this.object_stack[z] && !/string|number|undefined|boolean/.test(typeof(obj) && obj != null) ){
					if( use_array_style ){
						return '"***RECURSION***"';
					}
					return '"' + property + '":' + '"***RECURSION***"';
				}
			}
			this.object_stack.push(obj);
			
			var i = 0;
			var j = 0;
			var val = '';
			var special_properties = null;
			
			var type = this.getType(obj);
			//if( this.obj_first_flag == 1 ) alert( type );
			
			if( property != null ){
				if( use_array_style ){
					prefix = '';
					suffix = '';
				}else{
					// not need for now;
					prefix = '"' + property + '":';
					suffix = '';
				}
			}
			else{
				// this should been accessed once in every object encode!
				prefix = '';
				suffix = '';
			}
			
			
			// deal with BOM
			if( type == 'frame' ){
				type = 'special_window_element';
				special_properties = ['closed','defaultStatus', 'document','history','innerheight','innerwidth','length','location','name','outerheight','outerwidth','pageXOffset','pageYOffset','screen','status','screenLeft','screenTop','screenX','screenY'];
			}
			if ( type == 'navigator' && obj == window.navigator ) {
				type = 'special_window_element';
				special_properties  = ['appName', 'appCodeName', 'appVersion', 'appMinorVersion', 'browserLanguage', 'cookieEnabled', 'cpuClass', 'onLine', 'platform', 'systemLanguage', 'userAgent', 'userLanguage'];
			}
			
			// @todo, need consider frames[], opener.....
			if( type == 'window' && obj == window ){
				type = 'special_window_element';
				special_properties  = ['closed','defaultStatus','document','history','innerheight','innerwidth','length','location','name','navigator','outerheight','outerwidth','pageXOffset','pageYOffset','screen','status','screenLeft','screenTop','screenX','screenY'];	
			}
			
			if( type == 'htmldocument' ){
				type = 'special_window_element';
				special_properties = [/*'body',*/'cookie','domain','lastModified','referrer','title','url'];
			}
			
			// fix recursive styleSheets in firefox
			if( isMoz && type == 'cssstylesheet' ){
				type = 'string';
				var rules = obj.cssRules;
				var tmp = '';
				for( var k = 0; k < rules.length; k++ ){
					var rule = rules[k];
					// This is the text form of the rule
					tmp += rule.selectorText + ' { ' + rule.style.cssText + ' }' + "\n";
				}
				obj = tmp;
			}
			
			// deal with BOM
			// special_window_element, like window.history, window.screen, need afford special_properties to access this object in IE
			switch( obj ){
				case window.document:
					type = 'special_window_element';
					special_properties = [/*'body',*/'cookie','domain','lastModified','referrer','title','url'];
					break;
				case window.screen:
					type = 'special_window_element';
					special_properties = ['availHeight','availWidth','bufferDepth','colorDepth','deviceXDPI','deviceYDPI','fontSmoothingEnabled','height','logicalXDPI','logicalYDPI','pixelDepth','updateInterval','width'];
					break;
				case window.history:
					type = 'special_window_element';
					special_properties = ['length'];
					break;
				default:
					break;
			}
			
			// regexp|date|function|domelement
			switch( type ){
				case 'regexp':
					val += prefix + '"' + this.addslashes(obj) + '"' + suffix;
					break;
				case 'date':
					val += prefix + '"' + this.addslashes(obj) + '"' + suffix;
					break;
				case 'function':
					var t = type;
					// __log function directly.
					if( property == null ){
						val += prefix + '"' + obj.toString() + '"' + suffix;
					// a property of a object or a array, should not been access for now!
					}else{
						var fn_body = obj.toString();
						var m = fn_body.match(/\s*function\s*(.*)\{/);
						var fn_name = m[1];
						val += prefix + '"' + this.addslashes( fn_name ) + '{...}"' + suffix;
					}
					break;
				case 'domtext':
					val += prefix + '"' + this.addslashes( obj.nodeValue ) + '"' + suffix;
					break;
				case 'domelement':
					// fix bug in ie, script tag
					if( isIE && obj.nodeName.toLowerCase() == 'script' ){
						if( this.trim( obj.innerHTML ).length == 0 ){
							var src = obj.getAttribute('src');
							val += prefix + '"' + this.addslashes('<script src="' + src + '"></script>') + '"' + suffix;
						}else{
							val += prefix + '"' + this.addslashes('<script>' + obj.innerHTML + '</script>' ) + '"' + suffix;
						}
						break;
					}
					
					// fix recursive when access styleSheets
					if( isMoz && obj.nodeName.toLowerCase() == 'style' ){
						val += prefix + '"' + this.addslashes('<style></style>') + '"' + suffix;
						break;
					}
					
					var clone_obj = obj.cloneNode(true);
					var jslog_div = document.getElementById('__jslog_div');
					if( jslog_div == null ){
						var jslog_div = document.createElement('div');
						jslog_div.id = '__jslog_div';
						jslog_div.style.display = 'none';
						document.body.appendChild( jslog_div );
					}
					jslog_div.innerHTML = '';
					jslog_div.appendChild( clone_obj );
					
					val += prefix + '"' + this.addslashes( jslog_div.innerHTML ) + '"' + suffix;
					document.body.removeChild( document.body.lastChild );
					/*
					var attrs = obj.attributes;
					// actually this must be element
					var nodeContent = (obj.nodeType == 1) ? (obj.innerHTML) : (obj.innerText);
					
					val += prefix + '"<' + nodeName + '';
					if( attrs.length ){
						for( var i = 0; i < attrs.length; ++i ){
							// sigh, ie has problem when using attributes, will access all prosible key in object
							if( !(/undefined|null/.test( typeof(attrs[i].nodeName) )) && !(/undefined|null|^$/.test( attrs[i].nodeValue )) ){
								val += ' ' + attrs[i].nodeName +'=' + this.addslashes( '"' + attrs[i].nodeValue + '"');	
							}
						}
					}
					val += '>';
					val += this.addslashes( nodeContent );
					val += '<' + nodeName + '>';
					val += '"' + suffix;
					*/
					break;
			}
			// object|array
			if( /object|event|array/.test(type) ){
				this.obj_first_flag = 0;
				
				// array
				if( /array/.test(type) ){
					this.object_layer_stack.push('array');
					if( j < 1 ){
						val = prefix + '[';
						j++;
					}
					var elements = new Array();
					for( var k = 0; k < obj.length; k++ ){
						var property_type = this.getType(obj[k]);
						if ( property_type != 'function' )
							elements.push( this.encode(obj[k], k, 1) );
					}
					val += elements.join(',');
					elements = null;
					delete elements;
				// object
				}else{
					this.object_layer_stack.push('object');
					if( j < 1 ){
						val = prefix + '{';
						j++;
					}
					
					var elements = new Array();
					var prop;
					for( prop in obj ){
						try{
							var property_type = this.getType(obj[prop]);
						}catch(e){
							var property_type = 'function';
						}
						if ( property_type != 'function' )
							elements.push( this.encode(obj[prop], prop, 0) );
						
					}
					val +=  elements.join(',');
					elements = null;
					delete elements;
				}
				
				var _tmp_object_type = this.object_layer_stack.pop();
				
				
				// array should use array []
				if( _tmp_object_type == 'array' ){
					val += ']';
				// object should use {}
				}else{
					val += '}';
				}
				
				val += suffix;
				this.object_stack.pop();
				return val;
				
				// i don't know why this situation happen
				//if( val[val.length-1] != ',' ){
				//	val = '"":""';
				//	return val;
				//}
			}
			
			if( /special_window_element/.test(type) && special_properties.length > 0){
				this.obj_first_flag = 0;
				if( j < 1 ){
					val = prefix + '{';
					j++;
				}
				
				var elements = new Array();
				var prop;
				for( var k = 0; k < special_properties.length; k++ ){
					prop = special_properties[k];
					var property_type = this.getType(obj[prop]);
					if ( property_type != 'function' )
						elements.push( this.encode(obj[prop], prop, 0) );
				}
				val +=  elements.join(',');
				elements = null;
				delete elements;
				
				val += '}';
				
				val += suffix;
				this.object_stack.pop();
				
				return val;
			}
			
			
			// number|string|boolean|undefined|null
			if( j == 0 ){
				if( /string|number|undefined|boolean/.test(typeof(obj)) || obj == null ){
					val += prefix + '"' + this.addslashes(obj) + '"' + suffix;
				}
			}
			
			// @todo, i don't know why this situation happen, make sure of this situation, 
			// @desc, this situation happen in that type not matched
			if( val.length == 0){
				alert( 'Type,{' + type + '},e not matched! Or not implemented the corresponding function not exists!' );
				this.object_stack.pop();
				return '"":""';
			}
			if( this.obj_first_flag ){
				val = val.substr(1, val.length - 2);
				this.obj_first_flag = 0;
			}
			this.object_stack.pop();
			
			return encodeURIComponent(val);
		},
		
		addslashes: function(obj){
			if( obj == null ) return '';
			if( obj.toString == null )	return '';
			var str;
			str = obj + '';
			
			//str = str.replace(/\\/g, '____underline____');
			str = str.replace(/\\/g, '\\\\');
			str = str.replace(/\v/g, '\\v');
			str = str.replace(/\t/g, '\\t');
			str = str.replace(/\r/g, '\\r');
			str = str.replace(/\n/g, '\\n');
			str = str.replace(/\"/g, '\\"');
			
			str = str.replace(/\f/g, '\\f');
			
			// str = str.replace(/\b/g, '\\b');
			// str = str.replace(/\'/g, '\\\'');
			// str = str.replace(/\x0[0-9]|\x1[a-f]/gi, ''); 
			// str = str.replace(/\{/g, '____brace____');
			// str = str.replace(/\[/g, '____square____');
			
			// old legacy
			/*
			str = str.replace(/\v/g, '____vtable____');
			str = str.replace(/\t/g, '____table____');
			str = str.replace(/\r/g, '____carriage____');
			str = str.replace(/\n/g, '____newline____');
			str = str.replace(/\\"/g, '&quot;');
			str = str.replace(/\"/g, '____quot____');
			str = str.replace(/\\'/g, '&#039;');
			
			str = str.replace(/\{/g, '____brace____');
			str = str.replace(/\[/g, '____square____');
			*/
			return str;
			//return this.filter_broken_json_char(str);
		},
			
		getType: function(obj){
			var t = typeof( obj );
			
			if( t == 'function' ){
				var f = obj.toString();
				if( ( /^\/.*\/[gi]??[gi]??$/ ).test(f) ){
					return 'regexp';
				}else if( (/^\[object.*\]$/i).test(f) ){
					t = 'object';
				}
			}
			if( t != 'object' ){
				return t;
			}
			
			if( obj == window.navigator ){
				return 'navigator';
			}
			
			// @todo, consider this clear in firefox
			//*
			if( window.frames != null ){
				for( var i = 0; i < window.frames.length; i++ ){
					if(window.frames[i] == obj){
						return 'frame';
					}
				}
			}
			//*/
			
			/*
			//@todo, consider frame in firefox clear, window === window.frames .......
			if( window.frames == obj ){
				return 'array';
			}
			*/
			
			
			switch( obj ){
				case null:
					return 'null';
				case window:
					// this maybe frame or window in firefox
					if(obj.frameElement == null)
						return 'window';
					return 'frame';
				case document:
					return 'document';
				case window.event:
					return 'event';
			}
			if( window.event && (event.type == obj.type) ){
				return 'event';
			}
			
			var c = obj.constructor;
			if( c != null ){
				switch( c ){
					case Array:
						t = 'array';
						break;
					case Date:
						return 'date';
					case RegExp:
						return 'regexp';
					case Object:
						t = 'object';
						break;
					case ReferenceError:
						return 'error';
					default:
						// function like class, object.
						var sc = c.toString();
						var m = sc.match(/\s*function (.*)\(/);
						if( m != null ){
							return 'object';
						}
				}
			}
			
			
			var nt = obj.nodeType;
			if( nt != null ){
				switch( nt ){
					case 1:
						// @todo, why need adjust this?
						return 'domelement';
						if( obj.item == null ){
							return 'domelement';
						}
						break;
					case 3:
						return 'domtext';
				}
			}
			
			
			if( obj.toString != null ){
				var ex = obj.toString();
				var am = ex.match(/^\[object (.*)\]$/i);
				if( am != null ){
					var am = am[1];
					switch( am.toLowerCase() ){
						case 'event':
							return 'event';
						case 'nodelist':
						case 'htmlcollection':
						case 'elementarray':
							return 'array';
						case 'htmldocument':
						// window.document, will not been accessed, because the previous code has judge this condition.
							return 'htmldocument';
						case 'cssstylesheet':
							return 'cssstylesheet';
					}
				}
			}
			
			// should test tag name like div, input, others
			if( obj.tagName != null ){
				return 'domelement';
			}
			
			// function.arguments in firefox and ie, is 'object', this need been adjusted
			if( this.getType.caller.caller.caller.caller.caller != null && this.getType.caller.caller.caller.caller.caller.arguments == obj ){
				return 'array';
			}
			
			// when access this layer, some element maybe is array-like-object,
			// for example, document.getElementsByTagName('div') in ie is 'object', in firefox is 'array'
			// like jQuery('div')
			// @todo, how to do?
			if( t == 'object' && typeof(obj) == 'object' && 'length' in obj ){
			
				return 'array';
				/*
				var array_adjust = 1;
				for( var i = 0; i < obj.length; i++ ){
					// need consider this condition!
					if( obj[i] == undefined ){
						array_adjust = 0;
					}
				}
				if ( array_adjust ) {
					return 'array';
				}
				*/
			}
			
			return t;
		},
		
		trim: function(str){
			var str = str + '';
			return str.replace(/^\s*|\s*$/, '');
		}
	};
	
	
	var __log = window.__log = function(){
		for(var i = 0; i < arguments.length; i++){
			debug.prototype.initCache(arguments[i]);
		}
	};

	var __log_start = window.__log_start = function(){
		var t = new Date();
		block_code_time = t.getTime();
	};

	var __log_stop =  window.__log_stop = function(){
		var t = new Date();
		var diff = t.getTime() - block_code_time;
		cache.push('[Js]This Block Code Used Time : ' + diff + ' (ms) ');
		block_code_time = t.getTime();

		debug.prototype.initCache();
	};

	if( enable_dom_ready_ajax ){
		if (window.addEventListener){
			window.addEventListener('load', debug.prototype.log, false);
		}else if (window.attachEvent){
			window.attachEvent('onload', debug.prototype.log);
		}else{
			window.onload = debug.prototype.log;
		}
	}
})();


<?php

			exit;
		}
	}
}

$phplogClient = new phplogClient();
$phplogClient->handle();

// expire this page always
// do way real need these, maybe javascript, css can been hold?
phplogHelper::expireHeaders();

$phplog = phplog::instance();
$config = phplogConfig::getConfig();
$metadata = phplogMetadata::instance();

// Do not allow cache this page

$logs = $phplog->parse();

$page_end_line = count($logs);

$version_store_in_engine = phplogVersion::VERSION;
$software_need_upgrade = phplogVersion::compareVersion($metadata->get('version')); 
$last_version_check_date = $metadata->get('version-check-date');

$php_self = $_SERVER['PHP_SELF'];
$backoffice_refresh_timestamp = $metadata->get('refresh-tick');
$refresh_per_page = $metadata->get('refresh-per-page');

?>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>PhpLog</title>
<style type="text/css" >
body,html{
font-family: "Courier New", Verdana;
font-size: 14px;
margin: 0px;
padding: 0px;
background-color: #d5fad5;
}

/* general setting */
*{
margin: 0px;
padding: 0px;
font-family: "Courier New", Verdana;
}
a{
text-decoration: underline;
cursor: pointer;
}

#toolbar{
z-index: 10;
position:fixed !important; 
top: 0px;
position:absolute; 
top:expression(offsetParent.scrollTop);
width:100%;
text-align: center;
}
#tools{
width: 100%; 
height: 30px;
overflow: hidden; 
font-size: 14px;
background-color: #e0811b;
padding-top:5px;
text-align: center;
}
#toolbar lable{
height: 30px;
}
#toolbar input{
padding-top: 5px;
}
#tools a{
padding-right: 10px;
}
#tools .help{
cursor: help;
}

/* all logs container */
#container{
margin-top: 34px;
}

/* group style setting */
.group{
border: 4px solid #d9534f;
margin-bottom: 1px;
}
.group_info{
background: #d9534f none repeat scroll 0 0;
cursor: pointer;
font-size: 15px;
height: auto;
padding-bottom: 5px;
padding-left: 10px;
padding-top: 5px;
}
.from_to_line{

}
.group_info span{
font-size: 12px;
}
.group_data{
}

/* log style setting! */
.line{
margin-bottom: 0;
position:relative;
}
.line_info{
cursor: pointer;
height: auto;
padding-bottom: 5px;
padding-left: 5px;
padding-top: 5px;
}
.line_info span{
padding: 0 3px;
font-size: 12px;
color:#20c083;
}
.line_info span.file_line{
width: 50px;
text-align: right;
}
.line_data{
clear: left;
}
.line_data textarea{
border:0;
width: 100%;
height: auto;
overflow: visible;
font-size: 14px;
padding:15px;
background-color: #222222;
color:#c26647;
}
.line_data .xml_iframe{
width: 100%;
}

/* odd or even for line info, line data */
.line.even{
border-left: 0 solid #EEF1F1;
border-right: 0 solid #EEF1F1;
}
.line_info.even{
background: #485a83;
}
.line.odd{
border-left: 0 solid #EEF1F1;
border-right: 0 solid #EEF1F1;
}
.line_info.odd{
background: #393f4c;
}

/* line file input style */
.line_file_info{
cursor: pointer;
border: 0px;
color:#d07adf
}
.line.odd .line_file_info{
background: #393f4c;
}
.line.even .line_file_info{
background: #485a83;
}


/* Trace Info for a particular log */
.trace{
/*position: relative;*/
width: 100%;
background-color: white;
margin: 0px;
padding:0px;
border: 0px;
}
.trace.off{
display: none;
}
.trace.on{
/* nothing */
}
.trace .trace_block{
text-align: left;
border: 1px solid green;
width: 22%;
float: left;
overflow-x: auto;
height: 80px;
margin: 3px;
padding:3px;
}
.trace .trace_block span{
display: block;
text-decoration: none;
}
.trace .trace_block .function{
padding-bottom: 5px;
}
.trace .trace_block .fileline{
color: #007700;
font-size: 12px;
}
.trace  .trace_arrow{
float: left;
margin-top: 35px;
width: 1.5%;
}

/* Popup window style */
#decode_data_window{
position: absolute;
z-index: 995;
border: 2px solid green;
background-color: white;
width: 400px;
}
#decode_data_window textarea{
font-size: 12px;
width: 100%;
overflow-y: auto;
}
#decode_data_window .window_button{
background-color:green;
color: white;
cursor: pointer;
}

#setting_window{
position:absolute;
z-index: 996;
border:2px solid green;
background-color: white;
width: 400px;
}
#setting_window .window_button{
background-color:green;
color: white;
cursor: pointer;
}

#setting_window label{
	line-height: 25px;
}

#window{
position: absolute;
z-index: 999;
border: 2px solid green;
background-color: white;
}
#window textarea{
font-size: 12px;
width: 100%;
overflow-y: auto;
padding:10px;
background-color:#d5fad5;
}
#window .window_button{
background-color:green;
color: white;
cursor: pointer;
}
</style>
<script type="text/javascript">
// ajax related parameters
var timer;
var original_ajax_timeout = ajax_timeout = 1500;
var ajax_in_process = 0;
var original_ajax_step = ajax_step = 1000;

// Suggest ,turn on this option, to slow down page refresh when you are not focus on PhpLog.
var enable_load_balance = true;
var toggle_all_flag = 0;

var page_end_line = <?php echo $page_end_line;?>;
var php_self = "<?php echo $php_self;?>";
var backoffice_refresh_timestamp = "<?php echo $backoffice_refresh_timestamp;?>";
var refresh_per_page = <?php echo (int) $refresh_per_page;?>;

// template in javascript of this project.
var template_group = '<?php echo phplogHelper::escapeJavascriptString(phplogTemplate::groupTemplate());?>';
var template_line  = '<?php echo phplogHelper::escapeJavascriptString(phplogTemplate::lineTemplate());?>';
var template_line_data_xml = '<?php echo phplogHelper::escapeJavascriptString(phplogTemplate::lineDataXmlTemplate());?>';
var template_trace = '<?php echo phplogHelper::escapeJavascriptString(phplogTemplate::traceTemplate());?>';
var template_trace_arrow = '<?php echo phplogHelper::escapeJavascriptString(phplogTemplate::traceArrowTemplate());?>';

var version_store_in_engine = '<?php echo phplogHelper::escapeJavascriptString($version_store_in_engine);?>';
var software_need_upgrade = <?php if($software_need_upgrade) echo '1'; else echo '0';?>;
var last_version_check_date = '<?php echo phplogHelper::escapeJavascriptString($last_version_check_date);?>';

// editor
var editor_opener = '<?php echo addslashes($config['editor']['opener']);?>';
var editor_arguments = '<?php echo addslashes($config['editor']['arguments']);?>';
</script>

<script type="text/javascript">
var isOpera = navigator.userAgent.indexOf('Opera') > -1;
var isIE = navigator.userAgent.indexOf('MSIE') > 1 && !isOpera;
var isMoz = navigator.userAgent.indexOf('Mozilla/5.') == 0 && !isOpera;

//
// *****Server Side Action*****
//
// update action
function update(){
	if( ajax_in_process ) {
		return true;
	}
	var data = {current_last_line: page_end_line, ajax_ts: ajax_timeout, action: 'update', frontpage_refresh_timestamp: backoffice_refresh_timestamp};
	ajax( {url: php_self, data: data, callback: process_logs} );
}

// delete from->end lines in files
function delete_lines(start, end, event){
	var data = {action: 'delete', current_last_line: 0, from: start, to: end};
	$('container').innerHTML = '';
	page_end_line = 0;
	ajax( {url: php_self, data: data, callback: refresh} );
	return false;
}

// delete selected lines, include from->to, and all selected lines
function delete_selected_lines(){
	
	var elements = document.getElementsByName('delete_line[]');
	var e;
	var lines = '';
	var i;

	for( i = 0; i < elements.length; i++ ){
		e = elements[i];
		if( e.checked == true ){
			lines += e.value + ',';
		}
	}
	
	var from = to = 0;
	from = safe_parse_int( $('input_from_line').value );
	if( trim($('input_to_line').value) == '' && trim($('input_from_line').value) != ''){
		to = elements.length;
	}else{
		to  = safe_parse_int( $('input_to_line').value );
	}
	
	// parse int from unknown value
	function safe_parse_int( value ){
		if( trim(value) == '' ) return 0;
		var ret = parseInt(value);
		if( ! ret ) return 0;
		return ret;
	}
	
	// merge with selected lines
	for( i = from; i <= to; i++ ){
		if( i != 0){
			lines += i + ',';
		}
	}
	
	if( lines.length == 0 ){
		return false;
	}
	
	page_end_line = 0;
	$('container').innerHTML = '';
	
	var data = {action: 'delete', current_last_line: 0, selected_lines: lines};
	ajax( {url: php_self, data: data, callback: refresh} );
	return true;
}

// clean action
function clean(){
	var data = {action: 'clean'};
	page_end_line = 0;
	ajax( {url: php_self, data: data, callback: refresh} );
	//delete_lines(1, page_end_line + 99999);
}

// remove all log files except current one
function remove_log_files(){
	var data = {action: 'remove'};
	ajax( {url: php_self, data: data} );
	return true;
}

// decode input data, __log to the store engine
function decode_data(){
	var value = $('decode_data').value;
	
	$('decode_data_window').style.display = 'none';
	
	var data = {action: 'decode', data: value};
	ajax( {url: php_self, data: data} );
}

// set log types which will been shown in front page.
function setting_log_types(){
	
	var form = $('settingForm');
	$('settingForm');
	
	var log_types = document.getElementsByName('log.types[]');
	
	//var log_types = document.body.form['settingForm'].log_types;
	
	var preferences = [];
	
	for(var i=0; i<log_types.length; i++){
		var a = log_types[i];
		if( a.checked ){
			preferences.push(a.value);
		}
	}
	var refresh_elements = document.getElementsByName('refresh.per.page');
	var refresh_per_page = 1;
	for(var i=0; i<refresh_elements.length; i++){
		var b = refresh_elements[i];
		if( b.checked ){
			refresh_per_page = 1;
		}else{
			refresh_per_page = 0;
		}
	}
	
	var data = {action: 'setting', data: preferences, refresh_per_page:refresh_per_page};
	ajax({url:php_self, data: data, callback:refresh});
}

//
// *****Client Side Action*****
//

// toggel all lines;
function toggle_all(){
	var total = page_end_line;
	var group;
	var group_data;
	var line;
	var line_data;

	for(var i = 1; i <= page_end_line; i++){
		line = $( 'line_' + i );
		if( line != null ){
			line_data = 'line_data_' + i;
			toggle_by_id(line_data, toggle_all_flag);
		}
	}
	
	toggle_all_flag = toggle_all_flag ? 0 : 1;
}

// show/hidden a group or a line data, maybe need been renamed 
function toggle(type, id){
	var element_id;
	if( type == 'group' ){
		element_id = 'group_data_' + id;
	}else{
		element_id = 'line_data_' + id;
	}
	var e = $(element_id);
	
	toggle_by_id(e);
}

// record toggle group for toggle a group action
var toggled_group = {};
// toggle a group
function toggle_a_group(from, to){
	if( from > to ) return;
	var group = $('group_' + to);
	var line;
	var hash_key = 'group_' + to;
	var flag = 0;
	if( toggled_group[hash_key] != undefined && toggled_group[hash_key] != null ){
		flag = toggled_group[hash_key] ? 0 : 1;
		toggled_group[hash_key] = ( toggled_group[hash_key] ) ? 0 : 1;
	}else{
		toggled_group[hash_key] = 0;
	}
	for(var i = from; i <= to; i++){
		line = $('line_data_' + i);
		toggle_by_id(line, flag);
	}
	// toggle the group data show!
	toggle_by_id('group_data_' + to, 1);
	return true;
}

// toggle backtrace block of a line
function toggle_backtrace(line_number){
	var element = $( 'trace_' + line_number );
	// the first time, we need fetch data from server
	if( css_has(element, 'off') ) {
		css_remove(element, 'off');
		var data = {action: 'backtrace', line: line_number};
		ajax( {url:php_self, data:data, callback: process_backtrace} );
		toggle_by_id(element, 1);
		toggle_by_id( 'line_data_' + line_number, 1);
		return true;
	}
	
	var line_data = $( 'line_data_' + line_number );
	
	// the backtrace is available in page, toggle it
	if( ! line_data.style.display ){
		line_data.style.display = 'block';
		toggle_by_id( element, 1);
	}
	else if( line_data.style.display == 'none' ){
		toggle('line', line_number);
		toggle_by_id(element, 1);
	}else if( line_data.style.display == 'block' ){
		toggle_by_id(element);
	}
	return true;
}

// click a group or a line checkbox
function click_checkbox(type, from, to){
	if( type == 'group' ){
		id = 'group_checkbox_' + to;
	}else{
		id = 'line_checkbox_' + from;
	}
	var target = $(id);
	
	var e;
	if( type == 'group' ){
		var elements = document.getElementsByName('delete_line[]');
		for( var i = 0; i < elements.length; i++ ){
			e = elements[i];
			if(  e.value > from - 1 && e.value < to + 1){
				if ( target.checked == true ){
					e.checked = true;
				}else{
					e.checked = false;
				}
			}
			
		}
	}
	return false;
}

// @deprecated, it's not much valueable, so disable it!
// reverse all checkbox in a group.
function reverse_checkbox(from, to){
	var e;
	var elements = document.getElementsByName('delete_line[]');
	for( var i = 0; i < elements.length; i++ ){
		e = elements[i];
		if(  e.value > from - 1 && e.value < to + 1){
			if ( e.checked == true ){
				e.checked = false;
			}else{
				e.checked = true;
			}
		}
	}
}

//
// *****Popup Window*****
//

// show popup window at appropriate location
function show_window(obj, evt){
	var args = obj.getAttribute('args');
	var rows = parseInt(obj.getAttribute('rows'));
	
	var obj = $('window');
	var args_textarea = $('window_textarea');
	if( rows > 20 ){
		args_textarea.setAttribute('rows', 20);
	}else{
		args_textarea.setAttribute('rows', rows + 1);
	}
	args_textarea.value = args;
	
	xPos = parseInt(evt.clientX);
	yPos = parseInt(evt.clientY);
	var currentTop = document.body.scrollTop;
	
	var clientWidth;
	var clientHeight;
	var totalWidth;
	var totalHeight;
	
	if((document.body)&&(document.body.clientWidth))
       clientWidth = document.body.clientWidth;
    if((document.body)&&(document.body.clientHeight))
       clientHeight = document.body.clientHeight;
	
	if( document.body ){
		totalWidth = parseInt( document.body.scrollWidth );
		totalHeight =parseInt( document.body.scrollHeight );
	}
	
	//if( document.documentElement && document.documentElement.clientWidth && document.documentElement.clientHeight ){
	//	clientWidth = document.documentElement.clientHeight;
	//	clientHeight = document.documentElement.clientWidth;
    //}
	
	clientWidth = parseInt(clientWidth);
	clientHeight = parseInt(clientHeight);
	
	var window_max_height = 306;
	var each_line_height = 15;
	var content_width = 400;
	if( rows < 20 ){
		content_height = (rows + 1) * each_line_height;
	}else{
		content_height = 300;
	}
	
	//             |
	// left,top    | right,top
	// -------------------------- 
	// left,bottom | right,bottom
	//             |
	
	// x axis overflow, set x => left
	if( content_width + xPos > clientWidth ){
		obj.style.left = xPos - content_width - 20;
		// content is too height, then y => top!
		if( content_height > currentTop + yPos - 100 ){
			obj.style.top = currentTop + yPos;
		// content height is okay, then y => bottom
		}else{
			obj.style.top = currentTop + yPos - content_height - 35;
		}
	// y axis overflow, set y... 
	}else if(content_height + yPos > clientHeight) {
		// x axis not changed, x => right
		obj.style.left = xPos;
		// if content been show upward not extend the height, then set y => top! adjusted!
		if( content_height + 50 <  currentTop + yPos ){
			obj.style.top = currentTop + yPos - content_height - 35;
		// normal content, then y => bottom
		}else{
			obj.style.top = currentTop + yPos;
		}
	// normal, x => right, y => bottom;
	}else{ 
		obj.style.left = xPos;
		obj.style.top = currentTop + yPos;
	}
	obj.style.width = content_width;
	obj.style.display = 'block';
	
	//if( document.addEventListener ){
	//	document.addEventListener("mouseup", check_user_click_area, true);
	//}else{
	//	document.onmouseup = check_user_click_area;
	//}
	//function check_user_click_area(event){
	//}
	//*/
}

function open_decode_data_window(element, e){
	
	var e = e || event || window.event;
	
	var obj = $('decode_data_window');
	var xPos = parseInt(e.clientX);
	var yPos = parseInt(e.clientY);
	obj.style.top = yPos + 5;
	obj.style.left = xPos;
	obj.style.display = 'block';
}

function open_setting_window(element, e){
	var e = e || event || window.event;
	
	var obj = $('setting_window');
	var xPos = parseInt(e.clientX);
	var yPos = parseInt(e.clientY);
	obj.style.top = yPos + 5;
	obj.style.left = xPos;
	obj.style.display = 'block';
}

// drag popup window
function drag_window(element, e){
	e = e || event || window.event;
	
	if( document.addEventListener ){
		document.addEventListener('mousemove', startDrag, true);
		document.addEventListener('mouseup', stopDrag, true);
	}else{
		document.onmousemove = startDrag;
		document.onmouseup = stopDrag;
	}
	
	//var target = $('window');
	var target = element.parentNode;
	
	var delatX = e.clientX - parseInt(target.style.left);
	var delatY = e.clientY - parseInt(target.style.top);
	
	function startDrag(e){
		e = e || event || window.event;
		target.style.left = e.clientX - delatX;
		target.style.top = e.clientY - delatY;
	}
	
	function stopDrag(){
		if( document.removeEventListener ){
			document.removeEventListener('mousemove', startDrag, true);
			document.removeEventListener('mouseup', stopDrag, true);
		}else{
			document.onmousemove = "";
			document.onmouseup = "";
		}
	}
}

// close popup window, and clear the text in it.
function close_window(element){
	var obj = element.parentNode.parentNode;
	obj.style.display = 'none';
}

//
// *****Data Process*****
//

// update center
function process_logs(response){
	ajax_in_process = 1;
	var data = json_parse(response);
	
	var response_log_end_line = data['response_log_end_line'];
	
	if( response_log_end_line == 'page_should_update' ){
		refresh();
		return;
	}
	
	if( response_log_end_line < page_end_line ){
		ajax_in_process = 0;
		refresh();
		return;
	}
	else if( response_log_end_line == page_end_line ){
		// load balance, then slow down the refresh speed
		if( enable_load_balance  && ajax_timeout < 100000){
			ajax_timeout += ajax_step;
		}
		
		ajax_in_process = 0;
		if( timer ) window.clearTimeout(timer);
		timer = setTimeout(update, ajax_timeout);
		return;
	}
	
	// we need update log in page!
	var map = {};
	
	map.end_line = response_log_end_line;
	map.from_line = (page_end_line + 1);
    map.date = data['date'];
	map.log_data_html = '';
	
	// @see template_eval for more detail
	var group = template_eval(template_group, map);
	var container = $('container');
	
	var group_0 = $('group_0');
	// remove group 0, when user clear the log, group 0 will show in page.
	if( group_0 != null ){
		container.removeChild(container.lastChild);
	}
	
	// insert a new group to the document.
	prepend(container, group);
	
	var group_data_id = $('group_data_' + response_log_end_line);
	var lines = data['data'];
	
	var this_line = {};
	
	var html;
	// insert lines
	for( var i = page_end_line + 1, j = 0; i < response_log_end_line + 1; i++, j++ ){
		
		if( lines[j][0] == undefined ){
			continue;
		}else{
			var program  = lines[j][0];
			if( program != 'php' ){
				this_line.program = '<img src="' + php_self + '?action=image&program=' + program + '" />';
			}else{
				//this_line.program = '<img src="'+url+'?action=image&program=php" />';
				this_line.program = '';
			}
			
			this_line.date = lines[j][1];
			this_line.caller_file = lines[j][2] ? lines[j][2] : '';
			this_line.caller_line = lines[j][3] ? lines[j][3] : '';
			this_line.size = this_line.caller_file.toString().length;
			this_line.data = lines[j][4];
			this_line.rows = lines[j][5];
			this_line.line = i;
			this_line.parity = ( i % 2 == 0 ) ? 'odd' : 'even'; 
			
			if( program == 'data.xml' ){
				html = template_eval(template_line_data_xml, this_line);
			}else{
				html = template_eval(template_line, this_line);
			}
			
			prepend(group_data_id, html);
		}
	}
	
	page_end_line = response_log_end_line;
	ajax_timeout = original_ajax_timeout;
	ajax_in_process = 0;
	//if( isIE && CollectGarbage != undefined ) {
		//CollectGarbage();
	//}
	if( timer ) window.clearTimeout( timer );
	timer = setTimeout(update, ajax_timeout);
}

// resize iframe, xml content.
function resize_iframe(element){
	var frame = $(element);
	alert('dd');
	var innerDoc = (frame.contentDocument) ? frame.contentDocument : frame.contentWindow.document;
	var objToResize = (frame.style) ? frame.style : frame;
	objToResize.height = innerDoc.body.scrollHeight + 10;
}

// process call stack infomation return by ajax response
function process_backtrace(response){
	
	var data = json_parse(response);
	var line = data['line'];
	var traces = data['traces'];
	
	var trace_entity = {};
	var trace;
	var trace_html = '';
	var trace_obj = $( 'trace_' + line );
	
	// insert each BackTrace to this line data.
	for( var i = 0; i < traces.length; i++ ){
		var trace = traces[i];
		trace_entity.caller_function = trace['function'];
		trace_entity.caller_file = trace['file'];
		trace_entity.caller_line = trace['line'];
		trace_entity.args = trace['args'];
		trace_entity.rows = trace['rows'];
		trace_html = template_eval(template_trace, trace_entity);
		if( i != 0 )
			prepend(trace_obj, template_trace_arrow);
		prepend(trace_obj, trace_html);
	}
}

//
// *****General Used Function*****
//

// toggle a element by id or object, 
// if status afforded then set this element to that status
function toggle_by_id(e, status){
	if( typeof e == 'string' ) e = $(e);
	
	if( status != undefined ){
		var value = (status == 'block' || (status != 'none' && status) ) ? 'block' : 'none';
		e.style.display = value;
		return true;
	}
	
	if( e.style.display == 'block' ){
		e.style.display = 'none';
	}else if(e.style.display == 'none'){
		e.style.display = 'block';
	}else{
		e.style.display = 'none';
	}
}

// document.location.reload();
function refresh(){
	var redirect_to = document.location;
	document.location = redirect_to;
}

// stop a event bubble
function stop_bubble(event){
	if( event.stopPropagation ) event.stopPropagation();
	else event.cancelBubble = true;
}

// prepend html as firstChild, only for this project, ^_^. (@see Jquery.prepend())
function prepend(obj, html){
	if( typeof obj == 'string' ) obj = $(obj);

	html = trim(html);
	var pattern = /^<\s*(\w+)\s+([^>]*)>((.|\r|\n)*)<\s*\/\s*\w*\s*>$/;

	var result = html.match( pattern );
	var tagName = result[1];
	var target = document.createElement(tagName);

	var attributes = result[2];
	var inner_html = result[3];
	target.innerHTML = inner_html;

	if( trim(attributes) ){
		var get_attr = /(\w+)="([^"]+)"/g;
		while((result = get_attr.exec(attributes)) != null) {
			var key = trim(result[1]);
			var value = trim(result[2]);
			// need consider other properties?
			if( key == 'class' ){
				target.className = value;
			}else
				target.setAttribute(key, value);
		}
	}
	obj.insertBefore( target, obj.lastChild );
}

// a simple template engine only for this project,please not rely on these
function template_eval( template, params ){
	var ps = '';
	for( var p in params ){
		ps += "|(\{" + p + "\})";
	}
	ps = ps.substring(1);
	
	var pattern = new RegExp(ps, "g");
	
	// replace each word {key} => value
	return template.replace( pattern, function(word){
		var key = word.substring(1, word.length - 1);
		return params[key];
	});
}

	//
	// author <altsee@gmail.com>
	//
	function ajax(options){
		
		var isOpera = navigator.userAgent.indexOf('Opera') > -1;
		var isIE = navigator.userAgent.indexOf('MSIE') > 1 && !isOpera;
		var isMoz = navigator.userAgent.indexOf('Mozilla/5.') == 0 && !isOpera;
		
		var request = window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest();
		
		// default value for each ajax options;
		var method = 'post';
		var callback = null;
		var asyn = true;
		var values = null;
		var url  = null;
		
		if( options != null ){
			for( var k in options ){
				var v = options[k];
				switch( k ){
					case 'method':
						var method = (v + '' ).toLowerCase();
						break;
					case 'callback':
						callback = v;
						break;
					case 'asyn':
						if( v ){
							asyn = true;
						}else{
							asyn = false;
						}
						break;
					case 'data':
						values = v;
						break;
					case 'url':
						url = v;
						break;
					default:
						// error options
						break;
				}
			}
		}
		
		var pairs = [];
		
		// compose the request data;
		for(var p in values){
			var obj = values[p];	
			if( typeof(obj) == 'object' ){
				var c = obj.constructor;
				// Array or hash Array(object)
				if( c != null && (c == Array || c == Object) ){
					for( var z in obj ){
						pair = encodeURIComponent( p + '[' + z + ']' ) + '=' + encodeURIComponent( obj[z] );
						pairs.push( pair );
					}
				}else{
					pair = encodeURIComponent(p) + encodeURIComponent( obj );
					pairs.push( pair );
				}
			}else{
				pair = encodeURIComponent(p) + '=' + encodeURIComponent(obj);
				pairs.push(pair);
			}
		}
		var data = pairs.join('&');
        if (method == 'crossdomain') {
           url = url.replace('?', '');
           url = url + '?' +  data + '&ajaxaccessfrom=crossdomain';
           var objectHead = document.getElementsByTagName('head');

           var objectScript = document.createElement('script');
           objectScript.src = url;
           objectScript.type = 'text/javascript';
           objectHead[0].appendChild(objectScript);
           return true;
        }
	
		var result;
		
		if( asyn || (! asyn && ! isMoz) ){
			request.onreadystatechange = function(){
				if( request.readyState == 4 ){  // If the request is finished
					if(request.status == 200){  // If it was successful
						result = request.responseText;
						if( callback ){
							result = callback(result);  // Display the server's response
						}
					}
				}
			};
		}
		
		if( method == 'get' ){
			url = url.replace('?', '');
			url = url + '?' + data;
		}
		
		request.open( method.toUpperCase(), url, asyn);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		
		if( method == 'post' ){
			request.send( data );
		}else{
			request.send( null );
		}
		
		// @see, http://hi.baidu.com/snowleung/blog/item/2bbad188cbdd7d9da5c2728f.html
		if(  ! asyn && isMoz  ){
			result = request.responseText;
			if( callback ){
				result = callback( result );
			}
		}
		return result;
	}



//
// *****Help Function*****
//

// Help Function, document.getElementById
function $(id){
	return document.getElementById(id);
}

// Help Function trim space at head and tail!
function trim(str){
	var str = str + '';
	return str.replace(/^\s*|\s*$/, '');
}

// Help Function
function css_has(e, c){
	if( typeof e == 'string' ) e = $(e);
	
	var classes = e.className;
	if( ! classes ) return false;
	if( classes == c ) return true;
	
	return e.className.search("\\b" + c + "\\b") != -1;
}

// Help Function
function css_add(e, c){
	if( typeof e == 'string' ) e = $(e);
	if( css_has(e, c) ) return;
	if( e.className )  c = " " + c;
	e.className += c;
}

// Help Function
function css_remove(e, c){
	if( typeof e == 'string' ) e = $(e);
	e.className = e.className.replace( new RegExp("\\b" + c + "\\b\\s*", "g"), "");
}

function json_parse(str){
	return eval( '(' + str + ');' );
}

// 
// *****Begin The Magic***** 
//

// a cron to update in particular timeout
timer = setTimeout(update, ajax_timeout);

// simple load balance, will trigger event if onblur or onfocus
if( enable_load_balance ){

	// slow down the refresh
	window.onblur = function(){
		ajax_step = 2 * original_ajax_step;
		ajax_timeout = 10000;
	}

	// speed up the refresh
	window.onfocus = function(){
		if( refresh_per_page ){
			ajax_in_process = 0;
			return update();
		}
		if( ajax_in_process ) {
			return false;
		}
		ajax_timeout = 1000;
		ajax_step = original_ajax_step / 2;
		if(timer) window.clearTimeout(timer);
		update();
		return true;
	}
}


//
// *****SoftWare Upgrade*****
//

function version_upgrade(){
	var latest_file_url = 'http://sourceforge.net/projects/phplog/files/latest';
	
	if( software_need_upgrade ){
		var target_a = $('new_version_link');
		target_a.innerHTML =  'Download Version: ' + version_store_in_engine + '';
		//target_a.style.color = 'red';
		target_a.href = latest_file_url;
		blink('new_version_link', 3);
		target_a.style.color = 'red';
	// we fetch new version info, and save it into store engine
	}else{
		// test this date has checked upgrade version, or not
		var ts = new Date();
		var ts_y = ts.getFullYear();
		var ts_m = ts.getMonth() + 1;
		var ts_d_of_m = ts.getDate();
		var ts_d_of_w = ts.getDay();
		
		var check_date = last_version_check_date.split('-');
		check_date[1] = check_date[1].replace(/^0/, '');
		check_date[2] = check_date[2].replace(/^0/, '');
		
		// check if today has been upgraded!
		if( parseInt(check_date[0]) < ts_y || parseInt(check_date[1]) < ts_m || parseInt(check_date[2]) < ts_d_of_m ){
			// need upgrade!!
		}else{
			return true;
		}
		
		// actually, we check 4 weekdays for u!
		var upgrade_days = [0, 1, 3, 5];
		
		var need_fetch_new_version = false;
		
		for( var i = 0; i < upgrade_days.length; i++ ){
			if( ts_d_of_w == upgrade_days[i] ){
				need_fetch_new_version = true;
				break;
			}
		}
		if( need_fetch_new_version ){
			
			// record newest version info to database!
			ajax( {url: php_self, data: {action: 'version'}, callback: null } );
		}
		
	}
}

var blink_timeout = {};
function blink(id, times){
	if( $(id) == null ) return;
	if( times == null ) times = 10;
	
	blink_timeout[id] = setTimeout('blink_id(\'' + id + '\',' + times +')',  500 );	
}

function blink_id(id, remain_times){
		
	clearTimeout(blink_timeout[id]);
		
	if( remain_times < 1 ) return;
		
	if( remain_times % 2 == 0 ){
		$(id).style.backgroundColor = '#FFDFC0';
	}else{
		$(id).style.backgroundColor = '#B3F09F';
	}
	blink_timeout[id] = setTimeout('blink_id(\'' + id + '\',' + (remain_times-1) + ')',  500 );
}

if (window.addEventListener){
	window.addEventListener('load', version_upgrade, false);
}else if (window.attachEvent){
	window.attachEvent('onload', version_upgrade);
}else{
	window.onload = version_upgrade;
}

// editor in a special editor
function open_this_file(line){
	if( ! isIE ){
		return;
	}
	var filename = $('editor_file_'+line).value;
	var fileline = $('editor_line_'+line).value;
	open_in_editor(filename, fileline);
}

function open_in_editor(filename, fileline){
	var reg = /javascript\.source\.code\.js/;
	if( reg.test(filename) ){
		return;
	}
	editor.opener = windows_cmd_filename(editor_opener);	
	editor.filename = windows_cmd_filename(filename);
	fileline = parseInt(fileline);
	try{
		editor.arguments = editor_arguments.replace(/\%d/, fileline);
	}catch(e){
	}
	try{
		editor.open();
	}catch(e){
	}
}

function windows_cmd_filename(file){
	file = file + '';
	file = file.replace(/\//g, '\\');
	file = file.replace(/\\/g, '\\\\');
	file = '"' + file + '"';
	return file;
}
</script>
</head>
<body>
<?php 
if($config['editor']['enabled'])
	echo '<object id="editor" classid="CLSID:2255ACD8-5E45-414D-BB93-AE430326BDFB" style="display:none"></object>';
?>
<div id="decode_data_window" style="display:none;">
	<div class="window_button" onmousedown="return drag_window(this,event);">
		<a onclick="decode_data();">Decode</a>
		<a onclick="close_window(this);">Close</a>
	</div>
	<textarea id="decode_data" rows="20" cols="20" onclick="this.select();" style="padding:5px;background: #d5fad5;"></textarea>
</div>
<div id="window" style="display:none;">
	<div class="window_button" onmousedown="return drag_window(this,event);">
		<a onclick="close_window(this);">Close</a>
	</div>
	<textarea id="window_textarea" rows="" readonly="true"></textarea>
</div>
<div id="setting_window" style="display:none">
	<div class="window_button" onmousedown="return drag_window(this,event);">
		<a onclick="setting_log_types();">Setting</a>
		<a onclick="close_window(this);">Close</a>
	</div>
	<form id="settingForm" name="settingForm" action="" method="post" style="padding:5px;background-color:#d5fad5">
		<?php 
			$refresh_per_page = $metadata->get('refresh-per-page');
		?>
		<label><input type="checkbox" name="refresh.per.page" <?php if($refresh_per_page) {echo 'checked="checked"';} ?> style="margin-right:5px;"/>Refresh Per Page</label>
		<hr style="border-width:1px"/>
		<?php 
		// @todo, we need find ways overrider this
		$renderTypes = $metadata->get('render-types');
		foreach (array('php.included.files', 'php.request.info','javascript', 'data.xml', 'data.json', 'data.url', 'data.ini', 'data.session', 'data.serialize') as $type){
			if (in_array($type, $renderTypes)) {
				$checked = "checked='checked'";
			} else {
				$checked = '';
			}
			echo <<<EOT
				<label><input type="checkbox" name="log.types[]" $checked value="{$type}" style="margin-right:5px;" />{$type}</label>
				<br />
EOT;
		}?>
		<label><input type="checkbox" name="log.types[]" value="php" disabled="disabled" checked="checked" />php</label>
		<br />
	</form>
</div>
<?php
$version = phplogVersion::VERSION;
$help = phplogTemplate::helpContent();
$rows = phplogHelper::countRows($help) + 1;
$help = htmlspecialchars($help);
$helpA = "<a args=\"$help\" rows='$rows' class='help' onclick='show_window(this, event);'>Help</a>";

$about = phplogTemplate::aboutContent();
$rows = phplogHelper::countRows($about) + 1;
$about = htmlspecialchars($about);
$aboutA = "<a args=\"$about\" rows='$rows' onclick='show_window(this, event);'>About</a>";
?>
<div id="toolbar">
	<div id="tools">
		From:<input id="input_from_line" type="text" size="4" value="" maxlength="4" />
		To:<input id="input_to_line" type="text" size="4" value="" maxlength="4" />
		<a class="delete" onclick="delete_selected_lines();">Delete</a>
		<a onclick="toggle_all();">Toggle</a>
		<a class="clear" onclick="clean();">Clean</a>
		<a onclick="refresh();">Refresh</a>
		<a onclick="open_decode_data_window(this, event);">Decode</a>
		<a onclick="open_setting_window(this, event);">Setting</a>
		<?php echo $helpA;?>
		<?php echo $aboutA;?>
		<a id="new_version_link"  href="" target="_blank"></a>
	</div>
</div>
<div id="container">
<?php

// for speed up, split template_group, then render html immediately line by line;
$group_split_html = explode('{log_data_html}', phplogTemplate::groupTemplate());
$group_head = $group_split_html[0];
$group_tail = $group_split_html[1];

// output the head;
echo phplogTemplate::templateEval( $group_head, array('from_line'=>1, 'end_line'=>$page_end_line,'date'=>date('H:i',time())) );

// output the log line by line
for( $i = count($logs) - 1 ; $i >= 0; $i-- ){
	if( ! $logs[$i] ){
		continue;
	}
	
	$program = $logs[$i][0];
	$date = $logs[$i][1];
	$traces = $logs[$i][2];
	$data = $logs[$i][3];
	
	$log_entity = new stdClass();
	$log_entity->date = $date;
	$log_entity->caller_file = $traces[0]['file'];
	$log_entity->caller_line = $traces[0]['line'];
	$log_entity->size = strlen($log_entity->caller_file);
	$log_entity->data = $data;
	if( $program == 'data.xml' ){
		$log_entity->rows = phplogHelper::countXmlRows($data);
	}else{
		$log_entity->rows = phplogHelper::countRows($data);
	}
	
	$log_entity->line = $i + 1;
	if( $i % 2 == 0 ){
		$log_entity->parity = 'even';
	}else{
		$log_entity->parity = 'odd';
	}
	
	if($program != 'php'){
		$log_entity->program = "<img src='{$php_self}?action=image&program={$program}' />";
	}else{
		$log_entity->program = '';
	}
	if( $program == 'data.xml' ){
		echo phplogTemplate::templateEval(phplogTemplate::lineDataXmlTemplate(), $log_entity);
	}else{
		echo phplogTemplate::templateEval(phplogTemplate::lineTemplate(), $log_entity);
	}
}

echo $group_tail;

?>
</div>
</body>
</html>
<?php 
} // end of script 
?>