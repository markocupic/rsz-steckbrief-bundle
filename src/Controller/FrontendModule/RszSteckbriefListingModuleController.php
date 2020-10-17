<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Steckbrief Bundle.
*
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-steckbrief-bundle
 */

namespace Markocupic\RszSteckbriefBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\UserModel;
use Contao\Validator;
use Markocupic\RszSteckbriefBundle\Model\RszSteckbriefModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RszSteckbriefListingModuleController.
 */
class RszSteckbriefListingModuleController extends AbstractFrontendModuleController
{
    /**
     * @var string
     */
    protected $strAvatar = 'system/modules/steckbriefe/html/avatar.png';

    /**
     * @var string
     */
    private $projectDir;

    /**
     * RszSteckbriefListingModuleController constructor.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary.
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Lazyload some services.
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services['contao.framework'] = ContaoFramework::class;

        return $services;
    }

    /**
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->get('contao.framework')->getAdapter(Database::class);

        /** @var Validator $validatorAdapter */
        $validatorAdapter = $this->get('contao.framework')->getAdapter(Validator::class);

        /** @var FilesModel $filesModelAdapter */
        $filesModelAdapter = $this->get('contao.framework')->getAdapter(FilesModel::class);

        /** @var RszSteckbriefModel $rszSteckbriefModel */
        $rszSteckbriefModel = $this->get('contao.framework')->getAdapter(RszSteckbriefModel::class);

        // Die ganze Tabelle
        $objJumpTo = PageModel::findByPk($model->rszSteckbriefReaderPage);

        $portraits = [];

        $objSteckbrief = $databaseAdapter->getInstance()
            ->prepare("SELECT * FROM tl_rsz_steckbrief WHERE multiSRC != ''")
            ->execute()
        ;

        while ($objSteckbrief->next()) {
            /** @var UserModel $objUser */
            if (null === ($objUser = $rszSteckbriefModel->findByPk($objSteckbrief->id)->getRelated('pid'))) {
                continue;
            }

            if (!$this->isAthlete($objUser) || !$objUser->isRSZ) {
                continue;
            }

            $objSteckbrief->multiSRC = unserialize($objSteckbrief->multiSRC);

            if (!empty($objSteckbrief->multiSRC) && \is_array($objSteckbrief->multiSRC)) {
                if (null !== ($objFiles = $filesModelAdapter->findMultipleByUuids($objSteckbrief->multiSRC))) {
                    $images = [];

                    while ($objFiles->next()) {
                        if ($validatorAdapter->isUuid($objFiles->uuid) && is_file($this->projectDir.'/'.$objFiles->path)) {
                            $images[] = ['uuid' => $objFiles->uuid];
                        }
                    }

                    // Custom order
                    if (!empty($objSteckbrief->orderSRC) && \is_array(unserialize($objSteckbrief->orderSRC))) {
                        $tmp = unserialize($objSteckbrief->orderSRC);

                        // Remove all values
                        $arrOrder = array_map(
                            static function (): void {
                            },
                            array_flip($tmp)
                        );

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k => $v) {
                            if (\array_key_exists($v['uuid'], $arrOrder)) {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images)) {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }

                    // Take first image from stack
                    $image = $images[0];

                    $portraits[] = [
                        'id' => $objSteckbrief->id,
                        'pid' => $objSteckbrief->pid,
                        'src' => FilesModel::findByUuid($image['uuid'])->path,
                        'userModel' => $objUser,
                        'href' => $objJumpTo ? $objJumpTo->getFrontendUrl('/'.$objUser->username) : null,
                    ];
                }
            }
        }

        // Random order
        shuffle($portraits);

        $template->portraits = $portraits;

        return $template->getResponse();
    }

    protected function isAthlete(UserModel $objUser): bool
    {
        $arrFunktionen = StringUtil::deserialize($objUser->funktion, true);

        if (\in_array('Athlet', $arrFunktionen, true)) {
            return true;
        }

        return false;
    }
}
