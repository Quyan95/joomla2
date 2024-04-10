<?php

/**
 * @package         Convert Forms
 * @version         4.2.4 Pro
 *
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class AWeberEntryDataArray implements ArrayAccess, Countable, Iterator
{
	private $counter = 0;

	protected $data;
	protected $keys;
	protected $name;
	protected $parent;

	public function __construct($data, $name, $parent)
	{
		$this->data   = $data;
		$this->keys   = array_keys($data);
		$this->name   = $name;
		$this->parent = $parent;
	}

	public function count()
	{
		return sizeOf($this->data);
	}

	public function offsetExists($offset)
	{
		return (isset($this->data[$offset]));
	}

	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->data[$offset]         = $value;
		$this->parent->{$this->name} = $this->data;
		return $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	public function rewind()
	{
		$this->counter = 0;
	}

	public function current()
	{
		return $this->data[$this->key()];
	}

	public function key()
	{
		return $this->keys[$this->counter];
	}

	public function next()
	{
		$this->counter++;
	}

	public function valid()
	{
		if ($this->counter >= sizeOf($this->data))
		{
			return false;
		}
		return true;
	}

}

?>
