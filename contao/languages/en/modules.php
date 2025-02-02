<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Steckbrief Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-steckbrief-bundle
 */

use Markocupic\RszSteckbriefBundle\Controller\FrontendModule\RszSteckbriefListingController;
use Markocupic\RszSteckbriefBundle\Controller\FrontendModule\RszSteckbriefReaderController;

/*
 * Backend modules
 */
$GLOBALS['TL_LANG']['MOD']['rsz_tools'] = 'RSZ Tools';
$GLOBALS['TL_LANG']['MOD']['rsz_steckbrief'] = ['RSZ Steckbrief', 'Steckbrief-Modul für Regionalkader Sportklettern Zentralschweiz'];

/*
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['rsz_frontend_modules'] = 'RSZ Frontend Module';
$GLOBALS['TL_LANG']['FMD'][RszSteckbriefListingController::TYPE] = ['RSZ Steckbrief Liste', 'Fügen Sie der Seite eine RSZ Steckbrief Liste hinzu.'];
$GLOBALS['TL_LANG']['FMD'][RszSteckbriefReaderController::TYPE] = ['RSZ Steckbrief Reader', 'Fügen Sie der Seite einen RSZ Steckbrief Reader hinzu.'];
