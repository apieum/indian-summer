<?php
/**
 * File Directive.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Directive.php
 *
 */
require_once implode(DIRECTORY_SEPARATOR, array(__DIR__,'Abstract','Config.php'));
require_once __DIR__.DIRECTORY_SEPARATOR.'Comment.php';
 /**
 * A directive bind a name to a value.
 * They can be included in a parent object (sections)
 * and support the add of comments.
 *  
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Directive
 *
 */
class ATM_Config_Directive extends ATM_Config_Abstract
{
    protected $comment;
    public static $commentClass="ATM_Config_Comment";
    /**
     * Constructor 
     * 
     * @param string $name    the name of the directive
     * @param mixed  $value   the value
     * @param string $comment an optionnal comment
     */
    public function __construct($name, $value, $comment=null)
    {
        $comment = new self::$commentClass($comment);
        $this->setName(&$name)->setContent(&$value)->setComment($comment);
    }
    /**
     * add the given argument as a new line to comment  
     * 
     * @param string $note the the new line in comment
     * 
     * @return object the comment for chaining
     */
    public function annotate($note)
    {
        $this->comment->newLine($note);
        return $this->comment;
    }
    /**
     * set the comment of this directive and notify 'setCommentOn'
     * 
     * @param string|object $comment the comment
     * 
     * @return object this for chaining
     */
    public function setComment( ATM_Config_Abstract $comment)
    {
        $this->comment = $comment;
        $this->notify('setCommentOn', $this, $this->comment);
        return $this;
    }
    /**
     * return the directive comment
     * 
     * @return object
     */
    public function getComment()
    {
        return $this->comment;
    }
}