<?php

namespace Stopsopa\UtilsBundle\Lib\Standalone;

class UtilString
{
    protected $keys = false;
    protected $data = false;

    /**
     * !!! UWAGA !!! - Z TEGO CO WIDZĘ SPRAWDZANIE ZA POMOCĄ mb_detect_encoding Z UŻYCIEM TEJ TABLICY ZWRACA PIERWSZY Z PASUJĄCYCH KODOWAŃ
     * jak sie okaże że ta klasa z jakimś kodowaniem często się spotyka to można dodać na końcu talicy na stałe.
     */
    protected $list = array('ASCII', 'UTF-8', 'ISO-8859-2', 'ISO-8859-1');

    /**
     * Można przez konstruktor podać alternatywną listę wykrywanych kodowań
     * domyślna lista to: 'ASCII','UTF-8','ISO-8859-2','ISO-8859-1'.
     *
     * @param string|array $list - jeśli string to oddzielone "," lub "|"
     */
    public function __construct($list = false)
    {
        if (!function_exists('mb_convert_encoding')) {
            throw new Exception('Klasa: "'.get_class($this).'" - wymagana jest biblioteka "MB" - do prawidłowej pracy klasy');
        }
        $this->setupEncodingList($list);
    }

    /**
     * Obcina html tak aby między znacznikami była minimum zadeklarowana ilość znaków, oraz zamyka po obcięciu końcowe znaczniki.
     *
     * @param string $html
     * @param int    $length
     *
     * @return string
     */
    public static function subEndHtml($html, $length)
    {
        $offset = 10;
        $max = strlen($html);

        $str = mb_substr($html, 0, $length);
        $len = strlen($str);

        while (true) {
            if ($len == $max || strlen(self::reduceSpaces(strip_tags($str))) > $length) {
                return self::fixLastTags($str);
            }

            $str = mb_substr($html, 0, $length + ($offset++));
            $len = strlen($str);
        }
    }

    /**
     * Redukuje wystapnienia grup spacji do jednej, usuwa też znaki nowej linii
     * http://erisds.co.uk/code/getting-rid-of-non-breaking-spaces-nbsp.
     *
     * @return type
     */
    public static function reduceSpaces($data)
    {
        // http://erisds.co.uk/code/getting-rid-of-non-breaking-spaces-nbsp  g(Getting Rid of Non Breaking Spaces) g(white space characters tinymce) g(Have you ever tried to parse, process or preg_replace some HTML? Ever tried to do it when the HTML is UTF-8 encoded? Getting rid of white space can be tricky, here’s a few tricks I’ve learned)
    $data = str_replace('&nbsp;', ' ', $data);

        return trim(preg_replace("/[\s\r\n\xC2\xA0]+/i", ' ', $data));
    }

    protected function setupEncodingList($list)
    {
        if (is_string($list)) {
            if (strchr($list, ',')) {
                $this->list = explode(',', $string);
            } elseif (strchr($list, '|')) {
                $this->list = explode('|', $string);
            }
        } elseif (is_array($list)) {
            $this->list = $list;
        }
    }

