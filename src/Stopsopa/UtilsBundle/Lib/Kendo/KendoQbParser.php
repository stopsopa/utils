<?php

namespace Stopsopa\UtilsBundle\Lib\Kendo;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use \PDO;
use Doctrine\DBAL\Statement;

/**
 * @author Szymon Działowski
 * Klasa powstała aby przyspieszyć budowanie tabel z pełnym wsparciem sortowania i filtrowania
 * Dane wyciągane są na sposób Obiektowy przez QueryBuilder lub przez zwykły sql, z pominięciem hydracji do obiektów
 *
 *
 * ====== po stronie javascript, obsługa komponentu kendoui grid:
 *
  .....
  read: function(o) { // co to za read?: -->> http://stackoverflow.com/questions/13953341/kendoui-grid-only-use-create-no-delete-or-update
  var f = o.data
  log('f')
  dir(f);
  $.ajax(Routing.generate("lgholduserpanel_towns_data"), {
  type: 'post',
  contentType: 'application/json; charset=utf-8',
  data: JSON.stringify({filters:f})
  })
  .success(function (json) {
  o.success(json);
  })
  .error(function () {
  o.success({}); //test
  site.error("Wystąpił błąd przy pobieraniu danych dla tabeli z serwera...")
  });
  }
  .....
 * ====== Uzycie przy zapytaniach obiektowych: ====
 *
 *    // odbieram niezmienione parametry z kendogrida
  $post = Json::decode($request->getContent());
  $kendoFilters = $post['filters'];

  $qb = $this->getRepository(MainTree::EN)->createQueryBuilder('n');
  $qb->select('count(n)')
  $kqb = new KendoQbParser(); // nie potrzeba tu pchać EntityManager dla obróbki obiektowej
  $kqb->setMaxLimit(50);
 *
 *
  $kqb->setTransMap(array(
  'name'       => 'f.nazwa', // nazwa w js -> nazwa w db // PROSTA SKŁADNIA...
  'province'   => 'm.nazwa', // nazwa w js -> nazwa w db // PROSTA SKŁADNIA...
  'visits'     => 'COUNT(m)' // tutaj `m` jest obiektem - warto zwrócić uwagę że tu jest składnia qb a nie sql
  'date' => array(           // ROZSZERZONA SKŁADNIA TRANSLACJI POLA
  'dbfield'   => 'l.czas', // nazwa w js -> nazwa w db
  //                 true  - czy ma wartość interpretować jako string (w dalszej obróbce robi warunek where x like %y%),
  // vvv (def: true) false - interpretuje jako wartość (w dalszej obróbce robi warunek where x = y)
  'cmpstring' => false
  )
  ));
 *
 * // TUTAJ ODBYWA SIĘ KONKRETNA OBSŁUGA PARAMETRÓW Z KENDO I ODZWIERCIEDLANIE ICH W QUERYBUILDER
 * // po drodze validuje i rzuca exception'y, w zasadzie nie ma potrzeby ich obsługiwać, dane
 * // dla klasy pochodzą z kendogrida i będą miały prawidłowy format, jeśli ktoś będzie nimi manipulować to wewnętrzna
 * // walidacja rzuci exception co oznacza koniec hakowania
  $kqb->setupQb($qb, $kendoFilters);

  $count = $qb->getQuery()->getSingleScalarResult();
  $qb->select('n')
  $list = $qb->getQuery()->getResult();

  // Po nowemu jest tak
   public function countAndListMailUser($filters, $mailGroupId = false, $datePut = false, $dumperName = null) {
        if(!is_string($dumperName))
            $dumperName = MailerDumper::getClassName();

        $qb = $this->repository->createQueryBuilder('mu')
           ->leftJoin('mu.mail', 'm')
           ->leftJoin('m.mailGroup', 'mg');

        if($mailGroupId && $datePut)
            $qb->where($qb->expr()->eq('mg.id', $qb->expr()->literal($mailGroupId)))
               ->andWhere($qb->expr()->eq('mu.datePut', $qb->expr()->literal($datePut)));

        $kqb = new KendoQbParser();
        $kqb->setMaxLimit(1000); // zabezpieczenie
        $kqb->setTransMap(array(
            'id'            => 'mu.id',
            'email'         => 'mu.email',
            'dateSent'      => 'mu.dateSent',
            'mailGroupName' => 'mg.name',
            'mailGroupId'   => 'mg.id',
            'datePut'       => 'mu.datePut'
        ));

        $kqb->setupQb($qb, $filters);

        $count = $kqb->getCount('count(mu)');
        $list = $kqb->getList('mu', 'm', 'mg');
        $list = App::getDumperService()->dump($list, $dumperName);

        return array($count, $list);
    }


  ====== Uzycie przy zapytaniach dbal: ====
  // tu jest trochę więcej roboty, takie życie z dbal'em...
 *
 * // WAŻNA UWAGA:
 * DOBRZE JEST ZAMKNĄĆ ZAPYTANIE W PODZAPYTANIU A WARUNKI WYWALIĆ DO ZEWNĘTRZNEGO ZAPYTANIA ABY NIE TRZEBA BYŁO ROBIĆ TRANSLACJI PÓL Z JS DO SQL
 * // WAŻNA UWAGA:

  // odbieram niezmienione parametry z kendogrida
  $post = Json::decode($request->getContent());
  $kendoFilters = $post['filters'];

  // tu jest potrzebny entity manager
  // można zrobić z tego service z metodą factory ale czy jest sens dla jednego parametru który zawsze mamy pod ręką w kontrolerze ? ... kto jak woli
  $kqb = new KendoQbParser($this->getEntityManager());
  $kqb->setMaxLimit(500);  // zabezpiecznie przed wybieraniem zbyt wielu wierszy z kolumny

  // translacje nazw kolumn w js na nazwy w bazie danych i odwrotnie
  // normalnie jest to para klucz(js) => wartość(db)
  $kqb->setTransMap(array(
  'count'      => 'count(*)', // nazwa w js -> nazwa w db // PROSTA SKŁADNIA...
  'name'       => 'f.nazwa',  // nazwa w js -> nazwa w db // PROSTA SKŁADNIA...
  'province'   => 'm.nazwa',  // nazwa w js -> nazwa w db // PROSTA SKŁADNIA...
  'date' => array(                                   // ARRAY -> ROZSZERZONA SKŁADNIA...
  'dbfield'   => 'l.czas', // nazwa w db
  'cmpstring' => false     // (def: true) czy ma wartość interpretować jako string (robi where x like %y%), false - interpretuje jako wartość (robi where x = y)// (def: true) czy ma kolumnę w bazie interpretować jako string, false - interpretuje jako wartość
  )
  ));

  // TUTAJ ODBYWA SIĘ PRZETWARZANIE I PRODUKOWANIE WARUNKÓW PRZED 'WSADZANIEM' ICH DO ZAPYTANIA SQL...
  $kqb->setupFilters($kendoFilters);
  //    debugg($kqb->getOrderByPart($pre = ' ORDER BY '),11);
  $query = "          -- tutaj binduję od razu do stringu bo dane są dobrze zwalidowane
  SELECT * FROM (
  SELECT SQL_NO_CACHE -- @r0 := @r0 + 1 id,
  f.id_firmy id,
  m.nazwa as town,
  f.nazwa name,
  COUNT(id_log) visits -- ,
  --                    id_firmyodw id
  FROM                v3_logs l
  INNER JOIN v3_firma f
  ON l.id_firmyodw = f.id_firmy
  INNER JOIN lgh_miasto m
  ON f.id_miasta = m.id_miasta -- ,
  --  (SELECT @r0 := 0) r0
  WHERE               l.id_firmy = $fid
  AND (l.czas BETWEEN '$from' AND '$to')
  AND f.provider = 0
  GROUP BY            f.id_firmy
  ) t
  {$kqb->getWherePart($pre = ' WHERE ')}  -- jeśli w kendo zostaną wybrane jakieś warunki filtrowania to tu je wstawiamy
  {$kqb->getOrderByPart(' ORDER BY ')}
  {$kqb->getLimitOffset()}
  ";
  $stmt = $this->getDbal()->prepare($query);

  // tutaj samodzielne bindowanie danych do warunków
  // klasa je wszystkie pamięta i BEZPIECZNIE zbinduje
  $kqb->bindAllParams($stmt);

  $stmt->execute();
 *
 */
