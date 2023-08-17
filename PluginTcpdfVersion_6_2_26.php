<?php
class PluginTcpdfVersion_6_2_26{
  private $pdf = null;
  /**
   <p>Add method param to fill $pdf with data.</p>
   */
  public function widget_output($widget_data){
    /**
     * Include.
     */
    wfPlugin::includeonce('wf/array');
    /**
     * Get widget default.
     */
    $data = wfPlugin::getWidgetDefault($widget_data);
    /**
     * Merge data.
     */
    $data = new PluginWfArray(array_merge($data->get(), $widget_data['data']));
    /**
     * Data method.
     */
    if($data->get('data_method/plugin') && $data->get('data_method/method')){
      wfPlugin::includeonce($data->get('data_method/plugin'));
      $obj = wfSettings::getPluginObj($data->get('data_method/plugin'));
      $method = $data->get('data_method/method');
      $data = $obj->$method($data);
    }
    /**
     * Header logo
     */
    $data->set('header_logo', wfSettings::replaceTheme($data->get('header_logo')));
    /**
     * I18N
     */
    wfPlugin::includeonce('i18n/translate_v1');
    $i18n = new PluginI18nTranslate_v1();
    foreach ($data->get('pages') as $key => $value) {
      foreach ($value as $key2 => $value2) {
        $item = new PluginWfArray($value2);
        if($item->get('method')=='MultiCell' || $item->get('method')=='Cell'){
          $data->set("pages/$key/$key2/data/txt", $i18n->translateFromTheme($item->get('data/txt')));
        }
      }
    }
    /**
     * Image path in SetHeaderData should be set from root. 
     */
    if(!defined('K_PATH_IMAGES')){
      define ('K_PATH_IMAGES', ''); // Not need this?
    }
    /**
     * Include tcpdf.
     */
    include_once dirname(__FILE__).'/lib/tcpdf.php';
    include_once dirname(__FILE__).'/TCPDF_X.php';
    /**
     * Create doc.
     */
    $this->pdf = new TCPDF_X(PDF_PAGE_ORIENTATION, PDF_UNIT, $data->get('PDF_PAGE_FORMAT'), true, 'UTF-8', false);
    /**
     * Custom values.
     */
    $this->pdf->SetCreator(PDF_CREATOR);
    $this->pdf->SetAuthor($data->get('author'));
    $this->pdf->SetTitle($data->get('title'));
    $this->pdf->SetSubject($data->get('subject'));
    $this->pdf->SetKeywords($data->get('keywords'));
    if($data->get('header_logo') && $data->get('header_logo_width')){
      $header_logo = wfArray::get($GLOBALS, 'sys/web_dir').$data->get('header_logo');
      $this->pdf->SetHeaderData($header_logo, $data->get('header_logo_width'), $data->get('header_title'), $data->get('header_string'));
    }
    /**
     * Standard values.
     */
    $this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    /**
     * Margin
     */
    if(!$data->get('margin/left')){
      $data->set('margin/left', PDF_MARGIN_LEFT);
    }
    if(!$data->get('margin/top')){
      $data->set('margin/top', PDF_MARGIN_TOP);
    }
    if(!$data->get('margin/right')){
      $data->set('margin/right', PDF_MARGIN_RIGHT);
    }
    $this->pdf->SetMargins($data->get('margin/left'), $data->get('margin/top'), $data->get('margin/right'));
    /**
     * 
     */
    $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM-15);
    $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    if (false && @file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $this->pdf->setLanguageArray($l);
    }
    $this->pdf->setPrintHeader($data->get('print_header'));
    $this->pdf->setPrintFooter($data->get('print_footer'));
    /**
     * Footer
     */
    $this->pdf->footer_text = $data->get('footer_text');
    $this->pdf->Footer();
    /**
     * Pages.
     */
    if($data->get('pages')){
      foreach ($data->get('pages') as $ke => $valu) {
        $this->pdf->AddPage();
        foreach ($valu as $key => $value) {
          $item = new PluginWfArray($value);
          $method  = $item->get('method');
          if($method == 'Many'){
            foreach ($item->get('data') as $key2 => $value2) {
              $item2 = new PluginWfArray($value2);
              $this->runMethod($item2->get('method'), $item2, $data);
            }
          }else{
            $this->runMethod($method, $item, $data);
          }
        }
      }
    }
    /**
     * Run method if set.
     */
    if($data->get('method/plugin') && $data->get('method/method')){
      wfPlugin::includeonce($data->get('method/plugin'));
      $obj = wfSettings::getPluginObj($data->get('method/plugin'));
      $method = $data->get('method/method');
      $this->pdf = $obj->$method($this->pdf);
    }
    /**
     * Clean up method.
     */
    if($data->get('clean_up_method/plugin') && $data->get('clean_up_method/method')){
      wfPlugin::includeonce($data->get('clean_up_method/plugin'));
      $obj = wfSettings::getPluginObj($data->get('clean_up_method/plugin'));
      $method = $data->get('clean_up_method/method');
      $obj->$method($data);
    }
    /**
     * 
     */
    $data->set('filename', wfSettings::replaceDir($data->get('filename')));
    /**
     * Output.
     */
    $this->pdf->Output($data->get('filename'), $data->get('dest'));
    if($data->get('dest')!='F'){
      exit;
    }
  }
  private function runMethod($method, $item, $data){
    if($method == 'MultiCell'){
      $this->MultiCell($item, $data);
    }elseif($method == 'Cell'){
      $this->Cell($item);
    }elseif($method == 'SetFont'){
      $this->SetFont($item);
    }elseif($method == 'AddPage'){
      $this->AddPage($item);
    }elseif($method == 'Ln'){
      $this->Ln($item);
    }elseif($method == 'MoveY'){
      $this->MoveY($item);
    }elseif($method == 'SetY'){
      $this->SetY($item);
    }elseif($method == 'SetTextColor'){
      $this->SetTextColor($item);
    }elseif($method == 'SetFillColor'){
      $this->SetFillColor($item);
    }elseif($method == 'WriteHTML'){
      $this->WriteHTML($item);
    }elseif($method == 'WriteHTMLCell'){
      $this->WriteHTMLCell($item);
    }elseif($method == 'Line'){
      $this->Line($item);
    }elseif($method == 'Image'){
      $this->Image($item);
    }elseif($method == 'Text'){
      $this->Text($item);
    }elseif($method == 'new_page'){
      $this->new_page($item);
    }else{
      exit("PluginTcpdfVersion_6_2_26 says: Method $method does not exist.");
    }
    return null;
  }
  /**
   * Add a new page if method GetY are more than data/y.
   * @param object $pdf
   * @param PluginWfArray $item
   * @return object $pdf
   */
  private function new_page($item){
    if($this->pdf->getY()>$item->get('data/y')){
      $this->pdf->AddPage();
    }
    return null;
  }
  private function Line($item){
    $x1=10; $y1=10; $x2=20; $y2=20; $style = array();
    if($item->get('data')){
      foreach ($item->get('data') as $key2 => $value2){
        if(!is_array($value2)){
          eval('$'.$key2.' = "'.$value2.'";');
        }else{
          eval('$$key2 = $value2;');
        }
      }
    }
    $this->pdf->Line( $x1, $y1, $x2, $y2, $style );
    return null;
  }
  private function WriteHTML($item){
    $html = ''; $ln = true; $fill = false; $reseth = false; $cell = false; $align = '';
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->writeHTML( $html, $ln, $fill, $reseth, $cell, $align );
    return null;
  }
  private function WriteHTMLCell($item){
    $w = null; $h = null; $x = null; $y = null; $html=''; $border=0; $ln=0; $fill=false; $reseth=true; $align=''; $autopadding=true;
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->writeHTMLCell( $w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true );
    return null;
  }
  private function MultiCell($item, $data){
    /**
     * 
     */
    if($this->hide_element($item)){
      return null;
    }
    /**
     * 
     */
    $w = 40;
    $h = 10;
    $txt = 'Multicell text.';
    $border = 1;
    $align = 'L';
    $fill = false;
    $ln = 1;
    $x = '';
    $y = '';
    $reseth = true;
    $stretch = 0;
    $ishtml = false;
    $autopadding = true;
    $maxh = 0;
    $valign = 'T';
    $fitcell = false;
    if($item->get('data')){
      foreach ($item->get('data') as $key2 => $value2){
        $value2 = $this->clean_value($value2);
        eval('$'.$key2.' = "'.$value2.'";');
      }
    }
    if($item->get('data/y_minus')){
      $y = $this->pdf->GetY() - $item->get('data/y_minus');
    }
    if(wfPhpfunc::substr($txt, 0, 5)=='data:'){
      $txt = $data->get(wfPhpfunc::str_replace('data:', '', $txt));
    }
    $this->pdf->MultiCell( $w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
    return null;
  }
  private function Cell($item){
    /**
     * 
     */
    if($this->hide_element($item)){
      return null;
    }
    /**
     * 
     */
    $w = 40;
    $h = 5;
    $txt = 'Cell text.';
    $border = 1;
    $ln = 0;
    $align = '';
    $fill = false;
    $link = '';
    $stretch = 0;
    $ignore_min_height = false;
    $calign = 'T';
    $valign = 'T'; //M
    if($item->get('data')){
      foreach ($item->get('data') as $key2 => $value2){
        $value2 = $this->clean_value($value2);
        eval('$'.$key2.' = "'.$value2.'";');
      }
    }
    $this->pdf->Cell( $w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign );
    return null;
  }
  private function SetFont($item){
    $family = 'helvetica';
    $style = '';
    $size = null;
    $fontfile = '';
    $subset = 'default';
    $out = true;
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->SetFont( $family, $style, $size, $fontfile, $subset, $out );
    return null;
  }
  private function AddPage($item){
    $orientation = '';$format = '';$keepmargins = false;$tocpage = false;
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->AddPage( $orientation, $format, $keepmargins, $tocpage);
    return null;
  }
  private function Ln($item){
    $h = ''; $cell = false;
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->Ln( $h, $cell );
    return null;
  }
  private function MoveY($item){
    if($item->get('data/y')){
      $this->pdf->SetY($this->pdf->GetY()+$item->get('data/y'));
    }
    return null;
  }
  private function SetY($item){
    if($item->get('data/y')){
      $this->pdf->SetY($item->get('data/y'));
    }
    return null;
  }
  private function SetTextColor($item){
    $col1 = 0; $col2 = -1; $col3 = -1; $col4 = -1; $ret = false; $name = '';
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->SetTextColor( $col1, $col2, $col3, $col4, $ret, $name );
    return null;
  }
  private function SetFillColor($item){
    $col1 = 0; $col2 = 0; $col3 = 0;
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->SetFillColor( $col1, $col2, $col3 );
    return null;
  }
  private function Image($item){
    $file = ''; $x = ''; $y = ''; $w = 0; $h = 0; $type = ''; $link = ''; $align = ''; $resize = false; $dpi = 300; $palign = ''; $ismask = false; $imgmask = false; $border = 0; $fitbox = false; $hidden = false; $fitonpage = false; $alt = false; $altimgs = array();
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $file = wfSettings::replaceDir($file);
    if(!wfFilesystem::fileExist($file)){
      throw new Exception(__CLASS__." says: Could not find file $file!");
    }
    $this->pdf->Image( $file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage, $alt, $altimgs );
    return null;
  }
  private function Text($item){
    $x = ''; $y = ''; $txt = ''; $fstroke = false; $fclip = false; $ffill = true; $border = 0; $ln = 0; $align = ''; $fill = false; $link = ''; $stretch = 0; $ignore_min_height = false; $calign = 'T'; $valign = 'M'; $rtloff = false;
    if($item->get('data')){foreach ($item->get('data') as $key2 => $value2){eval('$'.$key2.' = "'.$value2.'";');}}
    $this->pdf->Text( $x, $y, $txt, $fstroke, $fclip, $ffill, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign, $rtloff );
    return null;
  }
  public function data_method_example($data){
    $data->set('pages', array(array(
      $this->getElement('Cell', array('txt' => 'This text is from an example method.')),
      )));
    return $data;
  }
  public function getElement($method, $data = array()){
    return array('method' => $method, 'data' => $data);
  }
  private function clean_value($v){
    return wfPhpfunc::str_replace('"', '', $v);
  }
  private function hide_element($item){
    if($item->get('settings/enabled')===false){
      return true;
    }elseif($item->get('settings/disabled')===true){
      return true;
    }
    return false;
  }
}
