<?php

    session_start();

    function getdata($arg)
    {
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
        $result = html_entity_decode($result, ENT_QUOTES, "UTF-8");

        return $result;
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

    function getdef($arg)
    {
        // récupérer la définition du mot
        $definitionmot= explode('<def>', $arg);

        $definitionmots = explode('</def>', $definitionmot[1]);
        //$definitionmots[0] contient toutes les définitions du mot

        return $definitionmots[0]."<br>";
    }

    function raffinement($arg)
    {
        $html = getdata($arg);

        return $arg."\n".":"."\n".getdef($html);
    }

    function getraffinement($arg)
    {
        $definitionmots = getdef($result);

        // Découper le contenu de la page par <code>
        $decoupeparcode = explode('<CODE>', $result);

        $apresformatedname = explode('formated name\'', $decoupeparcode[1]); // récupérer tout ce qui est après le mot et avant  " formated name " dans le texte. Ce qui correspond à tout ce dont on a besoin à partir de noeud

        // Découper apresformatedname[1] par //" tous les noeuds sont dans $getnoeud[0]
        $getnoeud = explode('//', $apresformatedname[1]);


        //toutes les types de relations sont dans $gettyperelation[1]
        $gettyperelation = explode('rthelp\'', $getnoeud[1]);

        // recupère tout le texte apres "formated name"
        //contient toutes les relations
        $touterelation = explode('les relations sortantes : r;rid;node1;node2;type;w ', $apresformatedname[1]);

        // découpe le texte par // pour récupérer les relations
        $getrelationsortante = explode('//', $touterelation[1]);
        //$getrelationsortante[0] contient toutes les relations sortantes

        // $getrelationentrante[1] contient toutes les relations entrantes
        $getrelationentrante = explode('les relations entrantes : r;rid;node1;node2;type;w ', $getrelationsortante[1]);

        // enlever le r; du début des relations sortantes
        //$getrelationsortante2 nouvelle table de relation sortante

        $getrelationsortante2 = explode('r;', $getrelationsortante[0]);

        //récupère la taille du tableau de relation sortante
        //$taillerelationsortante = $count($getrelationsortante2);

        // enlève le e; du début des noeuds
        // getnoeuds nouveau tableau de noeuds
        $getnoeuds = explode('e;', $getnoeud[0]);
        //print_r($getnoeuds[0]);

        // enleve "rt;" du debut de tous les types de relation
        // suprimert devient notre nouveau contenu de type de relation

        $supprimert = explode('rt;', $gettyperelation[1]);

        //on récupère le nom de chaque type de relations existant
        for ($i=1; $i<count($supprimert);$i++) {
            $r = explode(';', $supprimert[$i]);

            $type[$r[0]] = $r[1];
        }

        for ($i=1; $i<count($getrelationsortante2);$i++) {

            //	decoupe chaque ligne de la table des relations sortantes par un ";"
            $ri = explode(';', $getrelationsortante2[$i]);

            // crée une table de hachache de numero de relation sortante =====> poids
            $relationsortante[$ri[0]] = $ri[4];

            // crée une table de hachache de numero de relation sortante =====> identifiant du noeud
            $relationsortante2[$ri[0]] = $ri[2];

            // crée une table de hachache de numero de relation sortante =====> type de relation
            $typerelation[$ri[0]] = $ri[3];
        }

        for ($i=2; $i<count($getnoeuds);$i++) {

            // decoupe chaque ligne de la table des noeuds par un ";"
            $b = explode(';', $getnoeuds[$i]);

            // si c'est un string je récupère le string sans les identifiants (nettoyage)
            if (is_string($b[4])) {
                $node[$b[0]] = $b[4];
            } else {
                // recupère nom du noeud
                $node[$b[0]] = $b[1];
            }

            //$node contient le nom des noeuds
        }

        #je supprime de la table des noeuds, tous les noeuds qui ne sont pas dans la table de relation sortante
        foreach ($node as $key => $value) {
            if (!in_array($key, $relationsortante2)) {
                unset($node[$key]);
            }
        }

        #création d'un tableau relation(identifiant) => nom de noeud
        foreach ($relationsortante2 as $key => $value) {
            $relationsortante2[$key] = $node[$value];
        }


        foreach ($typerelation as $key => $value) {
            // création d'un tableau de noeuds liés par la relation r_raff_sem au noeud cherché
            if ($typerelation[$key] == 1) {
                $po = explode('\'', $relationsortante2[$key]);
                $raff[] = $po[1];
            }
        }

        return $raff;
    }

    function getinfo($arg)
    {
        $result = getdata($arg);

        // Découper le contenu de la page par <code>
        $decoupeparcode = explode('<CODE>', $result);

        $apresformatedname = explode('formated name\'', $decoupeparcode[1]); // récupérer tout ce qui est après le mot et avant  " formated name " dans le texte. Ce qui correspond à tout ce dont on a besoin à partir de noeud

        // Découper apresformatedname[1] par //" tous les noeuds sont dans $getnoeud[0]
        $getnoeud = explode('//', $apresformatedname[1]);

        //toutes les types de relations sont dans $gettyperelation[1]
        $gettyperelation = explode('rthelp\'', $getnoeud[1]);

        // recupère tout le texte apres "formated name"
        //contient toutes les relations
        $touterelation = explode('les relations sortantes : r;rid;node1;node2;type;w ', $apresformatedname[1]);

        // découpe le texte par // pour récupérer les relations
        $getrelationsortante = explode('//', $touterelation[1]);
        //$getrelationsortante[0] contient toutes les relations sortantes

        // $getrelationentrante[1] contient toutes les relations entrantes
        $getrelationentrante = explode('les relations entrantes : r;rid;node1;node2;type;w ', $getrelationsortante[1]);

        // enlever le r; du début des relations sortantes
        //$getrelationsortante2 nouvelle table de relation sortante

        $getrelationsortante2 = explode('r;', $getrelationsortante[0]);

        //récupère la taille du tableau de relation sortante
        //$taillerelationsortante = $count($getrelationsortante2);

        // enlève le e; du début des noeuds
        // getnoeuds nouveau tableau de noeuds
        $getnoeuds = explode('e;', $getnoeud[0]);

        // enleve "rt;" du debut de tous les types de relation
        // suprimert devient notre nouveau contenu de type de relation

        $supprimert = explode('rt;', $gettyperelation[1]);



        //on récupère le nom de chaque type de relations existant
        for ($i=1; $i<count($supprimert);$i++) {
            $r = explode(';', $supprimert[$i]);

            $type[$r[0]] = $r[1];
        }

        for ($i=1; $i<count($getrelationsortante2);$i++) {

            //	decoupe chaque ligne de la table des relations sortantes par un ";"
            $ri = explode(';', $getrelationsortante2[$i]);

            // crée une table de hachache de numero de relation sortante =====> poids
            $relationsortante[$ri[0]] = $ri[4];

            // crée une table de hachache de numero de relation sortante =====> identifiant du noeud
            $relationsortante2[$ri[0]] = $ri[2];

            // crée une table de hachache de numero de relation sortante =====> type de relation
            $typerelation[$ri[0]] = $ri[3];
        }

        for ($i=2; $i<count($getnoeuds);$i++) {

            // decoupe chaque ligne de la table des noeuds par un ";"
            $b = explode(';', $getnoeuds[$i]);

            //contient chaque noeud et son type
            switch ($b[2]) {
                case 1:
                    $typen[$b[0]] = "n_term";
                    break;
                case 2:
                    $typen[$b[0]] = "n_form";
                    break;
                case 4:
                    $typen[$b[0]] = "n_pos";
                    break;
                case 6:
                    $typen[$b[0]] = "n_flpot";
                    break;
                case 8:
                    $typen[$b[0]] = "n_chunk";
                    break;
                case 9:
                    $typen[$b[0]] = "n_question";
                    break;
                case 10:
                    $typen[$b[0]] = "n_relation";
                    break;
                case 12:
                    $typen[$b[0]] = "n_analogy";
                    break;
                case 18:
                    $typen[$b[0]] = "n_data";
                    break;
                case 36:
                    $typen[$b[0]] = "n_data_pot";
                    break;
                case 200:
                    $typen[$b[0]] = "n_context";
                    break;
                case 444:
                    $typen[$b[0]] = "n_link";
                    break;
                case 666:
                    $typen[$b[0]] = "n_AKI";
                    break;
                case 777:
                    $typen[$b[0]] = "n_wikipedia";
                    break;
                case 1002:
                    $typen[$b[0]] = "n_group";
                    break;
            }

            // si c'est un string je récupère le string sans les identifiants (nettoyage)
            if (is_string($b[4])) {
                $node[$b[0]] = $b[4];
            } else {
                // recupère nom du noeud
                $node[$b[0]] = $b[1];
            }

            //$node contient le nom des noeuds
        }


        //Ralenti l'execution
        //$node n'est plus utilisé
        /*#je supprime de la table des noeuds, tous les noeuds qui ne sont pas dans la table de relation sortante
        foreach ($node as $key => $value) {
            if (!in_array($key, $relationsortante2)) {
                w
            }
        }*/


        #création d'un tableau relation(identifiant) => nom de noeud
        foreach ($relationsortante2 as $key => $value) {
            $relationsortante2[$key] = $node[$value];
            $relationsortante3[$key] = $typen[$value];
        }

        //print_r($relationsortante3);

        foreach ($typerelation as $key => $value) {
            // création d'un tableau de noeuds liés par la relation r_raff_sem au noeud cherché
            if ($typerelation[$key] == 1) {
                $po = explode('\'', $relationsortante2[$key]);
                $raff[] = $po[1];
            }
        }

        //print_r($raff);

        foreach ($typerelation as $key => $value) {
            $typerelation[$key] = $type[$value];
        }

        //print_r($typerelation);
        ksort($typerelation);

        //print_r($typerelation);

        #tri par clé croissant
        ksort($relationsortante3);

        ksort($typen);

        #tri par valeur croissant
        ksort($relationsortante2);

        ksort($relationsortante);

        foreach ($relationsortante2 as $key => $value) {
            $relationsortante2[$key] =  $relationsortante[$key].",".$relationsortante2[$key].",".$typerelation[$key].",".$relationsortante3[$key].";";
        }

        return $relationsortante2;
    }

    ///////////////

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

    function getInformations($word, $data)
    {
        $info = array();

        $string = explode("les noeuds/termes (Entries) : e;eid;'name';type;w;'formated name'", $data);
        $string = explode("// les types de relations (Relation Types) : rt;rtid;'trname';'trgpname';'rthelp' ", $string[1]);

        $regexAll = '/([\n]*e;\d+;[^\n]+'.$word.'>[^\n]+;\d+;\d+(;[^\n]+|))/u';
        $regexSingle = '/e;(?P<eid>\d+);\'(?P<name>[^.]+)\';(?P<type>\d+);(?P<w>\d*)(;\'(?P<formated_name>[^\n]+)\'|)/u';

        $tab = array_sort(getToken($string[0], $regexAll, $regexSingle), 'w', SORT_DESC);

        foreach ($tab as $key => $subtab) {
            $def = getdef(getdata($subtab['formated_name']));
            $def = str_replace('<br />', '<br>', $def);
            $subtab['def'] = preg_replace('/^([\d]+\.\s[<br>]*|)([(]{1}[^\n]+[)]{1})([^\n]+)/mi', '$1 <strong>$2</strong> $3', $def);
            $subtab['formated_name'] = str_replace($word.'>', "", $subtab['formated_name']);
            $tab[$key] = $subtab;
        }

        $info['def'] = str_replace(array('<br />','<br>'), "", getdef($data));
        $info['raffSem'] = $tab;

        $string = explode("// les relations sortantes : r;rid;node1;node2;type;w", $data);
        $string = explode("// les relations entrantes : r;rid;node1;node2;type;w ", $string[1]);

        $regexAll = '/(([\n]*r;\d+;[^\n]+'.$word.'>[^\n]+;\d+;\d+))/u';
        $regexSingle = '/r;(?P<eid>\d+);\'(?P<name>[^.]+)\';(?P<type>\d+);(?P<w>\d*)(;\'(?P<formated_name>[^\n]+)\'|)/u';

        $info['RSortant'] = $tab;
        $info['REntrant'] = $tab;

        return $info;
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

        $info['RSortantes'] = array_sort(getToken($string[0], $regexAll, $regexSingle), 'w', SORT_DESC);

        $string = explode("// les relations entrantes : r;rid;node1;node2;type;w ", $data);
        $string = explode("// END", $string[1]);
        $regexSingle = '/r;\d+;(?P<node>\d+);\d+;(?P<type>\d+);(?P<w>\d+)/u';

        $info['REntrantes'] = array_sort(getToken($string[0], $regexAll, $regexSingle), 'w', SORT_DESC);

        return $info;
    }
    /////////////////////

    function getEntrie($entries, $eid)
    {
        foreach ($entries as $key => $subtab) {
            if ($subtab['eid'] == $eid) {
                return $subtab;
            }
        }
        return null;
    }

    function array_sort($array, $on, $order=SORT_DESC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }
