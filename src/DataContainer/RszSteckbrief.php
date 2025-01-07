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

namespace Markocupic\RszSteckbriefBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\DataContainer;
use Contao\UserModel;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RszSteckbrief
{
    public function __construct(
        private readonly Security $security,
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly LoggerInterface|null $contaoGeneralLogger,
    ) {
    }

    #[AsCallback(table: 'tl_rsz_steckbrief', target: 'config.onload', priority: 100)]
    public function filterList(): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            unset($GLOBALS['TL_DCA']['tl_rsz_steckbrief']['list']['sorting']['filter']);
        }
    }

    #[AsCallback(table: 'tl_rsz_steckbrief', target: 'config.onload', priority: 100)]
    public function createProfiles(): void
    {
        // Create a blanko profile if not exists.
        $users = $this->connection->fetchAllAssociative('SELECT id, username FROM tl_user');

        foreach ($users as $user) {
            if (!$this->connection->fetchOne('SELECT * FROM tl_rsz_steckbrief WHERE pid = ?', [$user['id']])) {
                $set = [
                    'pid' => $user['id'],
                ];

                $rowsAffected = $this->connection->insert('tl_rsz_steckbrief', $set);

                if ($rowsAffected > 0) {
                    $insertId = $this->connection->lastInsertId();

                    $this?->contaoGeneralLogger->info(
                        sprintf('A new entry in table "tl_rsz_steckbrief" has been created (ID: %d)', $insertId),
                        ['contao' => new ContaoContext(__METHOD__, 'INSERT_NEW_RSZ_STECKBRIEF')],
                    );
                }
            }
        }
    }

    /**
     * Replace the username with tl_user.name.
     */
    #[AsCallback(table: 'tl_rsz_steckbrief', target: 'list.label.label', priority: 100)]
    public function labelCallback(array $row, string $label, DataContainer $dc, array $args): array
    {
        $userModel = $this->framework->getAdapter(UserModel::class);
        $args[0] = null !== $userModel->findByUsername($args[0]) ? $userModel->findByUsername($args[0])->name : 'Unbekannt';

        return $args;
    }
}
