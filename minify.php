<?php
/***
 * @author Vitezslav Kis / thecnology@gmail.com / twitter.com/thecnology
 * 
 * Based on David Grudl's php shrink file (http://latrine.dgx.cz/jak-zredukovat-php-skripty)
 * and inspired on Lukas Dolezal's Nette minifier : (http://nettephp.com/cs/extras/nette-minifier)
 * 
 * @license    http://www.gnu.org/copyleft/gpl.html  General Public License 
 * @package    PHP Minifier
 */

// PHP 4 & 5 compatibility
if (!defined('T_DOC_COMMENT'))
define ('T_DOC_COMMENT', -1);

if (!defined('T_ML_COMMENT'))
define ('T_ML_COMMENT', -1);

// PHP <5.3 compatibility
if (!defined('T_USE'))
define ('T_USE', -1);

if (!defined('T_NAMESPACE'))
define ('T_NAMESPACE', -1);

if (!defined('T_NS_SEPARATOR'))
define ('T_NS_SEPARATOR', -1);

class Minify
{
    public static $set = '!"#$&\'()*+,-./:;<=>?@[\]^`{|}';
    private static $setArray = array();
    protected $parsedFiles = array();
    protected $classesList = array();
    protected $libDir = '.';
    private $br;
    private $signature;
    
    public function __construct()
	{
		if (!function_exists('token_get_all'))
			throw new Exception('PHP tokenizer module is not present.');
		self::$setArray = array_flip(preg_split('//',self::$set));
                
	}
        
        function setBr($br)
        {
            $this->br=$br;
        }
        
        function addSignature($signature)
        {
         $this->signature=$signature;   
        }
        
        protected function isAlreadyParsed($file)
	{
		$file = realpath($file);
		return array_search($file, $this->parsedFiles) !== false;
	}

	private $debuging = FALSE;

	public function toggleDebug($enabled = TRUE)
	{
		$this->debuging = (bool)$enabled;
	}
        
        public function getParsedFiles()
	{
		return $this->parsedFiles;
	}

