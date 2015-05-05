<?php

namespace DOMWrap\Traits;

use DOMWrap\Collections\NodeList;
use Symfony\Component\CssSelector\CssSelector;

define('DOM_NODE_TEXT_DEFAULT', 0);
define('DOM_NODE_TEXT_TRIM', 1);
define('DOM_NODE_TEXT_NORMALISED', 2);

/**
 * Node Trait
 *
 * @package DOMWrap\Traits
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
trait NodeTrait
{
    /**
     * @see \DOMNode::$previousSibling
     *
     * @var \DOMNode
     */
    public $previousSibling;

    /**
     * @see \DOMNode::$nextSibling
     *
     * @var \DOMNode
     */
    public $nextSibling;

    /**
     * @see \DOMNode::$childNodes
     *
     * @var \DOMNodeList
     */
    public $childNodes;

    /**
     * @see \DOMNode::$parentNode
     *
     * @var \DOMNode
     */
    public $parentNode;

    /**
     * @see \DOMNode::$textContent
     *
     * @var string
     */
    public $textContent;

    /**
     * @see \DOMNode::$ownerDocument
     *
     * @var \DOMDocument
     */
    public $ownerDocument;

    /**
     * @see \DOMNode::appendChild()
     */
    abstract public function appendChild(\DOMNode $newNode);

    /**
     * @see \DOMNode::replaceChild()
     */
    abstract public function replaceChild(\DOMNode $newNode, \DOMNode $oldNode);

    /**
     * @return Document
     */
    public function document() {
        return $this->ownerDocument;
    }

    /**
     * @param string $selector
     * @param string $prefix
     *
     * @return NodeList
     */
    public function filter($selector, $prefix = 'descendant::') {
        return $this->filterXPath(CssSelector::toXPath($selector, $prefix));
    }

    /**
     * @param string $xpath
     *
     * @return NodeList
     */
    public function filterXPath($xpath) {
        $domxpath = new \DOMXPath($this->document());

        return new NodeList($domxpath->query($xpath, $this));
    }

    /**
     * @see http://php.net/manual/en/dom.constants.php $nodeType values - XML_*_NODE constants
     *
     * @param int|null $nodeType 
     *
     * @return \DOMNode|null
     */
    public function previous($nodeType = null) {
        for ($sibling = $this; ($sibling = $sibling->previousSibling) !== null;) {
            if (is_null($nodeType) || $sibling->nodeType == $nodeType) {
                return $sibling;
            }
        }

        return null;
    }

    /**
     * @see http://php.net/manual/en/dom.constants.php $nodeType values - XML_*_NODE constants
     *
     * @param int|null $nodeType 
     *
     * @return NodeList
     */
    public function previousAll($nodeType = null) {
        $nodes = new NodeList();

        for ($sibling = $this; ($sibling = $sibling->previousSibling) !== null;) {
            if (is_null($nodeType) || $sibling->nodeType == $nodeType) {
                $nodes[] = $sibling;
            }
        }

        return $nodes->reverse();
    }

    /**
     * @see http://php.net/manual/en/dom.constants.php $nodeType values - XML_*_NODE constants
     *
     * @param int|null $nodeType 
     *
     * @return \DOMNode|null
     */
    public function next($nodeType = null) {
        for ($sibling = $this; ($sibling = $sibling->nextSibling) !== null;) {
            if (is_null($nodeType) || $sibling->nodeType == $nodeType) {
                return $sibling;
            }
        }

        return null;
    }

    /**
     * @see http://php.net/manual/en/dom.constants.php $nodeType values - XML_*_NODE constants
     *
     * @param int|null $nodeType 
     *
     * @return NodeList
     */
    public function nextAll($nodeType = null) {
        $nodes = new NodeList();

        for ($sibling = $this; ($sibling = $sibling->nextSibling) !== null;) {
            if (is_null($nodeType) || $sibling->nodeType == $nodeType) {
                $nodes[] = $sibling;
            }
        }

        return $nodes;
    }

    /**
     * @see http://php.net/manual/en/dom.constants.php $type values - XML_*_NODE constants
     *
     * @param int|null $nodeType 
     *
     * @return NodeList
     */
    public function siblings($nodeType = null) {
        return $this->previousAll($nodeType)->merge(
            $this->nextAll($nodeType)
        );
    }

    /**
     * NodeList is only array like. Removing items using foreach() has undesired results.
     *
     * @return NodeList
     */
    public function children() {
        return new NodeList($this->childNodes);
    }

    /**
     * @return Element
     */
    public function parent() {
        if ($this->parentNode instanceof \DOMDocument) {
            return null;
        }

        return $this->parentNode;
    }

    /**
     * @param string|null $selector
     *
     * @return self
     */
    public function remove($selector = null) {
        if (!is_null($selector)) {
            $nodes = $this->filter($selector);
        } else {
            $nodes = new NodeList([$this]);
        }

        $nodes->remove();

        return $this;
    }

    /**
     * @param \DOMNode $newNode
     *
     * @return self
     */
    public function replace($newNode) {
        if ($this->parent()) {
            $this->parent()->replaceChild($newNode, $this);
        }

        return $this;
    }

    /**
     * @param \DOMNode|NodeList $nodes
     *
     * @return self
     */
    public function append($nodes) {
        if (!($nodes instanceof NodeList)) {
            $nodes = new NodeList([$nodes]);
        }

        foreach ($nodes as $node) {
            $this->appendChild($node);
        }

        return $this;
    }

    /**
     * @param int $flag
     *
     * @return string
     */
    public function text($flag = 0) {
        $text = $this->textContent;

        if ($flag & DOM_NODE_TEXT_NORMALISED) {
            $text = preg_replace('@[\n\r\s\t]+@', " ", $text);
        }

        if ($flag & (DOM_NODE_TEXT_TRIM | DOM_NODE_TEXT_NORMALISED)) {
            $text = trim($text);
        }

        return $text;
    }

    /**
     * @param string $selector
     *
     * @return bool
     */
    public function is($selector) {
        $nodes = $this->filter($selector, 'self::');

        return $nodes->count() != 0;
    }

    /**
     * @param string $selector
     *
     * @return bool
     */
    public function has($selector) {
        $nodes = $this->filter($selector);

        return $nodes->count() != 0;
    }
}