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

use Markocupic\RszSteckbriefBundle\Model\RszSteckbriefModel;

/*
 * Backend modules
 */
$GLOBALS['BE_MOD']['rsz_tools']['rsz_steckbrief'] = [
    'tables' => ['tl_rsz_steckbrief'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_rsz_steckbrief'] = RszSteckbriefModel::class;