    /**
     * Przekształca także ze znakami rosyjskimi.
     *
     * @param string $text
     * @param string $delimiter
     *
     * @return string
     */
    public static function toSlugg($str, $delimiter = '-')
    {
        $rep = array(
            'ё' => 'yo','Ё' => 'Yo',
            'ъ' => '','Ъ' => '',
            'я' => 'ya','Я' => 'Ya',
            'ш' => 'sh','Ш' => 'Sh',
            'е' => 'e','Е' => 'E',
            'р' => 'r','Р' => 'R',
            'т' => 't','Т' => 'T',
            'ы' => 'y','Ы' => 'Y',
            'у' => 'u','У' => 'U',
            'и' => 'i','И' => 'I',
            'о' => 'o','О' => 'O',
            'п' => 'p','П' => 'P',
            'ю' => 'yu','Ю' => 'Yu',
            'щ' => 'sch','Щ' => 'Sch',
            'э' => 'e','Э' => 'E',
            'а' => 'a','А' => 'A',
            'с' => 's','С' => 'S',
            'д' => 'd','Д' => 'D',
            'ф' => 'f','Ф' => 'F',
            'г' => 'g','Г' => 'G',
            'ч' => 'ch','Ч' => 'Ch',
            'й' => 'j','Й' => 'J',
            'к' => 'k','К' => 'K',
            'л' => 'l','Л' => 'L',
            'ь' => '','Ь' => '',
            'ж' => 'zh','Ж' => 'Zh',
            'з' => 'z','З' => 'Z',
            'х' => 'h','Х' => 'H',
            'ц' => 'ts','Ц' => 'Ts',
            'в' => 'v','В' => 'V',
            'б' => 'b','Б' => 'B',
            'н' => 'n','Н' => 'N',
            'м' => 'm','М' => 'M',
        );

        $str = strtr($str, $rep);

        return Urlizer::urlize($str, $delimiter);

        // torchę nowsza logika vvv
//        return str_replace($rep, $rep2, $str);
//
//
//        setlocale(LC_ALL, 'en_US.UTF8');
//	if( !empty($replace) ) {
//		$str = str_replace((array)$replace, ' ', $str);
//	}
//
//	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
//	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
//	$clean = strtolower(trim($clean, '-'));
//	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
//
//	return $clean;
        // torchę nowsza logika ^^^
// ============= stara logika ========== vvv
//        $detect = $this->detectEncoding($text);
//        if ($detect !== 'ASCII') {
//            $text = mb_convert_encoding($text, 'UTF-8', $detect);
//        }
//        //      $text = iconv(mb_detect_encoding($text), 'us-ascii//TRANSLIT//IGNORE', $text);
//        $text = $this->removeAccents($text);
//        $text = preg_replace("/[^a-z0-9]/i", $delimiter, $text);
//        $text = mb_strtolower(trim($text, $delimiter));
//        $text = preg_replace("#$delimiter$delimiter+#", $delimiter, $text);
//        return $text;
// ============= stara logika ========== ^^^
    }

    /**
     * Zwraca oznaczenie kodowania użytego w stringu - patrz listę podawaną w konstruktorze tej klasy.
     *
     * @param string       $str
     * @param string|array $list
     *
     * @return string
     */
    public function detectEncoding($str, $list = false)
    {
        $this->setupEncodingList($list);

        return mb_detect_encoding($str, $this->list);
    }

    /**
     * źródło [http://mateusztymek.pl/blog/usuwanie-znakow-diakrytycznych]
     * metoda zdaje się zwracać poprawny string tylko dla stringu wejściowego w ASCII lub UTF-8
     * Uwaga - w Symfony2 w bundle gedmo jest znakomita biblioteka Gedmo\Sluggable\Util\Urlizer - świetny zastępnik.
     *
     * @param string $str
     *
     * @return string
     */
    protected function removeAccents($str)
    {
        /*
         * lazy loading
         */
        if (!$this->keys) {
            $transliteration = array(
                'Ĳ' => 'I', 'Ö' => 'O', 'Œ' => 'O', 'Ü' => 'U', 'ä' => 'a', 'æ' => 'a',
                'ĳ' => 'i', 'ö' => 'o', 'œ' => 'o', 'ü' => 'u', 'ß' => 's', 'ſ' => 's',
                'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
                'Æ' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C',
                'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E',
                'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E',
                'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
                'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
                'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĵ' => 'J',
                'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L',
                'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O',
                'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O',
                'Ŏ' => 'O', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S',
                'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
                'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ů' => 'U',
                'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y',
                'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a',
                'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
                'å' => 'a', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
                'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
                'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f',
                'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h',
                'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i',
                'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k',
                'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n',
                'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o',
                'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o',
                'ŏ' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's',
                'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ū' => 'u', 'ů' => 'u',
                'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y',
                'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'Α' => 'A',
                'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A',
                'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A',
                'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A',
                'Ᾱ' => 'A', 'Ὰ' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D',
                'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E',
                'Ἔ' => 'E', 'Ἕ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I',
                'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I',
                'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I',
                'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'ῌ' => 'I',
                'Θ' => 'T', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I',
                'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I',
                'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M',
                'Ν' => 'N', 'Ξ' => 'K', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O',
                'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Π' => 'P',
                'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y',
                'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y',
                'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'P', 'Ω' => 'O',
                'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O',
                'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O',
                'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O',
                'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a',
                'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a',
                'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a',
                'ᾇ' => 'a', 'ὰ' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a',
                'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
                'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e',
                'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
                'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i',
                'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i',
                'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ῂ' => 'i',
                'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 't', 'ι' => 'i',
                'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i',
                'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i',
                'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k',
                'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'k', 'ο' => 'o', 'ό' => 'o',
                'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o',
                'ὸ' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's',
                'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
                'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y',
                'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y',
                'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'p', 'ω' => 'o',
                'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o',
                'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o',
                'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o',
                'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', 'А' => 'A',
                'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
                'Ж' => 'Z', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L',
                'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S',
                'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'K', 'Ц' => 'T', 'Ч' => 'C',
                'Ш' => 'S', 'Щ' => 'S', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'Y', 'Я' => 'Y',
                'а' => 'A', 'б' => 'B', 'в' => 'V', 'г' => 'G', 'д' => 'D', 'е' => 'E',
                'ё' => 'E', 'ж' => 'Z', 'з' => 'Z', 'и' => 'I', 'й' => 'I', 'к' => 'K',
                'л' => 'L', 'м' => 'M', 'н' => 'N', 'о' => 'O', 'п' => 'P', 'р' => 'R',
                'с' => 'S', 'т' => 'T', 'у' => 'U', 'ф' => 'F', 'х' => 'K', 'ц' => 'T',
                'ч' => 'C', 'ш' => 'S', 'щ' => 'S', 'ы' => 'Y', 'э' => 'E', 'ю' => 'Y',
                'я' => 'Y', 'ð' => 'd', 'Ð' => 'D', 'þ' => 't', 'Þ' => 'T', 'ა' => 'a',
                'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z',
                'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n',
                'ო' => 'o', 'პ' => 'p', 'ჟ' => 'z', 'რ' => 'r', 'ს' => 's', 'ტ' => 't',
                'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'g', 'ყ' => 'q', 'შ' => 's',
                'ჩ' => 'c', 'ც' => 't', 'ძ' => 'd', 'წ' => 't', 'ჭ' => 'c', 'ხ' => 'k',
                'ჯ' => 'j', 'ჰ' => 'h',
            );
            $this->keys = array_keys($transliteration);
            $this->data = array_values($transliteration);
        }

        return str_replace($this->keys, $this->data, $str);
    }

