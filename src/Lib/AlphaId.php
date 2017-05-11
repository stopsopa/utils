<?php

namespace Stopsopa\UtilsBundle\Lib;

class AlphaId {
// pochodzi : [http://stackoverflow.com/questions/4514168/unique-alpha-numeric-generator]
  protected $key = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // youtube używa np : http://www.youtube.com/watch?v=BF1B1dlHEow&list=RD02sdHx_0s-_mQ
//  private $key = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.-';  // tylko te znaki są bezpiecznie jeśli chodzi o użycie w php urlencode()
//  private $key = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_)(.-*!~';  // te dodatkowe znaki nie są przetwarzane przez encodeURIComponent js więc na upartego też można ich użyć
  protected $div = 0;
  public function __construct( $key = false) {
    $this->setKey($key);
  }
  public function setKey($key) {
    if ($key && is_string($key) && strlen($key)) {
      $this->key = $key;
    }
    $this->count = strlen($this->key);
  }
  public function encode($num) {
    $encoded = '';
    $count   = $this->count; // optymalizacja - nie wiem czemu ale jest minimalnie szybciej, jeśli kilka takich zmiennych pomocniczych się zastosuje zamiast wywoływać składowe to daje efekt
    $c       = $this->key; // optymalizacja
    while ($num >= $count) {
      $div     = intval($num/$count);
      $encoded.= $c[$num-($count*$div)];
      $num     = $div;
    }
    if ($num) {
      $encoded .= $c[$num];
    }

    return strrev($encoded);
  }
  public function decode($alpha) {

    $alpha = strrev($alpha);

    $decoded = 0;
    $multi   = 1;
    $c       = $this->key; // optymalizacja
    $count   = $this->count; // optymalizacja
    $len     = strlen($alpha);
    for ( $i=0 ; $i<$len ; $i++ ) {
      $decoded += $multi * strpos($c, $alpha[$i]);
      $multi   *= $count;
    }
    return $decoded;
  }
  public static function encodeStatic($num, $key = false) {
    $ins = new static($key);
    return $ins->encode($num);
  }
  public static function decodeStatic($alpha, $key = false) {
    $ins = new static($key);
    return $ins->decode($alpha);
  }
}