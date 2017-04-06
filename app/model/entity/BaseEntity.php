<?php

namespace App\Model\Entity;

class BaseEntity {

	/**
	 * @param string $date
	 * @param string $format
	 * @return bool
	 */
	protected function isDateValid($date, $format) {
		$return = false;
		if(!empty($date)) {
			$d = \DateTime::createFromFormat($format, $date);
			$return = $d && $d->format('Y-m-d') === $date;
		}

		return $return;
	}
}