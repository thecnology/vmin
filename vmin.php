<?php

/* * *
 * @author Vitezslav Kis / thecnology@gmail.com / twitter.com/thecnology
 * 
 * Based on David Grudl's php shrink file (http://latrine.dgx.cz/jak-zredukovat-php-skripty)
 * and inspired on Lukas Dolezal's Nette minifier : (http://nettephp.com/cs/extras/nette-minifier)
 * 
 * @license    http://www.gnu.org/copyleft/gpl.html  General Public License 
 * @package    PHP Minifier
 */


if (!isset($argv)) {
    $argv = $_GET;
    $br = "<br>";

    if (!isset($_GET['dir']))
        echo "example: vmin.php?dir=dirWithScripts $br";
    else
        $argv[1] = $_GET['dir'];
}
else {
    $br = "\n";
    if (!isset($argv[1]) || (trim($argv[1]) == "")) {
        echo "***********************************************************$br";
        echo "****example: php vmin.php dirWithScripts            *******$br";
        echo "** if not defined dir used 'files' as default      ********$br";
        echo "** u can used this also as webpage vmin.php?1=dir  ********$br";
        echo "***********************************************************$br";
    }
}

require_once 'minify.php';
require_once 'class.finder.php';

$signature =
        "/** @created by vmin minifier (https://github.com/thecnology/vmin/ ) 
 *   @created " . date("Y-m-d H:i:s") . " 
**/
";

$minifier = new Minify();
$minifier->addSignature($signature);
$minifier->setBr($br);
$minifier->toggleDebug(array_search('--debug', $argv) !== false && array_search('--stdout', $argv) === false);
$minified = $minifier->minifyFiles(isset($argv[1]) ? $argv[1] : "files");

if (array_search('--stdout', $argv) !== false)
    echo $minified;
else {
    echo 'Parsed files: ' . count($minifier->getParsedFiles()) . "$br";

    $outfile = isset($argv[1]) ? $argv[1] . ".minified.php" : "files.minified.php";
    if (($i = array_search('--outfile', $argv)) !== false && isset($argv[$i + 1])) {
        $outfile = $argv[$i + 1];
    }

    fwrite(fopen($outfile, 'w'), $minified);
    echo "Minified version saved as $outfile $br";
}

