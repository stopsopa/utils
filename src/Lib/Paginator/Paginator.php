<?php

namespace Stopsopa\UtilsBundle\Lib\Paginator;

/**
 * Stopsopa\UtilsBundle\Lib\Paginator\Paginator
 */
class Paginator { 
  /**
   * @author Szymon Działowski
   * 
    * używać:

$limit   = $setup->get('news.length');
$buttons = $setup->get('news.buttons');
$count = $qb->getQuery()->getSingleScalarResult();

$pagin = new Paginator();
$pagin->count($count, $limit, $page);
$pageLimit = $pagin->getSqlPaginateParams();
$buttons = $pagin->getButtonsList($buttons, $page, '<<', '<', '>', '>>');
$qb
  ->select('n, i')
  ->orderBy('n.position', $order = 'asc')
  ->setFirstResult($pageLimit->getOffset())
  ->setMaxResults($pageLimit->getLimit());

$list = $qb->getQuery()->getResult();


return array(
    'list'    => $list,
    'buttons' => $buttons,
    'slug'    => $args[0]
);
    * (
    *     [<<<] => 1
    *     [<<] => 3
    *     [2] => 2
    *     [3] => 3
    *     [4] => 4
    *     [5] => 5
    *     [6] => 6
    *     [>>] => 5
    *     [>>>] => 13
    * )
   * 
   * obsługa w twig:
   * 
  {% include 'pagin' with {pasek:pasek,pre:'/'~lang~'/menu/'~menu.slug~'/',post:''} %}
   * 
   * 

<div class="btspa">
  {% if buttons.count %}
    <div class="btn-toolbar core-center paginate-div">
      <div class="btn-group paginate">
        {% for key,butt in buttons.list %}
          {% if butt.current %}
            <a title="Przejdź do strony {{ butt.num }}" class="btn btn-info btn-small">{{ butt.label }}</a>
          {% else %}
            <a title="Przejdź do strony {{ butt.num }}" class="btn btn-small" href="{{ path('#page',{slug:slug,p1:butt.num}) }}">{{ butt.label }}</a>                 
          {% endif %}
        {% endfor %}
      </div>
      <span>z</span>
      <div class="btn-group paginate">
        <a href="{{ path('#page',{slug:slug,p1:buttons.pages}) }}" title="Ostatnia strona nr {{ buttons.pages }}" class="btn btn-small">{{ buttons.pages }}</a>
      </div>
    </div>   
  {% else %}
  <div>coś nie tak</div>
  {% endif %}
</div>
   */
    protected $allWithoutLastOne = 0;
    protected $pages             = 0;
    protected $lastPage          = 0;
    protected $pickedPage;
    protected $elementsPerPage   = 20;
    /**
     * @param int $countFromDb
     * @param int $elementsPerPage
     * @param int $pickedPage
     */
    public function __construct($countFromDb=1,$elementsPerPage=20,$pickedPage=1) {
        return $this->count($countFromDb,$elementsPerPage,$pickedPage);
    }
    /**
     * @param int $countFromDb
     * @param int $elementsPerPage
     * @param int $pickedPage
     * @return SqlPaginateParams
     */
    public function count($countFromDb,$elementsPerPage,$pickedPage=1) {
        $this->pickedPage = $pickedPage;
        if ($countFromDb < 0 ) {
            $countFromDb = 0;
        }
        if ($elementsPerPage < 1) {
            $elementsPerPage = 1;
        }
        $this->elementsPerPage      = $elementsPerPage;
        $this->lastPage             = $countFromDb % $this->elementsPerPage;
        $this->allWithoutLastOne = $countFromDb - $this->lastPage ;
        $this->pages                = $this->allWithoutLastOne / $this->elementsPerPage;
        if ($this->lastPage!=0) {
            $this->pages++;
        }
        return $this->pages;
    }
    /**
     * pickedPage można podać wcześniej przy konstruktorze
     * @param int $pickedPage
     * @return SqlPaginateParams
     */
    public function getSqlPaginateParams($pickedPage = false) {
        $p = new SqlPaginateParams();
        if ($pickedPage) {
            $this->pickedPage = $pickedPage;
        }
        if ($this->pages == 0) {
            $p->setOffset(0);
            $p->setLimit(20);
            return $p;
        }
        if ($this->pickedPage > $this->pages) {
            $this->pickedPage = $this->pages;
        }
        if ($this->pickedPage < 1) {
            $this->pickedPage = 1;
        }
        $p->setOffset($this->pickedPage * $this->elementsPerPage - $this->elementsPerPage);
        $p->setLimit($this->elementsPerPage);

        return $p;
    }
    /**
     * @param int $buttonsOnSidesOfCurrent - ile przycisków w pasku
     * @param int $pickedPage - która strona jest aktywna
     * @param string $CCC
     * @param string $CC
     * @param string $DD
     * @param string $DDD
     * @return ButtonsList
     */
    public function getButtonsList($buttonsOnSidesOfCurrent,$pickedPage = null, $CCC = '<<', $CC = '<', $DD = '>', $DDD = '>>') {
        if ($pickedPage === null) {
            $pickedPage = $this->pages ?: 1;
        }
        if ($pickedPage > $this->pages) {
            $pickedPage = $this->pickedPage = $this->pages;
        }
        if ($buttonsOnSidesOfCurrent < 0) {
            $buttonsOnSidesOfCurrent = 0;
        }
        if ($buttonsOnSidesOfCurrent > 200) {
            $buttonsOnSidesOfCurrent = 200;
        }
        if ($pickedPage > $this->pages)  {
            $pickedPage = $this->pages;
        }
        if ($pickedPage < 1) {
            $pickedPage = 1;
        }

        $temp = Array();

        if ($buttonsOnSidesOfCurrent+$buttonsOnSidesOfCurrent+1 >= $this->pages) {
            for ($i = 1 ; $i <= $this->pages ; $i++) {
                $temp[] = new Button($i,$i);
            }
        } else {
            if (($pickedPage-$buttonsOnSidesOfCurrent <= 1) && ($pickedPage+$buttonsOnSidesOfCurrent >= $this->pages)) {
                //3  <  ^  >  3
                //1 2 3 4 5 6 7
                for ($i = 1 ; $i <= $this->pages ; $i++) {
                    $temp[] = new Button($i,$i);
                }
            }
            else if ($pickedPage-$buttonsOnSidesOfCurrent <= 1) {
                // 2 < ^ > 2
                // 1 2 3 4 5 >> >>>
                $war = $buttonsOnSidesOfCurrent+$buttonsOnSidesOfCurrent+1;
                for ($i = 1 ; $i <= $war ; $i++) {
                    $temp[] = new Button($i,$i);
                }
                $temp[] = new Button($pickedPage+1,$DD);
                $temp[] = new Button($this->pages,$DDD);
            }
            else if ($pickedPage+$buttonsOnSidesOfCurrent >= $this->pages) {
                //        2 < ^ > 2
                // <<< << 3 4 5 6 7
                $temp[] = new Button(1,$CCC);
                $temp[] = new Button($pickedPage-1,$CC);
                for ($i = $this->pages-$buttonsOnSidesOfCurrent-$buttonsOnSidesOfCurrent ; $i <= $this->pages ; $i++) {
                    $temp[] = new Button($i,$i);
                }
            }
            else {
                //        2 < ^ > 2
                // <<< << 2 3 4 5 6 >> >>>
                $temp[] = new Button(1,$CCC);
                $temp[] = new Button($pickedPage-1,$CC);
                for ($i = $pickedPage-$buttonsOnSidesOfCurrent ; $i <= $pickedPage+$buttonsOnSidesOfCurrent ; $i++) {
                    $temp[] = new Button($i,$i);
                }
                $temp[] = new Button($pickedPage+1,$DD);
                $temp[] = new Button($this->pages,$DDD);
//                niechginie($temp);
            }
        }
        $buttons = new ButtonsList($temp,$this->pages,$this->pickedPage);

        return $buttons;
    }
}