    /**
     * Przycina bez obcinania w połowie słowa.
     *
     * @param string $str
     * @param int    $start
     * @param int    $length
     * @param type   $addtoendifcut   - string dodawany na koniec rezultatu gdy string podany string został przycięty
     * @param type   $addtobeginifcut
     *
     * @return string
     * @return string
     *                kod testowy : JHN0cmluZyA9ICdhYiBjZCBlZic7DQokc3RyaW5nID0gJ3JheiBkd2EgdHJ6eSc7DQpmb3IgKCRpID0gMDskaTw4OyRpKyspIHsNCiAgd3JpdGVsbigiLS0tLSBzdGFydDogJGkgLS0tLSIpOw0KICAkbGVuID0gbWJfc3RybGVuKCRzdHJpbmcpKzM7DQogIHdoaWxlICgkbGVuLS0pIHsNCiAgICB3cml0ZWxuKCRsZW4uJ3wnLnN1YkVuZCgkc3RyaW5nLCRpLCRsZW4pKTsNCiAgfQ0KfQ0KZnVuY3Rpb24gd3JpdGVsbigkc3RyKSB7DQogIGVjaG8gIjxwcmU+JHN0cnw8L3ByZT4iOw0KfQ==
     */
    public static function subEndStartStop($str, $start = 0, $length = null, $addtoendifcut = ' ...', $addtobeginifcut = '... ', $encoding = 'UTF8')
    {
        $len = mb_strlen($str, $encoding);

        $str = self::subend($str, $start, $length, $encoding);

        //  echo $str;die();
        $new_len = mb_strlen($str, $encoding);
        $diff = $len - $new_len;
//    writeln('difff: '.$diff);
        if ($new_len < $len) {
            if ($diff == $start) {
                return $addtobeginifcut.$str;
            } elseif ($start == 0) {
                return $str.$addtoendifcut;
            } else {
                return $addtobeginifcut.$str.$addtoendifcut;
            }
        }

        return $str;
    }

