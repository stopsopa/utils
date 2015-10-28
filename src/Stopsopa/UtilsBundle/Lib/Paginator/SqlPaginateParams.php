<?php

namespace Stopsopa\UtilsBundle\Lib\Paginator;

class SqlPaginateParams {
    private $limit;
    private $offset;

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function getOffset() {
        return $this->offset;
    }
}
