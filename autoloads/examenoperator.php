<?php
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of version 2.0 of the GNU General
// Public License as published by the Free Software Foundation.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
// MA 02110-1301, USA.
//

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
