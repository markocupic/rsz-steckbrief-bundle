<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Mein Steckbrief
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-steckbrief-bundle
 *
 */

declare(strict_types=1);

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
 * Class RszSteckbriefListingModuleController
 * @package Markocupic\RszSteckbriefBundle\Controller\FrontendModule
 */
class RszSteckbriefListingModuleController extends AbstractFrontendModuleController
{

    /** @var string */
    private $projectDir;

    /**
     * RszSteckbriefListingModuleController constructor.
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary
     * @param Request $request
     * @param ModuleModel $model
     * @param string $section
     * @param array|null $classes
     * @param PageModel|null $page
     * @return Response
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Lazyload some services
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services['contao.framework'] = ContaoFramework::class;
        return $services;
    }

    /**
     * @var string
     */
    protected $strAvatar = 'system/modules/steckbriefe/html/avatar.png';

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
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
            ->execute();

        while ($objSteckbrief->next())
        {
            /** @var UserModel $objUser */
            if (($objUser = $rszSteckbriefModel->findByPk($objSteckbrief->id)->getRelated('pid')) === null)
            {
                continue;
            }

            if (!$this->isAthlete($objUser) || !$objUser->isRSZ)
            {
                continue;
            }

            $objSteckbrief->multiSRC = unserialize($objSteckbrief->multiSRC);
            if (!empty($objSteckbrief->multiSRC) && is_array($objSteckbrief->multiSRC))
            {
                if (($objFiles = $filesModelAdapter->findMultipleByUuids($objSteckbrief->multiSRC)) !== null)
                {
                    $images = [];

                    while ($objFiles->next())
                    {
                        if ($validatorAdapter->isUuid($objFiles->uuid) && is_file($this->projectDir . '/' . $objFiles->path))
                        {
                            $images[] = ['uuid' => $objFiles->uuid];
                        }
                    }

                    // Custom order
                    if (!empty($objSteckbrief->orderSRC) && is_array(unserialize($objSteckbrief->orderSRC)))
                    {
                        $tmp = unserialize($objSteckbrief->orderSRC);

                        // Remove all values
                        $arrOrder = array_map(function () {
                        }, array_flip($tmp));

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k => $v)
                        {
                            if (array_key_exists($v['uuid'], $arrOrder))
                            {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images))
                        {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }

                    // Take first image from stack
                    $image = $images[0];

                    $portraits[] = [
                        'id'        => $objSteckbrief->id,
                        'pid'       => $objSteckbrief->pid,
                        'src'       => FilesModel::findByUuid($image['uuid'])->path,
                        'userModel' => $objUser,
                        'href'      => $objJumpTo ? $objJumpTo->getFrontendUrl('/' . $objUser->username) : null,
                    ];
                }
            }
        }

        // Random order
        shuffle($portraits);

        $template->portraits = $portraits;

        return $template->getResponse();
    }

    /**
     * @param UserModel $objUser
     * @return bool
     */
    protected function isAthlete(UserModel $objUser): bool
    {
        $arrFunktionen = StringUtil::deserialize($objUser->funktion, true);
        if (in_array('Athlet', $arrFunktionen))
        {
            return true;
        }

        return false;
    }
}