	public function debugOut($out, $offset = 0)
	{
		if ($this->debuging)
			echo str_repeat(' ', $offset) . $out;
	}
        protected function minifyFile($file, $encloseWithPHPTags = FALSE, $ignoreNamespaces = TRUE, $nestingLevel = 0, $loaderFile = FALSE)
	{
		$this->debugOut("Parsing $file...".$this->br, $nestingLevel);
		$file = realpath($file);
		if ($file === false)
		{
			$this->debugOut("^ File not found".$this->br, $nestingLevel);
			return FALSE;
		}

		if ($this->isAlreadyParsed($file))
		{
			$this->debugOut("^ File is already parsed".$this->br, $nestingLevel);
			return FALSE;
		}

		$this->parsedFiles[] = $file;

		$space = $output = $preoutput = $namespace = '';
		$requireFollow = $requireHere = $wasFirstOpenTag = false;
		$parsingName = NULL;
		$uses = array();

		if ($this->debuging)
			$output = "/*@$file*/";

		// walk over all tokens in file
		$tokens = token_get_all(file_get_contents($file));
		$current_token_index = 0;
		while ($current_token_index < count($tokens))
		{
			// normalize token for use below
			$token = $tokens[$current_token_index++];
			if (!is_array($token))
				$token = array(0, $token);

			if ($loaderFile && $token[0] == T_REQUIRE_ONCE)
			{
				break;
			}


			//************ was required file ************//

			if ($requireFollow) {
				// until token is not string with file name
				if ($token[0] == T_CONSTANT_ENCAPSED_STRING) {
					// assume that before every file name is __DIR__
					$requireFile = realpath(dirname($file) . trim($token[1],"'"));
					$this->debugOut("Going into:".$this->br, $nestingLevel);

					// minificate and prepend required file to future output
					// prepending is important due to namespaces
					$_m = $this->minifyFile($requireFile, FALSE, $requireHere || $ignoreNamespaces, $nestingLevel+1);
					if ($_m !== FALSE)
					{
						if ($requireHere)
							$output .= $_m . '<?php '; // this is little bet to that required files ends outside php
						else
							$preoutput .= $_m;
					}
					unset($requireFile, $_m);
				}

				// wait until 'require' command do not end
				if ($token[1] == ';') {
					$requireFollow = $requireHere = false;
					continue; // next token
				}

				continue; // next token
			}


			//************ was namespace/use declared ************//

			if ($parsingName !== NULL) {
				switch ($parsingName) {
					case T_NAMESPACE:
						$into = &$namespace;
						break;
					case T_USE:
						$into = &$uses[key($uses)];
						break;
				}

				switch ($token[0]) {
					case 0:
						if ($token[1] == ';') {
							$parsingName = NULL;
							unset($into);
						}
						if ($token[1] == ',') {
							$uses[] = '';
							end($uses);
						}
						break;
					case T_NS_SEPARATOR:
					case T_STRING:
						$into .= $token[1];
				}
				continue; // next token
			}

			//************ parent class/interface requirement ************//
			if ($token[0] == T_EXTENDS || $token[0] == T_IMPLEMENTS)
			{
				// search T_STRING which is name of class/interface
				$i = $current_token_index;
				
				while (true) {
				$class_name = '';
				while (true)
				{
					if (is_array($tokens[$i]) && $tokens[$i][0] == T_STRING)
					{
						$class_name .= strtolower($tokens[$i][1]);
						if ($tokens[$i+1][0] != T_NS_SEPARATOR)
							break;
					}
					else if (is_array($tokens[$i]) && $tokens[$i][0] == T_NS_SEPARATOR)
						$class_name .= '\\';
					
						
					$i++;
				}
			
				// search file with this class/interface
				
				$this->debugOut("Found extends/implements $class_name ".$this->br, $nestingLevel);
				foreach ($this->classesList as $class => $class_file)
				{
					if ($class == $class_name || $class == strtolower($namespace) . '\\' . $class_name)
					{
						$this->debugOut("Find file $class_file ".$this->br, $nestingLevel);
						$_m = $this->minifyFile($this->libDir . $class_file, FALSE, $ignoreNamespaces, $nestingLevel+1);
						if ($_m !== FALSE)
						{
							$preoutput .= $_m;
						}
						break;
					}
				}
				
				if ($tokens[$i+1] == ',')
				{
					$i++;
					continue;
				}
				break;
				} // main loop
				unset($i, $class_name, $_m, $class_file);
			}

			//************ regular token recognition ************//

			switch ($token[0]) {
				case T_COMMENT:
				case T_ML_COMMENT:
				case T_DOC_COMMENT:
				case T_WHITESPACE:
					$space = ' ';
					continue 2; // next token
				case T_REQUIRE:
					// In sources is some places where is included HTML templates.
					// These places are only places where is require command.
					$requireHere = true;
				case T_REQUIRE_ONCE:
					// parse required file name in next tokens and go into it
					$requireFollow = true;
					continue 2; // next token
				case T_OPEN_TAG:
					// if it is first open tag in parsed file
					if (!$wasFirstOpenTag) {
						$wasFirstOpenTag = true;
						continue 2; // next token
					}
					// else print it into output (may be it is followed by non-PHP text)
					break;
				case T_NAMESPACE:
					// parse namespace definition in next tokens
					$parsingName = T_NAMESPACE;
					$namespace = '';
					continue 2; // next token
				case T_USE:
					// parse use definition in next tokens
					$parsingName = T_USE;
					$uses[] = '';
					end($uses);
					continue 2; // next token
				case T_CLASS:
				case T_INTERFACE:
				case T_STRING:
					$space = "\n";
					break;

			}
			if (isset(self::$setArray[substr($output, -1)]) ||	isset(self::$setArray[$token[1]{0}]))
				$space = '';

			$output .= $space . $token[1];
			$space = '';
		} // token walk


		//************ final phase ************//

		if ($preoutput != '' && !isset(self::$setArray[substr($preoutput, -1)]))
			$preoutput .= "\n";

		$heading = $preoutput;

		$footer = '';

		if (PHP_VERSION >= '5.3.0' && !$ignoreNamespaces)
		{
			$heading .= "namespace $namespace{";

			// print merged namespace uses
			if (count($uses) > 0) {
				$heading .= 'use ' . implode(',', array_unique($uses)) . ';';
			}

			$footer = '}';
		}

		if ($heading != '' && !isset(self::$setArray[substr($heading, -1)]) )
			$heading .= "\n";
		
		$output =  $heading . $output . $footer;

		if ($encloseWithPHPTags)
			$output = "<?php\n$output";

		$this->debugOut("Done $file ".$this->br, $nestingLevel);

		return $output;
	}

        
        public function minifyFiles($customDir = NULL)
	{
	   //$customDir = realpath("./test/");
           $customDir=realpath(is_string($customDir) ? $customDir : './files/');
           
           
          if (!is_dir($customDir))
			throw new InvalidArgumentException('Can not find '.$customDir.' directory.');

          $this->libDir=$customDir;
          $this->debugOut("skripts directory found $customDir ".$this->br);
        
            $this->parsedFiles = array();
		
        $this->classesList=$this->getFiles($customDir);    

		$minified = "<?php\n";
                $minified.=$this->signature."";
		foreach ($this->classesList as $filename) {
			if (($_m = $this->minifyFile($customDir ."/". $filename, FALSE, TRUE)) !== FALSE)
				$minified .= $_m;
		}

		return $minified;
	}
        /*return files array*/
        public function getFiles($customDir)
        {
               
                $finder = new NFinder();
                FOREACH ($finder->find('*.*')->from(basename($customDir)) AS $FILES )
                {
                    $array[]=basename($FILES);
                }
            return $array;
            
        }

        
}