class KendoQbParser {

    protected $c = 0;
    protected $bind;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var KendoQueryBuilder
     */
    protected $kqb;
    protected $maxlimit;
    /*
     * @var array
     */
    protected $map;

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * Podawać tylko dla dbal
     * @param false|EntityManager $em
     */
    public function  __construct($em = null) {
        $em and $this->setEM($em);
    }

    public function setEM(EntityManager $em) {
        $this->em = $em;
        return $this;
    }

    /**
     * Ustawia qb dla zapytania przez EntityManager
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param type $filters
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function setupQb(QueryBuilder $qb, $filters) {
        $this->qb = $qb;
        $this->c = 0;
        $this->bind = array();

        $filters = $this->_unify($filters);

        $this->_setupOrderyBy($qb, $filters['orderby']);

        $this->_setFilters($qb, $filters['filters']);

        $filters['offset'] and $qb->setFirstResult($filters['offset']);

        $filters['limit'] and $qb->setMaxResults($filters['limit']);

        return $qb;
    }

    /**
     * Ustawia qb dla zapytania przez Dbal
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param type $filters
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllParts() {

        if (!$this->kqb)
            throw new KendoQbParserException("First use setupFilters()");

        return $this->kqb->getAllParts() . $this->kqb->getLimitOffset();
    }

    public function getWherePart($pre = ' WHERE ') {
        return $this->kqb->getWherePart($pre);
    }

    public function getGroupByPart($pre = ' GROUP BY ', $separator = ', ') {
        return $this->kqb->getGroupByPart($pre, $separator);
    }

    public function getHavingPart($pre = ' HAVING ') {
        return $this->kqb->getHavingPart($pre);
    }

    public function getOrderByPart($pre = ' ORDER BY ', $separator = ', ') {
        return $this->kqb->getOrderByPart($pre, $separator);
    }

    public function getLimitOffset() {
        return $this->kqb->getLimitOffset();
    }

    public function setupFilters(array $filters) {
//debugg($filters);
        if (!$this->em)
            throw new KendoQbParserException("first use setEM(`em`) or pass `em` in constructor ...");

        $this->kqb = new KendoQueryBuilder($this->em);

        $this->c = 0;
        $this->bind = array();
        $filters = $this->_unify($filters);
//        debugg($filters,1);
        $this->_setupOrderyBy($this->kqb, $filters['orderby']);

//debugg($filters,1);
        $this->_setFilters($this->kqb, $filters['filters']);

        $limit = '';
        if ($filters['limit']) {
            $limit .= " LIMIT {$filters['limit']}";
            if ($filters['offset'])
                $limit .= " OFFSET {$filters['offset']}";
        }
        $this->kqb->setLimitOffset($limit);

        return $this;
    }
    protected function _setFilters(QueryBuilder $qb, $filters) {
//      debugg($filters,1);
        foreach ($filters as $f) {
          if (isset($f['logic']) && preg_match('#^(and|or)$#', $f['logic'])) { // podano dwa warunki filtrowania dla kolumny
            $expr1 = $this->_oneExpr($qb, $f['filters'][0]);
            $expr2 = $this->_oneExpr($qb, $f['filters'][1]);
            $qb
              ->andWhere(
                $qb->expr()->{"{$f['logic']}X"}(
                  $expr1['expr'], $expr2['expr']
                )
              )
            ;
          } else {                                                              // pojedynczy warunek
            if (isset($f['operator'])) {
              $expr = $this->_oneExpr($qb, $f);
              $qb
                ->andWhere($expr['expr'])
                ->setParameter($expr['param'], $expr['val'], PDO::PARAM_STR)
              ;
            }
          }
        }
    }

    public function bindAllParams(Statement $stmt) {

        foreach ($this->bind as $k => $d)
            $stmt->bindValue(":$k", $d, PDO::PARAM_STR);

        return $this;
    }

    /**
     * Metoda unifikuje dane pochodzące z javascript z komponentu kendoui grid do postaci: PD9waHANCkFycmF5DQooDQogIFtmaWx0ZXJzXSA9PiBBcnJheQ0KICAgICgNCiAgICAgIFswXSA9PiBBcnJheSAvLyBmaWx0ciBwaWVyd3N6eSAtIHcgamVnbyBza8WCYWQgd2Nob2R6xIUgZHdhIHdhcnVua2kNCiAgICAgICAgKA0KICAgICAgICAgIFtsb2dpY10gPT4gb3INCiAgICAgICAgICBbZmlsdGVyc10gPT4gQXJyYXkNCiAgICAgICAgICAgICgNCiAgICAgICAgICAgICAgWzBdID0+IEFycmF5DQogICAgICAgICAgICAgICAgKA0KICAgICAgICAgICAgICAgICAgW2ZpZWxkXSA9PiBuYW1lDQogICAgICAgICAgICAgICAgICBbb3BlcmF0b3JdID0+IGNvbnRhaW5zDQogICAgICAgICAgICAgICAgICBbdmFsdWVdID0+IG5hendhIGNvbnRhaW4NCiAgICAgICAgICAgICAgICApDQogICAgICAgICAgICAgIFsxXSA9PiBBcnJheQ0KICAgICAgICAgICAgICAgICgNCiAgICAgICAgICAgICAgICAgIFtmaWVsZF0gPT4gbmFtZQ0KICAgICAgICAgICAgICAgICAgW29wZXJhdG9yXSA9PiBlbmRzd2l0aA0KICAgICAgICAgICAgICAgICAgW3ZhbHVlXSA9PiBuYXp3YSBrb8WEY3p5IHNpxJkNCiAgICAgICAgICAgICAgICApDQogICAgICAgICAgICApDQogICAgICAgICkNCiAgICAgIFsxXSA9PiBBcnJheSAgIC8vIGZpbHRyIGRydWdpIC0gdyBqZWdvIHNrxYJhZCB3Y2hvZHphIGR3YSB3YXJ1bmtpDQogICAgICAgICgNCiAgICAgICAgICBbbG9naWNdID0+IGFuZA0KICAgICAgICAgIFtmaWx0ZXJzXSA9PiBBcnJheQ0KICAgICAgICAgICAgKA0KICAgICAgICAgICAgICBbMF0gPT4gQXJyYXkNCiAgICAgICAgICAgICAgICAoDQogICAgICAgICAgICAgICAgICBbZmllbGRdID0+IHRvd24NCiAgICAgICAgICAgICAgICAgIFtvcGVyYXRvcl0gPT4gc3RhcnRzd2l0aA0KICAgICAgICAgICAgICAgICAgW3ZhbHVlXSA9PiBtaWFzdG8gemFjenluYSBzacSZDQogICAgICAgICAgICAgICAgKQ0KICAgICAgICAgICAgICBbMV0gPT4gQXJyYXkNCiAgICAgICAgICAgICAgICAoDQogICAgICAgICAgICAgICAgICBbZmllbGRdID0+IHRvd24NCiAgICAgICAgICAgICAgICAgIFtvcGVyYXRvcl0gPT4gZG9lc25vdGNvbnRhaW4NCiAgICAgICAgICAgICAgICAgIFt2YWx1ZV0gPT4gbWlhc3RvIG5pZSB6YXdpZXJhDQogICAgICAgICAgICAgICAgKQ0KICAgICAgICAgICAgKQ0KICAgICAgICApDQogICAgICBbMl0gPT4gQXJyYXkgIC8vIGZpbHRyIHRyemVjaSAgLy8gamXFm2xpIGZpbHRyIGplc3Qgb3Bpc2FueSBqZWRueW0gd2FydW5raWVtIHRvIGplc3QgamVkbm9wb3ppb21vd2EgdGFibGljYQ0KICAgICAgICAoDQogICAgICAgICAgW2ZpZWxkXSA9PiB2aXNpdHMNCiAgICAgICAgICBbb3BlcmF0b3JdID0+IHN0YXJ0c3dpdGgNCiAgICAgICAgICBbdmFsdWVdID0+IHphY3p5bmEgc2nEmSBvZCAtIGplZGVuIHdhcnVuZWsNCiAgICAgICAgKQ0KICAgICkNCiAgW29yZGVyYnldID0+IEFycmF5DQogICAgKA0KICAgICAgWzBdID0+IEFycmF5DQogICAgICAgICgNCiAgICAgICAgICBbZmllbGRdID0+IG5hbWUNCiAgICAgICAgICBbZGlyXSA9PiBhc2MNCiAgICAgICAgKQ0KICAgICAgWzFdID0+IEFycmF5DQogICAgICAgICgNCiAgICAgICAgICBbZmllbGRdID0+IHZpc2l0cw0KICAgICAgICAgIFtkaXJdID0+IGRlc2MNCiAgICAgICAgKQ0KICAgICAgWzJdID0+IEFycmF5DQogICAgICAgICgNCiAgICAgICAgICBbZmllbGRdID0+IHRvd24NCiAgICAgICAgICBbZGlyXSA9PiBkZXNjDQogICAgICAgICkNCiAgICApDQogIFtvZmZzZXRdID0+IDANCiAgW2xpbWl0XSA9PiAxMA0KKQ==
     * @param array $f
     * @return array
     */
    protected function _unify($f) {
        $data = array();
        $data['orderby'] = isset($f['sort']) ? $f['sort'] : array();

        $d = array();
        if (isset($f['filter']) && isset($f['filter']['filters'])) {
          if (count($f['filter']['filters']) == 1) {
            $d[] = $f['filter']['filters'][0];
          }
          elseif (count($f['filter']['filters']) == 2 && !isset($f['filter']['filters'][0]['filters']) && !isset($f['filter']['filters'][1]['filters'])) {
            $d[] = $f['filter'];
          }
          else {
            $d = array_merge($d,$f['filter']['filters']);
          }
        }
        $data['filters'] = $d;

        $data['orderby'] = isset($f['sort']) ? $f['sort'] : array();

        $data['offset'] = isset($f['skip']) && (int) $f['skip'] ? (int) $f['skip'] : 0;

        $data['limit'] = isset($f['take']) && (int) $f['take'] ? (int) $f['take'] : 0;

        // zabezpieczenie przed wybieraniem zbyt dużej ilości rekordów
        if ($this->maxlimit && $data['limit'] > $this->maxlimit) {
            $data['limit'] = $this->maxlimit;
        }

        return $data;
    }


