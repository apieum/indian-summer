<?php
/**
 * File atmCoreContext.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Core
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmCoreContext.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'atmCoreContextTemplate.php';
$contextBehaviours= implode(
    DIRECTORY_SEPARATOR,
    array(
    __DIR__, '..', '..', 'abstracts', 'context', 'atmContextBehavioursAbstract.php'
    )
);
require_once $contextBehaviours;


/**
 * The context helps to provides high cohesion within objects while preserving
 * lows dependencies.
 * A minimal context is set by a subject and an environment, 
 * optionaly, you can set a moment while it occurs, describe some properties,
 * and add behaviours.
 * Properties are used to make replacements in other descriptions values, 
 * in behaviours names and values, in subject, environment or moment.
 * Replacements are made with a template system wich supports recursive defines.
 * However, take care as there is no controls on depths and recursion can be infinite
 * Template syntax is kept simple, just put your descriptions names between { and }. 
 *  
 * Behaviours helps to :
 * - launch functions within the context with 'proceed' method.
 * - creates objects within the context with 'construct' method. 
 *  
 * @category Automne
 * @package  Core
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmCoreContext
 *
 */
class atmCoreContext extends atmContextBehavioursAbstract
{
    protected $template='atmCoreContextTemplate';
}