    /**
     * Przycina bez obcinania w połowie słowa.
     *
     * @param string $str
     * @param int    $start
     * @param int    $length
     *
     * @return string
     *                kod testowy : JHN0cmluZyA9ICdhYiBjZCBlZic7DQokc3RyaW5nID0gJ3JheiBkd2EgdHJ6eSc7DQpmb3IgKCRpID0gMDskaTw4OyRpKyspIHsNCiAgd3JpdGVsbigiLS0tLSBzdGFydDogJGkgLS0tLSIpOw0KICAkbGVuID0gbWJfc3RybGVuKCRzdHJpbmcpKzM7DQogIHdoaWxlICgkbGVuLS0pIHsNCiAgICB3cml0ZWxuKCRsZW4uJ3wnLnN1YkVuZCgkc3RyaW5nLCRpLCRsZW4pKTsNCiAgfQ0KfQ0KZnVuY3Rpb24gd3JpdGVsbigkc3RyKSB7DQogIGVjaG8gIjxwcmU+JHN0cnw8L3ByZT4iOw0KfQ==
     */
    public static function subend($str, $start = 0, $length = null, $encoding = 'UTF8')
    {
        $len = mb_strlen($str, $encoding);

        if (!$start && $len <= $length) {
            return $str;
        }

        $sum = $length + $start;

        if ($sum == $len) {
            $str = mb_substr($str, $start, $length, $encoding);

            return $str;
        }

        $str2 = mb_substr($str, $start, $length, $encoding);

        $len = mb_strlen($str2, $encoding);

        if ($length != $len) {
            return $str2;
        }

        if (in_array(mb_substr($str, $start + $length, 1, $encoding), array(' ', "\n", "\t"))) {
            return $str2;
        }

        $str2 = mb_substr($str, $start, $length + 1, $encoding);
        do {
            if (in_array(mb_substr($str2, -1, mb_strlen($str2, $encoding), $encoding), array(' ', "\n", "\t"))) { // niestety muszę podać jawnie mb_strlen() zamiast null, związane z błędem: http://pl1.php.net/mb_substr#77515
            return rtrim($str2);
            }
            $str2 = mb_substr($str2, 0, -1, $encoding);
        } while (--$len > 0);

        return '';
        // stary kod
//      if ($length && mb_strlen($str) >= $length) {
//          $length = mb_strpos($str, ' ', $length);
//          echo ' = '.$length.' = '.preg_replace('/\s/', '-', $str).'<br />';
//          return mb_substr($str, $start, $length);
//      }
//      return mb_substr($str, $start);
    }

