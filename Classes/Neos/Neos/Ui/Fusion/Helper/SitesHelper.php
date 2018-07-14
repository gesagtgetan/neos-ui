<?php
namespace Neos\Neos\Ui\Fusion\Helper;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Projection\Site\SiteFinder;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\ValueObject\NodeName;

class SitesHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var SiteFinder
     */
    protected $siteFinder;


    public function isActive(NodeInterface $siteNode)
    {
        if ($siteModel = $this->siteFinder->findOneByNodeName(new NodeName($siteNode->getName()))) {
            return $siteModel->isOnline();
        }

        throw new \RuntimeException('Could not find a site for the given site node', 1473366137);
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}