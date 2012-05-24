<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

class ExamenOperator
{
    private $OperatorName = false;

    function __construct()
    {
        $this->OperatorName = 'examen';
    }

    function operatorList()
    {
        return array( $this->OperatorName );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            $this->OperatorName => array(
                'language' => array(
                    'type' => 'string',
                    'required' => true,
                ),
            ),
        );
    }

    static function examen( $content, $language )
    {
        $examen = new examen( $content, $language );
        $examen->set_link_target( 'ez_no_documentation' );
        return $examen->parse_code();
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace,
                     $currentNamespace, &$operatorValue, $namedParameters )
    {
        $operatorValue = ExamenOperator::examen( $operatorValue, $namedParameters['language'] );
    }

    function operatorTemplateHints()
    {
        return array( $this->OperatorName => array( 'parameters' => true,
                                                    'input' => true,
                                                    'output' => true,
                                                    'element-transformation' => true,
                                                    'transform-parameters' => true,
                                                    'input-as-parameter' => 'always',
                                                    'element-transformation-func' => 'transformation' ) );
    }

    function transformation( $operatorName, $node, $tpl, $resourceData,
                             $element, $lastElement, $elementList, $elementTree, $parameters )
    {
        $newElements[] = eZTemplateNodeTool::createCodePieceElement( "%output% = ExamenOperator::examen( %1%, %2% );\n", $parameters );
        return $newElements;
    }
}

?>