    protected function _oneExpr(QueryBuilder $qb, $f) {

        // walidacja
        if (!preg_match('#^(n?eq|startswith|endswith|contains|doesnotcontain|isNull)$#', $f['operator']))
            throw new KendoQbParserException("Nieprawidłowy warunek '{$f['operator']}'");

        $param = $this->_getUniqueParam();
        $info = $this->_getInformations($f);

        if ($info['cmpstring']) { // porównywanie jako string (where x like %y%)
            $cond = $this->_toLike($f); // validacja i przeygotowanie dla like
            switch (true) {
                case is_int(strpos($cond, '%')): // startswith, endswith, contains
                    $expr = $qb->expr()->like($info['dbfield'], ":$param");
                    $this->bind[$param] = $cond;
                    break;
                case $cond == 'nc': // doesnotcontain
                    $expr = $qb->expr()->not(
                            $qb->expr()->like($info['dbfield'], ":$param")
                    );
                    $this->bind[$param] = "%{$f['value']}%";
                    break;
                case $cond == 'isNull' :
                    $expr = $qb->expr()->{$cond}($info['dbfield']);
                    return array(
                        'param' => $param,
                        'val' => 'null',
                        'expr' => $expr
                    );
                    break;
                default: // eq, neq - walidacja w _toLike(), także tu trafi prawidłowa wartość
                    $expr = $qb->expr()->{$cond}($info['dbfield'], ":$param");
                    $this->bind[$param] = $f['value'];
                    break;
            }
        } else { // porównywanie jako wartość (where x = y)
            switch ($info['operator']) { // n?eq|startswith|endswith|contains|doesnotcontain
                case 'eq':
                case 'contains':
                    $expr = $qb->expr()->eq($info['dbfield'], ":$param");
                    break;
                case 'neq':
                case 'doesnotcontain':
                    $expr = $qb->expr()->neq($info['dbfield'], ":$param");
                    break;
                case 'startswith':
                    $expr = $qb->expr()->gte($info['dbfield'], ":$param");
                    break;
                case 'endswith':
                    $expr = $qb->expr()->lte($info['dbfield'], ":$param");
                    break;
            }
            $this->bind[$param] = "{$f['value']}";
        }

        return array(
            'param' => $param,
            'val'   => $this->bind[$param],
            'expr' => $expr
        );
    }

