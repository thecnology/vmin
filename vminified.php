<?php
/** @created by vmin minifier (https://github.com/thecnology/vmin/ ) 
 *   @created 2012-03-13 12:42:04 **/

class
NFinder implements
IteratorAggregate{private$paths=array();private$groups;private$exclude=array();private$order=RecursiveIteratorIterator::SELF_FIRST;private$maxDepth=-1;private$cursor;public static function
find($mask){if(!is_array($mask)){$mask=func_get_args();}$finder=new
self;return$finder->select(array(),'isDir')->select($mask,'isFile');}public static function
findFiles($mask){if(!is_array($mask)){$mask=func_get_args();}$finder=new
self;return$finder->select($mask,'isFile');}public static function
findDirectories($mask){if(!is_array($mask)){$mask=func_get_args();}$finder=new
self;return$finder->select($mask,'isDir');}private function
select($masks,$type){$this->cursor=&$this->groups[];$pattern=self::buildPattern($masks);if($type||$pattern){$this->filter(create_function('$file','extract(NClosureFix::$vars['.NClosureFix::uses(array('type'=>$type,'pattern'=>$pattern)).'], EXTR_REFS); 
				return (!$type || $file->$type())
					&& !$file->isDot()
					&& (!$pattern || preg_match($pattern, \'/\' . strtr($file->getSubPathName(), \'\\\\\', \'/\')));
			'));}return$this;}public function
in($path){if(!is_array($path)){$path=func_get_args();}$this->maxDepth=0;return$this->from($path);}public function
from($path){if($this->paths){throw new
LogicException('Directory to search has already been specified.');}if(!is_array($path)){$path=func_get_args();}$this->paths=$path;$this->cursor=&$this->exclude;return$this;}public function
childFirst(){$this->order=RecursiveIteratorIterator::CHILD_FIRST;return$this;}private static function
buildPattern($masks){$pattern=array();foreach($masks as$mask){$mask=rtrim(strtr($mask,'\\','/'),'/');$prefix='';if($mask===''){continue;}elseif($mask==='*'){return
NULL;}elseif($mask[0]==='/'){$mask=ltrim($mask,'/');$prefix='(?<=^/)';}$pattern[]=$prefix.strtr(preg_quote($mask,'#'),array('\*\*'=>'.*','\*'=>'[^/]*','\?'=>'[^/]','\[\!'=>'[^','\['=>'[','\]'=>']','\-'=>'-'));}return$pattern?'#/('.implode('|',$pattern).')$#i':NULL;}public function
getIterator(){if(!$this->paths){throw new
LogicException('Call in() or from() to specify directory to search.');}elseif(count($this->paths)===1){return$this->buildIterator($this->paths[0]);}else{$iterator=new
AppendIterator();foreach($this->paths as$path){$iterator->append($this->buildIterator($path));}return$iterator;}}private function
buildIterator($path){$iterator=new
NRecursiveDirectoryIteratorFixed($path);if($this->exclude){$filters=$this->exclude;$iterator=new
NRecursiveCallbackFilterIterator($iterator,create_function('$file','extract(NClosureFix::$vars['.NClosureFix::uses(array('filters'=>$filters)).'], EXTR_REFS); 
				if (!$file->isFile()) {
					foreach ($filters as $filter) {
						if (!call_user_func($filter, $file)) {
							return FALSE;
						}
					}
				}
				return TRUE;
			'));}if($this->maxDepth!==0){$iterator=new
RecursiveIteratorIterator($iterator,$this->order);$iterator->setMaxDepth($this->maxDepth);}if($this->groups){$groups=$this->groups;$iterator=new
NCallbackFilterIterator($iterator,create_function('$file','extract(NClosureFix::$vars['.NClosureFix::uses(array('groups'=>$groups)).'], EXTR_REFS); 
				foreach ($groups as $filters) {
					foreach ($filters as $filter) {
						if (!call_user_func($filter, $file)) {
							continue 2;
						}
					}
					return TRUE;
				}
				return FALSE;
			'));}return$iterator;}public function
exclude($masks){if(!is_array($masks)){$masks=func_get_args();}$pattern=self::buildPattern($masks);if($pattern){$this->filter(create_function('$file','extract(NClosureFix::$vars['.NClosureFix::uses(array('pattern'=>$pattern)).'], EXTR_REFS); 
				return !preg_match($pattern, \'/\' . strtr($file->getSubPathName(), \'\\\\\', \'/\'));
			'));}return$this;}public function
filter($callback){$this->cursor[]=$callback;return$this;}public function
limitDepth($depth){$this->maxDepth=$depth;return$this;}public function
size($operator,$size=NULL){if(func_num_args()===1){if(!preg_match('#^(?:([=<>!]=?|<>)\s*)?((?:\d*\.)?\d+)\s*(K|M|G|)B?$#i',$operator,$matches)){throw new
InvalidArgumentException('Invalid size predicate format.');}list(,$operator,$size,$unit)=$matches;static$units=array(''=>1,'k'=>1e3,'m'=>1e6,'g'=>1e9);$size*=$units[strtolower($unit)];$operator=$operator?$operator:'=';}return$this->filter(create_function('$file','extract(NClosureFix::$vars['.NClosureFix::uses(array('operator'=>$operator,'size'=>$size)).'], EXTR_REFS); 
			return NTools::compare($file->getSize(), $operator, $size);
		'));}public function
date($operator,$date=NULL){if(func_num_args()===1){if(!preg_match('#^(?:([=<>!]=?|<>)\s*)?(.+)$#i',$operator,$matches)){throw new
InvalidArgumentException('Invalid date predicate format.');}list(,$operator,$date)=$matches;$operator=$operator?$operator:'=';}$date=NTools::createDateTime($date)->format('U');return$this->filter(create_function('$file','extract(NClosureFix::$vars['.NClosureFix::uses(array('operator'=>$operator,'date'=>$date)).'], EXTR_REFS); 
			return NTools::compare($file->getMTime(), $operator, $date);
		'));}}class
NRecursiveDirectoryIteratorFixed extends
RecursiveDirectoryIterator{function
hasChildren(){return
parent::hasChildren(TRUE);}}class
NCallbackFilterIterator extends
FilterIterator{private$callback;function
__construct(Iterator$iterator,$callback){parent::__construct($iterator);$this->callback=$callback;}function
accept(){return
call_user_func($this->callback,$this);}}class
NRecursiveCallbackFilterIterator extends
FilterIterator implements
RecursiveIterator{private$callback;private$childrenCallback;function
__construct(RecursiveIterator$iterator,$callback,$childrenCallback=NULL){parent::__construct($iterator);$this->callback=$callback;$this->childrenCallback=$childrenCallback;}function
accept(){return$this->callback===NULL||call_user_func($this->callback,$this);}function
hasChildren(){return$this->getInnerIterator()->hasChildren()&&($this->childrenCallback===NULL||call_user_func($this->childrenCallback,$this));}function
getChildren(){return new
self($this->getInnerIterator()->getChildren(),$this->callback,$this->childrenCallback);}}class
NClosureFix{static$vars=array();static function
uses($args){self::$vars[]=$args;return
count(self::$vars)-1;}}final
class
NTools{public static function
createDateTime($time){if($time instanceof
DateTime){return clone$time;}elseif(is_numeric($time)){if($time<=self::YEAR){$time+=time();}return new
DateTime53(date('Y-m-d H:i:s',$time));}else{return new
DateTime53($time);}}public static function
compare($l,$operator,$r){switch($operator){case'>':return$l>$r;case'>=':return$l>=$r;case'<':return$l<$r;case'<=':return$l<=$r;case'=':case'==':return$l==$r;case'!':case'!=':case'<>':return$l!=$r;}throw new
InvalidArgumentException("Unknown operator $operator.");}} if(!defined('T_DOC_COMMENT'))define('T_DOC_COMMENT',-1);if(!defined('T_ML_COMMENT'))define('T_ML_COMMENT',-1);if(!defined('T_USE'))define('T_USE',-1);if(!defined('T_NAMESPACE'))define('T_NAMESPACE',-1);if(!defined('T_NS_SEPARATOR'))define('T_NS_SEPARATOR',-1);class
Minify{public static$set='!"#$&\'()*+,-./:;<=>?@[\]^`{|}';private static$setArray=array();protected$parsedFiles=array();protected$classesList=array();protected$libDir='.';private$br;private$signature;public function
__construct(){if(!function_exists('token_get_all'))throw new
Exception('PHP tokenizer module is not present.');self::$setArray=array_flip(preg_split('//',self::$set));}function
setBr($br){$this->br=$br;}function
addSignature($signature){$this->signature=$signature;}protected function
isAlreadyParsed($file){$file=realpath($file);return
array_search($file,$this->parsedFiles)!==false;}private$debuging=FALSE;public function
toggleDebug($enabled=TRUE){$this->debuging=(bool)$enabled;}public function
getParsedFiles(){return$this->parsedFiles;}public function
debugOut($out,$offset=0){if($this->debuging)echo
str_repeat(' ',$offset).$out;}protected function
minifyFile($file,$encloseWithPHPTags=FALSE,$ignoreNamespaces=TRUE,$nestingLevel=0,$loaderFile=FALSE){$this->debugOut("Parsing $file...".$this->br,$nestingLevel);$file=realpath($file);if($file===false){$this->debugOut("^ File not found".$this->br,$nestingLevel);return
FALSE;}if($this->isAlreadyParsed($file)){$this->debugOut("^ File is already parsed".$this->br,$nestingLevel);return
FALSE;}$this->parsedFiles[]=$file;$space=$output=$preoutput=$namespace='';$requireFollow=$requireHere=$wasFirstOpenTag=false;$parsingName=NULL;$uses=array();if($this->debuging)$output="/*@$file*/";$tokens=token_get_all(file_get_contents($file));$current_token_index=0;while($current_token_index<count($tokens)){$token=$tokens[$current_token_index++];if(!is_array($token))$token=array(0,$token);if($loaderFile&&$token[0]==T_REQUIRE_ONCE){break;}if($requireFollow){if($token[0]==T_CONSTANT_ENCAPSED_STRING){$requireFile=realpath(dirname($file).trim($token[1],"'"));$this->debugOut("Going into:".$this->br,$nestingLevel);$_m=$this->minifyFile($requireFile,FALSE,$requireHere||$ignoreNamespaces,$nestingLevel+1);if($_m!==FALSE){if($requireHere)$output.=$_m.'<?php ';else$preoutput.=$_m;}unset($requireFile,$_m);}if($token[1]==';'){$requireFollow=$requireHere=false;continue;}continue;}if($parsingName!==NULL){switch($parsingName){case
T_NAMESPACE:$into=&$namespace;break;case
T_USE:$into=&$uses[key($uses)];break;}switch($token[0]){case 0:if($token[1]==';'){$parsingName=NULL;unset($into);}if($token[1]==','){$uses[]='';end($uses);}break;case
T_NS_SEPARATOR:case
T_STRING:$into.=$token[1];}continue;}if($token[0]==T_EXTENDS||$token[0]==T_IMPLEMENTS){$i=$current_token_index;while(true){$class_name='';while(true){if(is_array($tokens[$i])&&$tokens[$i][0]==T_STRING){$class_name.=strtolower($tokens[$i][1]);if($tokens[$i+1][0]!=T_NS_SEPARATOR)break;}else if(is_array($tokens[$i])&&$tokens[$i][0]==T_NS_SEPARATOR)$class_name.='\\';$i++;}$this->debugOut("Found extends/implements $class_name ".$this->br,$nestingLevel);foreach($this->classesList as$class=>$class_file){if($class==$class_name||$class==strtolower($namespace).'\\'.$class_name){$this->debugOut("Find file $class_file ".$this->br,$nestingLevel);$_m=$this->minifyFile($this->libDir.$class_file,FALSE,$ignoreNamespaces,$nestingLevel+1);if($_m!==FALSE){$preoutput.=$_m;}break;}}if($tokens[$i+1]==','){$i++;continue;}break;}unset($i,$class_name,$_m,$class_file);}switch($token[0]){case
T_COMMENT:case
T_ML_COMMENT:case
T_DOC_COMMENT:case
T_WHITESPACE:$space=' ';continue 2;case
T_REQUIRE:$requireHere=true;case
T_REQUIRE_ONCE:$requireFollow=true;continue 2;case
T_OPEN_TAG:if(!$wasFirstOpenTag){$wasFirstOpenTag=true;continue 2;}break;case
T_NAMESPACE:$parsingName=T_NAMESPACE;$namespace='';continue 2;case
T_USE:$parsingName=T_USE;$uses[]='';end($uses);continue 2;case
T_CLASS:case
T_INTERFACE:case
T_STRING:$space="\n";break;}if(isset(self::$setArray[substr($output,-1)])||isset(self::$setArray[$token[1]{0}]))$space='';$output.=$space.$token[1];$space='';}if($preoutput!=''&&!isset(self::$setArray[substr($preoutput,-1)]))$preoutput.="\n";$heading=$preoutput;$footer='';if(PHP_VERSION>='5.3.0'&&!$ignoreNamespaces){$heading.="namespace $namespace{";if(count($uses)>0){$heading.='use '.implode(',',array_unique($uses)).';';}$footer='}';}if($heading!=''&&!isset(self::$setArray[substr($heading,-1)]))$heading.="\n";$output=$heading.$output.$footer;if($encloseWithPHPTags)$output="<?php\n$output";$this->debugOut("Done $file ".$this->br,$nestingLevel);return$output;}public function
minifyFiles($customDir=NULL){$customDir=realpath(is_string($customDir)?$customDir:'./files/');if(!is_dir($customDir))throw new
InvalidArgumentException('Can not find '.$customDir.' directory.');$this->libDir=$customDir;$this->debugOut("skripts directory found $customDir ".$this->br);$this->parsedFiles=array();$this->classesList=$this->getFiles($customDir);$minified="<?php\n";$minified.=$this->signature;foreach($this->classesList as$filename){if(($_m=$this->minifyFile($customDir."/".$filename,FALSE,TRUE))!==FALSE)$minified.=$_m;}return$minified;}public function
getFiles($customDir){$finder=new
NFinder();FOREACH($finder->find('*.*')->from(basename($customDir))AS$FILES){$array[]=basename($FILES);}return$array;}} if(!isset($argv)){$argv=$_GET;$br="<br>";if(!isset($_GET['dir']))echo"example: vmin.php?dir=dirWithScripts $br";else$argv[1]=$_GET['dir'];}else{$br="\n";echo"***********************************************************$br";echo"****example: php vmin.php dirWithScripts            *******$br";echo"** if not defined dir used 'files' as default      ********$br";echo"** u can used this also as webpage vmin.php?1=dir  ********$br";echo"***********************************************************$br";}$signature="/** @created by vmin minifier (https://github.com/thecnology/vmin/ ) 
  * @created ".date("Y-m-d H:i:s")." **/";$minifier=new
Minify();$minifier->addSignature($signature);$minifier->setBr($br);$minifier->toggleDebug(array_search('--debug',$argv)!==false&&array_search('--stdout',$argv)===false);$minified=$minifier->minifyFiles(isset($argv[1])?$argv[1]:"files");if(array_search('--stdout',$argv)!==false)echo$minified;else{echo'Parsed files: '.count($minifier->getParsedFiles())."$br";$outfile=isset($argv[1])?$argv[1].".minified.php":"files.minified.php";if(($i=array_search('--outfile',$argv))!==false&&isset($argv[$i+1])){$outfile=$argv[$i+1];}fwrite(fopen($outfile,'w'),$minified);echo"Minified version saved as $outfile $br";}