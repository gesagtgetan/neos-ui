<?php
namespace Neos\Neos\Ui\Domain\Model\Feedback\Operations;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Projection\Content\ContentGraphInterface;
use Neos\ContentRepository\Domain\Projection\Content\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Context\Content\NodeAddress;
use Neos\Neos\Domain\Context\Content\NodeAddressService;
use Neos\Neos\Ui\Domain\Model\FeedbackInterface;
use Neos\Neos\View\FusionView;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Neos\Ui\Domain\Model\RenderedNodeDomAddress;
use Neos\Fusion\Core\Cache\ContentCache;

class ReloadContentOutOfBand implements FeedbackInterface
{
    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * The node dom address
     *
     * @var RenderedNodeDomAddress
     */
    protected $nodeDomAddress;

    /**
     * @Flow\Inject
     * @var ContentCache
     */
    protected $contentCache;

    /**
     * @Flow\Inject
     * @var NodeAddressService
     */
    protected $nodeAddressService;

    /**
     * @Flow\Inject
     * @var ContentGraphInterface
     */
    protected $contentGraph;

    /**
     * Set the node
     *
     * @param NodeInterface $node
     * @return void
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;
    }

    /**
     * Get the node
     *
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set the node dom address
     *
     * @param RenderedNodeDomAddress $nodeDomAddress
     * @return void
     */
    public function setNodeDomAddress(RenderedNodeDomAddress $nodeDomAddress = null)
    {
        $this->nodeDomAddress = $nodeDomAddress;
    }

    /**
     * Get the node dom address
     *
     * @return RenderedNodeDomAddress
     */
    public function getNodeDomAddress()
    {
        return $this->nodeDomAddress;
    }

    /**
     * Get the type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'Neos.Neos.Ui:ReloadContentOutOfBand';
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return sprintf('Rendering of node "%s" required.', $this->getNode()->getNodeIdentifier());
    }

    /**
     * Checks whether this feedback is similar to another
     *
     * @param FeedbackInterface $feedback
     * @return boolean
     */
    public function isSimilarTo(FeedbackInterface $feedback)
    {
        if (!$feedback instanceof ReloadContentOutOfBand) {
            return false;
        }

        return (
            $this->getNode()->getNodeIdentifier() === $feedback->getNode()->getNodeIdentifier() &&
            $this->getNodeDomAddress() == $feedback->getNodeDomAddress()
        );
    }

    /**
     * Serialize the payload for this feedback
     *
     * @return mixed
     */
    public function serializePayload(ControllerContext $controllerContext)
    {
        return [
            'contextPath' => NodeAddress::fromNode($this->getNode())->serializeForUri(),
            'nodeDomAddress' => $this->getNodeDomAddress(),
            'renderedContent' => $this->renderContent($controllerContext)
        ];
    }

    /**
     * Render the node
     *
     * @param ControllerContext $controllerContext
     * @return string
     */
    protected function renderContent(ControllerContext $controllerContext)
    {
        $this->contentCache->flushByTag(sprintf('Node_%s', $this->getNode()->getNodeIdentifier()));

        $nodeDomAddress = $this->getNodeDomAddress();

        $fusionView = new FusionView();
        $site = $this->nodeAddressService->findSiteNodeForNodeAddress(NodeAddress::fromNode($this->getNode()));
        $fusionView->setControllerContext($controllerContext);

        $subgraph = $this->contentGraph->getSubgraphByIdentifier($site->getContentStreamIdentifier(), $site->getDimensionSpacePoint());
        $fusionView->assign('value', $this->getNode());
        $fusionView->assign('site', $site);
        $fusionView->assign('subgraph', $subgraph);
        $fusionView->setFusionPath($nodeDomAddress->getFusionPath());

        return $fusionView->render();
    }
}
