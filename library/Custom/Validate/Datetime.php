<?php
require_once 'Zend/Validate/Abstract.php';

/**
 *
 * $v->isValid('2011-04-29 10:00:00'); // TRUE,  valid ISO 8601 format
 * $v->isValid('04-2011-29', 'm-Y-d'); // TRUE,  valid date in given format
 * $v->isValid('2011-04-29 25:00:00'); // FALSE, invalid format
 * $v->isValid('2011-02-29 10:00:00'); // FALSE, valid format, but invalid date
 *
 * $v->isValid('10:45', 'H:i');
 * print_r($v->getMatchedParts()); // array('hour' => '10', 'minute' => '45')
 *
 */
class Custom_Validate_Datetime extends Zend_Validate_Abstract
{
	const INVALID_FORMAT = 'invalidFormat';
	const INVALID_DATE   = 'invalidDate';
	const ERROROUS       = 'regexErrorous';

	protected $_messageTemplates = array(
		self::INVALID_FORMAT => "'%value%' is not in valid date format",
		self::INVALID_DATE   => "'%value%' is not valid date",
		self::ERROROUS       => 'There was an internal error while using the pattern %pattern%'
	);

	protected $_messageVariables = array(
		'pattern' => '_pattern'
	);

	protected $_pattern;

	/**
	 * Disable matching against default formats
	 *
	 * @var boolean
	 */
	protected $_disableLoadDefaultFormats = false;

	/**
	 * Matched date format
	 *
	 * @var string
	 */
	protected $_matchedFormat = null;

	/**
	 * Matched date parts
	 *
	 * @var array
	 */
	protected $_matchedParts = null;

	/**
	 * User specified date formats
	 *
	 * @var array
	 */
	protected $_formats = array();

	/**
	 * Default date formats
	 *
	 * Value is defaulty tried to match against these formats
	 *
	 * @var array
	 */
	protected $_defaultFormats = array(
		// ISO 8601 international standard
		'Y-m-d H:i:s',
    'Y-m-d H:i',
		'Y-m-d',

		// Little endian forms, starting with the day
		'j/n/Y', // without leading zeros
		'j.n.Y',
		'j-n-Y',
		'd/m/Y', // with leading zeros
		'd.m.Y',
		'd-m-Y',

		// Big endian forms, starting with the year
		'Y/n/j', // without leading zeros
		'Y.n.j',
		'Y/m/d', // with leading zeros
		'Y.m.d'
	);
	
	/**
	 * Date format characters mapped to the corresponding regexp pattern
	 *
	 * @var array
	 */
	protected $_formatCharRegexps = array(
		// Numeric presentation of date parts _without_ leading zeros
		'Y' => '[0-9]{4}',
		'm' => '[0-9]{2}',
		'd' => '[0-9]{2}',

		// Numeric presentation of date parts _with_ leading zeros
		'y' => '[0-9]{2}',
		'n' => '[1-9]|[1-9][0-9]',
		'j' => '[1-9]|[1-9][0-9]',

		// Hours
		'g' => '[1-9]|1[0-2]',
		'h' => '0[1-9]|1[1-2]',
		'G' => '[0-9]|1[0-9]|2[0-3]',
		'H' => '0[0-9]|1[0-9]|2[0-3]',

		// Minute
		'i' => '[0-5][0-9]',

		// Second
		's' => '[0-5][0-9]'
	);

	/**
	 * Name of the date format character
	 *
	 * This list is used when an array of matched date parts is created.
	 *
	 * @var array
	 */
	protected $_formatCharName = array(
		'Y' => 'year',   'y' => 'year',
		'm' => 'month',  'n' => 'month',
		'd' => 'day',    'j' => 'day',
		'g' => 'hour',   'h' => 'hour', 'G' => 'hour', 'H' => 'hour',
		'i' => 'minute', 's' => 'second'
	);

	public function __construct($options = null)
	{
    if ($options instanceof Zend_Config) {
			$options = $formats->toArray();
		}

		if ($options !== null) {
			$this->setOptions($options);
		}
	}

	public function setOptions(array $options)
	{
		if (array_key_exists('formats', $options)) {
			$this->setFormats($options['formats']);
		}
		if (array_key_exists('disableLoadDefaultFormats', $options)) {
			$this->setDisableLoadDefaultFormats($options['disableLoadDefaultFormats']);
		}
	}

	public function setFormats(array $formats)
	{
		$this->_formats = $formats;
		return $this;
	}

	public function getFormats()
	{
		return $this->_formats;
	}

	public function getMatchedFormat()
	{
		return $this->_matchedFormat;
	}

	public function getMatchedParts()
	{
		return $this->_matchedParts;
	}

	public function setDisableLoadDefaultFormats($flag)
	{
		$this->_disableLoadDefaultFormats = (boolean) $flag;
	}

	/**
	 * Validate date
	 *
	 * @param string @value date
	 */
	public function isValid($value)
	{
		$value = (string) $value;
    
		$formats = $this->getFormats();

		if (!$this->_disableLoadDefaultFormats) {
			$formats = array_merge($formats, $this->_defaultFormats);
		}

		// Test value against date formats
		foreach ($formats as $format) {
			$pattern = $this->_formatToPattern($format);
			$match   = preg_match("/^$pattern$/", $value, $matches);

			if ($match === false) {
				$this->_pattern = $pattern;
				$this->_error(self::ERROROUS);
				return false;
			}
			if ($match === 1) {
				// Unique match found, resolve date parts and break the loop
				$this->_matchedFormat = $format;
				$parts = $this->_trimMatches($matches);
				break;
			}
			// else continue to the next format
		}
		if (!isset($parts)) {
			// No match, invalid format
			$this->_setValue($value);
			$this->_error(self::INVALID_FORMAT);
			return false;
		}

		// Date has to be still valid, e.g. 29.02.2011 is not valid
		// because 28 was the last day in February 2011
		if (isset($parts['month']) && isset($parts['day']) && isset($parts['year'])) {
			if (!checkdate($parts['month'], $parts['day'], $parts['year'])) {
				$this->_setValue($value);
				$this->_error(self::INVALID_DATE);
				return false;
			}
		}

		$this->_matchedParts = $parts;
		return true;
	}

	protected function _formatToPattern($format)
	{
		$format  = $this->_escapeFormat($format);
		$pattern = '';

		foreach (str_split($format) as $char) {
			if (array_key_exists($char, $this->_formatCharRegexps)) {
				$cname = $this->_formatCharName[$char];
				$cregexp = $this->_formatCharRegexps[$char];
				$pattern .= "(?P<$cname>$cregexp)";
			} else {
				$pattern .= $char;
			}
		}

		return $pattern;
	}

	protected function _escapeFormat($format)
	{
		$search = array(
			'/',
			'.'
		);
		$replace = array(
			'\/',
			'\.'
		);
		return str_replace($search, $replace, $format);
	}

	protected function _trimMatches($matches)
	{
		$l = count($matches);
		for ($i = 0; $i < $l; $i++) {
			unset($matches[$i]);
		}
		return $matches;
	}
}
