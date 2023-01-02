<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Steckbrief Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-steckbrief-bundle
 */

namespace Markocupic\RszSteckbriefBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
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

#[AsFrontendModule(RszSteckbriefListingModuleController::TYPE, category:'rsz_frontend_modules', template: 'mod_rsz_steckbrief_listing')]
class RszSteckbriefListingModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'rsz_steckbrief_listing_module';
    private ContaoFramework $framework;
    private string $projectDir;

    public function __construct(ContaoFramework $framework, string $projectDir)
    {
        $this->framework = $framework;
        $this->projectDir = $projectDir;
    }

    /**
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $databaseAdapter = $this->framework->getAdapter(Database::class);
        $validatorAdapter = $this->framework->getAdapter(Validator::class);
        $filesModelAdapter = $this->framework->getAdapter(FilesModel::class);
        $rszSteckbriefModel = $this->framework->getAdapter(RszSteckbriefModel::class);

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

                        // Remove empty (not replaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }

                    // Take first image from stack
                    $image = $images[0];

                    $portraits[] = [
                        'id' => $objSteckbrief->id,
                        'pid' => $objSteckbrief->pid,
                        'src' => FilesModel::findByUuid($image['uuid'])->path,
                        'user_model' => $objUser,
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

    private function isAthlete(UserModel $objUser): bool
    {
        $arrFunktionen = StringUtil::deserialize($objUser->funktion, true);

        if (\in_array('Athlet', $arrFunktionen, true)) {
            return true;
        }

        return false;
    }
}
