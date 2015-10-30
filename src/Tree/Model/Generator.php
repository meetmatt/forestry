<?php

namespace Forestry\Tree\Model;

class Generator
{
    const MAX_GENERATE_DEPTH = 3;
    const MAX_GENERATE_SIZE = 10;

    // possibility that generator will create deeper nodes
    const DEPTH_POSSIBILITY = 50;
    const MAX_EXECUTION_TIME = 10;

    /** @var Tree */
    private $tree;

    /**
     * @param Tree $tree
     */
    public function __construct(Tree $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @param int $depth
     * @param int $size
     * @param int $parentId
     * @return int
     * @throws \Exception
     */
    public function generateTree($depth = self::MAX_GENERATE_DEPTH, $size = self::MAX_GENERATE_SIZE, $parentId = null)
    {
        static $started;

        if (!isset($started)) {
            $started = time();
        }

        if (time() - $started > self::MAX_EXECUTION_TIME) {
            throw new \Exception('Max execution time reached');
        }

        $generated = 0;

        if ($size > 0) {
            $node = $this->tree->createNode($this->generateRandomNodeLabel(), $parentId);

            if ($depth > 0) {
                // we are not deep yet - create child nodes
                // flip coin - to create child nodes or to create siblings
                if (mt_rand(0, 100) < self::DEPTH_POSSIBILITY) {
                    // go deeper
                    $depth--;
                    $parentId = $node->getId();
                }
            } else {
                // go up random N levels, where N is [0, depth]
                $originalDepth = $depth;
                $requiredDepth = $originalDepth + mt_rand(0, $originalDepth);
                while ($depth < $requiredDepth) {
                    $parentNode = $this->tree->getNode($parentId);
                    $parentId = $parentNode->getId();
                    $depth++;
                }
            }

            // decrement size
            $size--;

            if ($node !== false) {
                // recursively generate same or deeper level nodes
                $generated += $this->generateTree($depth, $size, $parentId);

            } else {
                // increase counter even if we failed somehow to prevent infinite loop
                $generated++;
            }
        }

        return $generated;
    }

    /**
     * @return string
     */
    private function generateRandomNodeLabel()
    {
        $prefix = [
            'Retaliate',
            'Koine',
            'Bugger',
            'Deluded',
            'Ambage',
            'Cainozoic',
            'Nonenervating',
            'Villainess',
            'Sigmation',
            'Orangy',
            'Nonpoisonous',
            'Unexplored',
            'Temple',
            'Tick',
            'Alpinist',
            'Compatriotic',
            'Subpool',
            'Haemophiliac',
            'Lowbred',
            'Andes',
        ];

        $names = [
            'Arborvitae',
            'Black Ash',
            'White Ash',
            'Bigtooth Aspen',
            'Quaking Aspen',
            'Basswood',
            'American Beech',
            'Black Birch',
            'Gray Birch',
            'Paper Birch',
            'Yellow Birch',
            'Butternut',
            'Black Cherry',
            'Pin Cherry',
            'American Chestnut',
            'Eastern Cottonwood',
            'Cucumber Tree',
            'American Elm',
            'Slippery Elm',
            'Balsam Fir',
            'Hawthorn',
            'Eastern Hemlock',
            'Bitternut Hickory',
            'Pignut Hickory',
            'Shagbark Hickory',
            'American Hophornbeam',
            'American Hornbeam',
            'American Larch',
            'Black Locust',
            'Honey-Locust',
            'Red Maple',
            'Silver Maple',
            'Sugar Maple',
            'The Oaks',
            'Black Oak',
            'Chestnut Oak',
            'Northern Red Oak',
            'Scarlet Oak',
            'White Oak',
            'Eastern White Pine',
            'Pitch Pine',
            'Red Pine',
            'Eastern Redcedar',
            'Sassafras',
            'Shadbush',
            'Red Spruce',
            'White Spruce',
            'Sycamore',
            'Tulip Tree',
            'Black Walnut',
            'Black Willow',
        ];

        return $prefix[array_rand($prefix)] . ' ' . $names[array_rand($names)];
    }
}