<?php

date_default_timezone_set('Europe/Oslo');
$scriptStart = microtime(TRUE);

/* Configuration */

$rootDir = '.';
$resultsDir = $rootDir.'/bin';
$articlesDir = $rootDir .'/articles';
$filesDir = $rootDir .'/files';
$pagesDir = $rootDir.'/pages';
$templatesDir = $rootDir .'/templates';
$imagesDir = $articlesDir.'/imgs';
$page_template = $templatesDir.'/template.html';
$article_template = $templatesDir.'/article_template.html';
$current_year = date('Y');

/* Verification */

if (!is_dir($resultsDir)) {
  exit('The results directory "'.$resultsDir.'" does not exist'."\n");
}

if (!is_dir($articlesDir)) {
  exit('The articles directory "'.$articlesDir.'" does not exist'."\n");
}

if (!is_dir($imagesDir)) {
  exit('The images directory "'.$imagesDir.'" does not exist'."\n");
}

if (!is_readable($articlesDir)) {
  exit('The articles directory "'.$articlesDir.'" is not readable'."\n");
}

if (!is_readable($imagesDir)) {
  exit('The images directory "'.$imagesDir.'" is not readable'."\n");
}

if (!is_writable($resultsDir)) {
  exit('The results directory "'.$resultsDir.'" is not writeable'."\n");
}

if (!is_dir($templatesDir)) {
  exit('Templates directory "'.$templatesDir.'" does not exist'."\n");
}

if (!is_dir($filesDir)) {
  exit('Files directory "'.$filesDir.'" does not exist'."\n");
}

if (!is_dir($pagesDir)) {
  exit('Pages directory "'.$pagesDir.'" does not exist'."\n");
}

if (!is_readable($pagesDir)) {
  exit('Pages directory "'.$pagesDir.'" is not readable'."\n");
}


if (!is_readable($page_template)) {
  exit('Page template file "'.$page_template.'" is not readable'."\n");
}

if (!is_readable($article_template)) {
  exit('Article template file "'.$article_template.'" is not readable'."\n");
}

/* Class definition & functions */

class Article
{
  public $filename;
  public $title;
  public $description;
  public $image;
  public $imageType;
  public $newImage;
  public $date;
  public $body;
}

function articleCmp($a, $b) {
  if ($a->date == $b->date) {
    if (filectime($a->image) == filectime($b->image)) {
     return 0;
    } else {
      return (filectime($a->image) < filectime($b->image)) ? 1 : -1;
    }
  }
  
  return ($a->date < $b->date) ? 1 : -1;
}

function getTagContents(&$data, $startTag, $endTag, $default, $reverseEnd = FALSE) {
  if (!$data || !$startTag || !$endTag || !is_string($data)
    || !is_string($startTag) || !is_string($endTag) || !is_bool($reverseEnd)) {
    return $default;
  }
  
  $start = stripos($data, $startTag);
  
  if (!$reverseEnd) {
    $end = stripos($data, $endTag);
  } else {
    $end = strripos($data, $endTag, -1);
  }
  
  if ($start) {
    $start += strlen($startTag);
  }
  
  if (($start && $end) && ($end > $start)) {
    return substr($data, $start, ($end-$start));
  } else {
    return $default;
  }
}

function getTagContents2(&$data, $startTag, $endTag, $default, $reverseEnd = FALSE) {
  if (!$data || !$startTag || !$endTag || !is_string($data)
    || !is_string($startTag) || !is_string($endTag) || !is_bool($reverseEnd)) {
    return $default;
  }
  
  $start = stripos($data, $startTag);
  
  if (!$reverseEnd) {
    $end = stripos($data, $endTag, $start);
  } else {
    $end = strripos($data, $endTag, -1);
  }
  
  if ($start) {
    $start += strlen($startTag);
  }
  
  if (($start && $end) && ($end > $start)) {
    return substr($data, $start, ($end-$start));
  } else {
    echo('HIEST 2! start: '.$start.' | end: '.$end."\n");
    return $default;
  }
}

function findImage($filePath, &$fileType) {
  $supportedFiles = [
    'png',
    'jpg',
    'gif',
    'svg'
  ];
  $image = FALSE;
  
  foreach ($supportedFiles as $type) {
    if (file_exists($filePath.'.'.$type)) {
      $image= $filePath.'.'.$type;
      $fileType = $type;
      break;
    }
  }
  
  return $image;
}

/* http://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php */
function recursive_copy($src, $dst) { 
  $dir = opendir($src); 
  
  if (!$dir) {
    echo('[ERROR]: '.$src.' is not a directory!'."\n");
    exit(1);
  }
  
  @mkdir($dst); 
  while(false !== ( $file = readdir($dir)) ) {
    if (( $file != '.' ) && ( $file != '..' ) && ( $file != 'Thumbs.db' )) { 
      if ( is_dir($src . '/' . $file) ) { 
        recursive_copy($src . '/' . $file,$dst . '/' . $file); 
      } 
      else { 
        @copy($src . '/' . $file,$dst . '/' . $file); 
      } 
    } 
  } 
  closedir($dir); 
}

