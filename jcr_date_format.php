<?php
if (txpinterface === 'public') {

    // TODO check timezone offset for date_format

    if (class_exists('\Textpattern\Tag\Registry')) {
        Txp::get('\Textpattern\Tag\Registry')
                ->register('jcr_date_range')
                ->register('jcr_date_format')
        ;
    }

    /**
     * Output a date range string
     *
     * @param array $atts
     *
     * @return string
     */

    function jcr_date_range($atts)
    {
        extract(lAtts(array(
            'start_date'     => '',
            'end_date'       => '',
            'start_time'     => '',
            'input_format'   => '%Y-%m-%d',
            'separator'      => '&thinsp;–&thinsp;',
            'until'          => 'bis',
            'concise'        => false
        ), $atts));


        if (empty($start_date)) {
            return;
        }

        $start_equals_end = false;
        //  if event_end = event_start or event_end not specified, start = end
        if (!$end_date || $start_date == $end_date) {
            $start_equals_end = true; // one-day event
        }

        // TODO: better time format validation
        if (empty($start_time)) {
            $start_time = '00:00';
        }

        // TODO: make end time settable?
        $end_time ='23:59';

        // convert to begin and end timestamps
        $begin = safe_strtotime($start_date . ' ' . $start_time);
        $end = ($start_equals_end) ? $begin : safe_strtotime($end_date . ' ' . $end_time);
        $today = safe_strtotime(date("Y-m-d"));

        // is start day already past
        $start_is_in_past = ($begin < $today) ? true : false;


        // string formatting
        $end_format = $concise ? '%e.%m.%y' : '%e.&#160;%b&#160;%Y';

        // if same year
        if (safe_strftime('%G', $begin) == safe_strftime('%G', $end)) {

            if (safe_strftime('%m', $begin) == safe_strftime('%m', $end)) {
                // and same month
                $start_format = '%e.';
            } else {
                // but not same month
                $start_format = $concise ? '%e.%m' : '%e.&#160;%b';
            }
        } else {
            // different year
            $start_format = $end_format;
        }

        // output cases
        if ($start_equals_end) {
            // one-day event: just the date
            $out = safe_strftime($end_format, $begin);
        } else if ($start_is_in_past) {
            // until end date
            $out = $until . ' ' . safe_strftime($end_format, $end);
        } else {
            // multi-day event formated from-to date
            $out = safe_strftime($start_format, $begin) .
                   $separator .
                   safe_strftime($end_format, $end);
        }

        return $out;
    }

    /**
     * Format a time string
     * If unspecified returns current time
     *
     * @param array $date
     *
     * @return string
     */
    function jcr_date_format($atts)
    {
        extract(lAtts(array(
            'date'     => '',
            'format'   => '%G-%m-%d %H:%M'
        ), $atts));

        // get timestamp, if not specified use current time
        $timestamp = (empty($date)) ? time() : safe_strtotime($date);

        // return timestamp or formatted time
        return ($format == "timestamp") ? $timestamp : intl_strftime($format, $timestamp);
    }

}
