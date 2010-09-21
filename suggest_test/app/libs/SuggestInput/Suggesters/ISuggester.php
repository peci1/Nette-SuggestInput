<?php

/**
 * A model that can return items from a list matching a given pattern
 * 
 * @package Nette\Forms
 * @version 1.0.0
 * @copyright (c) 2009 Martin Pecka (Clevis)
 * @author Martin Pecka <martin.pecka@clevis.cz> 
 * @license I mostly like BSD, but you can do everything you want with this 
 * library except of removing my name and the download link from the file 
 * (espacially you CAN use it free of charge in commercial applications)  
 */
interface ISuggester extends IComponent
{

    /**
     * Returns array of all suggestions that match the given query string 
     * 
     * @param string $query The query to match
     * @return array The array of suggestions
     */
    function getSuggestions($query);

    /**
     * Returns true if the whole given string is one of the suggested items 
     * and not only a substring of some
     *
     * Case insensitive!
     * 
     * @param string $query The string we try to find       
     * @return bool
     */
    function isSuggested($query);

}

/* ?> omitted intentionally */

