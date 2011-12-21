<?php
/**
 * File ATM_Context.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Context.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'Template.php';
require_once 
    implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Abstract', 'Behaviours.php'));


/**
 * The context helps to provides high cohesion within objects while preserving
 * lows dependencies.
 * A minimal context is set by a subject and an environment, 
 * optionaly, you can set a moment while it occurs, describe some properties,
 * and add behaviours.
 * Properties are used to make replacements in other descriptions values, 
 * in behaviours names and values, in subject, environment or moment.
 * Replacements are made with a template system wich supports recursive defines.
 * However, take care as there is no control on depths and recursion can be infinite
 * Template syntax is kept simple, just put your descriptions names between { and }.
 *  
 * Behaviours helps to :
 * - launch functions or creates objects within the context with 'proceed' method.
 * - share a contextual object created with 'proceedOnce'
 *  
 * @category Automne
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Context
 *
 */
class ATM_Context extends ATM_Context_Behaviours_Abstract
{
    protected $template;
    /**
     * Constructor
     * 
     * @param mixed $subject     the context subject
     * @param mixed $environment the context environment
     * @param mixed $moment      optional property to set while the context occured
     */
    public function __construct($subject, $environment, $moment=self::DEFAULT_MOMENT)
    {
        $this->template = new ATM_Context_Template($this, 'about');
        $this->with(&$subject)->into(&$environment)->during(&$moment);
    }
    /**
     * Clean datas with template
     * 
     * @param mixed $datas the datas to normalize
     * 
     * @return mixed normalized datas
     */
    public function normalize($datas)
    {
        return $this->template->apply($datas);
    }
    /**
     * Give an hash that identify the current context
     * 
     * @return string a hash of contextual properties
     */
    public function identify()
    {
        return md5(
            serialize($this->subject)
            .serialize($this->environment)
            .serialize($this->moment)
            .serialize($this->behaviours)
            .serialize($this->descriptions)
        );
    }
}