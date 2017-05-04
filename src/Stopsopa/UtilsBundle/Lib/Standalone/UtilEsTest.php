<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Exception;

class UtilEsTest extends WebTestCase {
    /**
     * @dataProvider dataProvider
     */
    public function dataProvider() {
        return [
            ['--START--', '--END--', '<SPAN CLASS="TEST">', '</SPAN>'],
            ['<SPAN CLASS="TEST">', '</SPAN>', '--START--', '--END--'],
            ['--START--', '--END--', 'a', 'b'],
            ['a', 'b', '--START--', '--END--'],
            ['a', '--END--', '<SPAN CLASS="TEST">', '</SPAN>'],
            ['<SPAN CLASS="TEST">', '</SPAN>', '--START--', '--END--'],
            ['--START--', '--END--', '<SPAN CLASS="TEST">', '</SPAN>'],
            ['a', '</SPAN>', '--START--', '--END--'],
            ['--START--', '--END--', 'a', '</SPAN>'],
            ['<SPAN CLASS="TEST">', '</SPAN>', '--START--', '--END--'],
            ['--START--', '--END--', '<SPAN CLASS="TEST">', '</SPAN>'],
            ['<SPAN CLASS="TEST">', 'a', '--START--', '--END--'],
            ['--START--', '--END--', '<SPAN CLASS="TEST">', 'a'],
            ['<SPAN CLASS="TEST">', '</SPAN>', '--START--', '--END--'],
            ['--START--', '--END--', '<SPAN CLASS="TEST">', '</SPAN>'],
            ['<SPAN CLASS="TEST">', '</SPAN>', '--START--', 'a'],
            ['--START--', '--END--', '<SPAN CLASS="TEST">', '</SPAN>'],
            ['<SPAN CLASS="TEST">', '</SPAN >', '--START--', '--END-'],
            ['--START--', '--END--', '<SPAN CLASS="TES">', '</ SPAN>'],
            ['<SPAN CLASS="TEST">', '</SPAN>', '--START--', '--END--'],
        ];
    }
    /**
     * @dataProvider dataProvider
     */
    public function testTwoInTheMiddle($sstart, $send, $tstart, $tend) {

        $source = <<<TXT
one {$sstart}two{$send} three {$sstart}four{$send} six
TXT;

        $target = <<<TXT
ONE two THREE four SIX
TXT;

        $expected = <<<TXT
ONE {$tstart}two{$tend} THREE {$tstart}four{$tend} SIX
TXT;

        $result = UtilEs::highlightLC($source, $target, $sstart, $send, $tstart, $tend);

        $this->assertSame($expected, $result);
    }
    /**
     * @dataProvider dataProvider
     */
    public function testEnd($sstart, $send, $tstart, $tend) {

        $source = <<<TXT
one {$sstart}two{$send} three {$sstart}four{$send}
TXT;

        $target = <<<TXT
ONE two THREE four
TXT;

        $expected = <<<TXT
ONE {$tstart}two{$tend} THREE {$tstart}four{$tend}
TXT;

        $result = UtilEs::highlightLC($source, $target, $sstart, $send, $tstart, $tend);

        $this->assertSame($expected, $result);
    }
    /**
     * @dataProvider dataProvider
     */
    public function testStart($sstart, $send, $tstart, $tend) {

        $source = <<<TXT
{$sstart}two{$send} three {$sstart}four{$send} six
TXT;

        $target = <<<TXT
two THREE four SIX
TXT;

        $expected = <<<TXT
{$tstart}two{$tend} THREE {$tstart}four{$tend} SIX
TXT;

        $result = UtilEs::highlightLC($source, $target, $sstart, $send, $tstart, $tend);

        $this->assertSame($expected, $result);
    }
    /**
     * @dataProvider dataProvider
     */
    public function testNewLine($sstart, $send, $tstart, $tend) {

        $source = <<<TXT
{$sstart}two{$send} three {$sstart}
four{$send} six
TXT;

        $target = <<<TXT
two THREE 
four SIX
TXT;

        $expected = <<<TXT
{$tstart}two{$tend} THREE {$tstart}
four{$tend} SIX
TXT;

        $result = UtilEs::highlightLC($source, $target, $sstart, $send, $tstart, $tend);

        $this->assertSame($expected, $result);
    }
    public function testTheSame() {

        $message = <<<MSG
From and To can't be the same: [
    "--START--",
    "--END--",
    "--START--",
    "<\/SPAN>"
]
MSG;

        $this->setExpectedException(
            Exception::class,
            $message,
            0
        );

        $this->testTwoInTheMiddle('--START--', '--END--', '--START--', '</SPAN>');
    }
    public function testLonger() {

        try {
            UtilEs::highlightLC('short', 'longer', '--START--', '--END--', '<SPAN>', '</SPAN>');
        }
        catch (Exception $e) {
            $this->assertContains('Target length string (6) shouldn\'t be equal/longer', $e->getMessage());
            return;
        }

        $this->assertFalse(true);
    }
    public function testNotMatch() {

        try {
            UtilEs::highlightLC('short <span>test</span> almost <span> end', 'longer', '<span>', '</span>', '<SPAN>', '</SPAN>');
        }
        catch (Exception $e) {
            $this->assertContains('Not found the same number of starts (2) and ends (1)', $e->getMessage());
            return;
        }

        $this->assertFalse(true);
    }
}

