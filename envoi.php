<?php

session_start();

function getdata($arg)
{
    $cache = 'cache/'.$arg.'.appcache';
    $expire = time()-3600;

    if (file_exists($cache) && filemtime($cache) > $expire) {
        ob_start();
        readfile($cache);
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    } else {
        $start = time();
        $postfields = http_build_query(array(
      'gotermrel' => $arg
    ));

        $url = "http://www.jeuxdemots.org/rezo-dump.php";

        $options = array(
      'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => $postfields,
      ),
    );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);
        $result = utf8_encode($result);
        $data = html_entity_decode($result, ENT_QUOTES, "UTF-8");

        file_put_contents($cache, $data);

        return $data;
    }
}


  function getmots()
  {
      ini_set('memory_limit', '10240M');
      $url = "http://www.jeuxdemots.org/JDM-LEXICALNET-FR/01012020-LEXICALNET-JEUXDEMOTS-ENTRIES.txt";
      $i =2;
      $nbMaxValue = 100;

      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded",
              'method'  => 'POST',
              'content' => $postfields,
          ),
      );

      $context = stream_context_create($options);

      $result = file_get_contents($url, false, $context);
      $result = utf8_encode($result);

      $regexAll = '/([\d]+;[^\n]+)/u';
      $regexSingle = '/\d+;(?P<name>[^\n]+);/u';

      $tokens = array();

      preg_match_all($regexAll, $result, $tab);

      foreach ($tab[1] as $key => $value) {
          preg_match($regexSingle, $value, $token);
          $tokens[] = $token;

          $i++;
          if ($i > $nbMaxValue && false) {
              break;
          }
      }

      return $tokens;
  }

function getToken($text, $regexAll, $regexSingle)
{
    $tokens = array();

    preg_match_all($regexAll, $text, $tab);


    foreach ($tab[1] as $key => $value) {
        preg_match($regexSingle, $value, $token);
        $tokens[] = $token;
    }

    return $tokens;
}

function getEntries($data)
{
    $string = explode("les noeuds/termes (Entries) : e;eid;'name';type;w;'formated name'", $data);
    $string = explode("// les types de relations (Relation Types) : rt;rtid;'trname';'trgpname';'rthelp' ", $string[1]);

    $regexAll = '/([\n]*e;\d+;[^\n]+;\d+;\d+(;[^\n]+|))/u';
    $regexSingle = '/e;(?P<eid>\d+);(?P<name>[^.]+);(?P<type>\d+);(?P<w>\d*)(;(?P<formated_name>[^\n]+)|)/u';

    return array_sort(getToken($string[0], $regexAll, $regexSingle), 'w', SORT_DESC);
}

function getWordInfo($word, $data)
{
    $info = array();

    $string = explode("les noeuds/termes (Entries) : e;eid;'name';type;w;'formated name'", $data);
    $string = explode("// les types de relations (Relation Types) : rt;rtid;'trname';'trgpname';'rthelp' ", $string[1]);

    $regexAll = '/([\n]*e;\d+;[^\n]+;\d+;\d+(;[^\n]+|))/u';
    $regexSingle = '/e;(?P<eid>\d+);\'(?P<name>[^.]+)\';(?P<type>\d+);(?P<w>\d*)(;(?P<formated_name>[^\n]+)|)/u';

    $info['entries'] = getToken($string[0], $regexAll, $regexSingle);

    $string = explode("les noeuds/termes (Entries) : e;eid;'name';type;w;'formated name'", $data);
    $string = explode("// les types de relations (Relation Types) : rt;rtid;'trname';'trgpname';'rthelp' ", $string[1]);

    $regexAll = '/([\n]*e;\d+;[^\n]+'.$word.'>[^\n]+;\d+;\d+(;[^\n]+|))/u';
    $regexSingle = '/e;(?P<eid>\d+);\'(?P<name>[^.]+)\';(?P<type>\d+);(?P<w>\d*)(;\'(?P<formated_name>[^\n]+)\'|)/u';

    $info['raffinement'] = getToken($string[0], $regexAll, $regexSingle);
    $info['definition'] = str_replace(array('<br />','<br>'), "", getdef($data));

    foreach ($info['raffinement'] as $key => $subtab) {
        $def = getdef(getdata($subtab['formated_name']));
        $def = str_replace('<br />', '<br>', $def);
        $subtab['def'] = preg_replace('/^([\d]+\.\s[<br>]*|)([(]{1}[^\n]+[)]{1})([^\n]+)/mi', '$1 <strong>$2</strong> $3', $def);
        $subtab['formated_name'] = str_replace($word.'>', "", $subtab['formated_name']);
        $info['raffinement'][$key] = $subtab;
    }

    $string = explode("// les relations sortantes : r;rid;node1;node2;type;w ", $data);
    $string = explode("// les relations entrantes : r;rid;node1;node2;type;w ", $string[1]);

    $regexAll = '/(r;\d+;\d+;\d+;\d+;\d+)/u';
    $regexSingle = '/r;\d+;\d+;(?P<node>\d+);(?P<type>\d+);(?P<w>\d+)/u';

    $info['RSortantes'] = getToken($string[0], $regexAll, $regexSingle);

    $string = explode("// les relations entrantes : r;rid;node1;node2;type;w ", $data);
    $string = explode("// END", $string[1]);
    $regexSingle = '/r;\d+;(?P<node>\d+);\d+;(?P<type>\d+);(?P<w>\d+)/u';

    $info['REntrantes'] = getToken($string[0], $regexAll, $regexSingle);

    return $info;
}

function getAutocomplete()
{
    ini_set('memory_limit', '10240M');
    $url = "http://www.jeuxdemots.org/JDM-LEXICALNET-FR/01012020-LEXICALNET-JEUXDEMOTS-ENTRIES.txt";

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => $postfields,
        ),
    );
    $time_start = microtime(true);
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);
    $result = utf8_encode($result);

    $regexAll = '/([\d]+;[^\n]+)/u';
    $regexSingle = '/\d+;(?P<name>[^\n]+);/u';


    $tokens = getToken($result, $regexAll, $regexSingle);
    $time_end = microtime(true);
    $time = $time_end - $time_start;

    echo "Ne rien faire pendant $time secondes\n";
    return $tokens;
}

function getdef($arg)
{
    // récupérer la définition du mot
    $definitionmot= explode('<def>', $arg);

    $definitionmots = explode('</def>', $definitionmot[1]);
    //$definitionmots[0] contient toutes les définitions du mot

    return $definitionmots[0]."<br>";
}

function getEntrie($entries, $eid)
{
    foreach ($entries as $key => $subtab) {
        if ($subtab['eid'] == $eid) {
            return $subtab;
        }
    }
    return null;
}
