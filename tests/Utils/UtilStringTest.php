<?php

namespace Stopsopa\Utils\Utils;

use Stopsopa\Utils\TestAbstract;

use Stopsopa\Utils\Utils\UtilString;

class UtilStringTest extends TestAbstract {
    public function stripTagsProvider() {
        return array(
            array('<span>test</span>','test'),
            array(' <span>test</span>','test'),
            array(' <span> test</span>','test'),
            array(' <span> test </span>','test'),
            array(' <span> test </span> ','test'),
            array(' <span class="test"> middle <end>','middle'),
            array(' <span class="test"> middle and<end>','middle and'),
            array(' <span class="test"> middle  and<end>','middle and'),
            array(' <span class="test"> middle<div>and<end>','middle and'),
            array(' <span class="test"> middle <div>and<end>','middle and'),
            array(' <span class="test"> middle<div> and<end>','middle and'),
            array("<span> test2</span>\ntest\n<div>test\nend</div>",'test2 test test end'),
        );
    }

    /**
     * @dataProvider stripTagsProvider
     */
    public function testStripTags($a, $b) {
        $this->assertSame(UtilString::stripTags($a), $b);
    }
}