    protected function _getUniqueParam() {
        $c = ++$this->c;
        return "_f_$c";
    }

    /**
     * @param type $o
     * @return string
     * @throws KendoQbParserException
     */
    protected function _toLike($f) {
        switch ($f['operator']) {
            case 'startswith':
                return "{$f['value']}%";  // like
            case 'endswith':
                return "%{$f['value']}";  // like
            case 'contains':
                return "%{$f['value']}%"; // like
            case 'doesnotcontain':
                return 'nc';              // not like
            default:
                return $f['operator'];    // eq, neq
        }
    }

    protected function _setupOrderyBy(QueryBuilder $qb, $sort) {
        foreach ($sort as $d) {
            // tutaj ma rzucać exception - poprawić
            if (preg_match('#^(asc|desc)$#i', $d['dir'])) {
                $info = $this->_getInformations($d);
                $qb->addOrderBy($info['dbfield'], strtolower($info['dir']));
            }
        }
    }

    /**
     * Zabezpiecza żeby nie było możliwe wyciąganie zbyt długich list z bazy danych
     * @param type $c
     * @return KendoQbParser
     */
    public function setMaxLimit($c) {
        $c = (int) $c;
        $c and $this->maxlimit = $c;
        return $this;
    }

    protected function _transToJs($key) {
        foreach ($this->map as $k => $d) {
            if ($key == $d) {
                return $k;
            }
        }

        return $key;
    }

