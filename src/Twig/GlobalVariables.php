<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DashboardBundle\Twig;

use Sonata\DashboardBundle\CmsManager\CmsManagerInterface;
use Sonata\DashboardBundle\CmsManager\CmsManagerSelectorInterface;

/**
 * GlobalVariables.
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
final class GlobalVariables
{
    /**
     * @var CmsManagerSelectorInterface
     */
    private $cmsManagerSelector;

    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
    }

    public function getCmsManager(): CmsManagerInterface
    {
        return $this->cmsManagerSelector->retrieve();
    }

    public function isEditor(): bool
    {
        return $this->cmsManagerSelector->isEditor();
    }
}
