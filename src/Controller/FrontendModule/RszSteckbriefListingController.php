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
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\UserModel;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use Markocupic\RszSteckbriefBundle\Model\RszSteckbriefModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(RszSteckbriefListingController::TYPE, category:'rsz_frontend_modules')]
class RszSteckbriefListingController extends AbstractFrontendModuleController
{
    public const TYPE = 'rsz_steckbrief_listing';

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly string $projectDir,
    ) {
    }

    /**
     * @throws \Exception
     */
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $validator = $this->framework->getAdapter(Validator::class);
        $filesModel = $this->framework->getAdapter(FilesModel::class);
        $rszSteckbrief = $this->framework->getAdapter(RszSteckbriefModel::class);
        $pageModel = $this->framework->getAdapter(PageModel::class);

        $objJumpTo = $pageModel->findByPk($model->rszSteckbriefReaderPage);

        $portraits = [];

        $result = $this->connection->executeQuery("SELECT * FROM tl_rsz_steckbrief WHERE multiSRC != ''");

        while (false !== ($profile = $result->fetchAssociative())) {
            /** @var UserModel $objUser */
            if (null === ($objUser = $rszSteckbrief->findByPk($profile['id'])->getRelated('pid'))) {
                continue;
            }

            if (!$this->isAthlete($objUser) || !$objUser->isRSZ) {
                continue;
            }

            $profile['multiSRC'] = unserialize($profile['multiSRC']);

            if (!empty($profile['multiSRC']) && \is_array($profile['multiSRC'])) {
                if (null !== ($objFiles = $filesModel->findMultipleByUuids($profile['multiSRC']))) {
                    $images = [];

                    while ($objFiles->next()) {
                        if ($validator->isUuid($objFiles->uuid) && is_file($this->projectDir.'/'.$objFiles->path)) {
                            $images[] = ['uuid' => $objFiles->uuid];
                        }
                    }

                    // Custom order
                    if (!empty($profile['orderSRC']) && \is_array(unserialize($profile['orderSRC']))) {
                        $tmp = unserialize($profile['orderSRC']);

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
                        'id' => $profile['id'],
                        'pid' => $profile['pid'],
                        'src' => $filesModel->findByUuid($image['uuid'])->path,
                        'user_model' => $objUser,
                        'href' => $objJumpTo ? $objJumpTo->getFrontendUrl('/'.$objUser->username) : null,
                    ];
                }
            }
        }

        // Random order
        shuffle($portraits);

        $template->set('portraits', $portraits);

        return $template->getResponse();
    }

    private function isAthlete(UserModel $objUser): bool
    {
        $stringUtil = $this->framework->getAdapter(StringUtil::class);
        $arrFunktionen = $stringUtil->deserialize($objUser->funktion, true);

        if (\in_array('Athlet', $arrFunktionen, true)) {
            return true;
        }

        return false;
    }
}
