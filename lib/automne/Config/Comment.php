<?php
/**
 * File Comment.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Comment.php
 *
 */
require_once implode(DIRECTORY_SEPARATOR, array(__DIR__,'Abstract','Config.php'));
 /**
 * A config multiline comment
 * This helps to manage multiline comments.
 * You can add, insert, replace comments lines :
 * - from a string : methods that ends with 'Line'
 * - from an array : methods that ends with 'Lines'
 * If a line (string) contains delimiters set in this object,
 * it's replaced by lines (array).
 * If you give lines (array) that contains delimiters, they stay as is.
 * You can set delimiters for :
 * - the beginning of a line : by default empty
 * - the end of line : by default new line symbols for linux, mac and windows
 * A delimiter must be a regexp subpattern (see tests for some common usage). 
 *  
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Comment
 *
 */
class ATM_Config_Comment extends ATM_Config_Abstract
{
    protected $content     = array();
    protected $startOfLine = '';
    protected $endOfLine   = '(\n|\r|\r\n)';
    /**
     * Constructor
     *  
     * @param string $comment a comment to split in lines
     */
    public function __construct($comment)
    {
        $this->setContent($comment);
    }
    /**
     * return an array of given argument 
     * 
     * @param mixed &$content lines of this comment
     * 
     * @return null
     */
    protected function &validateContent(&$content) 
    {
        if (is_string($content)) {
            $content = $this->splitLines($content);
        } else {
            $content = (array) $content;
        }
        return $content;
    }
    /**
     * Split a string in array of line delimited by $startOfLine and $endOfLine 
     *  
     * @param string $comment the comment to split
     * 
     * @return array with one line of comment per index
     */
    public function splitLines($comment)
    {
        $lines = preg_split('@'.$this->endOfLine.$this->startOfLine.'@xm', $comment);
        $lines[0]     = preg_replace('@'.$this->startOfLine.'@x', '', $lines[0]);
        $last         = count($lines) - 1;
        $lines[$last] = preg_replace('@'.$this->endOfLine.'@x', '', $lines[$last]);
        return $lines;
    }
    /**
     * Set the pattern used to identify the begininning of a comment line
     * 
     * @param string $delimiter a pattern to match begininning of lines
     * 
     * @return object $this for chaining
     */
    public function setStartOfLines($delimiter)
    {
        $this->startOfLine = str_replace('@', '\@', $delimiter);
        return $this;
    }
    /**
     * Set the pattern used to identify the end of a comment line
     * 
     * @param string $delimiter a pattern to match end of lines
     * 
     * @return object $this for chaining
     */
    public function setEndOfLines($delimiter)
    {
        $this->endOfLine = str_replace('@', '\@', $delimiter);
        return $this;
    }
    /**
     * Set patterns used to identify the beginning and the end of a comment line
     * 
     * @param string $startOfline a pattern to match begininning of lines
     * @param string $endOfLine   a pattern to match end of lines
     * 
     * @return object $this for chaining
     */
    public function setDelimiters($startOfline, $endOfLine)
    {
        $this
            ->setStartOfLines($startOfline)
            ->setEndOfLines($endOfLine);
        return $this;
    }
    /**
     * Fill comment with news lines of $value until it has $max lines.
     *  
     * @param int    $max   the maximum of lines the comment must have
     * @param string $value the value of new lines
     * 
     * @return object $this for chaining
     */
    public function fillTo($max, $value='')
    {
        $num_lines = count($this->content);
        if ($max > $num_lines) {
            $new_lines = array_fill(0, $max - $num_lines, $value);
            $this->content = array_merge($this->content, $new_lines);
        }
        return $this;
    }
    /**
     * return the line at position $line_pos by reference or $default if not set 
     * 
     * @param int    $line_pos the position of the line
     * @param string $default  the returned value if there is no line at $line_pos
     * 
     * @return string line content or $default
     */
    public function &getLine($line_pos, $default='')
    {
        if (isset($this->content[$line_pos])) {
            return $this->content[$line_pos];
        }
        return $default;
    }
    /**
     * return by reference all the lines of this comment
     * 
     * @return array all the line of this comment
     */
    public function &getLines()
    {
        return $this->content;
    }
    /**
     * append a new line at the end of this comment.
     * If the line has delimiters, multiple line are sets 
     * 
     * @param string $line the content of the new line
     * 
     * @return object $this for chaining
     */
    public function newLine($line)
    {
        $this->content = array_merge($this->content, $this->splitLines($line));
        return $this;
    }
    /**
     * edit the content of line at position $line_pos
     * if the line contains delimiters, the existing line is replaced by the first
     * the others lines are inserted after.
     *  
     * @param int    $line_pos the position of the line to edit
     * @param string $line     the content of the line
     * 
     * @return object $this for chaining
     */
    public function editLine($line_pos, $line)
    {
        return $this->spliceLine($line, $line_pos, 1);
    }
    /**
     * splices $num_lines from $from by $lines
     * if array length is lower than $from it's filled by '' until $from
     * 
     * @param array $lines     lines to splice 
     * @param int   $from      the index from where start splice
     * @param int   $num_lines the number of line to splice
     * 
     * @see http://fr.php.net/manual/en/function.array-splice.php
     * @return object $this for chaining
     */
    public function spliceLines(array $lines, $from=0, $num_lines=null) 
    {
        $this->fillTo($from);
        if (is_null($num_lines)) {
            $num_lines = count($this->content) - $from;
        }
        array_splice($this->content, $from, $num_lines, $lines);
        return $this;
    }
    /**
     * splices $num_lines from $from by the splited value of $lines
     * 
     * @param string $line      a comment line that can contains delimiters 
     * @param int    $from      the index from where start splice
     * @param int    $num_lines the number of line to splice
     * 
     * @see spliceLines
     * @return object $this for chaining
     */
    public function spliceLine($line, $from=0, $num_lines=null) 
    {
        return $this->spliceLines($this->splitLines($line), $from, $num_lines);
    }
    /**
     * replaces lines by the given one from position $from
     * 
     * @param array|string $lines lines that will replace existing ones
     * @param integer      $from  the position of the first line to replace
     * 
     * @return object $this for chaining
     */
    public function replaceLines($lines, $from=0) 
    {
        if (is_string($lines)) {
            $lines = $this->splitLines($lines);
        }
        return $this->spliceLines($lines, $from, count($lines));
    }
    /**
     * insert lines at position $from 
     * 
     * @param array|string $lines the lines to insert
     * @param integer      $from  the position where to insert lines
     * 
     * @return object $this for chaining
     */
    public function insertLines($lines, $from=0)
    {
        if (is_string($lines)) {
            $lines = $this->splitLines($lines);
        }
        return $this->spliceLines($lines, $from, 0);
    }
}