function recursive_delete($src) { 
  $dir = opendir($src); 
  
  if (!$dir) {
    echo('[WARNING]: '.$src.' is not a directory!'."\n");
    return;
  }
  
  while(false !== ( $file = readdir($dir)) ) {
    if (( $file != '.' ) && ( $file != '..' )) { 
      if ( is_dir($src . '/' . $file) ) { 
        recursive_delete($src . '/' . $file);
      } 
      else { 
        unlink($src . '/' . $file); 
      } 
    } 
  } 
  closedir($dir); 
  rmdir($src);
}

function createThumbnail($imageType, $src, $dst) {
  if (!file_exists($src) || !is_readable($src)) {
    echo('[WARNING] Image "'.$src.'" does not exist.'."\n");
    return;
  }
  
  if (!extension_loaded('gd') || !function_exists('gd_info')) {
    echo('[WARNING] PHP GD library is NOT installed on this machine.'."\n");
    return;
  }
  
  switch(strtolower($imageType)) {
    case 'jpg':
    case 'jpeg':
      $source = imagecreatefromjpeg($src);
      break;
    case 'png':
      $source = imagecreatefrompng($src);
      break;
    case 'gif':
      $source = imagecreatefromgif($src);
      break;
    default:
      $source = FALSE;
  }
  
  if (!$source) {
    echo('[WARNING] Unsupported image type ('.$imageType.'?) for "'.$src.'"'."\n");
    return;
  }
  
  list($width, $height) = getimagesize($src);
  $thumbnail = imagecreatetruecolor(350, 210);
  imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, 
    350, 210, $width, $height);
  
  imagepng($thumbnail, $dst);
  
  imagedestroy($source);
  imagedestroy($thumbnail);
}



/* Generator */

$articles = [];

$rootFiles = scandir($filesDir);
$binFiles = scandir($resultsDir);
$articleFiles = scandir($articlesDir);
$pageFiles = scandir($pagesDir);

/* Wipe existing data */
foreach ($binFiles as $file) {
  if ($file != '.' && $file != '..') {
    if (is_dir($resultsDir.'/'.$file)) {
      recursive_delete($resultsDir.'/'.$file);
    } else {
      unlink($resultsDir.'/'.$file);
    }
  }
}

@mkdir($resultsDir.'/articles');
@mkdir($resultsDir.'/articles/article_imgs');

foreach ($rootFiles as $file) {
  if ($file != '.' && $file != '..') {
    if (is_dir($filesDir.'/'.$file)) {
      recursive_copy($filesDir.'/'.$file, $resultsDir.'/'.$file);
    } else {
      @copy($filesDir.'/'.$file, $resultsDir.'/'.$file);
    }
  }
}

