<?php

/**
 * A suggester that uses dibi for fetching suggestions
 *
 * You need to have dibi configured before you use this suggester (eg. you
 * have to connect to the database)
 *
 * If you search using the LIKE statement, remember that (at least) MySQL
 * doesn't allow search for patterns shorter than 3 characters
 * 
 * @package Nette\Forms
 * @version 1.0.0
 * @copyright (c) 2009 Martin Pecka (Clevis)
 * @author Martin Pecka <martin.pecka@clevis.cz> 
 * @license I mostly like BSD, but you can do everything you want with this 
 * library except of removing my name and the download link from the file 
 * (espacially you CAN use it free of charge in commercial applications)  
 *
 * @see ISuggester
 */
class DibiSuggester extends /*Nette\*/Component implements ISuggester
{

    /** @var string Name of the table we fetch suggestions from  */
    protected $table = NULL;

    /** @var string Name of the column we fetch suggestions from  */
    protected $column = NULL;

    /** @var string The WHERE clause (without WHERE), place %s in the place 
     * of the query string (do not type ' or " around it!) */
    protected $where = NULL;

    /** @var array Matched items for current query */
    protected $matches = array();

    /** @var bool Match only items beginning with query? */
    protected $matchFromBeginning = FALSE;

    /** @var bool Match only items ending with query? */
    protected $matchToEnd = FALSE;

    /**
     * Just set member variables from arguments 
     * 
     * @param string $table Name of the table we fetch suggestions from
     * @param string $column Name of the column we fetch suggestions from
     * @param string $where The WHERE clause (without WHERE), place %s in the place
     *                      of the query string (do not type ' or " around it!)
     * @return void
     */
    public function __construct($table = NULL, $column = NULL, $where = NULL)
    {
        $this->setTable($table);
        $this->setColumn($column);
        $this->setWhere($where);
    }

    /**
     * Returns array of all suggestions that match the given query string 
     * 
     * @param string $query The query to match
     * @param bool $wholeQuery If true, return only items that match the whole
     *                         query string and nothing more
     *
     * @return array The array of suggestions
     *
     * @throws InvalidStateException if at least one of (table, column, where)
     * is empty or NULL
     */
    public function getSuggestions($query, $wholeQuery = FALSE)
    {
        //intentionally ==
        if ($this->table == '' || $this->column == '' || $this->where == '') {
            throw new InvalidStateException(
                'Neither $table, $column nor $where can be empty or NULL in' .
                __CLASS__ . '::' . __FUNCTION__);
        }

        if (!$wholeQuery && !$this->matchFromBeginning())
            $query = '%' . $query;

        if (!$wholeQuery && !$this->matchToEnd())
            $query .= '%';

        $matches = dibi::select($this->column)
            ->from($this->table)
            ->where($this->where, $query)
            ->fetchAll();

        $this->matches = array();
        foreach ($matches as $match)
            $this->matches[] = $match[$this->column];

        return $this->matches;
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
     * Returns the name of the table we fetch suggestions from 
     * 
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the name of the table we fetch suggestions from 
     * 
     * @param string $value The new table name
     * @return ISuggester Provides fluent interface
     */
    public function setTable($value)
    {
        $this->table = $value;
        return $this;
    }

    /**
     * Returns the name of the column we fetch suggestions from 
     * 
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Sets the name of the column we fetch suggestions from 
     * 
     * @param string $value The new column name
     * @return ISuggester Provides fluent interface
     */
    public function setColumn($value)
    {
        $this->column = $value;
        return $this;
    }

    /**
     * Returns the WHERE clause we use when searching for matching suggestions
     * 
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Sets the WHERE clause we use when searching for matching suggestions
     *
     * Place %s in the place of the typed text (do not type ' or " around it!)
     *
     * @example Example value: '[text] LIKE %s' (you should enclose column 
     * identifiers in [])
     * 
     * @param string $value The new WHERE clause (in dibi format)
     *
     * @return ISuggester Provides fluent interface
     */
    public function setWhere($value)
    {
        $this->where = $value;
        return $this;
    }

}

/* ?> omitted intentionally */

