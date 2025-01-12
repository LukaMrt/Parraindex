<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */

return [
    'common' => [
        'path'       => './assets/scripts/common.js',
        'entrypoint' => true,
    ],
    'carousel' => [
        'path'       => './assets/scripts/carousel.js',
        'entrypoint' => true,
    ],
    'contact' => [
        'path'       => './assets/scripts/contact.js',
        'entrypoint' => true,
    ],
    'contactAdmin' => [
        'path'       => './assets/scripts/contactAdmin.js',
        'entrypoint' => true,
    ],
    'editPerson' => [
        'path'       => './assets/scripts/editPerson.js',
        'entrypoint' => true,
    ],
    'editSponsor' => [
        'path'       => './assets/scripts/editSponsor.js',
        'entrypoint' => true,
    ],
    'popupInitialize' => [
        'path'       => './assets/scripts/popup/popupInitialize.js',
        'entrypoint' => true,
    ],
    'home' => [
        'path'       => './assets/scripts/home.js',
        'entrypoint' => true,
    ],
    'login' => [
        'path'       => './assets/scripts/login.js',
        'entrypoint' => true,
    ],
    'logoutConfirmation' => [
        'path'       => './assets/scripts/logoutConfirmation.js',
        'entrypoint' => true,
    ],
    'person' => [
        'path'       => './assets/scripts/person.js',
        'entrypoint' => true,
    ],
    'resetPassword' => [
        'path'       => './assets/scripts/resetPassword.js',
        'entrypoint' => true,
    ],
    'signup' => [
        'path'       => './assets/scripts/signup.js',
        'entrypoint' => true,
    ],
    'signupConfirmation' => [
        'path'       => './assets/scripts/signupConfirmation.js',
        'entrypoint' => true,
    ],
    'sponsor' => [
        'path'       => './assets/scripts/sponsor.js',
        'entrypoint' => true,
    ],
];
