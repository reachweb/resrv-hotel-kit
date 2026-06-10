<?php

/*
 * Kit overrides for Resrv frontend strings (Laravel merges these over the
 * package file — only the keys below change). `quantityLabel` labels the
 * quantity stepper in the availability search: this kit only exposes quantity
 * for the spa / restaurant single-date widgets, where it means people.
 */
return [
    'quantityLabel' => 'Guests',
    'date' => 'Date',
];
