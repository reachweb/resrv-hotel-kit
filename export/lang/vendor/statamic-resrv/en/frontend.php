<?php

/*
 * Kit overrides for Resrv frontend strings (Laravel merges these over the
 * package file — only the keys below change). `quantityLabel` labels the
 * quantity stepper in the availability search: this kit only exposes quantity
 * for the spa / restaurant single-date widgets, where it means people.
 * `selectDate` names the date field that opens the calendar; the range form
 * matches the visible Check-in / Check-out labels.
 */
return [
    'quantityLabel' => 'Guests',
    'date' => 'Date',
    'selectDate' => 'Choose a date|Choose check-in and check-out dates',
];
