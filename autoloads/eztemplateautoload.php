<?php
//
//

/*! \file eztemplateautoload.php
*/



$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] = array( 'script' => eZExtension::baseDirectory() . '/examen/classes/examenoperators.php',
                                    'class' => 'examenOperators',
                                    'operator_names' => array( 'number' ) );

$eZTemplateFunctionArray[] = array( 'function' => 'examForwardInit',
                                    'function_names' => array( 'exam_edit_gui',
                                                               'exam_view_gui',
                                                               'exam_result_gui' ) );

if ( !function_exists( 'examForwardInit' ) )
{
    function &examForwardInit()
    {
        $forward_rules = array(
            'exam_edit_gui' => array( 'template_root' => 'examen/edit',
                                                 'input_name' => 'element',
                                                 'output_name' => 'element',
                                                 'namespace' => 'ExamElement',
                                                 'attribute_access' => array( array( 'template_name' ) ),
                                                 'use_views' => false ),

            'exam_view_gui' => array( 'template_root' => 'examen/view',
                                                 'input_name' => 'element',
                                                 'output_name' => 'element',
                                                 'namespace' => 'ExamElement',
                                                 'attribute_access' => array( array( 'template_name' ) ),
                                                 'use_views' => false ),

            'exam_result_gui' => array( 'template_root' => 'examen/results',
                                                   'input_name' => 'element',
                                                   'output_name' => 'element',
                                                   'namespace' => 'ExamElement',
                                                   'attribute_access' => array( array( 'template_name' ) ),
                                                   'use_views' => false ) );

        $forwarder = new eZObjectForwarder( $forward_rules );
        return $forwarder;
    }
}

?>
