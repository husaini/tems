<?php
/** Include PHPExcel_IOFactory */
require_once (dirname(__FILE__).'/phpexcel/Classes/PHPExcel/IOFactory.php');

if(!function_exists('checkworkscope'))
{
    function checkworkscope($catbit, $bit) {
        return ($catbit & $bit);
    }
}


class TEMSExcelFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow  =   0;
    private $_endRow    =   0;
    private $_columns   =   array();

    public function __construct($startRow, $endRow, $columns)
    {
        $this->_startRow    =   $startRow;
        $this->_endRow      =   $endRow;
        $this->_columns     =   $columns;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if($this->_endRow)
        {
            if ($row >= $this->_startRow && $row <= $this->_endRow)
            {
                if (in_array($column,$this->_columns))
                {
                    return true;
                }
            }
        }
        else
        {
            //no limit
            if (in_array($column,$this->_columns))
            {
                return true;
            }
        }
        return false;
    }
}

function output_workorder_pdf($wo_id, $save=false)
{
    global $mysqli;

    if(!is_numeric($wo_id) || !$wo_id)
    {
        return;
    }

    $upload_dir =   dirname(__FILE__).'/../upload/';

    $sql    =   'SELECT '.
                    'wo.*,'.
                    'u.name AS reporter, '.
                    'a.serialno,'.
                    'a.remarks,'.
                    'a.siteid,'.
                    'a.assetno AS tems_no, '.
                    'am.name AS model,'.
                    'sd.name AS department,'.
                    'sl.name AS location '.
                'FROM '.
                    'workorder wo '.
                'INNER JOIN '.
                    'asset a ON a.id = wo.assetid '.
                'INNER JOIN '.
                    'user u ON u.id = wo.author '.
                'LEFT JOIN '.
                    'asset_model am ON am.id = a.modelid '.
                'LEFT JOIN '.
                    'site_department sd ON sd.id = a.department_id AND sd.siteid = a.siteid '.
                'LEFT JOIN '.
                    'site_location sl ON sl.id = a.department_id AND sl.siteid = a.siteid '.
                'WHERE '.
                    'wo.id = '.mysqli_real_escape_string($mysqli, $wo_id);

    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli));

    if ($result)
    {

        $data    =   $result->fetch_assoc();
        mysqli_free_result($result);

        if($data)
        {
            $data['wo_no']          =   str_pad($data['id'],5,'0', STR_PAD_LEFT);
            $data['is_corrective']  =   checkworkscope($data['category'], 2);
            $data['is_preventive']  =   checkworkscope($data['category'], 1);
            $data['report_date']    =   date('d/m/Y', strtotime($data['created']));
            $data['report_time']    =   date('g:i a', strtotime($data['created']));
        }

        # Load Twig
        require_once (dirname(__FILE__).'/Twig/Autoloader.php');
        Twig_Autoloader::register();

        $template_cache =   dirname(__FILE__).'/../templates_c/';
        $template_path  =   dirname(__FILE__).'/../templates/';
        $template       =   'workorder.html';
        $loader         =   new Twig_Loader_Filesystem($template_path);
        $twig           =   new Twig_Environment($loader, array(
            'cache' =>  $template_cache,
            'debug' =>  false,

        ));
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addFilter('stripslashes',new Twig_Filter_Function('stripslashes'));

        $tems_no    =   array_pop(explode('-', $data['tems_no']));
        $site_id    =   array_shift(explode('-', $data['tems_no']));

        $variables  =   array(
            'data'          =>  $data,
            'site_arr'      =>  str_split($site_id),
            'site_id'       =>  $site_id,
            'tems_no'       =>  $tems_no,
            'tems_no_arr'   =>  str_split($tems_no),
            'year_arr'      =>  str_split(date('y')),
            'wo_no_arr'     =>  str_split($data['wo_no']),

        );

        $html   =   $twig->render($template, $variables);


        //die($html);

        //ini_set('display_errors', 0);

        include(dirname(__FILE__).'/MPDF56/mpdf.php');
        //($mode='',$format='A4',$default_font_size=0,$default_font='',$mgl=15,$mgr=15,$mgt=16,$mgb=16,$mgh=9,$mgf=9, $orientation='P')
        $mpdf=new mPDF('c','A4', 11,'',15,15,15,15,0,0,'L');

        $mpdf->shrink_tables_to_fit     =   TRUE;
        $mpdf->keep_table_proportions   =   TRUE;
        $mpdf->ignore_table_widths      =   FALSE;

        $title  =   'TEMS Workorder '.$data['wo_no'];
        $mpdf->SetTitle($title);
        $mpdf->SetSubject($title);
        $mpdf->SetDisplayMode('fullpage');

        // LOAD a stylesheet
        $stylesheet = file_get_contents(dirname(__FILE__).'/../css/workorder/pdf.css');
        $mpdf->WriteHTML($stylesheet,1);    // The parameter 1 tells that this is css/style only and no body/html/text

        $mpdf->WriteHTML($html);
        $mode   =   $save ? 'D' : 'I';
        $y = date('y');
        $filename   =   $site_id.$tems_no.$y.'BEM'.$data['wo_no'];
        $mpdf->Output('tems_workorder_'.$filename.'.pdf', $mode);
    }

    if(!$save)
    {
        die();
    }
    return true;
}
