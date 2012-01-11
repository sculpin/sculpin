<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\bundle\twigLiquidCompatibilityBundle\tokenParser;

class AssignTokenParser extends \Twig_TokenParser_Set
{

    /**
     * (non-PHPdoc)
     * @see Twig_TokenParser_Set::decideBlockEnd()
     */
    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('endassign');
    }

    /**
     * (non-PHPdoc)
     * @see Twig_TokenParser_Set::getTag()
     */
    public function getTag()
    {
        return 'assign';
    }

}