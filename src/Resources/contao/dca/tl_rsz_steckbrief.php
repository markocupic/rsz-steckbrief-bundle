<?php

/*
 * This file is part of RSZ Steckbrief Bundle.
*
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-steckbrief-bundle
 */

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\UserModel;

/**
 * Table tl_rsz_steckbrief
 */
$GLOBALS['TL_DCA']['tl_rsz_steckbrief'] = array(
	// Config
	'config'   => array(
		'ptable'           => 'tl_user',
		'dataContainer'    => 'Table',
		'enableVersioning' => true,
		'onload_callback'  => array(
			array("tl_rsz_steckbrief", "createProfiles"),
			array("tl_rsz_steckbrief", "filterList")
		),
		'notDeletable'     => true,
		'notSortable'      => true,
		'notCopyable'      => true,
		'notSortable'      => true,
		'notCreatable'     => true,
		'sql'              => array(
			'keys' => array(
				'id'  => 'primary',
				'pid' => 'index'
			)
		)
	),
	// List
	'list'     => array(
		'sorting'           => array(
			'mode'            => 1,
			// Do not permit access to foreign profiles (except admins)
			'filter'          => array(array('pid=?', BackendUser::getInstance()->id)),
			'fields'          => array('pid'),
			'flag'            => 1,
			'disableGrouping' => true,
		),
		'label'             => array(
			'fields'         => array('pid'),
			'label_callback' => array('tl_rsz_steckbrief', 'labelCallback')
		),
		'global_operations' => array(
			'all' => array(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations'        => array(
			'edit'   => array(
				'label' => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),
			'delete' => array(
				'label'      => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'default' => '{publish_legend},aktiv;{gallery},multiSRC,image_description,video_integration;{competitions},klettert_seit,best_competition_results;{indoorleistungen},schwerste_rotpunktroute_indoor,schwerste_boulderroute_indoor; {outdoorleistungen_routen},schwerste_route_gebiet,schwerste_route_routenname,schwerste_route_difficulty;{outdoorleistungen_boulders},schwerster_boulder_gebiet,schwerster_boulder_routenname,schwerster_boulder_difficulty;{allgemeines},lieblingsklettergebiet,sponsoren,ziele,leitsatz,hobbies'
	),
	// Fields
	'fields'   => array(
		'id'                             => array(
			'sql' => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid'                            => array(
			'foreignKey' => 'tl_user.username',
			'sql'        => "int(10) unsigned NOT NULL default '0'",
			'relation'   => array('type' => 'belongsTo', 'load' => 'lazy')
		),
		'tstamp'                         => array(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'aktiv'                          => array(
			'exclude'   => true,
			'flag'      => 1,
			'inputType' => 'checkbox',
			'sql'       => "int(1) unsigned NOT NULL default '0'"
		),
		'multiSRC'                       => array(
			'exclude'   => true,
			'inputType' => 'fileTree',
			'eval'      => array('multiple' => true, 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png', 'files' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderSRC'),
			'sql'       => "blob NULL",
		),
		'orderSRC'                       => array(
			'sql' => "blob NULL"
		),
		'image_description'              => array(
			'exclude'     => true,
			'flag'        => 1,
			'inputType'   => 'textarea',
			'explanation' => &$GLOBALS['TL_LANG']['tl_rsz_steckbrief']['image_description_explanation'],
			'eval'        => array('allowHtml' => false),
			'sql'         => "text NOT NULL default ''",
		),
		'klettert_seit'                  => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'best_competition_results'       => array(
			'exclude'   => true,
			'inputType' => 'textarea',
			'eval'      => array('allowHtml' => false, 'tl_class' => 'clr'),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerste_rotpunktroute_indoor' => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerste_boulderroute_indoor'  => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerste_route_gebiet'         => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerste_route_routenname'     => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerste_route_difficulty'     => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerster_boulder_gebiet'      => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerster_boulder_routenname'  => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'schwerster_boulder_difficulty'  => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		),
		'lieblingsklettergebiet'         => array(
			'exclude'   => true,
			'inputType' => 'textarea',
			'eval'      => array('allowHtml' => false, 'tl_class' => 'clr'),
			'sql'       => "text NOT NULL default ''",
		),
		'sponsoren'                      => array(
			'exclude'   => true,
			'inputType' => 'textarea',
			'eval'      => array('allowHtml' => false, 'tl_class' => 'clr'),
			'sql'       => "text NOT NULL default ''",
		),
		'ziele'                          => array(
			'exclude'   => true,
			'inputType' => 'textarea',
			'eval'      => array('allowHtml' => false, 'tl_class' => 'clr'),
			'sql'       => "text NOT NULL default ''",
		),
		'leitsatz'                       => array(
			'exclude'   => true,
			'inputType' => 'textarea',
			'eval'      => array('allowHtml' => false, 'tl_class' => 'clr'),
			'sql'       => "text NOT NULL default ''",
		),
		'hobbies'                        => array(
			'exclude'   => true,
			'inputType' => 'textarea',
			'eval'      => array('allowHtml' => false, 'tl_class' => 'clr'),
			'sql'       => "text NOT NULL default ''",
		),
		'video_integration'              => array(
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('allowHtml' => false),
			'sql'       => "text NOT NULL default ''",
		)
	)
);

/**
 * Class tl_rsz_steckbrief
 */
class tl_rsz_steckbrief extends Backend
{
	/**
	 * tl_rsz_steckbrief constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Onload callback
	 */
	public function filterList()
	{
		// Nur Admins haben Zugriff auf fremde Profile
		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			unset($GLOBALS['TL_DCA']['tl_rsz_steckbrief']['list']['sorting']['filter']);
		}
	}

	/**
	 * Onload callback
	 * Create profiles
	 */
	public function createProfiles()
	{
		// erstellt von allen Benutzern ein Blanko-Profil, wenn noch keines vorhanden ist.
		$objUser = $this->Database->execute("SELECT id, username FROM tl_user");

		while ($objUser->next())
		{
			$objSteckbriefe = $this->Database->prepare("SELECT * FROM tl_rsz_steckbrief WHERE pid=?")
				->execute($objUser->id);

			if (!$objSteckbriefe->numRows)
			{
				$objSteckbriefe = $this->Database->prepare("INSERT INTO  tl_rsz_steckbrief (pid) VALUES (?)")
					->execute($objUser->id);

				if ($objSteckbriefe->affectedRows)
				{
					$insertID = $objSteckbriefe->insertId;
					$this->log('A new entry in table "tl_rsz_steckbrief" has been created (ID: ' . $insertID . ')', self::class . " " . __FUNCTION__ . "()", TL_GENERAL);
				}
			}
		}
	}

	/**
	 * Replace pid with tl_user.name
	 *
	 * @param array         $row
	 * @param string        $label
	 * @param DataContainer $dc
	 * @param array         $args
	 *
	 * @return array
	 */
	public function labelCallback($row, $label, DataContainer $dc, $args)
	{
		$args[0] = UserModel::findByPk($args[0]) !== null ? UserModel::findByPk($args[0])->name : 'Unbekannt';

		return $args;
	}
}
