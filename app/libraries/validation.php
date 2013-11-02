<?php
/**
 * DMyers Super Simple MVC
 *
 * @package    Bootstrap File
 * @language   PHP
 * @author     Don Myers
 * @copyright  Copyright (c) 2011
 * @license    Released under the MIT License.
 *
 * Some portions copyright CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 */

// ------------------------------------------------------------------------

class validation {
	public $valid = false;
	public $errors = array();
	public $posted = array();

	function __construct() {
		$this->language();
	}

	function validate($fields,$validate_fields,&$posted=null) {
		// clear some stuff
		$this->valid = false;
		$this->errors = array();

		$labels = array();
		foreach ($validate_fields as $key => $value)
			$labels[$key] = $value['label'];

		$this->posted = ($posted == null) ? $_POST : $posted;

		// only process these fields
		foreach ($fields as $key=>$field)
			if (array_key_exists($field,$validate_fields))
				$validate_these[$field] = $validate_fields[$field];

		// validate nothing!
		if (count($validate_these) == 0) return true;

		// ok now process them
		foreach ($validate_these as $field => $validation) {
			// Get validation settings
			$label = $validation['label'];
			$rules = $validation['rules'];

			foreach ($rules as $rule) {
				$result = '';

				// Strip the parameter (if exists) from the rule
				// Rules can contain a parameter: max_length[5]
				$param = '';
				if (preg_match("/(.*?)\[(.*?)\]/", $rule, $match)) {
					$rule = $match[1];
					$param  = $match[2];
				}

				if (method_exists($this,'_'.$rule)) {
					$result = !call_user_func_array(array($this,'_'.$rule),array($field,$param));
				} else if (function_exists($rule)) { // PHP Function?
					$this->posted[$field] = call_user_func($rule,$this->posted[$field]);
				}

				// did we get a error?
				if ($result == 1) {
					// Get corresponding error from language file
					$line = $this->geterror($rule);

					// Check if param is an array
					if (is_array($param)) {
						// Convert into a string so it can be used in the error message
						$param = implode(', ', $param);

						// Replace last ", " with " or "
						if (false !== ($pos = strrpos($param, ', '))) {
							$param = substr_replace($param, ' or ', $pos, 2);
						}
					}

					// Check if param is a validation field
					if (array_key_exists($param,$labels)) {
						// Change it to the label value
						$param = $labels[$param];
					}

					// Add error message
					$this->errors[][$field] = sprintf($line, $label, $param);
				}
			}
		}

		// Set whether validation passed
		$this->valid = (count($this->errors) == 0);
		return $this->posted; // return prepped input array
	}

	function json() {
		return array('valid'=>$this->valid,'errors'=>$this->errors);
	}

	// validations
	function _required($field) {
		return !empty($this->posted[$field]);
	}

