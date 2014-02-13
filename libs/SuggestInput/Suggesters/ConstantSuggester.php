<?php

namespace Nette\Addons\SuggestInput;

use \Nette\Utils\Strings;

/**
 * A suggester that always returns the same array
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
class ConstantSuggester extends \Nette\ComponentModel\Component implements ISuggester
{

    /** @var array The array holding all suggestable items */
    protected $items;

    /**
     * Set items 
     * 
     * @param array|string|NULL $items The array holding all items to suggest
     * @return void
     */
    public function __construct($items = NULL)
    {
        $this->setItems($items);
    }

    /**
     * Returns array of all suggestions
     * 
     * @param string $query Unused here
     *
     * @return array The array of suggestions
     */
    public function getSuggestions($query) {
        return $this->items;
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
        $query = preg_quote($query);

        foreach ($this->items as $item) {
            if (preg_match('/^' . $query . '$/', Strings::lower($item))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the array holding all items to suggest
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
     * Returns the array of all suggestable items 
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add items to the array holding all items to suggest
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