foreach ($articleFiles as $article) {
  $articleFileName = $article;
  
  // Update proper dir
  $article = $articlesDir.'/'.$article;
  
  if (is_dir($article) && $articleFileName != 'imgs'
    && $articleFileName != '.' && $articleFileName != '..') {
    recursive_copy($article, $resultsDir.'/articles/'.$articleFileName);
    continue;
  }
  
  // Verify file
  if (!is_file($article)) {
    continue;
  }
  
  if (!is_readable($article)) {
    echo('[ERROR]: no read access to article "'.$article."\"\n");
    continue;
  }
  
  $articleData = @file_get_contents($article);
  
  if (!$articleData) {
    echo('[ERROR]: failed to retrieve data of article "'.$article."\"\n");
    continue;
  }
  
  // Verify there's a dot in the file type
  $FileNameDot = strrpos($articleFileName, '.', -2);
  
  if (!$FileNameDot) {
    echo('[ERROR]: unknown file type of article "'.$article."\"\n");
    continue;
  }
  
  $articleFileName = substr($articleFileName, 0, $FileNameDot);
  unset($FileNameDot);
  
  $a = new Article;
  
  // Title
  $a->title = getTagContents($articleData, '<title>', '</title>', 'Untitled');
  $a->filename = $articleFileName;
  
  if ($a->title == 'Untitled') {
    echo('[WARNING]: Invalid/no title for article "'.$article."\"\n");
  }
  
  // Date
  $now = time();
  $a->date = strtotime(getTagContents($articleData, '<p class="date">', '</p>', $now));
  
  if ($a->date >= $now) {
    echo('[WARNING]: Invalid/no date for article "'.$article."\"\n");
  }
  
  // Body
  $a->body = getTagContents($articleData, '<body>', '</body>', FALSE, TRUE);
  
  if (!$a->body) {
    echo('[WARNING]: Invalid/no body for article "'.$article."\"\n");
    continue;
  }
  
  // Description
  $a->description = strip_tags(getTagContents2($articleData, '<p>', '</p>', FALSE));
  $a->description = str_replace(array('&', '<', '>'), array('&#x26;', '&#x3C;', '&#x3E;'), $a->description);
  
  // Image
  $a->imageType = '';
  $a->image = findImage($imagesDir.'/'.$articleFileName, $a->imageType);
  $a->newImage = false;
  if ($a->image) {
    $dateStr = '<p class="date">'.date('Y-m-d', $a->date).'</p>';
    $datePos = stripos($a->body, $dateStr);
    
    if ($datePos) {
      $datePos += strlen($dateStr);
      $bodyStart = substr($a->body, 0, $datePos);
      $bodyEnd = substr($a->body, $datePos);
      
      $a->newImage = 'article_imgs/'.$articleFileName.'.'.pathinfo($a->image, PATHINFO_EXTENSION);
      $a->body = $bodyStart."\n".'<img src="'.$a->newImage.'" alt="'.$a->title.'" />'."\n\n".$bodyEnd;
      unset($bodyStart, $bodyEnd);
      
      createThumbnail($a->imageType, $a->image,
        $resultsDir.'/articles/article_imgs/'.$a->filename.'-thmb.'.$a->imageType);
    } else {
      echo('[WARNING]: Failed to find date string in article "'.$article."\"\n");
    }
  } else {
    echo('[WARNING]: No image "'.$articleFileName.'" found for article "'.$article."\"\n");
  }
  
  // Is this a multi article?
  $articleSeparator = '<!-- SPLIT PAGE -->';
  $currentPage = &$a->body;
  $subArticles = [];
  $numSubArticles = 0;
  
  while ($currentPage && (strlen($currentPage) > 1)) {
    if ((($separatorPos = stripos($currentPage, $articleSeparator)) === FALSE)) {
      if (!$numSubArticles) {
        break;
      }
      
      $separatorPos = strlen($currentPage)-(strlen($articleSeparator) + 1);
    }
    
    $sub = new Article;
    $sub->body = substr($currentPage, 0, $separatorPos);
    $currentPage = substr($currentPage, $separatorPos + strlen($articleSeparator));
    
    if (!$numSubArticles) {
      $sub->title = &$a->title;
      $sub->filename = &$a->filename;
    } else {
      $sub->title = getTagContents($sub->body, '<h1>', '</h1>', 'Untitled');;
      $sub->filename = $a->filename.'-p'.($numSubArticles + 1);
    }
    
    $subArticles[$numSubArticles++] = $sub;
  }
  
  if ($numSubArticles) {
    $a->body = $subArticles;
  }
  
  // Store it
  $articles[] = $a;
  usort($articles, 'articleCmp');
  
  unset($articleData, $now);
}

/* Retrieve page template */
$templateContents = @file_get_contents($page_template);
if (!$templateContents) {
  exit('Failed to retrieve template file "'.$page_template."\"\n");
}

$pages = [];

foreach ($pageFiles as $page) {
  if ($page == '.' || $page == '..') {
    continue;
  }
  
  $realFile = $pagesDir.'/'.$page;
  
  if (!file_exists($realFile) || !is_readable($realFile)) {
    echo('[WARNING] Page "'.$realFile.'" does not exist.'."\n");
    continue;
  }
  
  $pageData = @file_get_contents($realFile);
  
  if (!$pageData) {
    echo('[WARNING] Failed to read page "'.$realFile.'"'."\n");
  }
  
  $p = new Article;
  
  // Title
  $p->title = getTagContents($pageData, '<title>', '</title>', 'Untitled');
  $p->filename = $page;
  
  if ($p->title == 'Untitled') {
    echo('[WARNING]: Invalid/no title for page "'.$page."\"\n");
  }
  
  // Body
  $p->body = '<div id="content">'."\n";
  $p->body .= getTagContents($pageData, '<body>', '</body>', FALSE, TRUE);
  $p->body .= '</div>'."\n";
  
  
  if (!$p->body) {
    echo('[WARNING]: Invalid/no body for page "'.$page."\"\n");
    continue;
  }
  
  $pageData = str_replace('<!-- ## TITLE ## -->', $p->title.' | EiMF', $templateContents);
  $pageData = str_replace('<!-- ## CONTENT ## -->', $p->body, $pageData);
  $pageData = str_replace('<!-- ## YEAR ## -->', $current_year, $pageData);
  $p->body = $pageData;
  
  unset($pageData);
  $pages[] = $p;
}

$mainMenu = '';
$subMenu =  '';

