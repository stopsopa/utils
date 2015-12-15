<?php

namespace Stopsopa\UtilsBundle\Lib;

/**
 *  U�ycie
$p = new Performance();
$p->start('test2');
$p->start('test1');
sleep(1);
$p->stopLast();
$p->start('test3');
sleep(2);
$p->stop('test2');
sleep(3);
$p->stop('test3');
 
$p->getStats();
 // lub statycznie
 Performance::getInstance()->start('key');
 Performance::getInstance()->stop('key');
 Performance::getInstance()->getStatCharts();
 Performance::getInstance()->reset();
 */

class Performance {
  protected $allstart;
  protected $start;
  protected $stop;  
  
  protected static $instance;
  protected static $enabled = true;
  public static function enable($enable = true) {
    self::$enabled = (bool)$enable;
  }
  /**
   * Je�li potrzebujemy jeden obiekt a nie chce nam si� kombinowa� �eby
   * @return Performance
   */
  public static function getInstance() {
    
    if (!self::$instance) 
      self::$instance = new self();
    
    return self::$instance;
  }

  public function __construct() {  
    if (!self::$enabled) return null;    
    $this->allstart or $this->reset();    
  }
  public function reset() {  
    if (!self::$enabled) return null; 
    $this->allstart = $this->getTime();
    $this->start    = array();
    $this->stop     = array();    
  }

  public function start($name) {  
    if (!self::$enabled) return null; 
    $this->start[$name] = $this->getTime();
    return $this;
  }
  public function stop ($name) {  
    if (!self::$enabled) return null; 
    $this->stop[$name] = $this->getTime();
    return $this;
  }
  public function stopLast() {  
    if (!self::$enabled) return null; 
    if (count($this->start)) {
      end($this->start);  
      $this->stop(key($this->start));
    }        
    return $this;    
  }

  protected function getArray() {  
    if (!self::$enabled) return null; 
    $data = array('list'=>array());        
    foreach ($this->start as $k => $d) { 
      if (!empty($this->start[$k]) && !empty($this->stop[$k]))          
        $data['list'][$k] = $this->stop[$k] - $this->start[$k];
    }
    $data['all'] = $this->getTime() - $this->allstart;
    return $data;
  }
  protected function getFormattedArray() {  
    if (!self::$enabled) return null; 
    $data = $this->getArray();
    foreach ($data['list'] as $k => $d) 
      $data['list'][$k] = str_pad($d, 15, ' ', STR_PAD_RIGHT);    
    $data['all'] = str_pad($data['all'], 15, ' ', STR_PAD_RIGHT);
    return $data;
  }

  public function getStatCharts($return = false, $pre = true) {  
    if (!self::$enabled) return null; 
    $_  = $this->getStats(true, $pre);
    $_ .= $this->getChart();
        
    if ($return)
      return $_;
    echo $_;
    return $_;
  }

  public function getStats($return = false, $pre = true) {  
    if (!self::$enabled) return null; 
    $_  = $pre && php_sapi_name() != 'cli' ? '<pre>' : "\n";
    $data = $this->getFormattedArray();
    foreach ($data['list'] as $k => $d) {
      $_ .= "$d : $k\n";      
      
    }
    $_ .= "{$data['all']} : -=all=-\n";
        
    if ($return)
      return $_;
    echo $_;
    return $_;
  }
  protected function getChartArray() {  
    if (!self::$enabled) return null; 
    $data = $this->getArray();
    $_ = array();
    foreach ($data['list'] as $k => $d) {      
      $_[] = array($k,$d);
    }
    return $_;
  }

  protected function getChart() {  
    if (!self::$enabled) return null; 
    if (count($this->start) < 2 || php_sapi_name() == 'cli')
      return '';    
    $data = $this->getChartArray();
    ob_start();
    ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
  var _data = {
    label : 'Peak memory: <?php echo $this->getMaxMemoryMb(); ?> MB',
    data  : <?php echo json_encode($data); ?>
  }
  google.load('visualization', '1.0', {'packages':['corechart']});
  google.setOnLoadCallback(function drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Topping');
    data.addColumn('number', 'Slices');
    data.addRows(_data.data);
    var options = {title:_data.label,width:500};
    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  });
</script>
<div id="chart_div"></div>
    <?php
    return ob_get_clean();
  }

  protected function getDiff($name = null) {  
    if (!self::$enabled) return null; 
    if ($name === null)
      return str_pad($this->getTime() - $this->allstart, 15, ' ', STR_PAD_RIGHT);
    return str_pad($this->stop[$name] - $this->start[$name], 15, ' ', STR_PAD_RIGHT);
  }

  public function getTime () {  
    if (!self::$enabled) return null; 
     return (float) array_sum(explode(' ',microtime()));
  }
  /**
   * Zwraca warto�� szczytowego obci��enia pami�ci przez skrypt w megabajtach
   */
  public function getMaxMemoryMb () {
    return memory_get_peak_usage(true) / (1024*1024);
  }
}
   
   