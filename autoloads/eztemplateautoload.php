<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */
$eZTemplateOperatorArray = array();

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
                                                 'namespace' => 'examElement',
                                                 'attribute_access' => array( array( 'template_name' ) ),
                                                 'use_views' => false ),

            'exam_view_gui' => array( 'template_root' => 'examen/view',
                                                 'input_name' => 'element',
                                                 'output_name' => 'element',
                                                 'namespace' => 'examElement',
                                                 'attribute_access' => array( array( 'template_name' ) ),
                                                 'use_views' => false ),

            'exam_result_gui' => array( 'template_root' => 'examen/results',
                                                   'input_name' => 'element',
                                                   'output_name' => 'element',
                                                   'namespace' => 'examAnswer',
                                                   'attribute_keys' => array( 'attribute_identifier' => array( 'contentclass_attribute_identifier' ),
                                                                            'attribute' => array( 'contentclassattribute_id' ),
                                                                            'class_identifier' => array( 'object', 'class_identifier' ),
                                                                            'class' => array( 'object', 'contentclass_id' ) ),
                                                   'attribute_access' => array( array( 'template_name' ) ),
                                                   'use_views' => false ) );

        $forwarder = new eZObjectForwarder( $forward_rules );
        return $forwarder;
    }
}

?>
