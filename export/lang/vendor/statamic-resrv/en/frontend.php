<?php

/*
 * Kit overrides for Resrv frontend strings (Laravel merges these over the
 * package file — only the keys below change). `quantityLabel` labels the
 * quantity stepper in the availability search: this kit only exposes quantity
 * for the spa / restaurant single-date widgets, where it means people.
 * `selectDate` names the date field that opens the calendar; the range form
 * matches the visible Check-in / Check-out labels.
 *
 * The empty booking panel asks for what that search needs: dates for a room
 * (`pleaseSelectDates`), one date for a treatment or a table (`pleaseSelectDay`,
 * a kit key the restyled availability-results view picks for single-date
 * searches; it says "date", like the field's "Add date"). A treatment's rates
 * are its durations, so its rate list asks for one (`pleaseSelectDuration`, a kit
 * key); rooms and tables keep `pleaseSelectRateToBook`.
 * Checkout step 2 is "Your details" in the step strip, its heading and the
 * button that leads to it; stock used three names for it.
 * `startNewSearch` is the kit's way forward from a checkout with no booking.
 * `paymentAlreadyCompleted` is the lead under "Reservation confirmed" on the pay
 * page, which is also where a guest lands straight after paying by link, so it
 * no longer says "already".
 */
return [
    'quantityLabel' => 'Guests',
    'date' => 'Date',
    'selectDate' => 'Choose a date|Choose check-in and check-out dates',
    'pleaseSelectDates' => 'Choose your dates to see live rates.',
    'pleaseSelectDay' => 'Choose a date to see live prices.',
    'pleaseSelectRateToBook' => 'Choose a rate',
    'pleaseSelectDuration' => 'Choose a duration',
    'customerInfo' => 'Your details',
    'continueToPersonalDetails' => 'Continue to your details',
    'personalDetails' => 'Your details',
    'optionsDescription' => 'You can customise your booking with the options below.',
    'startNewSearch' => 'Start a new search',
    'paymentAlreadyCompleted' => 'Thank you. We look forward to welcoming you.',
];
