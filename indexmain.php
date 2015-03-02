<?php
error_reporting(E_ALL);
ini_set("log_errors", 1);
require_once 'simple_html_dom.php';
$comicStrip = '';
//http://www.mangahere.co/manga/noblesse/v01/c001/


$formValue = ( (isset($_GET['read']) ) ? urldecode($_GET['read']) :'');
if( isset($_GET['read']) )
{
    if( isset($_GET['read']) )
        $url = urldecode($_GET['read']);


    // make sure the url is good
    $urlPieces = explode('/', $url);
    $numPieces = count($urlPieces);
    foreach($urlPieces as $idx => $val )
    {
        if( $idx == $numPieces )
        {
            if( !strpos('.', $val) )
                $url .= '/';
        }
    }

    $sites = array(
        array('name' => 'Manga Here', 'domain' => 'mangahere.co', 'function' => 'hereScrape'),
        array('name' => 'Manga Fox', 'domain' => 'mangafox.me', 'function' => 'foxScrape' ),
        array('name' => 'Manga Reader', 'domain' => 'mangareader.net', 'function' => 'readerScrape' ),
        array('name' => 'Dynasty Reader', 'domain' => 'dynasty-scans.com', 'function' => 'dynastyScrape')
    );

    function hereScrape( $_url )
    {
        $cacheFile = 'cache/'.sha1($_url);
        if( file_exists($cacheFile) )
        {
            return file_get_contents($cacheFile);
        }
        $concat = '';
        $pageStillHasContent = true;
        $idx = 2;
        $output = '';
        $previous = null;
        $html = '';
        $startChapter = '';

        $urlParts = explode('/', $_url);
        foreach( $urlParts as $idx => $part )
        {
            if( preg_match("(\b(c[0-9]{1,4})\b)", $part) )
            {
                $startChapter = $part;
            }
        }
        array_pop($urlParts);
        $thisChapter = implode('/', $urlParts);
        // die( $startChapter );

        while( $pageStillHasContent )
        // for( $i = 0 ; $i < 10; $i++ )
        {
            $urlParts = explode('/', $_url);
            foreach( $urlParts as $idx => $part )
            {
                if( preg_match("(\b(c[0-9]{1,4})\b)", $part) )
                {
                    $chapter = $part;
                    break;
                }
                $chapter = false;
            }
            $previous = $html;
            if( $chapter != $startChapter )
            { 
                // $output .= '<br><a href="index.php?read='.$nextChapterURL.'">Next Chapter</a>';
                $pageStillHasContent = false;
                continue;
            }
            $html  = file_get_html($_url);
            $image = $html->find('img[id=image]');
            $_url = $image[0]->parent();
            // echo (var_dump($url));
            $_url = $_url->getAttribute('href');
            

            $imageSrc = $image[0]->getAttribute('src');
            $output .= '<br><img src="'.$imageSrc.'" >';

            $concat = $idx.'.html';
            $idx++;
        }
        file_put_contents($cacheFile, $output);
        return $output;
    }

    function dynastyScrap($_url)
    {
        $html = file_get_html($url);
        die( var_dump($html) );
    }

    foreach( $sites as $sidx => $site )
    {
        if( strpos($url, $site['domain']) )
        {
            $comicStrip = $site['function']($url);
        }
    }

}


?>

<html>
<head>
    <title>Manga Here 2 PDF</title>
    <style type="text/css">
        body {
            background: #666;
            font-family: Arial, sans-serif;
        }
        #w {
            width: 50%;
            margin: 0 auto;
        }
        #w img { width: 100%; }
        form input[type=text]{
            width: 50%;
        }
    </style>
</head>
<body>
   

    <div id="w">
        <h1>Manga2PDF</h1>
        <h4>Version 0.2</h4>
        <p>Currently supports: mangahere.co</p>
        <p>To use paste a mangahere link and it will output the images beneath, its set to fill the browser 80% so resize your window to taste.</p>
        <form action="index.php" method="get">
            Enter a chapter URL: <input type="text" name="read" value="<?php echo $formValue; ?>"> <input type="submit">
        </form>
        <?php echo $comicStrip; ?>

        <p>Change Log</p>
        <ul>
            <li>Now supports get param, "read" for bookmarking etc.</li>
        </ul>
        <p>Todo: add next chapter link</p>
    </div>
</body>
</html>