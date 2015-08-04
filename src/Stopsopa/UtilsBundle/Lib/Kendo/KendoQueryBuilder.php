<?php

namespace Stopsopa\UtilsBundle\Lib\Kendo;

use Doctrine\ORM\QueryBuilder;

class KendoQueryBuilder extends QueryBuilder
{
    public function getAllParts()
    {
        return  $this->_getReducedDQLQueryPart('where', array('pre' => ' WHERE '))
          .$this->_getReducedDQLQueryPart('groupBy', array('pre' => ' GROUP BY ', 'separator' => ', '))
          .$this->_getReducedDQLQueryPart('having', array('pre' => ' HAVING '))
          .$this->_getReducedDQLQueryPart('orderBy', array('pre' => ' ORDER BY ', 'separator' => ', '));
    }
    public function getWherePart($pre = ' WHERE ', $translate = true)
    {
        return $this->_getReducedDQLQueryPart('where', array('pre' => $pre));
    }
    public function getGroupByPart($pre = ' GROUP BY ', $separator = ', ')
    {
        $params = array();
        $separator and $params['separator'] = $separator;
        $pre       and $params['pre'] = $pre;

        return $this->_getReducedDQLQueryPart('groupBy', $params);
    }
    public function getHavingPart($pre = ' HAVING ')
    {
        return $this->_getReducedDQLQueryPart('having', array('pre' => $pre));
    }
    public function getOrderByPart($pre = ' ORDER BY ', $separator = ', ')
    {
        $params = array();
        $separator and $params['separator'] = $separator;
        $pre       and $params['pre'] = $pre;

        return $this->_getReducedDQLQueryPart('orderBy', $params);
    }

    protected $limitOffset = '';
    public function setLimitOffset($lo)
    {
        $this->limitOffset = trim($lo);

        return $this;
    }
    public function getLimitOffset()
    {
        return $this->limitOffset;
    }

  /**
   * Co za idiota oznaczył tę metody jako private... !!!
   * ... powinny być protected
   * przez to musze je kopiować ręcznie.
   */
  private function _getReducedDQLQueryPart($queryPartName, $options = array())
  {
      $queryPart = $this->getDQLPart($queryPartName);

      if (empty($queryPart)) {
          return (isset($options['empty']) ? $options['empty'] : '');
      }

      return (isset($options['pre']) ? $options['pre'] : '')
           .(is_array($queryPart) ? implode($options['separator'], $queryPart) : $queryPart)
           .(isset($options['post']) ? $options['post'] : '');
  }
}
