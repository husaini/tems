<?php
    require_once(dirname(__FILE__).'/includes/conn.php');

    # Include all files in lib
    $libs = glob(dirname(__FILE__).'/lib/*.php');

    foreach($libs as $lib)
    {
        require_once($lib);
    }

    if(!is_ajax())
    {
        //die(0);
    }

    ob_start();

    $chart_type     =   (isset($_GET['chart_type']) && $_GET['chart_type']) ? $_GET['chart_type'] : null;
    $chart_title    =   (isset($_GET['title']) && $_GET['title']) ? $_GET['title'] : null;
    $type           =   (isset($_GET['type']) && $_GET['type']) ? $_GET['type'] : null;
    $url            =   (isset($_GET['url']) && $_GET['url']) ? $_GET['url'] : null;

    if($type && $chart_type)
    {
        switch($type)
        {
            case 'oxygen_level':
                $args       =   array(
                    'id'    =>  (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id'],10) : null,
                    'limit' =>  (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? intval($_GET['limit'],10) : 10,

                );
                $chart_data =   getChartData($type,$args);

                if ($chart_data)
                {
                    $chart_data['chart_title']          =   htmlspecialchars($chart_title);
                    $chart_data['chart_x_axis_label']   =   'Time';
                    $chart_data['chart_y_axis_label']   =   'Oxygen Level';

                    $xml        =   generate_area_xml($chart_data, $chart_type,$type);

                    if ($xml)
                    {
                        if(isset($_GET['output']) && $_GET['output'] == 'xml')
                        {
                            header("Content-type: text/xml");
                            exit($xml);
                        }
                        $result['xmlstring']        =   $xml;
                        exit(json_encode($result));
                    }
                }
                break;

            case 'pending_work':
            case 'upcoming_work':
            case 'total_pending':
            case 'total_upcoming':
            case 'pending_distribution':
            case 'upcoming_distribution':
            case 'total_pending_distribution':
            case 'total_upcoming_distribution':
                $chart_data =   getChartData($type);
                if ($chart_data)
                {
                    $chart_data['chart_title']  =   htmlspecialchars($chart_title);

                    if($url)
                    {
                        $chart_data['url']  =   $url;
                    }

                    if(isset($_GET['period']))
                    {
                        $chart_data['period']   =   $_GET['period'];
                    }

                    $xml        =   generate_workorder_xml($chart_data,$chart_type,$type);

                    if ($xml)
                    {
                        if(isset($_GET['output']) && $_GET['output'] == 'xml')
                        {
                            header("Content-type: text/xml");
                            exit($xml);
                        }
                        $result['xmlstring']        =   $xml;
                        exit(json_encode($result));
                    }
                }
            break;

            default:
                exit(json_encode(0));
        }
    }

    ob_end_flush();

    function getBarColor($type, $key='color1')
    {
        $bar_colors     =   array(
            'default'   =>  array(
                'color1'    =>  '0078AD',
                'color2'    =>  'A9A9A9',
            ),
            /*
            'pending_work'  =>  array(
                'color1'    =>  'A6CF00',
                'color2'    =>  'BD0060',
            ),
            'total_pending'  =>  array(
                'color1'    =>  'FF5A00',
                'color2'    =>  '#96C2FF',
            ),
            'total_pending_distribution'  =>  array(
                'color1'    =>  'FF5A00',
                'color2'    =>  '#96C2FF',
            ),
            'total_upcoming'  =>  array(
                'color1'    =>  '00D4E6',
                'color2'    =>  'FF0FCD',
            ),
            'total_upcoming_distribution'  =>  array(
                'color1'    =>  '00D4E6',
                'color2'    =>  'FF0FCD',
            ),
            'upcoming_work'  =>  array(
                'color1'    =>  '2576D7',
                'color2'    =>  'BD0400',
            ),
            'upcoming_distribution'  =>  array(
                'color1'    =>  '2576D7',
                'color2'    =>  'BD0400',
            ),
            */
        );

        if (isset($bar_colors[$type][$key]))
        {
            return $bar_colors[$type][$key];
        }

        if (isset($bar_colors['default'][$key]))
        {
            return $bar_colors['default'][$key];
        }
        $bar_colors['default']['color1'];
    }

    function getChartData($type,$args=array())
    {
        $max    =   0;
        $chart_data['categories']   =   array();

        switch ($type)
        {
            case 'oxygen_level':
                $id     =   (isset($args['id']) && is_numeric($args['id'])) ? intval($args['id'],10) : null;
                $limit  =   (isset($args['limit']) && is_numeric($args['limit'])) ? intval($args['limit'],10) : 10;

                $args['limit'] = $limit;

                if ($id)
                {
                    $data   =   ares_get_oxygen_level($id, $args);


                    //debug($data);
                    //debug($mote);
                }
                break;

            case 'pending_work':
            case 'pending_distribution':
                $data   =   dashboard_get_pending();
                break;

            case 'total_pending':
            case 'total_pending_distribution':
                $data   =   dashboard_get_pending(true);
                break;

            case 'upcoming_work':
            case 'upcoming_distribution':
                $data   =   dashboard_get_upcoming();
                break;

            case 'total_upcoming':
            case 'total_upcoming_distribution':
                $data   =   dashboard_get_upcoming(true);
                break;


            default:
                return;
                break;
        }

        if (!$data)
        {
            return;
        }

        switch ($type)
        {
            case 'oxygen_level':
                if ($data)
                {
                    $mote           =   ares_get_mote($id);
                    $data2_value    =   0;

                    $chart_data['categories'][]   =   array(
                        'key'   =>  '',
                        'label' =>  '0.00',
                    );

                    foreach ($data as $d)
                    {
                        $chart_data['categories'][] =  array(
                            'key'   =>  '',
                            'label' =>  $d['times'],
                        );

                        if ($d['data2'] > $max)
                        {
                            $max = $d['data2'];
                        }
                        $data2_value   +=  $d['data2'];

                        $chart_data['dataset'][]    =   array(
                            'label' =>  '',
                            'value' =>  $d['data2'],
                        );

                    }
                    sort($chart_data['categories']);

                    // Normal value
                    $chart_data['dataset_limit']  =   array(
                        array(
                            'alpha'             =>  100,
                            'anchor_bgcolor'    =>  'FF2A00',
                            'anchor_side'       =>  3,
                            'anchor_radius'     =>  3,
                            'color'             =>  'FF000F',
                            'key'               =>  'high_level',
                            'label'             =>  'High',
                            'line_thickness'    =>  1,
                            'show_value'        =>  0,
                            'value'             =>  $mote->temphi,
                        ),

                        array(
                            'alpha'             =>  100,
                            'anchor_radius'     =>  3,
                            'anchor_side'       =>  4,
                            'color'             =>  '00ABF2',
                            'key'               =>  'low_level',
                            'label'             =>  'Low',
                            'line_thickness'    =>  1,
                            'show_value'        =>  0,
                            'value'             =>  $mote->templo,
                        ),
                        array(
                            'alpha'             =>  100,
                            'anchor_bgcolor'    =>  '7BB300',
                            'anchor_radius'     =>  4,
                            'anchor_side'       =>  100,
                            'color'             =>  '1DE500',
                            'key'               =>  'normal_level',
                            'label'             =>  'Normal',
                            'line_thickness'    =>  1,
                            'show_value'        =>  0,
                            'value'             =>  round($data2_value/count($data), 2),
                        ),
                    );

                    //debug($chart_data);
                }

                break;

            case 'pending_work':
            case 'upcoming_work':
            case 'total_pending':
            case 'total_upcoming':
            case 'pending_distribution':
            case 'upcoming_distribution':
            case 'total_pending_distribution':
            case 'total_upcoming_distribution':

                $chart_data['categories']   =   array(
                    array(
                        'key'   =>  'week_1',
                        'label' =>  '&lt; 1 Week',
                    ),
                    array(
                        'key'   =>  'week_2',
                        'label' =>  '1-2 Weeks',
                    ),
                    array(
                        'key'   =>  'week_3',
                        'label' =>  '2-4 Weeks',
                    ),
                    array(
                        'key'   =>  'month_1',
                        'label' =>  '1-2 Months',
                    ),
                    array(
                        'key'   =>  'month_2',
                        'label' =>  '2-4 Months',
                    ),
                    array(
                        'key'   =>  'month_3',
                        'label' =>  '&gt; 6 Months',
                    ),
                );

                foreach ($chart_data['categories'] as $key => $cat)
                {
                    if(!isset($chart_data['dataset']['workorder']['label']))
                    {
                        $chart_data['dataset']['workorder']['label']  =   'From Work Order';
                    }
                    if(!isset($chart_data['dataset']['workorder']['color']))
                    {
                        $chart_data['dataset']['workorder']['color']  =   getBarColor($type,'color2');
                    }

                    if(!isset($chart_data['dataset']['ppm']['label']))
                    {
                        $chart_data['dataset']['ppm']['label']  =   'From PPM';
                    }
                    if(!isset($chart_data['dataset']['ppm']['color']))
                    {
                        $chart_data['dataset']['ppm']['color']  =   getBarColor($type,'color1');
                    }

                    if (isset($data['workorder_'.$cat['key']]))
                    {
                        $chart_data['dataset']['workorder'][$cat['key']]    =   $data['workorder_'.$cat['key']];
                        if($data['workorder_'.$cat['key']] > $max)
                        {
                            $max    =   $data['workorder_'.$cat['key']];
                        }
                    }
                    else
                    {
                        unset($chart_data['categories'][$key]);
                    }

                    if (isset($data['ppm_'.$cat['key']]))
                    {
                        $chart_data['dataset']['ppm'][$cat['key']]    =   $data['ppm_'.$cat['key']];
                        if ($data['ppm_'.$cat['key']] > $max)
                        {
                            $max    =   $data['ppm_'.$cat['key']];
                        }
                    }
                    else
                    {
                        unset($chart_data['categories'][$key]);
                    }
                }
                break;

            default:
                $chart_data['categories']   =   array();
                break;
        }

        $chart_data['max_y']        =   0;

        if (isset($chart_data['categories']))
        {
            $chart_data['max_y']        =   $max ? round(($max + ($max / (count($chart_data['categories']) - 1))),2) : $max;
        }
        //debug($chart_data);
        return $chart_data;
    }

    function generate_area_xml($args, $chart_type)
    {
        if(!$args || !is_array($args))
        {
            return;
        }

        //debug($args);

        //debug($args);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $graph              =   $doc->createElement('graph');
        $xmlroot            =   $doc->appendChild($graph);
        $categories         =   $dataset = '';

        if($args)
        {
            $max_y  =   (isset($args['max_y'])) ? $args['max_y'] : 10000;
            $min_y  =   (isset($args['min_y'])) ? $args['min_y'] : 0;
            $dataset=   array();

            if(!$max_y)
            {
                $max_y  =   100;
            }

            $common_attributes  =   array(
                'caption'           =>  htmlspecialchars($args['chart_title']),
                'SYAxisMaxValue'    =>  $max_y,
                'SYAxisMinValue'    =>  $min_y,
                'xAxisName'         =>  '',
                'yAxisName'         =>  '',
                'yAxisMaxValue'     =>  $max_y,
                'yAxisMinValue'     =>  $min_y,
                'yAxisMaxValue'     =>  $max_y,
            );


            if (isset($args['chart_x_axis_label']))
            {
                $common_attributes['xAxisName']    =   htmlspecialchars($args['chart_x_axis_label']);
            }
            if (isset($args['chart_y_axis_label']))
            {
                $common_attributes['yAxisName']    =   htmlspecialchars($args['chart_y_axis_label']);
            }

            switch($chart_type)
            {

                default:
                    $attributes =   get_chart_properties($chart_type);
                    $attributes =   array_merge($attributes,$common_attributes);

                    $attributes['caption']          =   '';
                    $attributes['numdivlines']      =   4;
                    $attributes['numVDivLines']     =   18;
                    $attributes['chartTopMargin'] =   20;
                    $attributes['chartLeftMargin']  =   10;
                    $attributes['chartRightMargin'] =   20;
                    $attributes['chartBottomMargin'] =   20;
                    $attributes['showShadow']       =   0;

                    foreach ($attributes as $key => $value)
                    {
                        $attr           =   $doc->createAttribute($key);
                        $attr->value    =   htmlspecialchars($value);
                        $graph->appendChild($attr);
                    }

                break;
            }

            $categories      =   $doc->createElement('categories');

            $font_size  =   $doc->createAttribute('fontSize');
            $font_size->value = 10;
            $categories->appendChild($font_size);

            foreach($args['categories'] as $cat)
            {
                $label      =   htmlspecialchars($cat['label']);
                $category   =   $categories->appendChild($doc->createElement('category'));
                $cat_name   =   $doc->createAttribute('name');
                $cat_name->value    =   $label;
                $category->appendChild($cat_name);
            }

            $anchor  =   array(
                'showAnchors'           =>  1, //"1/0": Configuration whether the anchors would be shown for this dataset or not. If the anchors are not shown, then the hover caption and link functions won't work.
                'anchorSides'           =>  6, //"Numeric Value greater than 3": This attribute sets the number of sides the anchors (of this dataset) will have. For e.g., an anchor with 3 sides would represent a triangle, with 4 it would be a square and so on.
                'anchorRadius'          =>  6, //"Numeric Value" : This attribute sets the radius (in pixels) of the anchor. Greater the radius, bigger would be the anchor size.
                //'anchorBorderColor'     =>  '000000', //"Hex Code" : Border Color of the anchor.
                //'anchorBorderThickness' =>  2, //"Numeric Value" : Thickness of the anchor border (in pixels).
                //'anchorBgColor'         =>  '', //"Hex Code" : Background color of the anchor.
                //'anchorBgAlpha'         =>  60, //"Numeric Value" : Alpha of the anchor background.
                'anchorAlpha'           =>  100, //"Numeric Value" : This function lets you set the tranparency of the entire anchor (including the border). This attribute is particularly useful, when you do not want the anchors to be visible on the chart, but you want the hover
            );

            if (isset($args['dataset_limit']) && $args['dataset_limit'])
            {
                $dataset =   $args['dataset_limit'];

                foreach ($dataset as $d)
                {
                    $value  =   htmlspecialchars($d['value']);
                    $ds =   $doc->createElement('dataset');

                    $ds_attributes  =   array_merge($anchor, array(
                        'seriesname'    =>  $d['label'],
                        'alpha'         =>  isset($d['alpha']) ? $d['alpha'] : 20,
                        'anchorBgColor' =>  (isset($d['anchor_bgcolor'])) ? $d['anchor_bgcolor'] : '',
                        'anchorRadius'  =>  (isset($d['anchor_radius'])) ? $d['anchor_radius'] : '6',
                        'color'         =>  (isset($d['color'])) ? $d['color'] : 'FF0000',
                        'lineThickness' =>  (isset($d['line_thickness'])) ? $d['line_thickness'] : 1,
                        'showValues'    =>  isset($d['show_value']) ? $d['show_value'] : 1,
                    ));

                    if (isset($d['anchor_side']))
                    {
                        $ds_attributes['anchorSides']   =   $d['anchor_side'];
                    }

                    foreach ($ds_attributes as $attr_name => $attr_value)
                    {
                        $attr        =   $doc->createAttribute($attr_name);
                        $attr->value =   htmlspecialchars($attr_value);
                        $ds->appendChild($attr);
                    }

                    // just loop x-axis values
                    for ($x=0; $x < count($args['categories']); $x++)
                    {
                        $set        =   $doc->createElement('set');
                        $sv         =   $doc->createAttribute('value');
                        $sv->value  =   $value;
                        $set->appendChild($sv);
                        $ds->appendChild($set);
                    }

                    $graph->appendChild($ds);
                }
            }

            if (isset($args['dataset']) && $args['dataset'])
            {
                $ds =   $doc->createElement('dataset');

                $ds_attributes  =   array_merge($anchor, array(
                    'seriesname'            =>  "Current Level",
                    'alpha'                 =>  100,
                    'anchorBgColor'         =>  '000000',
                    'anchorBorderColor'     =>  '2597FF',
                    'anchorBorderThickness' =>  2,
                    'anchorRadius'          =>  5,
                    'anchorSides'           =>  100,
                    'color'                 =>  '000000',
                    'lineThickness'         =>  2,
                    'showValues'            =>  1,
                ));


                foreach ($ds_attributes as $attr_name => $attr_value)
                {
                    $attr        =   $doc->createAttribute($attr_name);
                    $attr->value =   htmlspecialchars($attr_value);
                    $ds->appendChild($attr);
                }

                foreach ($args['dataset'] as $d)
                {
                    $value  =   htmlspecialchars($d['value']);
                    $set        =   $doc->createElement('set');
                    $sv         =   $doc->createAttribute('value');
                    $sv->value  =   $value;
                    $set->appendChild($sv);
                    $ds->appendChild($set);
                }
                $graph->appendChild($ds);
            }

            $graph->appendChild($categories);

        }

        $doc->appendChild($xmlroot);

        $xml_string = $doc->saveXML();
        return $xml_string;
    }

    function get_chart_properties($type=null)
    {
        $background =   array(
            'bgColor'   =>  'F7FBFF',
            'bgAlpha'   =>  100,
            //'bgSWF'     =>  '',
        );

        $canvas =   array(
            'canvasBgColor'         =>  'F7FBFF',
            'canvasBgAlpha'         =>  100,
            'canvasBorderColor'     =>  'D3D3D3',
            'canvasBorderThickness' =>  1,
        );

        $axis   =   array(
            'caption'       =>  '',
            'subCaption'    =>  '',
            'xAxisName'     =>  '',
            'yAxisName'     =>  '',
        );

        $limits =   array(
            'yAxisMinValue' =>  '',
            'yAxisMaxValue' =>  '',
        );

        $general    =   array(
            'animation'     =>  1, //This attribute sets whether the animation is to be played or whether the entire chart would be rendered at one go.
            'rotateNames'   =>  0, //Configuration that sets whether the category name text boxes would be rotated or not.
            'showLegend'    =>  1, //This attribute sets whether the legend would be displayed at the bottom of the chart.
            'showLimits'    =>  1, //Option whether to show/hide the chart limit textboxes.
            'shownames'     =>  1, //This attribute can have either of the two possible values: 1,0. It sets the configuration whether the x-axis values (for the data sets) will be displayed or not. By default, this attribute assumes the value 1, which means that the x-axis names will be displayed.
            'showValues'    =>  1, //This attribute can have either of the two possible values: 1,0. It sets the configuration whether the data numerical values will be displayed along with the columns, bars, lines and the pies. By default, this attribute assumes the value 1, which means that the values will be displayed.

        );

        $area   =   array(
            'areaAlpha'             =>  100, //"0-100" : Transparency of the area fill.
            'areaBorderColor'       =>  '000000', //"Hex Color" : If the area border is to be shown, this attribute sets the color of the area border.
            'areaBorderThickness'   =>  1, //If the area border is to be shown, this attribute sets the thickness (in pixels) of the area border.
            'showAreaBorder'        =>  0, //Configuration whether the border over the area would be shown or not.
        );

        $font   =   array(
            /*
             * This attribute sets the base font family of the chart font which lies on the canvas
             * i.e., all the values and the names in the chart which lie on the canvas will be displayed using the font name provided here.
            */
            'baseFont'              =>  "Arial",
            'baseFontSize'          =>  12, //"FontSize" : This attribute sets the base font size of the chart i.e., all the values and the names in the chart which lie on the canvas will be displayed using the font size provided here.
            'baseFontColor'         =>  '000000', //"HexColorCode" : This attribute sets the base font color of the chart i.e., all the values and the names in the chart which lie on the canvas will be displayed using the font color provided here.
            'outCnvBaseFont'        =>  'Arial', //"FontName" : This attribute sets the base font family of the chart font which lies outside the canvas i.e., all the values and the names in the chart which lie outside the canvas will be displayed using the font name provided here.
            'outCnvBaseFontSze'     =>  8, //"FontSize" : This attribute sets the base font size of the chart i.e., all the values and the names in the chart which lie outside the canvas will be displayed using the font size provided here.
            'outCnvBaseFontColor'   =>  '000000', //"HexColorCode": This attribute sets the base font color of the chart i.e., all the values and the names in the chart which lie outside the canvas will be displayed using the font color provided here.
        );

        $numbers    =   array(
            'numberPrefix'  =>  '', //"$" : Using this attribute, you could add prefix to all the numbers visible on the graph. For example, to represent all dollars figure on the chart, you could specify this attribute to ' $' to show like $40000, $50000.
            /*
             * "p.a" : Using this attribute, you could add prefix to all the numbers visible on the graph.
             * For example, to represent all figure quantified as per annum on the chart, you could
             * specify this attribute to ' /a' to show like 40000/a, 50000/a.
             *
             * To use special characters for numberPrefix or numberSuffix, you'll need to URL Encode them.
             * That is, suppose you wish to have numberSuffix as % (like 30%), you'll need to specify it as under:
             * numberSuffix='%25'
             *
             */
            'numberSuffix'              =>  '',
            'formatNumber'              =>  1, //"1/0" : This configuration determines whether the numbers displayed on the chart will be formatted using commas, e.g., 40,000 if formatNumber='1' and 40000 if formatNumber='0 '
            'formatNumberScale'         =>  0, //"1/0" : Configuration whether to add K (thousands) and M (millions) to a number after truncating and rounding it - e.g., if formatNumberScale is set to 1, 10434 would become 1.04K (with decimalPrecision set to 2 places). Same with numbers in millions - a M will added at the end.
            'decimalSeparator'          =>  '.', //"." : This option helps you specify the character to be used as the decimal separator in a number.
            'thousandSeparator'         =>  ',',//"," : This option helps you specify the character to be used as the thousands separator in a number.
            'decimalPrecision'          =>  2, // Number of decimal places to which all numbers on the chart would be rounded to.
            'divLineDecimalPrecision'   =>  0, //"2": Number of decimal places to which all divisional line (horizontal) values on the chart would be rounded to.
            'limitsDecimalPrecision'    =>  0, //"2" : Number of decimal places to which upper and lower limit values on the chart would be rounded to.
        );

        /*
         * Zero Plane
         * ==========
         * The zero plane is a simple plane (line) that signifies the 0 position on the chart.
         * If there are no negative numbers on the chart, you won't see a visible zero plane.
        */
        $zero_plane =   array(
            'zeroPlaneThickness'    =>  2, // "Numeric Value" : Thickness (in pixels) of the line indicating the zero plane.
            'zeroPlaneColor'        =>  '000000', //"Hex Code" : The intended color for the zero plane.
            'zeroPlaneAlpha'        =>  100, //"Numerical Value 0-100" : The intended transparency for the zero plane.
        );

        /*
         * Divisional Lines (Horizontal)
         * =============================
         * Divisional Lines are horizontal or vertical lines running through the canvas.
         * Each divisional line signfies a smaller unit of the entire axis thus aiding the users in interpreting the chart.
        */

        $division_horizontal    =   array(
            'numdivlines'               =>  10,       //"NumericalValue" : This attribute sets the number of divisional lines to be drawn.
            'divlinecolor'              =>  'CCCCCC', //"HexColorCode" : The color of grid divisional line.
            'divLineThickness'          =>  1, //"NumericalValue" : Thickness (in pixels) of the grid divisional line.
            'divLineAlpha'              =>  100, //"NumericalValue0-100" : Alpha (transparency) of the grid divisional line.
            'showDivLineValue'          =>  1, //"1/0" : Option to show/hide the textual value of the divisional line.
            'showAlternateHGridColor'   =>  0, //"1/0" : Option on whether to show alternate colored horizontal grid bands.
            'alternateHGridColor'       =>  '333333', //"HexColorCode" : Color of the alternate horizontal grid bands.
            'alternateHGridAlpha'       =>  80, // "NumericalValue0-100" : Alpha (transparency) of the alternate horizontal grid bands.
        );

        /*
         * Divisional Lines (Vertical)
         * ===========================
        */
        $division_vertical  =   array(
            'numVDivLines'              =>  10, //"NumericalValue" : Sets the number of vertical divisional lines to be drawn.
            'vDivlinecolor'             =>  'C6C6C6', //"HexColorCode" : Color of vertical grid divisional line.
            'vDivLineThickness'         =>  1, //"NumericalValue" : Thickness (in pixels) of the line
            'vDivLineAlpha'             =>  100, //"NumericalValue0-100" : Alpha (transparency) of the line.
            'showAlternateVGridColor'   =>  0, //"1/0" : Option on whether to show alternate colored vertical grid bands.
            'alternateVGridColor'       =>  '616161', //"HexColorCode" : Color of the alternate vertical grid bands.
            'alternateVGridAlpha'       =>  50, //"NumericalValue0-100" : Alpha (transparency) of the alternate vertical grid bands.
        );

        /*
         * Hover Caption Properties
         * ========================
         * The hover caption is the tool tip which shows up when the user moves his mouse over a particular data item (column, line, pie, bar etc.).
        */
        $hover_caption  =   array(
            'showhovercap'          =>  1, //"1/0" : Option whether to show/hide hover caption box.
            'hoverCapBgColor'       =>  'FFF300', //"HexColorCode" : Background color of the hover caption box.
            'hoverCapBorderColor'   =>  '000000', //"HexColorCode" : Border color of the hover caption box.
            'hoverCapSepChar'       =>  ' : ', //"Char" : The character specified as the value of this attribute separates the name and value displayed in the hover caption box.
        );

        /*
         * Chart Margins
         * =============
         * Chart Margins refers to the empty space left on the top, bottom, left and right of the chart.
         * That means, FusionCharts would leave that much amount of empty space on the chart, before it starts plotting.
        */
        $margin =   array(
            'chartLeftMargin'   =>  0, //"Numerical Value (in pixels)" : Space to be left unplotted on the left side of the chart.
            'chartRightMargin'  =>  0, //"Numerical Value (in pixels)" : Empty space to be left on the right side of the chart
            'chartTopMargin'    =>  0, //"Numerical Value (in pixels)" : Empty space to be left on the top of the chart.
            'chartBottomMargin' =>  0, //"Numerical Value (in pixels)" : Empty space to be left at the bottom of the chart.
        );

        // LINES
        $line   =   array(
            'lineColor'     =>  '', //"Hex Code" : If you want the entire line chart to be plotted in one color, set that color for this attribute.
            'lineThickness' =>  1, //"Numeric Value" : Thickness of the line (in pixels).
            'lineAlpha'     =>  20, //"0-100" : Transparency of the line.
        );

        /*
         * Line Shadow Properties
         * ======================
         *
         * The Line shadow is applicable only in Multi series line chart (Type 2).
        */
        $line_shadow    =   array(
            'showShadow'        =>  1, //"1/0" : This attribute helps you set whether the line shadow would be shown or not.
            //'shadowColor'       =>  '', //"Hex Code" : If you want to set your own shadow color, you'll need to specify that color for this attribute.
            'shadowThickness'   =>  2, //"Numeric Value" : This attribute helps you set the thickness of the shadow line (in pixels).
            'shadowAlpha'       =>  40, //"0-100" : This attribute sets the transparency of the shadow line.
            'shadowXShift'      =>  2, //"Numeric Value" : This attribute helps you set the x shift of the shadow line from the chart line. That is, if you want to show the shadow 3 pixel right from the actual line, set this attribute to 3. Similarly, if you want the shadow to appear on the left of the actual line, set it to -3.
            'shadowYShift'      =>  2, //"Numeric Value" : This attribute helps you set the y shift of the shadow line from the chart line. That is, if you want to show the shadow 3 pixel below the actual line, set this attribute to 3. Similarly, if you want the shadow to appear above the actual line, set it to -3.
        );

        return array_merge(
            $background,
            $canvas,
            $axis,
            $limits,
            $general,
            $area,
            $font,
            $numbers,
            $zero_plane,
            $division_horizontal,
            $division_vertical,
            $hover_caption,
            $margin,
            $line,
            $line_shadow
        );
    }

    function generate_workorder_xml($args, $chart_type)
    {
        if(!$args || !is_array($args))
        {
            return;
        }



        $add_category   =   true;

        //debug($args);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $graph      =   $doc->createElement('graph');
        $xmlroot    =   $doc->appendChild($graph);

        $xml        =   null;
        $categories =   $dataset = '';

        if($args)
        {
            $max_y  =   (isset($args['max_y'])) ? $args['max_y'] : 10000;
            $min_y  =   (isset($args['min_y'])) ? $args['min_y'] : 0;
            $dataset=   array();

            if(!$max_y)
            {
                $max_y  =   100;
            }

            switch($chart_type)
            {
                case 'bar2d':
                case 'column3d':

                break;

                case 'pie2d':
                case 'pie3d':
                    $add_category       =   false;
                    $common_attributes  =   array(
                        'caption'               =>  $args['chart_title'],
                        'subCaption'            =>  '',
                        'pieBorderAlpha'        =>  '40',//"0-100" : This attribute helps you set the border transparency for all the pie borders.
                        'pieBorderThickness'    =>  '2', //Each pie on the chart has a border, whose thickness you can specify using this attribute.
                        'pieFillAlpha'          =>  '100', //"0-100" : This attribute helps you set the transparency for all the pies on the chart.
                        'pieRadius'             =>  '100', //FusionCharts automatically calculates the best fit pie radius for the chart. However, if you want to enforce one of your own radius values, you can set it using this attribute.
                        'pieSliceDepth'         =>  '20', //This attribute helps you set the 3D height (depth) of the pies on the chart (in pixels)
                        'pieYScale'             =>  '50',//This value sets the skewness of the pie chart (vertical slant)
                        'showPercentageInLabel' =>  '1',
                        'chartTopMargin'        =>  40,
                    );

                    $attributes =   get_chart_properties($chart_type);
                    $attributes =   array_merge($attributes,$common_attributes);

                    foreach ($attributes as $key => $value)
                    {
                        $attr           =   $doc->createAttribute($key);
                        $attr->value    =   htmlspecialchars($value);
                        $graph->appendChild($attr);
                    }

                    $from_workorder =   0;
                    $from_ppm       =   0;
                    $pie_color  =   array(
                        'ppm'       =>  '00B2FC',
                        'workorder' =>  'FF2B00',
                    );


                    if (isset($args['dataset']['workorder']))
                    {

                        if(isset($args['dataset']['workorder']['color']))
                        {
                            $pie_color['workorder']    =   $args['dataset']['workorder']['color'];
                        }

                        $wods =   $args['dataset']['workorder'];


                        foreach ($wods as $key => $value)
                        {
                            if(!in_array($key, array('week_1', 'week_2', 'week_3', 'month_1', 'month_2', 'month_3')))
                            {
                                unset($wods[$key]);
                            }
                        }

                        if (!isset($args['period']))
                        {
                            $from_workorder =   array_sum($wods);
                        }
                        else
                        {
                            $from_workorder =   (isset($wods[$args['period']])) ? $wods[$args['period']] : 0;
                        }
                    }
                    if (isset($args['dataset']['ppm']))
                    {

                        if(isset($args['dataset']['ppm']['color']))
                        {
                            $pie_color['ppm']    =   $args['dataset']['ppm']['color'];
                        }

                        $ppmds   =   $args['dataset']['ppm'];

                        foreach ($ppmds as $key => $value)
                        {
                            if(!in_array($key, array('week_1', 'week_2', 'week_3', 'month_1', 'month_2', 'month_3')))
                            {
                                unset($ppmds[$key]);
                            }
                        }

                        if (!isset($args['period']))
                        {
                            $from_ppm =   array_sum($ppmds);
                        }
                        else
                        {
                            $from_ppm =   (isset($ppmds[$args['period']])) ? $ppmds[$args['period']] : 0;
                        }
                    }

                    if(!$from_workorder)
                    {
                        $from_workorder =   0.01;
                    }

                    if(!$from_ppm)
                    {
                        $from_ppm =   0.01;
                    }


                    $set        =   $doc->createElement('set');

                    $sv         =   $doc->createAttribute('value');
                    $sv->value  =   htmlspecialchars($from_workorder);
                    $set->appendChild($sv);

                    $sn         =   $doc->createAttribute('name');
                    $sn->value  =   'From Work Order';
                    $set->appendChild($sn);

                    $sc         =   $doc->createAttribute('color');
                    $sc->value  =   $pie_color['workorder'];
                    $set->appendChild($sc);

                    if(isset($args['url']) && $args['url'])
                    {
                        $surl           =   $doc->createAttribute('link');
                        $surl->value    =   htmlspecialchars($args['url']);
                        $set->appendChild($surl);
                    }

                    $graph->appendChild($set);

                    $set2        =   $doc->createElement('set');
                    $sv2         =   $doc->createAttribute('value');
                    $sv2->value  =   htmlspecialchars($from_ppm);
                    $set2->appendChild($sv2);

                    $sn2         =   $doc->createAttribute('name');
                    $sn2->value  =   'From PPM';
                    $set2->appendChild($sn2);

                    $sc2         =   $doc->createAttribute('color');
                    $sc2->value  =   $pie_color['ppm'];
                    $set2->appendChild($sc2);

                    if(isset($args['url']) && $args['url'])
                    {
                        $surl2           =   $doc->createAttribute('link');
                        $surl2->value    =   htmlspecialchars($args['url']);
                        $set2->appendChild($surl2);
                    }

                    $graph->appendChild($set2);


                break;

                case 'multicolumn3d':
                default:
                    //$graph      =   $doc->appendChild($doc->createElement('graph'));


                    $attributes =   array(
                        'animation'             =>  1,
                        'baseFontSize'          =>  12,
                        'bgColor'               =>  'F7FBFF',
                        'canvasBaseColor'       =>  '718197',
                        'canvasBaseDepth'       =>  10,
                        'canvasBgColor'         =>  'FFFFE5',
                        'canvasBgDepth'         =>  10,
                        'caption'               =>  htmlspecialchars($args['chart_title']),
                        'chartRightMargin'      =>  0,
                        'chartTopMargin'        =>  20,
                        'decimalPrecision'      =>  0,
                        'formatNumberScale'     =>  0,
                        //'outCnvBaseFontColor'   =>  '00537e',
                        'rotateNames'           =>  0,
                        'showCanvasBase'        =>  1,
                        'showLegend'            =>  1,
                        'showLimits'            =>  1,
                        'shownames'             =>  1,
                        'showValues'            =>  0,
                        'SYAxisMaxValue'        =>  $max_y,
                        'SYAxisMinValue'        =>  $min_y,
                        'yAxisName'             =>  '',
                        'yAxisMaxValue'         =>  $max_y,
                        'yAxisMinValue'         =>  0,
                    );

                    //#0078AD
                    $chart_attributes   =   get_chart_properties($chart_type);
                    $attributes         =   array_merge($chart_attributes,$attributes);

                    foreach ($attributes as $key => $value)
                    {
                        $attr           =   $doc->createAttribute($key);
                        $attr->value    =   htmlspecialchars($value);
                        $graph->appendChild($attr);
                    }

                    $xml    =   "<graph caption='$args[chart_title]' ".
                                    "bgColor='FFFFFF' ".
                                    "baseFontSize='12' ".
                                    //"numberPrefix='' ".
                                    "showValues='0' ".
                                    "decimalPrecision='0' ".
                                    "formatNumberScale='0' ".
                                    "outCnvBaseFontColor='000000' ".
                                    "canvasBgColor='DDDDDD' ".
                                    "canvasBaseColor='718197' ".
                                    "yAxisName='' ".
                                    "yAxisMinValue='0' ".
                                    "yAxisMaxValue='$max_y' ".
                                    "SYAxisMinValue='0' ".
                                    "SYAxisMinValue='$min_y' ".
                                    "chartTopMargin='0' ".
                                    "chartRightMargin='0' rotateNames='1'>";

                break;
            }

            if($add_category)
            {
                $categories      =   $doc->createElement('categories');

                $font_size  =   $doc->createAttribute('fontSize');
                $font_size->value = 10;
                $categories->appendChild($font_size);

                $xml    .=  '<categories>';

                foreach($args['categories'] as $cat)
                {
                    $label  =   htmlentities($cat['label']);
                    $xml    .=  '<category name="'.$label.'"/>';

                    $category   =   $categories->appendChild($doc->createElement('category'));
                    $cat_name   =   $doc->createAttribute('name');
                    $cat_name->value    =   $label;
                    $category->appendChild($cat_name);



                    foreach ($args['dataset'] as $key => $row)
                    {
                        if(!isset($dataset[$key]['label']))
                        {
                            $dataset[$key]['label'] =   $row['label'];
                        }
                        if(!isset($dataset[$key]['color']))
                        {
                            $dataset[$key]['color'] =   $row['color'];
                        }

                        if (isset($row[$cat['key']]))
                        {
                            $dataset[$key]['values'][$cat['key']]  =   $row[$cat['key']];
                        }
                    }
                }
                $xml    .=  '</categories>';

                //debug($args['categories']);
                //debug($dataset);

                foreach ($dataset as $d)
                {
                    if(!isset($d['values']))
                    {
                        continue;
                    }
                    $xml    .=  '<dataset seriesname="'.htmlspecialchars($d['label']).'" color="'.$d['color'].'" showValues="1">';
                    foreach ($d['values'] as $value)
                    {
                        $xml    .=  '<set value="'.htmlspecialchars($value).'"/>';
                    }
                    $xml    .=  '</dataset>';

                    $ds =   $doc->createElement('dataset');

                    $series_name        =   $doc->createAttribute('seriesname');
                    $series_name->value =   htmlspecialchars($d['label']);
                    $ds->appendChild($series_name);

                    $color              =   $doc->createAttribute('color');
                    $color->value       =   htmlspecialchars($d['color']);
                    $ds->appendChild($color);

                    $showvalue          =   $doc->createAttribute('showValues');
                    $showvalue->value   =   1;
                    $ds->appendChild($showvalue);

                    foreach ($d['values'] as $value)
                    {
                        $set        =   $doc->createElement('set');
                        $sv         =   $doc->createAttribute('value');
                        $sv->value  =   htmlspecialchars($value);
                        $set->appendChild($sv);

                        if(isset($args['url']) && $args['url'])
                        {
                            $surl           =   $doc->createAttribute('link');
                            $surl->value    =   htmlspecialchars($args['url']);
                            $set->appendChild($surl);
                        }

                        $ds->appendChild($set);
                    }

                    $graph->appendChild($ds);
                }
                $xml    .=  '</graph>';

                $graph->appendChild($categories);

            }
        }

        $doc->appendChild($xmlroot);

        $xml_string = $doc->saveXML();
        //return $xml;
        return $xml_string;
    }

    exit(json_encode(0));