    /**
     * Dodaje na końcu brakujące znaczniki html.
     *
     * @param string $html
     * @param array  $ignore - lista znaczników ignorowanych
     *
     * @return string
     */
    public static function fixLastTags($html, $ignore = array())
    {
        preg_match_all('/[<>]/', $html, $last);
        if (count($last)) {
            $last = $last[0];
            if (count($last) && $last[count($last) - 1] == '<') {
                $html = preg_replace('/<[^>]*$/', '', $html); // odcinam ostatni uszkodzony tag
            }
        }
        foreach ($ignore as $k => $d) {
            $ignore[$k] = strtolower($d);
        }

        #put all opened tags into an array
        preg_match_all('#<([a-z0-9]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];   #put all closed tags into an array
        preg_match_all('#</([a-z0-9]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        # all tags are closed
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        # close tags
        for ($i = 0; $i < $len_opened; ++$i) {
            if (!in_array($openedtags[$i], $closedtags)) {
                if (!in_array(strtolower($openedtags[$i]), $ignore)) {
                    $html .= '</'.$openedtags[$i].'>';
                }
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }

        return $html;
    }

    public static function formatMonay($str)
    {
        return number_format(str_replace(',', '.', (double) $str), 2, ',', ' ');
    }

    /**
     * test: JHN0cmluZyA9ICd8ZnN8Z2dzYV9kc3Nhc3NffGZkc2E5fGZzYXNzZGY5OXxmZHNhZnNkYTAwfGZkc2Fmc2RhMDAwOXxmZGFzXzl8ZmRhc2RmYXNfMDl8ZmRzYV8wMDA4OHxmZHNhXzB8ZmRzYV8wMDAnOw0KZWNobyAnPHByZT4nOw0KZm9yZWFjaCAoZXhwbG9kZSgnfCcsICRzdHJpbmcpIGFzICRkKSB7DQogIHdyaXRlbG4oIickZCcgLSAnIi51cCgkZCkuIiciKTsNCn0NCg0KZnVuY3Rpb24gd3JpdGVsbigkc3RyKSB7DQogIGVjaG8gUEhQX0VPTC4kc3RyOw0KfQ==.
     */
    public static function incrementString($string, $delimiter = '_', $first = 1)
    {
        $delimiter or $delimiter = '_';
        $parts = explode($delimiter, $string);
        if (count($parts) > 1) {
            $num = $parts[count($parts) - 1];
            if (preg_match('/^\d+$/i', $num)) {
                return preg_replace("/^(.*$delimiter)\d+$/i", '$1', $string).($num + 1);
            }
        }

        return rtrim($string, $delimiter).$delimiter.$first;
    }

    /**
     * Okazuje się że jest już taka funkcja :) preg_quote()
     * Niestety odkryłem ją długo po napisaniu swojej wersji,
     * o dziwo moja jest zgodna jeśli chodzi o składnię do preg_quote().
     *
     *
     *
     *
     * Poprzedza slashami znaki zastrzeżone w wyrażeniach regularnych z rodziny preg_*
     *
     * @param string $string
     * @param string $delimiter (dev: '/')
     *
     * @return string
     *                test: ZGVidWcocHJlZ19tYXRjaCgnIycuYWRkU2xhc2hlc1ByZWdNYXRjaCgndGUhLlsgLyouL1wjW107P14kIF1zdCcsJyMnKS4nIycsICdkZnNhZmRzdGUhLlsgLyouL1wjW107P14kIF1zdGFhZnNkJyksMTEpOw0KZGVidWdnKHByZWdfbWF0Y2goJy8nLmFkZFNsYXNoZXNQcmVnTWF0Y2goJ3RlIS5bIC8qLi9cI1tdOz9eJCBdc3QnKS4nLycsICdkZnNhZmRzdGUhLlsgLyouL1wjW107P14kIF1zdGFhZnNkJyksMTEpOw==
     *
     * podobna funkcja dla js: (zdaje się działać prawidłowo - na górze funkcji znajdującej się w base64 obok): ZnVuY3Rpb24gc3Mocyxjcikgew0KICAgIHZhciBjID0gY3IucmVwbGFjZSgvKFtcLlxcXFtcXVx7XH1cP1wqXC9dKS8sJ1xcJDEnKTsNCiAgICB2YXIgciA9IG5ldyBSZWdFeHAoJ1xcXFwnK2MsJ2dpJyk7DQogICAgdmFyIGQgPSBbXTsNCiAgICB2YXIgbCA9IDA7DQogICAgZm9yICggdmFyIGkgPSAwIDsgaSA8IHMubGVuZ3RoIDsgaSsrICkgew0KICAgICAgICBpZiAoc1tpXSA9PSBjciAmJiAoc1tpLTFdICE9ICdcXCcpKSB7DQogICAgICAgICAgZC5wdXNoKHMuc3Vic3RyaW5nKGwsaSkucmVwbGFjZShyLGNyKSk7DQogICAgICAgICAgbCA9IGkrMTsNCiAgICAgICAgfQ0KICAgIH0NCiAgICBkLnB1c2gocy5zdWJzdHJpbmcobCkucmVwbGFjZShyLGNyKSk7IA0KICAgIA0KICAgIHJldHVybiBkDQp9
     */
    public static function addSlashesPreg($string, $delimiter = '/')
    {
        // dla całych stringów - przypuszczam że jest cięże
        //  $string = str_replace( // dla każdej pray przelatuje cały string raz
        //          array('\\','?','.',':','*','[',']','^','$',/* for test */'\*'),
        //          array('\\\\','\?','\.','\:','\*','\[','\]','\^','\$',/* for test */'******'),
        //          $string
        //  );
        // dla znaków - przypuszczam że jest lżejsze

        $string = strtr($string, array(// string przelatuje znak po znaku i sprawdza czy którać para pasuje
            '\\' => '\\\\',
            '?' => '\?',
            '.' => '\.',
            ':' => '\:',
            '*' => '\*',
            '[' => '\[',
            ']' => '\]',
            '^' => '\^',
            '$' => '\$',
            $delimiter => "\\$delimiter",
        ));

        return $string;
    }

//  public static function toUrl($data,$replacement = '-') {
//    // logika pochodzi z Propel behavior sluggable
//    $data = self::removeAccents($data);
//
//		if (function_exists('iconv')) {
//			$data = iconv('utf-8', 'us-ascii//TRANSLIT', $data);
//		}
//
//		// lowercase
//		if (function_exists('mb_strtolower')) {
//			$data = mb_strtolower($data);
//		} else {
//			$data = strtolower($data);
//		}
//		// remove accents resulting from OSX's iconv
//		$data = str_replace(array('\'', '`', '^'), '', $data);
//
//		// replace non letter or digits with separator
//		$data = preg_replace('/\W+/', $replacement, $data);
//
//		// trim
//		$data = trim($data, $replacement);
//
//    $data = preg_replace('#_#', $replacement, $data);
//
//    $data = preg_replace('#'.str_repeat($replacement, 2).'+#', $replacement, $data);
//
//    return $data;
//  }

    public static function hlight($string, $find, $cls = 'hresult')
    {
        if (!$find) {
            // gdy pusty string
        return $string;
        }

        foreach (array_map('trim', (array) explode(' ', $find)) as $tag) {
            $part = preg_quote($tag, '/');
            $string = preg_split('~(</?[\w][^>]*>)~', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($string as $kk => $tt) {
                if ($tt[0] != '<') {
                    $string[$kk] = preg_replace("/($part)/i", "<span class=\"$cls\">$1</span>", $tt);
                }
            }
            $string = implode('', $string);
        }

        return $string;
    }
    /**
     * Szuka podanej pierwszego wystąpienia frazy w stringu oraz obcina otoczenie
     * do podanej długości.
     *
     * @param string $string
     * @param string $find
     * @param int    $forward
     * @param int    $backward
     *
     * @return string
     */
    public static function zoom($string, $find, $forward = 50, $backward = 50, $encoding = 'UTF8')
    {
        $findLength = mb_strlen($find, $encoding);
        $findString = mb_strlen($string, $encoding);

        if ($forward + $backward + $findLength >= $findString) {
            return $string;
        }

        $pos = mb_strpos(
          mb_strtolower($string, $encoding),
          mb_strtolower($find, $encoding),
          0,
          $encoding
        );

        if ($pos === false) {
            $pos = 0;
        }

        $diff = abs($backward - $pos);
        if ($diff > $backward) {
            $diff = $backward;
        }

        $string = self::subEndStartStop(
          $string,
          $pos - $backward > 0 ? $pos - $forward : 0,
          $pos + mb_strlen($find, $encoding) + $forward < mb_strlen($string, $encoding) ? mb_strlen($find, $encoding) + $forward + $diff : mb_strlen($string, $encoding) - $pos
        );

        return $string;
    }
    public static function randomChars($length, $pula = 'QWERTYUIOPASDFGHJKLZXCVBNM')
    {
        $str = '';

        for ($i = 0; $i < $length; ++$i) {
            $str .= static::randomChar($pula);
        }

        return $str;
    }
    public static function randomChar($pula = 'QWERTYUIOPASDFGHJKLZXCVBNM')
    {
        return mb_substr($pula, mt_rand(0, mb_strlen($pula) - 1), 1);
    }
    protected static $_flipget;

    /**
     * Zamienia znaki w zserializowanym json tak aby oszczędniej je można było przekazać jako wartość w jednym parametrze get
     * Odpowiednik js:.
     (function (w, c, name) {
     for (var i in c)
     c[c[i]] = i;
     
     window[name] = function (s) {
     s = s.split('');
     for (var i = 0, l = s.length ; i < l ; ++i ) {
     if (c[s[i]]) s[i] = c[s[i]];
     }
     return s.join('');
     };
     })(window, {
     ' ' : '.',
     '"' : '!',
     ':' : '-',
     '{' : '(',
     '}' : ")",
     '?' : "_",
     '&' : "~",
     }, 'flipget');
     * @param string $s
     *
     * @return string
     */
    public static function flipget($s)
    {
        if (!static::$_flipget) {
            foreach (array(
                ' ' => '.',
                '"' => '!',
                ':' => '-',
                '{' => '(',
                '}' => ')',
                '?' => '_',
                '&' => '~',
            ) as $key => $data) {
                static::$_flipget[static::$_flipget[$key] = $data] = $key;
            }
        }

        $s = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($s as $key => $t) {
            if (in_array($t, static::$_flipget)) { // można użyć w zasadzie in_array ze względu na dublowaną zawartość tablicy
                $s[$key] = static::$_flipget[$t];
            }
        }

        return implode('', $s);
    }
}
