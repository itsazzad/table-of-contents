<?php
class ResolutionsController {

    //On uploading/generating any new pdf in the Agendas folder, extract the resolution info from the pdf
    public function __construct(){
        // Include 'Composer' autoloader.
        include 'vendor/autoload.php';

        // Parse pdf file and build necessary objects.
        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile('April20.pdf');
        $subject = $pdf->getText();

        //Get index of the starting of every resolutions only
        //preg_match_all("/\n[\s]*[[:alnum:]](.)[\s]*(Resolution)[\s]*[\d]+[-][\d]+(,)[\s]*/", $subject, $resolutions, PREG_OFFSET_CAPTURE);
        //print_r($resolutions);
        //Get index of every sort of indices
        //{Starting of a new line}{any number of whitespaces}{Alphabetic|Numeric}{.}
        preg_match_all("/\n[\s]*[a-zA-Z|0-9]*[.|)]/", $subject, $indices, PREG_OFFSET_CAPTURE);
        $this->chained_indices($indices, $subject);
        
        preg_match_all("/\n[\s]*[a-zA-Z|0-9]*[.|)][\s]*(Resolution)[\s]*(R)[\d]+[-][\d]+(,)[\s]*/", $subject, $resolutions, PREG_OFFSET_CAPTURE);
        $this->chained_indices($resolutions, $subject);
        
        return array(
            'all'           =>$indices,
            'resolutions'   =>$resolutions
        );
    }
    public function chained_indices($indices, $subject){
        $length=strlen($subject);
        $chained_indices=array();
        //Filter matched indices
        foreach ($indices[0] as $i => $index){
            $indices[0][$i]['clean']=str_replace('.', '', trim($index[0]));
            if(isset($indices[0][$i-1])){
                $indices[0][$i]['prev']=$indices[0][$i-1]['clean'];

                if(is_numeric($indices[0][$i]['clean'])){
                    $indices[0][$i]['now?']=$indices[0][$i]['prev']+1;
                }else if(is_string($indices[0][$i]['clean'])){
                    $indices[0][$i]['now?']=chr(ord($indices[0][$i]['prev'])+1);
                }
                if($indices[0][$i]['clean']!=$indices[0][$i]['now?']){
                    if(strtoupper($indices[0][$i]['clean'])=='A'||$indices[0][$i]['clean']==1) {
                    }else{
                        $indices[0][$i]['condition'] = "{$indices[0][$i]['clean']}";
                        $indices[0][$i]['unset'] = true;
                        //unset($indices[0][$i]);
                    }
                }
            }else{
                $chained_indices[0]=$i;
            }
            if(isset($indices[0][$i-1])){
                $indices[0][$i-1]['next']=$indices[0][$i]['clean'];
            }
        }
        foreach ($indices[0] as $i => $index){
            //$indices[0][$i]['clean']=trim($index[0]);
            //$indices[0][$i]['length'][0]=strlen($index[0]);
            //$indices[0][$i]['length'][1]=strlen($indices[0][$i]['clean']);
            if(isset($indices[0][$i+1])){
                $l=$indices[0][$i+1][1]-$indices[0][$i][1];
            }else{
                $l=$length;
            }
            $index_l=strlen($index[0]);
            $indices[0][$i]['data']=substr($subject, $index[1]+$index_l, $l-$index_l);
        }
        return $indices;
    }
 


}


$resolution=new ResolutionsController;