	/**
		* Alpha Dash Dot (pre-process)
		*
		* Alpha-numeric with underscores, dashes and full stops.
		*
		* @access  private
		* @param string
		* @return  bool
		*/
	function _alpha_dash_dot($field) {
		return (!preg_match('/^([\.-a-z0-9_-])+$/i', $this->posted[$field])) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
		* Alpha Slash Dot (pre-process)
		*
		* Alpha-numeric with underscores, dashes, forward slashes and full stops.
		*
		* @access  private
		* @param string
		* @return  bool
		*/
	function _alpha_slash_dot($field) {
		return (!preg_match('/^([\.\/-a-z0-9_-])+$/i', $this->posted[$field])) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
		* Matches (pre-process)
		*
		* Match one field to another.
		* This replaces the version in CI_Form_validation.
		*
		* @access  private
		* @param string
		* @param string
		* @return  bool
		*/
	function _matches($field, $other_field) {
		//echo 'field '.$field.' other_field '.$other_field;
		return ($this->posted[$field] !== $this->posted[$other_field]) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
		* Min Date (pre-process)
		*
		* Checks if the value of a property is at least the minimum date.
		*
		* @access  private
		* @param string
		* @param string
		* @return  bool
		*/
	function _min_date($field, $date) {
		return (strtotime($this->posted[$field]) < strtotime($date)) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
		* Max Date (pre-process)
		*
		* Checks if the value of a property is at most the maximum date.
		*
		* @access  private
		* @param string
		* @param string
		* @return  bool
		*/
	function _max_date($field, $date) {
		return (strtotime($this->posted[$field]) > strtotime($date)) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
		* Min Size (pre-process)
		*
		* Checks if the value of a property is at least the minimum size.
		*
		* @access  private
		* @param string
		* @param integer
		* @return  bool
		*/
	function _min_size($field, $size) {
		return (strlen($this->posted[$field]) < $size) ? false : true;
	}

	// --------------------------------------------------------------------

	/**
		* Max Size (pre-process)
		*
		* Checks if the value of a property is at most the maximum size.
		*
		* @access  private
		* @param string
		* @param integer
		* @return  bool
		*/
	function _max_size($field, $size) {
		return (strlen($this->posted[$field]) > $size) ? false : true;
	}


	function _exact_size($str, $val) {
		if (preg_match("/[^0-9]/", $val)) {
			return FALSE;
		}

		if (function_exists('mb_strlen')) {
			return (mb_strlen($str) != $val) ? FALSE : TRUE;
		}

		return (strlen($str) != $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
		* Unique (pre-process)
		*
		* Checks if the value of a property is unique.
		* If the property belongs to this object, we can ignore it.
		*
		* @access  private
		* @param string
		* @return  bool
		*/
	function _unique($field) {
		$val = $this->posted[$field];
		if (!empty($val)) {
			$query = $this->CI->db->get_where($this->tablename, array($field => $this->posted[$field]), 1, 0);
			if ($query->num_rows() > 0) {
				$row = $query->row();

				// If unique value does not belong to this object
				if ($this->primary() != $row->id) {
					// Then it is not unique
					return false;
				}
			}
		}

		// No matches found so is unique
		return true;
	}

	// --------------------------------------------------------------------

	/**
		* Unique Pair (pre-process)
		*
		* Checks if the value of a property, paired with another, is unique.
		* If the properties belongs to this object, we can ignore it.
		*
		* @access  private
		* @param string
		* @param string
		* @return  bool
		*/
	function _unique_pair($field, $other_field = '') {
		if ( ! empty($this->posted[$field]) && ! empty($this->{$other_field})) {
			$query = $this->CI->db->get_where($this->tablename, array($field => $this->posted[$field], $other_field => $this->{$other_field}), 1, 0);

			if ($query->num_rows() > 0) {
				$row = $query->row();

				// If unique pair value does not belong to this object
				if ($this->primary() != $row->id) {
					// Then it is not a unique pair
					return false;
				}
			}
		}

		// No matches found so is unique
		return true;
	}

	// --------------------------------------------------------------------

	/**
		* Valid Date (pre-process)
		*
		* Checks whether the field value is a valid DateTime.
		*
		* @access  private
		* @param string
		* @return  bool
		*/
	function _valid_date($field) {
		// Ignore if empty
		if (empty($this->posted[$field])) return true;

		$date = date_parse($this->posted[$field]);

		return checkdate($date['month'],$date['day'],$date['year']);
	}

	// --------------------------------------------------------------------

	/**
		* Valid Date Group (pre-process)
		*
		* Checks whether the field value, grouped with other field values, is a valid DateTime.
		*
		* @access  private
		* @param string
		* @param array
		* @return  bool
		*/
	function _valid_date_group($field, $fields = array()) {
		// Ignore if empty
		if (empty($this->posted[$field])) return true;

		$date = date_parse($this->{$fields['year']}.'-'.$this->{$fields['month']} .'-'. $this->{$fields['day']});

		return checkdate($date['month'],$date['day'],$date['year']);
	}

	// --------------------------------------------------------------------

	/**
		* Valid Match (pre-process)
		*
		* Checks whether the field value matches one of the specified array values.
		*
		* @access  private
		* @param string
		* @param array
		* @return  bool
		*/
	function _valid_match($field, $param = array()) {
		return ($this->posted[$field] == $this->posted[$param]);
	}

	// --------------------------------------------------------------------

	/**
		* Encode PHP Tags (prep)
		*
		* Convert PHP tags to entities.
		* This replaces the version in CI_Form_validation.
		*
		* @access  private
		* @param string
		* @return  void
		*/
	function _encode_php_tags($field) {
		$this->posted[$field] = encode_php_tags($this->posted[$field]);
	}

	// --------------------------------------------------------------------

	/**
		* Prep for Form (prep)
		*
		* Converts special characters to allow HTML to be safely shown in a form.
		* This replaces the version in CI_Form_validation.
		*
		* @access  private
		* @param string
		* @return  void
		*/
	function _prep_for_form($field) {
		$this->posted[$field] = $this->CI->form_validation->prep_for_form($this->posted[$field]);
	}

	// --------------------------------------------------------------------

	/**
		* Prep URL (prep)
		*
		* Adds "http://" to URLs if missing.
		* This replaces the version in CI_Form_validation.
		*
		* @access  private
		* @param string
		* @return  void
		*/
	function _prep_url($field) {
		$this->posted[$field] = $this->CI->form_validation->prep_url($this->posted[$field]);
	}

	// --------------------------------------------------------------------

	/**
		* Strip Image Tags (prep)
		*
		* Strips the HTML from image tags leaving the raw URL.
		* This replaces the version in CI_Form_validation.
		*
		* @access  private
		* @param string
		* @return  void
		*/
	function _strip_image_tags($field) {
		$this->posted[$field] = strip_image_tags($this->posted[$field]);
	}

	// --------------------------------------------------------------------

	/**
		* XSS Clean (prep)
		*
		* Runs the data through the XSS filtering function, described in the Input Class page.
		* This replaces the version in CI_Form_validation.
		*
		* @access  private
		* @param string
		* @param bool
		* @return  void
		*/
	function _xss_clean($field, $is_image = false) {
		$this->CI->load->helper('security');
		$this->posted[$field] = xss_clean($this->posted[$field],$is_image);
	}

	/**
		* Additional - Patched on Validations
		*
		* These are patched on Validations added to the base DataMapper Set
		*
		* @access  private
		* @param string
		* @return  bool
		*/

	function _alpha_number_space($field) {
		return (!preg_match('#^[a-zA-Z0-9 ]+$#i', $this->posted[$field])) ? false : true;
	}

	function _alpha_space($field) {
		return (!preg_match('#^[a-zA-Z ]+$#i', $this->posted[$field])) ? false : true;
	}

	function _dollars($field) {
		return (!preg_match('#^\$?\d+(\.(\d{2}))?$#', $this->posted[$field])) ? false : true;
	}

	function _percent($field) {
		return (!preg_match('#^\s*(\d{0,2})(\.?(\d*))?\s*\%?$#', $this->posted[$field])) ? false : true;
	}

	function _float($field) {
		return (!preg_match('#^[-+]?[0-9]+\.?[0-9]*$#', $this->posted[$field])) ? false : true;
	}

	function _integer($field) {
		return (!preg_match('#^[0-9]+$#', $this->posted[$field])) ? false : true;
	}

	function _zip($field) {
		return (!preg_match('#^\d{5}$|^\d{5}-\d{4}$#', $this->posted[$field])) ? false : true;
	}

	function _phone($field) {
		return (!preg_match('/^\(?([2-9]\d{2})\)?[\.\s-]?([2-4|6-9]\d\d|5([0-4-|6-9]\d|\d[0-4|6-9]))[\.\s-]?(\d{4})$/', $this->posted[$field])) ? false : true;
	}

	function _hexcolor($field) {
		return (!preg_match('/(^[\w\.!#$%"*+\/=?`{}|~^-]+)@(([-\w]+\.)+[A-Za-z]{2,})$/', $this->posted[$field])) ? false : true;
	}

	function _url($field) {
		return (!preg_match('/^(https?|ftp):\/\/([-\w]+\.)+[A-Za-z]{2,}(:\d+)?([\\\/]\S+)*?[\\\/]?(\?\S*)?$/i', $this->posted[$field])) ? false : true;
	}

	function _valid_email($field) {
		return (!preg_match('/^[a-z0-9_-]+(\.[a-z0-9_-]+)*@[a-z0-9_-]+(\.[a-z0-9_-]+)+$/i', $this->posted[$field])) ? false : true;
	}

	function _valid_emails($str) {
		if (strpos($str, ',') === FALSE) {
			return $this->_email(trim($str));
		}

		foreach(explode(',', $str) as $email) {
			if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE) {
				return FALSE;
			}
		}

		return TRUE;
	}

	function _alpha($str) {
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}

	function _alpha_numeric($str) {
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}

	function _alpha_dash($str) {
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}

	function _numeric($str) {
		return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
	}

	function _is_numeric($str) {
		return ( ! is_numeric($str)) ? FALSE : TRUE;
	}

	function _is_natural($str) {
		return (bool)preg_match( '/^[0-9]+$/', $str);
	}

	function _is_natural_no_zero($str) {
		if ( ! preg_match( '/^[0-9]+$/', $str)) {
			return FALSE;
		}

		if ($str == 0) {
			return FALSE;
		}

		return TRUE;
	}

	function _valid_base64($str) {
		return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
	}

	function language() {
		$this->lang['required']       = "The %s field is required.";
		$this->lang['isset']        = "The %s field must have a value.";
		$this->lang['valid_email']    = "The %s field must contain a valid email address.";
		$this->lang['valid_emails']     = "The %s field must contain all valid email addresses.";
		$this->lang['valid_url']       = "The %s field must contain a valid URL.";
		$this->lang['valid_ip']       = "The %s field must contain a valid IP.";
		$this->lang['min_length']      = "The %s field must be at least %s characters in length.";
		$this->lang['max_length']      = "The %s field can not exceed %s characters in length.";
		$this->lang['exact_length']    = "The %s field must be exactly %s characters in length.";
		$this->lang['alpha']        = "The %s field may only contain alphabetical characters.";
		$this->lang['alpha_numeric']    = "The %s field may only contain alpha-numeric characters.";
		$this->lang['alpha_dash']      = "The %s field may only contain alpha-numeric characters, underscores, and dashes.";
		$this->lang['numeric']      = "The %s field must contain a number.";
		$this->lang['is_numeric']      = "The %s field must contain a number.";
		$this->lang['integer']      = "The %s field must contain an integer.";
		$this->lang['matches']      = "The %s field does not match the %s field.";
		$this->lang['is_natural']      = "The %s field must contain a number.";
		$this->lang['is_natural_no_zero']  = "The %s field must contain a number greater than zero.";

		$this->lang['alpha_dash_dot']    = 'The %s field may only contain alpha-numeric characters, underscores, dashes, and full stops.';
		$this->lang['alpha_slash_dot']  = 'The %s field may only contain alpha-numeric characters, underscores, dashes, slashes, and full stops.';
		$this->lang['min_date']    = 'The %s field must be at least %s.';
		$this->lang['max_date']    = 'The %s field can not exceed %s.';
		$this->lang['min_size']    = 'The %s field must be at least %s characters.';
		$this->lang['max_size']    = 'The %s field can not exceed %s characters.';
		$this->lang['transaction']    = 'The %s failed to %s.';
		$this->lang['unique']     = 'The %s you supplied is already taken.';
		$this->lang['unique_pair']     = 'The combination of %s and %s you supplied is already taken.';
		$this->lang['valid_date']    = 'The %s field must contain a valid date.';
		$this->lang['valid_date_group']  = 'The %2$s fields must contain a valid date.';
		$this->lang['valid_match']    = 'The %s field may only be %s.';

		$this->lang['related_required']  = 'The %s relationship is required.';
		$this->lang['related_min_size']  = 'The %s relationship must be at least %s.';
		$this->lang['related_max_size']  = 'The %s relationship can not exceed %s.';

		/** custom messages */
		$this->lang['username_check']  = '%s user name check failed'; // custom function test

		$this->lang['alpha_number_space'] = 'The %s field may only contain alpha-numeric characters and space.';
		$this->lang['alpha_space'] = 'The %s field may only contain alpha characters and space.';
		$this->lang['dollars'] = 'The %s field may only contain a dollar amount.';
		$this->lang['phone'] = 'The %s field may only contain a valid us phone number.';
		$this->lang['percent'] = 'The %s field may only contain a valid percent.';
		$this->lang['hexcolor'] = 'The %s field may only contain a valid w3c hex formatted color.';
		$this->lang['float'] = 'The %s field may only contain floating number.';
		$this->lang['integer'] = 'The %s field may only contain integer.';
		$this->lang['zip'] = 'The %s field may only contain a valid us zip code.';
	}

	function geterror($rule) {
		if (array_key_exists($rule,$this->lang))
			return $this->lang[$rule];

		return str_replace('_',' ',ucwords($rule));
	}

} /* close validation class */