foreach ($pages as $page) {
  $mainMenu .= '        <li><a href="'.$page->filename.'">'.$page->title.'</a></li>'."\n";
  $subMenu .=  '        <li><a href="../'.$page->filename.'">'.$page->title.'</a></li>'."\n";
}

foreach ($pages as $page) {
  //$page->body = str_replace('<!-- ## MENU ## -->', $mainMenu, $page->body);
  $page->body = str_replace('<!-- ## MENU ## -->', '', $page->body);
  file_put_contents($resultsDir.'/'.$page->filename, $page->body);
}

$articleData = str_replace('<!-- ## TITLE ## -->', 'Everything is My Fault', $templateContents);
$contents = '<div id="articles">'."\n";

foreach ($articles as $article) {
  $contents .= "\t".'<div class="article">'."\n\t\t".'<a href="articles/'.$article->filename.'.html">';
  $contents .= '<img src="articles/article_imgs/'.$article->filename.'-thmb.'.$article->imageType.'" alt="'.$article->title.'" /></a>'."\n\t\t";
  $contents .= '<h2><a href="articles/'.$article->filename.'.html">'.$article->title.'</a></h2>'."\n\t";
  $contents .= '</div>'."\n";
}

$contents .= '</div>';
$articleData = str_replace('<!-- ## CONTENT ## -->', $contents, $articleData);
$articleData = str_replace('<!-- ## YEAR ## -->', $current_year, $articleData);
//$articleData = str_replace('<!-- ## MENU ## -->', $mainMenu, $articleData);
$articleData = str_replace('<!-- ## MENU ## -->', '', $articleData);
file_put_contents($resultsDir.'/index.html', $articleData);

/* Retrieve article template */
$templateContents = @file_get_contents($article_template);
if (!$templateContents) {
  exit('Failed to retrieve template file "'.$article_template."\"\n");
}

$rss = '<?xml version="1.0" encoding="UTF-8" ?>'."\n".'<rss version="2.0">'."\n\n".'<channel>'."\n";
$rss .= '  <title>Everything is My Fault</title>'."\n".'  <link>http://www.everythingismyfault.com/</link>'."\n";
$rss .= '  <description>A developer\'s Dumpbin.</description>'."\n".'  <language>en-us</language>'."\n".'  ';
$rss .= '<lastBuildDate>'.date('D, d M Y G:i:s T', time()).'</lastBuildDate>'."\n";

/* Articles are properly sorted at this point */
foreach ($articles as $article) {
  if (is_array($article->body)) {
    $numSubArticles = count($article->body);
    
    $partNavigation = '<p><h4>This article is separated into the following parts:</h4>'."\n\n".'<ul>'."\n";
    
    for ($i = 0; $i < $numSubArticles; ++$i) {
      $partNavigation .= '<li><a href="./'.$article->body[$i]->filename.'.html">Part '.($i + 1).'</a>: '.$article->body[$i]->title.'</li>'."\n";
    }
    $partNavigation .= '</ul>'."\n";
  } else {
    $numSubArticles = 1;
    $a = &$article;
  }
  
  for ($i = 0; $i < $numSubArticles; ++$i) {
    if ($numSubArticles > 1) {
      $a = &$article->body[$i];
    }
    
    $articleData = str_replace('<!-- ## TITLE ## -->', $a->title.' | EiMF', $templateContents);
    $articleData = str_replace('<!-- ## CONTENT ## -->', (($numSubArticles > 1) ? $a->body.$partNavigation : $a->body), $articleData);
    $articleData = str_replace('<!-- ## YEAR ## -->', $current_year, $articleData);
    //$articleData = str_replace('<!-- ## MENU ## -->', $subMenu, $articleData);
    $articleData = str_replace('<!-- ## MENU ## -->', '', $articleData);
    
    file_put_contents($resultsDir.'/articles/'.$a->filename.'.html', $articleData);
  }
  
  if ($article->newImage) {
    @copy($article->image, $resultsDir.'/articles/'.$article->newImage);
  }
  
  $rss .= '  <item>'."\n".'    <title>'.$article->title.'</title>'."\n".'    ';
  $rss .= '<link>http://www.everythingismyfault.com/articles/'.$article->filename.'.html</link>'."\n    ";
  $rss .= '<description>'.$article->description.'</description>'."\n    ";
  $rss .= '<pubDate>'.date('D, d M Y G:i:s e', $article->date).'</pubDate>'."\n".'  </item>'."\n";
}

$rss .= '  </channel>'."\n".'</rss>';
file_put_contents($resultsDir.'/eimf.rss', $rss);

$scriptEnd = (float)((microtime(TRUE) - $scriptStart) / ((float)1000));
echo('Generated '.count($articles).' articles and '.count($pages).' pages in '.number_format($scriptEnd, 6, '.', '').' seconds'."\n");

?>