    protected function _getInformations($d) {
        if (is_array($this->map) && array_key_exists($d['field'], $this->map)) {
            if (is_array($this->map[$d['field']])) { // jeśli translacja nazwy kolumny z js do db jest w formacie rozszerzonym, czyli w array
                $d['dbfield'] = $this->map[$d['field']]['dbfield'];
                $d['cmpstring'] = (isset($this->map[$d['field']]['cmpstring']) && $this->map[$d['field']]['cmpstring']);
            } else { // jeśli translacja kolumny z js do db jest podana w fromie prostej, czyli para klucz wartość
                $d['dbfield'] = $this->map[$d['field']];
                $d['cmpstring'] = true; // domyślnie wartość z kendo interpretowana jest jako string czyli warunek jest budowany where x like %y% a nie where x = y
            }
        } else {
            $d['dbfield'] = $d['field']; // nie tłumaczymy: nazwa kolumny w js jest taka sama jak w sql
            $d['cmpstring'] = true;
        }

        // walidacja przed sql injection
        if (!preg_match('#^[a-z0-9_\.]+$#i', $d['field'])) {
            throw new KendoQbParserException("Not valid #^[a-z0-9_\.]+$#i chars in column name '{$d['field']}' in database");
        }

        return $d;
    }

    /**
     * pary nazw pól: js => db
     * @param array $map
     */
    public function setTransMap($map) {
        $this->map = $map;
    }
    /**
     * @param type $select string podawany do $qb->select np $qb->select('count(*)'[, ...])
     *  Nalezy podać tylko jeden parametr!!!!!!!!!!!!!!!!
     */
    public function getCount(){
        $offset = $this->qb->getFirstResult();
        $this->qb->setFirstResult(null);
        $tmp = call_user_func_array(array($this->qb, 'select'), func_get_args());
        $return = $tmp->getQuery()->getSingleScalarResult();
        $this->qb->setFirstResult($offset);
        return $return;
    }
    /**
     * @param type $select string podawany do $qb->select np $qb->select('m'[,'mu'[, ...]]) m to alias w query builderze
     */
    public function getList(){
        $tmp = call_user_func_array(array($this->qb, 'select'), func_get_args());
        return $tmp->getQuery()->getResult();
    }

}