<?php

namespace Nette\Addons\SuggestInput;

use \Nette\Utils\Strings;

/**
 * A suggester that uses an array as its list
 * 
 * @package Nette\Addons\SuggestInput
 * @version 2.1
 * @copyright (c) 2013 Martin Pecka
 * @author Martin Pecka <peci1@seznam.cz> 
 * @license I mostly like BSD, but you can do everything you want with this 
 * library except of removing my name and the download link from the file 
 * (espacially you CAN use it free of charge in commercial applications)  
 *
 * @see Nette\Addons\SuggestInput\ISuggester
 */
class ArraySuggester extends \Nette\ComponentModel\Component implements ISuggester
{

    /** @var array The array holding all suggestable items */
    protected $items;

    /** @var array The items matching for current query */
    protected $matches;

    /** @var bool Match only items beginning with query? */
    protected $matchFromBeginning = FALSE;

    /** @var bool Match only items ending with query? */
    protected $matchToEnd = FALSE;

    /**
     * Set items 
     * 
     * @param array|string $items The array holding all suggestable items
     * @return void
     */
    public function __construct($items = NULL)
    {
        $this->setItems($items);
    }

    /**
     * Returns array of all suggestions that match the given query string 
     * 
     * @param string $query The query to match
     * @param bool $wholeQuery If true, return only items that match the whole
     *                         query string and nothing more
     *
     * @return array The array of suggestions
     */
    public function getSuggestions($query, $wholeQuery = FALSE) {
        $this->matches = array(); 

        //escape all PREG regex dangerous characters and the regex delimiter /
        $query = preg_quote(Strings::lower($query), '/');

        if ($wholeQuery || $this->matchFromBeginning())
            $query = '^' . $query;

        if ($wholeQuery || $this->matchToEnd())
            $query .= '$';

        array_walk($this->items, array($this, 'matchQuery'), $query);

        return $this->matches;
    }

    /**
     * If $query is found in $value, add the $value to $this->matches
     *
     * Case insensitive!
     * 
     * @param string $value The value in which we want to match
     * @param string $key Not used here
     * @param string $query The query to match (simple string or regex without
     *                      delimiters)
     *
     * @return void
     */
    protected function matchQuery($value, $key, $query) {
        if (preg_match('/' . $query . '/', Strings::lower($value))) {
            $this->matches[] = $value;
        }
    }

    /**
     * Returns true if the whole given string is one of the suggested items 
     * and not only a substring of some
     * 
     * @param string $query The string we try to find
     * @return bool
     */
    public function isSuggested($query)
    {
        $this->getSuggestions($query, TRUE);
        return (count($this->matches) > 0);
    }

    /**
     * If value is NULL, return current setting for matching only items 
     * beginning with query; if a value is provided, set the setting to
     * that value
     * 
     * @param bool|NULL $value Value to set or NULL to return value
     * @return bool|ISuggester Value of the setting or (if setting) $this to
     * provide fluent interface
     */
    public function matchFromBeginning($value = NULL)
    {
        if ($value === NULL)
            return $this->matchFromBeginning;

        $this->matchFromBeginning = (bool)$value;
        return $this;
    }

    /**
     * If value is NULL, return current setting for matching only items 
     * ending with query; if a value is provided, set the setting to
     * that value
     * 
     * @param bool|NULL $value Value to set or NULL to return value
     * @return bool|ISuggester Value of the setting or (if setting) $this to
     * provide fluent interface
     */
    public function matchToEnd($value = NULL)
    {
        if ($value === NULL)
            return $this->matchToEnd;

        $this->matchToEnd = (bool)$value;
        return $this;
    }

    /**
     * Returns the array of all suggestable items 
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets the array holding all suggestable items 
     * 
     * @param array|string $items If string, add just one item, if array, add
     *                            the whole array to the items to suggest
     * @return ISuggester Provides fluent interface
     */
    public function setItems($items)
    {
        if (is_string($items))
            $items = array($items);

        $this->items = (array)$items;
        return $this;
    }

    /**
     * Add items to the array holding all suggestable items
     * 
     * @param array|string $items If string, add just one item, if array, add
     *                            the whole array to the items to suggest
     * @return ISuggester Provides fluent interface
     */
    public function addItems($items)
    {
        if (is_string($items))
            $items = array($items);

        $this->setItems(array_merge((array)$this->items, (array)$items));
        return $this;
    }

}

/* ?> omitted intentionally */

