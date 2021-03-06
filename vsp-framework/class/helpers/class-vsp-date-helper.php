<?php

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 14-02-2018
 * Time: 03:57 PM
 */
Class VSP_Date_Time extends \DateTime {

    /**
     * Outputs an ISO 8601 date string in local timezone.
     * @since 4.6.0
     * @return string
     */
    public function __toString() {
        return $this->format(DATE_ATOM);
    }

    /**
     * Gets a date based on the offset timestamp.
     * @since 4.6.0
     * @param  string $format date format
     * @return string
     */
    public function date($format) {
        return gmdate($format, $this->getOffsetTimestamp());
    }

    /**
     * Gets the timestamp with the WordPress timezone offset added or subtracted.
     * @since 4.6.0
     * @return int
     */
    public function getOffsetTimestamp() {
        return $this->getTimestamp() + $this->getOffset();
    }

    /**
     * Gets the UTC timestamp.
     * Missing in PHP 5.2.
     * @since 4.6.0
     * @return int
     */
    public function getTimestamp() {
        return method_exists('DateTime', 'getTimestamp') ? parent::getTimestamp() : $this->format('U');
    }


    /**
     * Gets a localised date based on offset timestamp.
     * @since 4.6.0
     * @param  string $format date format
     * @return string
     */
    public function date_i18n($format = 'Y-m-d') {
        return date_i18n($format, $this->getOffsetTimestamp());
    }
}